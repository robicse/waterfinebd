@extends('backend.layouts.master')
@section('title', 'Suppliers Create')
@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.9/css/intlTelInput.css" rel="stylesheet"
        media="screen">
    <link rel="stylesheet" href="{{ asset('backend/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/css/phone-prefix.css') }}">
@endpush
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Suppliers</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active"><a href="{{ route(Request::segment(1) . '.dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active">suppliers</li>
                        <li class="breadcrumb-item active">create</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Suppliers Create</h3>
                            <div class="float-right">
                                <a href="{{ route(Request::segment(1) . '.suppliers.index') }}">
                                    <button class="btn btn-success">
                                        <i class="fa fa-plus-circle"></i>
                                        Back
                                    </button>
                                </a>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {!! Form::open([
                                'route' => Request::segment(1) . '.suppliers.store',
                                'method' => 'POST',
                                'files' => true,
                            ]) !!}
                            @csrf
                            <input type="hidden" value="Supplier" name="user_type">
                            <input type="hidden" value="Non Payable" name="pay_type">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="warehouse_id">Warehouse Id <span class="required">*</span></label>

                                    {!! Form::select('warehouse_id', $warehouses, @Auth::user()->warehouse_id ?: null, [
                                        'id' => 'warehouse_id',
                                        'class' => 'form-control',
                                        'placeholder' => 'Select One',
                                        'required',
                                        'autofocus',
                                    ]) !!}
                                </div>

                                {{-- <div class="form-group col-md-4">
                                    <label for="route_id">Route Id <span class="required">(Optional)</span></label>
                                    <button type="button" class="btn-primary btn-sm" onclick="showRouteForm()">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                    <select class="form-control custom-select" name="route_id" id="route_id">

                                    </select>
                                </div> --}}
                                <div class="form-group col-md-4">
                                    <label for="name">Supplier Name <span class="required">*</span></label>
                                    {!! Form::text('name', null, [
                                        'id' => 'name',
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => 'Enter Name',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="arabic_name">Supplier Arabic Name <span class="required">*</span></label>
                                    {!! Form::text('arabic_name', null, [
                                        'id' => 'arabic_name',
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => 'Enter Arabic Name',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <div>
                                        <label for="phone">Phone<span class="required">*</span></label>
                                    </div>
                                    <input id="phone1" type="tel" class="phone_val" name="phone"
                                        placeholder="@lang('website.phone')" required>
                                    <input id="countyCodePrefix1" type="hidden" name="country_code" required>
                                    <span id="valid-msg1" class="hide text-success">Valid</span>
                                    <span id="error-msg1" class="hide text-danger">Invalid number</span>
                                    <span id="error-msg2" class="hide text-danger">This Phone number already Exists </span>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="email">Email <span class="required">*</span></label>
                                    {!! Form::email('email', null, [
                                        'id' => 'email',
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => 'Enter email',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="vat_no">Vat No <span class="required">*</span></label>

                                    {!! Form::text('vat_no', null, [
                                        'id' => 'vat_no',
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => 'Enter vat no',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="comercial_registration_no">Commercial Registration No <span
                                            class="required">*</span></label>

                                    {!! Form::text('comercial_registration_no', null, [
                                        'id' => 'comercial_registration_no',
                                        'class' => 'form-control',
                                        'required',
                                        
                                        'placeholder' => 'Enter vat no',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="supplier_location">Supplier Location <span class="required">*</span></label>

                                    {!! Form::text('supplier_location', null, [
                                        'id' => 'supplier_location',
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => 'Enter location',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="address">Address <span class="required">(Optional)</span></label>
                                    {!! Form::text('address', null, [
                                        'id' => 'address',
                                        'class' => 'form-control',
                                        'placeholder' => 'Enter address',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="inputStatus">Supplier Type <span class="required">*</span></label>

                                    {!! Form::select('type', ['Local' => 'Local', 'International' => 'International'], null, [
                                        'id' => 'type',
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => 'Select One',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="credit_limit">Credit Limit <span class="required">*</span></label>
                                    {!! Form::number('credit_limit', 0, [
                                        'id' => 'credit_limit',
                                        'class' => 'form-control',
                                        'step' => 'any',
                                        'min'=>0,
                                         'max'=>9999999999999999,
                                        'required',
                                        'placeholder' => 'Enter credit limit',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="days_limit">Days Limit <span class="required">*</span></label>
                                    {!! Form::number('days_limit', 0, [
                                        'id' => 'days_limit',
                                        'class' => 'form-control',
                                        'required',
                                        'min'=>0,
                                         'max'=>9999999999999999,
                                        'placeholder' => 'Enter days limit',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="inputStatus">Status <span class="required">*</span></label>
                                    {!! Form::select('status', [1 => 'Active', 0 => 'In-Active'], 1, [
                                        'id' => 'status',
                                        'class' => 'form-control',
                                        'required',
                                        'min'=>0,
                                         'max'=>9999999999999999,
                                        'placeholder' => 'Select One',
                                    ]) !!}
                                </div>


                                <div class="form-group col-md-4">
                                    <label for="previous_balance">Previous Balance<span class="required">*</span></label>
                                    {!! Form::number('previous_balance', 0, [
                                        'id' => 'previous_balance',
                                        'class' => 'form-control',
                                        'step' => 'any',
                                        'min'=>0,
                                         'max'=>9999999999999999,
                                        'required',
                                        'placeholder' => 'Enter previous value',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-12">
                                    <label for="payment_terms">Payment Terms <span class="required">*</span></label>
                                    {{ Form::textarea('payment_terms', null, ['class' => 'form-control', 'rows' => 5, 'required']) }}
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="bank_accounts_details">Bank Account Details</label>
                                    {{ Form::textarea('bank_accounts_details', null, ['class' => 'form-control', 'rows' => 5,]) }}
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="product_groups">Product Groups <span class="required">(Optional)</span>
                                    </label>
                                    {{ Form::textarea('product_groups', null, ['class' => 'form-control', 'rows' => 5,]) }}
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" id="SUBMIT_BTN" class="btn btn-primary">Submit</button>
                            </div>
                            {!! Form::close() !!}
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
@stop


@push('js')
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.9/js/intlTelInput.min.js"></script> --}}
    {{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.9/js/utils.js"></script> --}}

    <script>
        // $(document).ready(function() {
        //     $('.demo-select2').select2();
        // });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.9/js/intlTelInput.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.9/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.9/js/utils.js"></script>
    {{-- this section for custom js, only for this page --}}
    <script>
        //For register
        function showRouteForm() {

            var page = "{{ url(Request::segment(1) . '/routes/create') }}";
            var myWindow = window.open(page, "_blank", "scrollbars=yes,width=700,height=1000,top=30");
            // focus on the popup //
            myWindow.focus();
        }
        var telInput1 = $("#phone1"),
            errorMsg1 = $("#error-msg1"),
            errorMsg2 = $("#error-msg2"),
            validMsg1 = $("#valid-msg1");

        telInput1.intlTelInput({

            allowExtensions: true,
            formatOnDisplay: true,
            autoFormat: true,
            autoHideDialCode: true,
            autoPlaceholder: true,
            defaultCountry: "auto",
            ipinfoToken: "yolo",

            nationalMode: false,
            numberType: "MOBILE",

            preferredCountries: ['bn', 'sa', 'ae', 'qa', 'om', 'bh', 'kw', 'ma'],
            preventInvalidNumbers: true,
            separateDialCode: true,
            initialCountry: "auto",

            geoIpLookup: function(callback) {
                $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                    //console.log(resp);
                    var countryCode = (resp && resp.country) ? resp.country : "";

                    callback(countryCode);
                });
            },
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.9/js/utils.js"

        });

        var reset = function() {
            telInput1.removeClass("error");
            errorMsg1.addClass("hide");
            validMsg1.addClass("hide");
        };

        telInput1.blur(function() {
            var countyCodePrefix1 = $(".flag-container").find('.selected-dial-code').html();
            console.log(countyCodePrefix1)
            if (countyCodePrefix1 != '+880') {
                $("#division_area").hide();
                $("#district_area").hide();
            } else {
                $("#division_area").show();
                $("#district_area").show();
            }
            const string = $('.phone_val').val();
            let info = (string.slice(0, 1));
            if (info == 0) {
                $('.phone_val').val(string.slice(1));
            }
            var phone_val = $('.phone_val').val();
            console.log(phone_val);
            $.post('{{ route('super_admin.check.phone') }}', {
                _token: '{{ csrf_token() }}',
                phone_val: phone_val
            }, function(data) {
                // console.log(data);
                if (data == 1) {
                    toastr.warning('This phone number already exist!');
                }
            });

            $("#countyCodePrefix1").val(countyCodePrefix1);

            reset();
            if ($.trim(telInput1.val())) {
                if (telInput1.intlTelInput("isValidNumber")) {
                    validMsg1.removeClass("hide");
                } else {
                    telInput1.addClass("error");
                    errorMsg1.removeClass("hide");
                }
            }
        });

        telInput1.on("keyup change", reset);

        $('#supplier_type').change(function() {
            var supplier_type = $(this).val();
            // console.log(supplier_type)
            if (supplier_type === "Local") {
                $("#payment_type_id option[value=3]").hide();
                $("#payment_type_id option[value=4]").show();
                $("#payment_type_id option[value=1]").show();
            }
            if (supplier_type === "International") {
                $("#payment_type_id option[value=1]").hide();
                $("#payment_type_id option[value=3]").show();
                $("#payment_type_id option[value=4]").show();
            }
        })

        /* $('#warehouse_id').change(function() {
            //console.log('kkk')
            $('#route_id').html('')
            var warehouse_id = $(this).val();
            $.ajax({
                url: "{{ URL(Request::segment(1) . '/get-route-warehouse') }}",
                method: 'GET',
                data: {
                    warehouse_id: warehouse_id
                },
                success: function(data) {
                    //console.log(data);
                    $('#route_id').append('<option value=""> Select One </option>');
                    $.each(data, function(key, value) {
                        $('#route_id').append('<option value="' + value.id + '">' + value.name +
                            '</option>');
                    });
                },
                error: function(err) {
                    console.log(err);
                }
            })

        }); */

        var loggedIn = @json(Auth::user()->user_type);
        if (loggedIn !== 'Super Admin') {
            $.ajax({
                url: "{{ URL(Request::segment(1) . '/get-route-warehouse') }}",
                method: 'GET',
                data: {
                    warehouse_id: $('#warehouse_id').val()
                },
                success: function(data) {
                    //console.log(data);
                    $('#route_id').append('<option value=""> Select One </option>');
                    $.each(data, function(key, value) {
                        $('#route_id').append('<option value="' + value.id + '">' + value.name +
                            '</option>');
                    });
                },
                error: function(err) {
                    console.log(err);
                }
            })
        };
    </script>
@endpush
