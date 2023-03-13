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
                    <h1>Customer Ledger</h1>
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
                                        <label>Select Store:</label>
                                        <select class="form-control" name="store_id" id="store_id" autofocus>
                                            <option value="All">All Store</option>
                                            @if(count($stores))
                                                @foreach($stores as $store)
                                                    <option value="{{$store->id}}">{{$store->name}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group">
                                        <label>Select Customer:</label>
                                        <select class="form-control" name="customer_id" id="customer_id" autofocus>
                                            <option>Select One</option>
                                            @if(count($customers))
                                                @foreach($customers as $customer)
                                                    <option value="{{$customer->id}}">{{$customer->name}}</option>
                                                @endforeach
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
                                <div class="col-lg-1">
                                    <label for="previewtype">
                                        <input type="radio" name="previewtype" value="htmlview" checked id="previewtype">
                                        Normal</label>
                                    <label for="pdfprintview">
                                        <input type="radio" name="previewtype" value="pdfview" id="printview"> Pdf
                                    </label>
                                </div>
                                <div class="col-2">
                                    <div class="form-group ">
                                        <br>
                                        <button class="btn btn-primary  mt-2" id="SUBMIT_BTN">Submit</button>
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
    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });
    </script>
@endpush
