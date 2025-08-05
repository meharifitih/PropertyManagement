<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Tenant;
use App\Models\PropertyUnit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Notifications\PasswordChangeNotification;
use Illuminate\Support\Str;
use App\Traits\PhoneNumberFormatter;

class UnifiedSingleSheetImport implements ToModel, WithHeadingRow
{
    use PhoneNumberFormatter;

    protected $propertyId;
    protected $importedCount = 0;
    protected $errorCount = 0;
    protected $errors = [];

    public function __construct($propertyId)
    {
        $this->propertyId = $propertyId;
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function model(array $row)
    {
        try {
            // Log the row data for debugging
            \Log::info('Processing row: ' . json_encode($row));
            
            // Skip empty rows
            if (empty($row['unit_name']) && empty($row['first_name'])) {
                \Log::info('Skipping empty row');
                return null;
            }

            // Create unit first
            $unit = null;
            if (!empty($row['unit_name'])) {
                \Log::info('Creating unit: ' . $row['unit_name']);
                
                // Check if unit already exists
                $existingUnit = PropertyUnit::where('property_id', $this->propertyId)
                    ->where('name', $row['unit_name'])
                    ->first();
                
                if ($existingUnit) {
                    throw new \Exception("Unit '{$row['unit_name']}' already exists in this property");
                }
                
                $unit = new PropertyUnit([
                    'property_id' => $this->propertyId,
                    'name' => $row['unit_name'] ?? '',
                    'bedroom' => $row['bedroom'] ?? 0,
                    'kitchen' => $row['kitchen'] ?? 0,
                    'baths' => $row['baths'] ?? 0,
                    'rent' => $row['rent'] ?? 0,
                    'rent_type' => $row['rent_type'] ?? 'monthly',
                    'deposit_type' => $row['deposit_type'] ?? 'fixed',
                    'deposit_amount' => $row['deposit_amount'] ?? 0,
                    'late_fee_type' => $row['late_fee_type'] ?? 'fixed',
                    'late_fee_amount' => $row['late_fee_amount'] ?? 0,
                    'incident_receipt_amount' => $row['incident_receipt_amount'] ?? 0,
                    'notes' => $row['unit_notes'] ?? '',
                    'parent_id' => parentId(),
                ]);
                $unit->save();
                \Log::info('Unit created with ID: ' . $unit->id);
                $this->importedCount++;
            }

            // Create tenant if tenant data is provided
            if (!empty($row['first_name']) && !empty($row['email'])) {
                \Log::info('Creating tenant: ' . $row['first_name'] . ' ' . $row['last_name']);
                // Validate required fields
                $requiredFields = ['first_name', 'last_name', 'email', 'phone_number'];
                foreach ($requiredFields as $field) {
                    if (empty($row[$field])) {
                        throw new \Exception("Missing required field: {$field}");
                    }
                }

                // Validate email format
                if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception("Invalid email format: {$row['email']}");
                }

                // Check if email already exists
                if (User::where('email', $row['email'])->exists()) {
                    throw new \Exception("Email already exists: {$row['email']}");
                }

                // Normalize phone number first
                $normalizedPhone = $this->formatPhoneNumber($row['phone_number']);

                // Validate phone number format after normalization
                if (!$this->isValidPhoneNumber($normalizedPhone)) {
                    throw new \Exception("Invalid phone number format: {$row['phone_number']}. Phone number must be in the format +251XXXXXXXXX (e.g. +251912345678). Accepted: 9XXXXXXXX, 251XXXXXXXXX, +251XXXXXXXXX");
                }

                // Find or create user role
                $userRole = Role::where('name', 'tenant')->first();
                if (!$userRole) {
                    throw new \Exception('Tenant role not found');
                }

                // Generate a random password
                $password = Str::random(12);

                // Create user
                try {
                    $user = new User();
                    $user->first_name = $row['first_name'];
                    $user->last_name = $row['last_name'];
                    $user->email = $row['email'];
                    $user->phone_number = $normalizedPhone;
                    $user->password = Hash::make($password);
                    $user->type = $userRole->name;
                    $user->email_verified_at = now();
                    $user->profile = 'avatar.png';
                    $user->lang = 'english';
                    $user->parent_id = parentId();
                    $user->save();

                    // Assign tenant role
                    $user->assignRole($userRole);

                    // Send password notification
                    $user->notify(new PasswordChangeNotification($password));
                } catch (\Exception $e) {
                    \Log::error('Error creating user in import: ' . $e->getMessage());
                    
                    // Check for specific database errors
                    if (strpos($e->getMessage(), 'SQLSTATE[23505]') !== false) {
                        if (strpos($e->getMessage(), 'users_email_unique') !== false) {
                            throw new \Exception("Email '{$row['email']}' is already registered. Please use a different email.");
                        } elseif (strpos($e->getMessage(), 'users_phone_number_unique') !== false) {
                            throw new \Exception("Phone number '{$normalizedPhone}' is already registered. Please use a different phone number.");
                        }
                    }
                    
                    throw new \Exception("Failed to create user: " . $e->getMessage());
                }

                // Parse lease dates
                $leaseStartDate = null;
                $leaseEndDate = null;
                if (!empty($row['lease_start_date'])) {
                    $leaseStartDate = $this->parseExcelDate($row['lease_start_date']);
                }
                if (!empty($row['lease_end_date'])) {
                    $leaseEndDate = $this->parseExcelDate($row['lease_end_date']);
                }

                // Create tenant
                $tenant = new Tenant();
                $tenant->user_id = $user->id;
                $tenant->family_member = $row['family_member'] ?? 0;
                $tenant->sub_city = $row['sub_city'] ?? '';
                $tenant->woreda = $row['woreda'] ?? '';
                $tenant->house_number = $row['house_number'] ?? '';
                $tenant->location = $row['location'] ?? '';
                $tenant->city = $row['city'] ?? '';
                $tenant->property = $this->propertyId ?? 0;
                $tenant->unit = $unit ? $unit->id : 0;
                $tenant->lease_start_date = $leaseStartDate;
                $tenant->lease_end_date = $leaseEndDate;
                $tenant->parent_id = parentId();
                $tenant->save();
                
                \Log::info('Tenant created with ID: ' . $tenant->id);
                $this->importedCount++;

                return $tenant;
            }

            // Return unit if no tenant data
            if ($unit) {
                \Log::info('Returning unit only: ' . $unit->id);
            }
            return $unit;

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->errors[] = "Row " . ($this->importedCount + $this->errorCount) . ": " . $e->getMessage();
            \Log::error('Import error in row: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function parseExcelDate($date)
    {
        // Try d/m/Y
        $d = \DateTime::createFromFormat('d/m/Y', $date);
        if ($d !== false) return $d->format('Y-m-d');
        // Try Y-m-d
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        if ($d !== false) return $d->format('Y-m-d');
        // Try m/d/Y (US Excel)
        $d = \DateTime::createFromFormat('m/d/Y', $date);
        if ($d !== false) return $d->format('Y-m-d');
        // Try Excel serial number
        if (is_numeric($date)) {
            $unixDate = ($date - 25569) * 86400;
            return gmdate('Y-m-d', $unixDate);
        }
        return null;
    }
} 