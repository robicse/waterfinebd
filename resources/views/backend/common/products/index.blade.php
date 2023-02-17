@extends('backend.layouts.master')
@section("title","Product Lists")
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
                    <h1>Products</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route(Request::segment(1).'.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Products</li>
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
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Products Lists</h3>
                            <div class="float-right">
                                @can('products-create')
                                <a href="{{route(Request::segment(1).'.products.create')}}">
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
                            <table id="example1" class="table table-bordered table-striped data-table">
                                <thead>
                                <tr>
                                    <th>#Id</th>
                                    <th>Name</th>
                                    <th>Specification</th>
                                    <th>Unit / Measurement</th>
                                    <th>Barcode</th>
                                    <th>Local Purchase Price ({{ $default_currency->symbol }})</th>
                                    <th>Internation Purchase Price ({{ $default_currency->symbol }})</th>
                                    
                                    <th>War Sale Price ({{ $default_currency->symbol }})</th>
                                    <th>Min War Sale Price ({{ $default_currency->symbol }})</th>
                                    <th>Local Sale Price ({{ $default_currency->symbol }})</th>
                                    <th>Min Local Sale Price ({{ $default_currency->symbol }})</th>
                                    <th>Outer Sale Price ({{ $default_currency->symbol }})</th>
                                    <th>Min Outer Sale Price ({{ $default_currency->symbol }})</th>
                                    <th>Variant</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>#Id</th>
                                    <th>Name</th>
                                    <th>Specification</th>
                                    <th>Unit / Measurement</th>
                                    <th>Barcode</th>
                                    <th>Local Purchase Price</th>
                                    <th>Internation Purchase Price</th>
                                    <th>War Sale Price</th>
                                    <th>Min War Sale Price</th>
                                    <th>Local Sale Price</th>
                                    <th>Min Local Sale Price</th>
                                    <th>Outer Sale Price</th>
                                    <th>Min Outer Sale Price</th>
                                    <th>Variant</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Action</th>
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
                responsive: true,
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
                ajax: "{{ route(Request::segment(1).'.products.index') }}",
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'detail', name: 'detail'},
                    {data: 'unit_measurement', name: 'unit_measurement'},
                    {data: 'barcode', name: 'barcode'},
                    {data: 'local_purchase_price', name: 'local_purchase_price'},
                    {data: 'international_purchase_price', name: 'international_purchase_price'},
                    {data: 'warehouse_sale_price', name: 'warehouse_sale_price'},
                    {data: 'min_warehouse_sale_price', name: 'min_warehouse_sale_price'},
                    {data: 'local_sale_price', name: 'local_sale_price'},
                    {data: 'minimum_local_sale_price', name: 'minimum_local_sale_price'},
                    {data: 'outer_sale_price', name: 'outer_sale_price'},
                    {data: 'minimum_outer_sale_price', name: 'minimum_outer_sale_price'},
                    {data: 'unit_variant', name: 'unit_variant'},
                    {data: 'unit', name: 'unit'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action', orderable: false, searchable: true},
                ]
            });
        });
    </script>

@endpush
