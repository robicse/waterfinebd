@extends('backend.layouts.master')
@section('title', 'Customer Ledger Lists')
@push('css')

    <link rel="stylesheet" href="{{ asset('backend/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
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
                                        {!! Form::select('warehouse_id', $warehouses, $warehouse_id, [
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
                                            @if(count($customers))
                                                @foreach($customers as $customer)
                                                    <option value="{{$customer->id}}" {{$customer->id == $customer_user_id ? 'selected' : ''}}>{{$customer->name}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group">
                                        <label>Start Date:</label>
                                        {!! Form::date('start_date', $from, ['class' => 'form-control', 'id' => 'myDatepicker', 'required']) !!}
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group">
                                        <label>End Date:</label>
                                        {!! Form::date('end_date', $to, ['class' => 'form-control', 'id' => 'myDatepicker1', 'required']) !!}
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group">
                                        <br>
                                        <button class="btn btn-primary  mt-2">Submit</button>
                                        <a href="{{ route(Request::segment(1) . '.customer-ledgers.index') }}" class="btn btn-primary" type="button" style="margin-top:8px;">Reset</a>
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


    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <div class="row justify-content-center">
                                <div class="col-md-6">
                                    <h6><strong>Pre Balance: </strong>{{ $preBalance }}</h6>
                                    <h6><strong>Current Balance: </strong>{{customerLedgerCurrentBalance($customerReports)}}</h6>
                                </div>
                                <div class="col-md-6">

                                </div>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive">
                            @if($customerReports->isNotEmpty())
                                <table class="table table-bordered table-striped data-table">
                                    <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Debit</th>
                                            <th>Credit</th>
                                            <th>Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $balance = 0;
                                        @endphp
                                        @foreach ($customerReports as $cus)
                                        @php
                                            $current_debit = $cus->debit;
                                            $current_credit = $cus->credit;
                                            if($current_debit > 0){
                                                $balance += $current_debit;
                                            }else{
                                                $balance -= $current_credit;
                                            }
                                        @endphp
                                            <tr>
                                                <td class="text-right">{{ $loop->index + 01 }}</td>
                                                <td class="text-right">{{ $cus->date }}</td>
                                                <td class="text-right">{{ $cus->description }}</td>
                                                <td class="text-right">{{ $cus->debit }}</td>
                                                <td class="text-right">{{ $cus->credit }}</td>
                                                <td class="text-right">{{ $balance}}</td>
                                            </tr>
                                        @endforeach
                                        <tfoot>
                                            <td colspan="2"></td>
                                            <td class="text-right"><strong> Total : </strong> </td>
                                            <td class="text-right"> <strong> {{ $customerReports->sum('debit') }}</strong></td>
                                            <td class="text-right"> <strong> {{ $customerReports->sum('credit') }}</strong></td>
                                            <td class="text-right"> <strong id="ledger_balance"> {{ $balance}}</strong></td>
                                        </tfoot>
                                    </tbody>
                                </table>
                            @else
                                <div>
                                    <h2 class="text-center">No Data found</h2>
                                </div>
                            @endif
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

    <script src="{{ asset('backend/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>


    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.data-table').DataTable( {
                dom: 'Bflrtip',
                lengthMenu :
                [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print','colvis'
                ]
            } );

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

        })
    </script>
@endpush
