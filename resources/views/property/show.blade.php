@extends('layouts.app')
@section('page-title')
    {{ __('Property Details') }}
@endsection
@section('page-class')
    product-detail-page
@endsection
@push('script-page')
<script>
    $(document).ready(function() {
        $('#uploadExcelForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            
            $.ajax({
                url: "{{ route('property.upload.tenant.excel', $property->id) }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        toastrs('success', response.msg, 'success');
                        loadExcelUploads();
                    } else {
                        toastrs('error', response.msg, 'error');
                    }
                },
                error: function(xhr) {
                    toastrs('error', xhr.responseJSON.msg || 'Error uploading file', 'error');
                }
            });
        });

        function loadExcelUploads() {
            $.ajax({
                url: "{{ route('property.tenant.excel.uploads', $property->id) }}",
                type: 'GET',
                success: function(response) {
                    if (response.status === 'success') {
                        var html = '';
                        response.uploads.forEach(function(upload) {
                            html += '<tr>';
                            html += '<td>' + upload.original_name + '</td>';
                            html += '<td>' + upload.status + '</td>';
                            html += '<td>' + upload.created_at + '</td>';
                            if (upload.error_log) {
                                html += '<td><span class="text-danger">' + upload.error_log + '</span></td>';
                            } else {
                                html += '<td>-</td>';
                            }
                            html += '</tr>';
                        });
                        $('#excelUploadsTable tbody').html(html);
                    }
                }
            });
        }

        // Load uploads on page load
        loadExcelUploads();
    });
