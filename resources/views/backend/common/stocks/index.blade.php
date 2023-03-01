@extends('backend.layouts.master')
@section("title","Stocks Lists")
@push('css')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{asset('backend/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('backend/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('backend/plugins/datatables-buttons/css/buttons.bootstrap4.min.css')}}">
@endpush
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Stocks</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active"><a
                                href="{{route(Request::segment(1).'.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">stocks</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Stock Lists</h3>
                            <div class="float-right">
                                @can('stocks-create')
                                {{-- <a href="{{ @url('/backend/demo_xlsx/stock.xlsx') }}"> <button class="btn btn-info">
                                        Download Demo <i class="fa fa-download"></i>
                                    </button></a> --}}
                                <a href="{{route(Request::segment(1).'.stocks.create')}}">
                                    <button class="btn btn-success">
                                        <i class="fa fa-plus-circle"></i>
                                        Add
                                    </button>
                                </a>
                                @endcan
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <table class="table table-bordered table-striped data-table">
                                <thead>
                                <tr>
                                    <th>Sl</th>
                                    <th>Store</th>
                                    <th>Category</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Buy Amount</th>
                                    <th>Sale Amount</th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>Sl</th>
                                    <th>Store</th>
                                    <th>Category</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Buy Amount</th>
                                    <th>Sale Amount</th>
                                </tr>
                                </tfoot>
                            </table>
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
    <script src="{{asset('backend/plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('backend/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
    <script src="{{asset('backend/plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
    <script src="{{asset('backend/plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
    <script src="{{asset('backend/plugins/datatables-buttons/js/dataTables.buttons.min.js')}}"></script>
    <script src="{{asset('backend/plugins/datatables-buttons/js/buttons.bootstrap4.min.js')}}"></script>
    <script src="{{asset('backend/plugins/jszip/jszip.min.js')}}"></script>
    <script src="{{asset('backend/plugins/pdfmake/pdfmake.min.js')}}"></script>
    <script src="{{asset('backend/plugins/pdfmake/vfs_fonts.js')}}"></script>
    <script src="{{asset('backend/plugins/datatables-buttons/js/buttons.html5.min.js')}}"></script>
    <script src="{{asset('backend/plugins/datatables-buttons/js/buttons.print.min.js')}}"></script>
    <script src="{{asset('backend/plugins/datatables-buttons/js/buttons.colVis.min.js')}}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                dom: 'Bflrtip',
                lengthMenu :
                [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],

                buttons: [
                    {
                        extend: 'csv',
                        text: 'Excel',
                        exportOptions: {
                            stripHtml: true,
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: 'PDF',
                        exportOptions: {
                            stripHtml: true,
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        exportOptions: {
                            stripHtml: true,
                            columns: ':visible'
                        }
                    },
                    'colvis'

                ],
                ajax: "{{ route(Request::segment(1).'.stocks.index') }}",
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'store',name: 'store'},
                    {data: 'category',name: 'category'},
                    {data: 'product',name: 'product'},
                    {data: 'quantity',name: 'quantity'},
                    {data: 'buy_price',name: 'buy_price'},
                    {data: 'sell_price',name: 'sell_price'},
                ]
            });
        });

        function updateStatus(el,tableName){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post("{{ route(Request::segment(1).'.updateStatus')  }}",
                {_token:'{{ csrf_token() }}', id:el.value, status:status,tableName:tableName},
                function(data){
                if(data == 1){
                    toastr.success('success', 'Status updated successfully');
                }
                else{
                    toastr.danger('danger', 'Something went wrong');
                }
            });
        }
    </script>
@endpush
