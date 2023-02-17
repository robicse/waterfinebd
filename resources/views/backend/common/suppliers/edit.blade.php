@extends('backend.layouts.master')
@section('title', 'Suppliers Edit')
@push('css')
    <link rel="stylesheet" href="{{ asset('backend/css/custom.css') }}">
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
                        <li class="breadcrumb-item active">edit</li>
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
                            <h3 class="card-title">Suppliers Edit</h3>
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
                                {!! Form::model($supplier, array('route' =>[Request::segment(1).'.suppliers.update', $supplier->id],'method'=>'PUT','files'=>true)) !!}
                                <input type="hidden" value="Supplier" name="user_type">
                                <input type="hidden" value="Non Payable" name="pay_type">
                                {{Form::hidden('warehouse_id', $supplier->warehouse_id,['class' => 'form-control']) }}
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="warehouse_id">Warehouse Id <span class="required">*</span></label>
                                        {!! Form::select('warehouse_id', $warehouses, $supplier->warehouse_id, [
                                            'id' => 'warehouse_id',
                                            'class' => 'form-control',
                                            'placeholder' => 'Select One',
                                            'disabled'
                                        ]) !!}
                                    </div>

                                    {{-- <div class="form-group col-md-4">
                                        <label for="route_id">Route Id <span class="required">(Optional)</span></label>
                                        <select class="form-control custom-select" name="route_id" id="route_id">
                                            
                                        </select>
                                    </div> --}}
                                    <div class="form-group col-md-4">
                                        <label for="name">Name <span class="required">*</span></label>
                                        {!! Form::text('name', $supplier->name, [
                                            'id' => 'name',
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => 'Enter Name',
                                            'autofocus',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="arabic_name">Supplier Arabic Name <span class="required">*</span></label>
                                        {!! Form::text('arabic_name', $supplier->arabic_name, [
                                            'id' => 'arabic_name',
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => 'Enter Arabic Name',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="vat_no">VAT NO <span class="required">*</span></label>
                                        {!! Form::text('vat_no', $supplier->supplier->vat_no, [
                                            'id' => 'vat_no',
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => 'Enter vat no',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="supplier_location">Supplier Location <span class="required">*</span></label>
                                        {!! Form::text('supplier_location', $supplier->supplier->supplier_location, [
                                            'id' => 'supplier_location',
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => 'Enter location',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="comercial_registration_no">Commercial Registration No <span
                                                class="required">*</span></label>
                                         {!! Form::text('comercial_registration_no', $supplier->supplier->comercial_registration_no, [
                                            'id' => 'comercial_registration_no',
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => 'Enter vat no',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="inputStatus">Supplier Type <span class="required">*</span> </label>
                                        {!! Form::select('type', ['Local' => 'Local', 'International' => 'International'], $supplier->supplier->type, ['id' => 'type', 'class' => 'form-control', 'required','placeholder' => 'Select One']) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="phone">Phone <span class="required">*</span></label>
                                        <div class="row">
                                            <input type="text" class="form-control col-md-3" name="country_code"
                                                value="{{ $supplier->country_code }}" readonly>
                                            <input type="number" class="form-control col-md-8" name="phone"
                                                value="{{ $supplier->phone }}" required>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="email">Email <span class="required">*</span></label>
                                        {!! Form::email('email', $supplier->email, [
                                            'id' => 'email',
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => 'Enter email',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="address">Address <span class="required">(Optional)</span></label>
                                        {!! Form::text('address', $supplier->address, [
                                            'id' => 'address',
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter address',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="credit_limit">Credit Limit <span class="required">*</span></label>
                                        {!! Form::number('credit_limit', $supplier->supplier->credit_limit, [
                                            'id' => 'credit_limit',
                                            'class' => 'form-control',
                                            'step'=> 'any',
                                            'min'=>0,
                                            'max'=>9999999999999999,
                                            'required',
                                            'placeholder' => 'Enter credit limit',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="days_limit">Days Limit <span class="required">*</span></label>
                                        {!! Form::number('days_limit', $supplier->supplier->days_limit, [
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
                                        {!! Form::select('status', [1 => 'Active', 0 => 'In-Active'], $supplier->status, ['id' => 'status', 'class' => 'form-control', 'required','placeholder' => 'Select One']) !!}
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="payment_terms">Payment Terms <span class="required">*</span></label>
                                       
                                         {{ Form::textarea('payment_terms', $supplier->supplier->payment_terms, array('class' =>'form-control', 'rows' =>5, 'required'))}}
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="bank_accounts_details">Bank Account Details</label>
                                       
                                        {{ Form::textarea('bank_accounts_details', $supplier->supplier->bank_accounts_details, array('class' =>'form-control', 'rows' =>5,))}}
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="product_groups">Product Groups <span
                                                class="required">(Optional)</span> </label>
                                        
                                         {{ Form::textarea('product_groups', $supplier->supplier->product_groups, array('class' =>'form-control', 'rows' =>5,))}}
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            {!! Form::close()!!}
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
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.9/js/utils.js"></script> --}}

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
        $(document).ready(function() {
            var supplier_type = $('#supplier_type').val();
            //alert(supplier_type)
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
        });

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

        /* $.ajax({
            url: "{{ URL(Request::segment(1) . '/get-route-warehouse-edit') }}",
            method: 'GET',
            data: {
                warehouse_id: $('#warehouse_id').val()
            },
            success: function(data) {
                //$('#route_id').html(data.data.routeOptions)
                var id = "{{ @$supplier->route_id }}";
                //console.log(id);
                $('#route_id').append('<option value=""> Select One </option>');
                $.each(data, function(key, value) {
                    if (value.id == id) {
                        $('#route_id').append('<option value="' + value.id + '" selected>' + value
                            .name + '</option>');
                    } else {
                        //console.log(value);
                        $('#route_id').append('<option value="' + value.id + '">' + value.name +
                            '</option>');
                    }
                });
            },
            error: function(err) {
                console.log(err);
            }
        });

        $('#warehouse_id').change(function() {
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
    </script>
@endpush
