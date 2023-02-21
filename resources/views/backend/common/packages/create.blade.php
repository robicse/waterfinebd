@extends('backend.layouts.master')
@section('title', 'Package Create')
@push('css')
    <link rel="stylesheet" href="{{ asset('backend/css/custom.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@10.10.1/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css"
        integrity="sha512-aOG0c6nPNzGk+5zjwyJaoRUgCdOrfSDhmMID2u4+OIslr0GjpLKo7Xm0Ao3xmpM4T8AmIouRkqwj1nrdVsLKEQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Package</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route(Request::segment(1) . '.dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Package</li>
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
                            @can('packages-create')
                            <h3 class="card-title">Package Create</h3>
                            <div class="float-right">
                                <a href="{{ route(Request::segment(1) . '.packages.index') }}">
                                    <button class="btn btn-success">
                                        <i class="fa fa-plus-circle"></i>
                                        Back
                                    </button>
                                </a>
                            </div>
                            @endcan
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
                            @php
                            $package = '';
                            @endphp
                            {!! Form::open(['route' => Request::segment(1) . '.packages.store', 'method' => 'POST', 'files' => true]) !!}
                            @include('backend.common.packages.form')
                            <div class="col-lg-12 col-md-12 ">
                                <div id="dynamic" class="row card-info  card border  customcontent" >
                                    <table class="table table-responsive" id="table1">
                                        <thead>
                                            <tr>
                                                <th style="width: 4%">Category</th>
                                                <th style="width: 20%">
                                                    Product <span class="required">*</span>
                                                    {{-- <button type="button" class="btn btn-primary btn-sm"
                                                        title="Add New Product And Find Product By Type Product Name"
                                                        onclick="showProductForm()">
                                                        <i class="fa fa-plus"></i>
                                                    </button> --}}
                                                </th>
                                                <th style="width: 9%">Quantity</th>
                                                <th style="width: 10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemlist">
                                            <tr>
                                                <td>
                                                    <div>
                                                        <select class="form-control category_id select2"
                                                            name="category_id[]" required id="category_id_1"
                                                            onchange="getCategoryVal(1,this);" style="width: 100% !important">
                                                            <option value="">Select Category</option>
                                                            @if(count($categories) > 0)
                                                                @foreach($categories as $category)
                                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <select class="form-control product_id select2"
                                                        name="product_id[]" id="product_id_1"
                                                        required style="width: 100% !important">
                                                    </select>
                                                </td>
                                                <td>
                                                    <input class="input-sm text-right form-control" type="number"
                                                        name="quantity[]" id='quantity_id_1'
                                                        placeholder="0.00" data-cell="D1" step="any" min="0"
                                                        max="99999999999999" required data-format="0[.]00">
                                                </td>
                                                <td>
                                                    <input type="button" class="btn btn-success addProduct"
                                                        value="+">
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Submit</button>
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

@section('calx')
    <script src="{{ asset('backend/jquery-calx-sample-2.2.8.min.js') }}"></script>
@endsection
@push('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
            $(document).on('click', '.addProduct', function() {
                var category = $('.category_id').html();
                var n = ($('#itemlist tr').length - 0) + 1;
                var tr =
                    '<tr>' +
                        '<td width="12%"><div><select  class="form-control category_id select2" name="category_id[]" id="category_id_' +
                    n + '" onchange="getCategoryVal(' + n + ',this);" required>' + category +
                    '</select></div></td>' +
                    '<td><select class="form-control product_id select2"  name="product_id[]" id="product_id_' +
                    n + '" onchange="getval(' + n +
                    ',this);" required></select> </td>' +
                    '<td width="12%"><input type="number"  class="input-sm text-right form-control" name="quantity[]" id="quantity_id_' +
                    n +
                    '" required   step="any" min="0" max="99999999999999" placeholder="0.00" data-cell="D' +
                    n +
                    '" data-format="" data-format="0[.]00"></td>' +
                    '<td><span class="d-inline-flex"><input type="button"  class="btn btn-success addProduct" value="+"> <input type="button" class="btn btn-danger delete float-left" style="margin-left: 5px" value="x" title="Remove This Product"></span></td>' +
                    '</tr>';
                $('#itemlist').append(tr);
                $('.select2').select2();
                $('#product_id_' + n).select2('open').trigger('select2:open');
            });

            //new item
            $('#itemlist').delegate('.delete', 'click', function() {
                $(this).parent().parent().parent().remove();
            });
        });

        function getCategoryVal(row, sel) {
            console.log('111')
            var current_row = row;
            var current_category_id = sel.value;
            console.log('current_category_id',current_category_id)

            if (current_row > 1) {
                for (let index = 1; index < current_row; index++) {
                    var previous_category_id = $(('#category_id_' + index)).val();
                    console.log('previous_category_id',previous_category_id)
                    var current_category_id = $('#category_id_' + current_row).val();
                    if (previous_category_id === current_category_id) {
                        $('#category_id_' + current_row).val('');
                        alert('You selected same category, Please selected another category!');
                        return false;
                    }
                }
            }

            $.ajax({
                url: "{{ URL(Request::segment(1) . '/category-product-info') }}",
                method: "get",
                data: {
                    current_category_id: current_category_id
                },
                success: function(res) {
                    console.log('res', res)
                    $(("#product_id_" + current_row)).html(res.data.productOptions);
                },
                error: function(err) {
                    console.log(err)
                }
            })
        }
    </script>
@endpush