</script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">
        <a href="{{ route('property.index') }}">{{ __('Property') }}</a>
    </li>
    <li class="breadcrumb-item active">
        <a href="#">{{ __('Details') }}</a>
    </li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">

                        </div>
                        @can('create property')
                            <div class="col-auto">
                                <a class="btn btn-secondary customModal" data-size="lg" href="#"  data-url="{{ route('unit.create', $property->id) }}" data-title="{{ __('Add Unit') }}"> <i
                                        class="ti ti-circle-plus align-text-bottom " ></i>
                                    {{ __('Add Unit') }}</a>

                                {{-- <a href="#" class="btn btn-secondary btn-sm customModal" data-title="{{ __('Add Unit') }}"
                                    data-url="{{ route('unit.create', $property->id) }}" data-size="lg"> <i
                                        class="ti-plus mr-5"></i>{{ __('Add Unit') }}</a> --}}
                            </div>
                        @endcan
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="row property-page mt-3">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <ul class="nav nav-tabs profile-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="profile-tab-1" data-bs-toggle="tab" href="#profile-1"
                                role="tab" aria-selected="true">
                                <i class="material-icons-two-tone me-2">meeting_room</i>
                                {{ __('Property Details') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab-2" data-bs-toggle="tab" href="#profile-2" role="tab"
                                aria-selected="true">
                                <i class="material-icons-two-tone me-2">ad_units</i>
                                {{ __('Property Units') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab-3" data-bs-toggle="tab" href="#profile-3" role="tab" aria-selected="true">
                                <i class="material-icons-two-tone me-2">upload_file</i>
                                {{ __('Tenant Excel Upload') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane show active" id="profile-1" role="tabpanel" aria-labelledby="profile-tab-1">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="row justify-content-center">
                                        <div class="col-xl-12 col-xxl-12">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-5">
                                                            <div class="sticky-md-top product-sticky">
                                                                <div id="carouselExampleCaptions"
                                                                    class="carousel slide carousel-fade"
                                                                    data-bs-ride="carousel">
                                                                    <div class="carousel-inner">
                                                                        @foreach ($property->propertyImages as $key => $image)
                                                                            @php
                                                                                $img = !empty($image->image)
                                                                                    ? $image->image
                                                                                    : 'default.jpg';
                                                                            @endphp
                                                                            <div
                                                                                class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                                                                                <img src="{{ asset(Storage::url('upload/property') . '/' . $img) }}"
                                                                                    class="d-block w-100 rounded"
                                                                                    alt="Product image" />
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                    <ol
                                                                        class="carousel-indicators position-relative product-carousel-indicators my-sm-3 mx-0">
                                                                        @foreach ($property->propertyImages as $key => $image)
                                                                            @php
                                                                                $img = !empty($image->image)
                                                                                    ? $image->image
                                                                                    : 'default.jpg';
                                                                            @endphp
                                                                            <li data-bs-target="#carouselExampleCaptions"
                                                                                data-bs-slide-to="{{ $key }}"
                                                                                class="{{ $key === 0 ? 'active' : '' }} w-25 h-auto">
                                                                                <img src="{{ asset(Storage::url('upload/property') . '/' . $img) }}"
                                                                                    class="d-block wid-50 rounded"
                                                                                    alt="Product image" />
                                                                            </li>
                                                                        @endforeach
                                                                    </ol>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-7">

                                                            <h3 class="">
                                                                {{ ucfirst($property->name) }}

                                                            </h3>
                                                            <span class="badge bg-light-primary f-14 mt-1"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('Type') }}">{{ \App\Models\Property::$Type[$property->type] }}</span>
                                                            <h5 class="mt-4">{{ __('Property Details') }}</h5>
                                                            <hr class="my-3" />
                                                            <p class="text-muted">
                                                                {{ $property->description }}
                                                            </p>

                                                            <h5>{{ __('Property Address') }}</h5>
                                                            <hr class="my-3" />
                                                            <div class="mb-1 row">
                                                                <label
                                                                    class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                    {{ __('Woreda') }} :

                                                                </label>
                                                                <div
                                                                    class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                    {{ $property->woreda }}
                                                                </div>
                                                            </div>
                                                            <div class="mb-1 row">
                                                                <label
                                                                    class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                    {{ __('Sub-city') }} :

                                                                </label>
                                                                <div
                                                                    class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                    {{ $property->sub_city }}
                                                                </div>
                                                            </div>
                                                            <div class="mb-1 row">
                                                                <label
                                                                    class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                    {{ __('House Number') }} :

                                                                </label>
                                                                <div
                                                                    class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                    {{ $property->house_number }}
                                                                </div>
                                                            </div>
                                                            <div class="mb-1 row">
                                                                <label
                                                                    class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                    {{ __('Location') }} :

                                                                </label>
                                                                <div
                                                                    class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                    {{ $property->location }}
                                                                </div>
                                                            </div>
                                                            <div class="mb-1 row">
                                                                <label
                                                                    class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                    {{ __('City') }} :

                                                                </label>
                                                                <div
                                                                    class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                    {{ $property->city }}
                                                                </div>
                                                            </div>

                                                            <hr class="my-3" />

                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane " id="profile-2" role="tabpanel" aria-labelledby="profile-tab-2">
                            <div class="row">
                                @foreach ($units as $unit)
                                    <div class="col-xxl-3 col-xl-4 col-md-6">
                                        <div class="card follower-card">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-start mb-3">
                                                    <div class="flex-grow-1 ">
                                                        <h2 class="mb-1 text-truncate">{{ ucfirst($unit->name) }}</h2>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <div class="dropdown">
                                                            <a class="dropdown-toggle text-primary opacity-50 arrow-none"
                                                                href="#" data-bs-toggle="dropdown"
                                                                aria-haspopup="true" aria-expanded="false">
                                                                <i class="ti ti-dots f-16"></i>
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-end">

                                                                @can('edit unit')
                                                                    <a class="dropdown-item customModal" href="#"
                                                                        data-url="{{ route('unit.edit', [$property->id, $unit->id]) }}"
                                                                        data-title="{{ __('Edit Unit') }}" data-size="lg">
                                                                        <i
                                                                            class="material-icons-two-tone">edit</i>{{ __('Edit Unit') }}</a>
                                                                @endcan

                                                                @can('delete unit')
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['unit.destroy', $property->id, $unit->id],
                                                                        'id' => 'unit-' . $unit->id,
                                                                    ]) !!}

                                                                    <a class="dropdown-item confirm_dialog" href="#">
                                                                        <i class="material-icons-two-tone">delete</i>
                                                                        {{ __('Delete Unit') }}

                                                                    </a>
                                                                    {!! Form::close() !!}
                                                                @endcan
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr class="my-3" />


                                                <div class="row">
                                                    <p class="mb-1">{{ __('Bedroom') }} :
                                                        <span class="text-muted">{{ $unit->bedroom }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Bath') }} :
                                                        <span class="text-muted">{{ $unit->baths }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Rent Type') }} :
                                                        <span class="text-muted">{{ $unit->rent_type }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Rent') }} :
                                                        <span class="text-muted">{{ priceFormat($unit->rent) }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Rent Start Date') }} :
                                                        <span class="text-muted">{{ $unit->start_date ? dateFormat($unit->start_date) : '-' }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Rent End Date') }} :
                                                        <span class="text-muted">{{ $unit->end_date ? dateFormat($unit->end_date) : '-' }}</span>
                                                    </p>
                                                </div>

                                                <hr class="my-2" />
                                                <p class="my-3 text-muted text-sm">
                                                    {{ $unit->notes }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="tab-pane" id="profile-3" role="tabpanel" aria-labelledby="profile-tab-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>{{ __('Upload Tenant Excel') }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <form id="uploadExcelForm" enctype="multipart/form-data">
                                                @csrf
                                                <div class="form-group">
                                                    <label for="excel_file">{{ __('Excel File') }}</label>
                                                    <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                                                    <small class="form-text text-muted">
                                                        {{ __('Supported formats: XLSX, XLS, CSV. Maximum file size: 2MB') }}
                                                    </small>
                                                </div>
                                                <div class="form-group mt-3">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="material-icons-two-tone me-2">upload</i>
                                                        {{ __('Upload') }}
                                                    </button>
                                                    <a href="{{ route('property.tenant.excel.template') }}" class="btn btn-secondary">
                                                        <i class="material-icons-two-tone me-2">download</i>
                                                        {{ __('Download Template') }}
                                                    </a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>{{ __('Upload History') }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table" id="excelUploadsTable">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('File Name') }}</th>
                                                            <th>{{ __('Status') }}</th>
                                                            <th>{{ __('Date') }}</th>
                                                            <th>{{ __('Error') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
