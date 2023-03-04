@extends('backend.layouts.master')
@section('title', 'Customer Ledger Lists')
@push('css')

    {{-- <link rel="stylesheet" href="{{ asset('backend/datetimepicker/css/bootstrap-datetimepicker.min.css') }}"> --}}

    <link rel="stylesheet" href="{{ asset('backend/css/custom.css') }}">
@endpush
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Customer</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route(Request::segment(1) . '.dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Customer Ledger</li>
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
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            {!! Form::open(['url' => Request::segment(1) . '/customer-ledgers']) !!}
                            <div class="row justify-content-center">
                                <div class="col-2">
                                    <div class="form-group">
                                        <label>Select Warehouse:</label>
                                        {!! Form::select('warehouse_id', $warehouses, @Auth::user()->warehouse_id?:null, [
                                            'class' => 'form-control',
                                            'placeholder' => 'Select One',
                                            'id' => 'warehouse_id',
                                            'required',
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group">
                                        <label>Select Customer:</label>
                                        <select class="form-control" name="customer_user_id" id="customer_user_id">
                                            <option>Select One</option>
                                            @if(@Auth::user()->warehouse_id)
                                                @if(count($customers))
                                                    @foreach($customers as $customer)
                                                        <option value="{{$customer->id}}">{{$customer->name}}</option>
                                                    @endforeach
                                                @endif
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group">
                                        <label>Start Date:</label>
                                        {!! Form::date('start_date', null, ['class' => 'form-control', 'id' => 'myDatepicker', 'required']) !!}
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group">
                                        <label>End Date:</label>
                                        {!! Form::date('end_date', date('Y-m-d'), ['class' => 'form-control', 'id' => 'myDatepicker1', 'required']) !!}
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group ">
                                        <br>
                                        <button class="btn btn-primary  mt-2">Submit</button>
                                    </div>
                                </div>
                                <div class="col-2">&nbsp;</div>
                            </div>
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

    <!-- DataTables  & Plugins -->
    {{-- <script src="{{ asset('backend/datetimepicker/js/bootstrap-datetimepicker.min.js') }}"></script> --}}

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });

        $('#warehouse_id').change(function () {
            var warehouse_id = $(this).val();
            $.ajax({
                url: "{{ url(Request::segment(1)) }}" +'/get-warehouse-customer',
                method: 'POST',
                data: {
                    warehouse_id: warehouse_id
                },
                success: function (res) {
                    console.log(res);
                    if (res !== '') {
                        $html = '<option value="">Select One</option>';
                        res.forEach(element => {
                            $html += '<option value="'+element.id+'">'+element.name+'</option>';
                        });
                        $('#customer_user_id').html($html);
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        })

    </script>

@endpush
