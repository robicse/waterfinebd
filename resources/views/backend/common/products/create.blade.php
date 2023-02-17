@extends('backend.layouts.master')
@section('title', 'Product Create')
@push('css')
    <link rel="stylesheet" href="{{ asset('backend/css/custom.css') }}">
@endpush
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Product</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route(Request::segment(1) . '.dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Product</li>
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
                            @can('products-create')
                            <h3 class="card-title">Product Create</h3>
                            <div class="float-right">
                                <a href="{{ route(Request::segment(1) . '.products.index') }}">
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
                            {!! Form::open(['route' => Request::segment(1) . '.products.store', 'method' => 'POST', 'files' => true]) !!}
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="product_department_id">Product Department<span
                                            class="required">*</span></label>
                                    <a type="button" class="test btn btn-primary btn-sm" onclick="modal_customer()" data-toggle="modal"><i class="fa fa-plus"></i></a>

                                    {!! Form::select('product_department_id', $productdepartment, null, [
                                        'required',
                                        'id' => 'product_department_id',
                                        'class' => 'form-control',
                                        'placeholder' => 'Select One',
                                        'autofocus',

                                    ]) !!}

                                </div>
                                <div class="form-group col-md-3">
                                    <label for="product_section_id">Product Section <span class="required">*</span></label>
                                    <a type="button" class="test btn btn-primary btn-sm" onclick="modal_section()" data-toggle="modal"><i class="fa fa-plus"></i></a>

                                    {!! Form::select('product_section_id', $productsection, null, [
                                        'required',
                                        'id' => 'product_section_id ',
                                        'class' => 'form-control',
                                        'placeholder' => 'Select One',

                                    ]) !!}

                                </div>
                                <div class="form-group col-md-3">
                                    <label for="category_id">Category <span class="required">*</span></label>
                                    <a type="button" class="test btn btn-primary btn-sm" onclick="modal_category()" data-toggle="modal"><i class="fa fa-plus"></i></a>
                                    {!! Form::select('category_id', $categories, null, [
                                        'required',
                                        'id' => 'category_id',
                                        'class' => 'form-control',
                                        'placeholder' => 'Select One',

                                    ]) !!}

                                </div>
                                <div class="form-group col-md-3">
                                    <label for="subcategory_id">Sub-Category <span class="required">*</span></label>
                                    <a type="button" class="test btn btn-primary btn-sm" onclick="modal_sub_category()" data-toggle="modal"><i class="fa fa-plus"></i></a>
                                    <select class="form-control custom-select"  name="subcategory_id"
                                        id="subcategory_id" required>
                                        <option value="">Select One *</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="brand_id">Brand <span class="required">*</span></label>
                                    <a type="button" class="test btn btn-primary btn-sm" onclick="modal_brand()" data-toggle="modal"><i class="fa fa-plus"></i></a>
                                    {!! Form::select('brand_id', $brands, null, [
                                        'required',
                                        'id' => 'brand_id',
                                        'class' => 'form-control',
                                        'placeholder' => 'Select One',

                                    ]) !!}

                                </div>
                                <div class="form-group col-md-3">
                                    <label for="supplier_user_id">Suppliers <span class="required">*</span></label>
                                    <button type="button" class="btn btn-primary btn-sm"
                                    title="Add New Supllier"
                                    onclick="showSuppliertForm()">
                                    <i class="fa fa-plus"></i>
                                </button>
                                    {!! Form::select('supplier_user_ids[]', $suppliers, null, ['class' => 'form-control demo-select2', 'required','multiple','id' =>'supplier_user_ids']) !!}
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="name">Name <span class="required">*</span></label>
                                    {!! Form::text('name', null, ['id' => 'name', 'class' => 'form-control', 'required', ]) !!}
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="arabic_name">Arabic Name <span class="required">*</span></label>
                                    {!! Form::text('arabic_name', null, [
                                        'id' => 'arabic_name',
                                        'class' => 'form-control',
                                        'required',

                                    ]) !!}
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="barcode">Barcode <span class="required">*</span> <span class="input-group-btn">
                                    </span></label>
                                    <button type="button" class="btn btn-primary btn-sm" id="randnumber"><i title="Click Here"
                                        class="fa fa-random"></i></button>
                                    {!! Form::text('barcode', null, ['class' => 'form-control', 'id' => 'barcode', 'required', ]) !!}

                                </div>
                                <div class="form-group col-md-3">
                                    <label for="unit_measurement">Unit / Measurement <span class="required">*</span> <span class="input-group-btn">
                                    </span></label>
                                    {!! Form::text('unit_measurement', null, ['class' => 'form-control', 'id' => 'unit_measurement', 'required']) !!}

                                </div>
                                <div class="form-group col-md-3 d-none">
                                    <label for="product_barcode">Product Barcode <span class="required"></span></label>
                                    {!! Form::text('product_barcode', null, ['class' => 'form-control', 'id' => 'product_barcode']) !!}

                                </div>
                                <div class="form-group col-md-3">
                                    <label for="country_of_origin">Country Of Origin <span class="required">*</span></label>
                                    {!! Form::select('country_of_origin', $countries, null, [
                                        'required',
                                        'id' => 'country_of_origin ',
                                        'class' => 'form-control demo-select2',
                                        'placeholder' => 'Select One',
                                        'required'
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="low_inventory_alert">Low Inventory Alert</label>
                                    {!! Form::number('low_inventory_alert', 0, ['class' => 'form-control', 'id' => 'low_inventory_alert']) !!}

                                </div>
                                <div class="form-group col-md-3">
                                    <label for="status">Status <span class="required">*</span></label>
                                    {!! Form::select('status', [1 => 'Active', 0 => 'Inactive'], null, [
                                        'id' => 'status',
                                        'class' => 'form-control',
                                        'required',

                                    ]) !!}
                                </div>

                                {{-- <div class="form-group col-md-3">
                                    <label for="expire_date">Expire Date </label>
                                    {!! Form::date('expire_date', null, ['id' => 'vat_id',
                                        'class' => 'form-control',
                                        'placeholder' => 'Select Expire Date',
                                    ]) !!}
                                </div> --}}
                                <div class="form-group col-md-3">
                                    <label for="vat_id">Vat Percent <span class="required">*</span></label>
                                    {!! Form::select('vat_id', $vatPercents, 1, [
                                        'id' => 'vat_id',
                                        'class' => 'form-control',
                                        'placeholder' => 'Select One',
                                        'required'
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="local_purchase_price">Local Purchase Price <span
                                            class="required">*</span></label>
                                    {!! Form::number('local_purchase_price', null, [
                                        'class' => 'form-control',
                                        'id' => 'local_purchase_price',
                                        'step' => 'any',
                                        'min'=>0,
                                         'max'=>9999999999999999,
                                        'required',

                                    ]) !!}
                                </div>
                                {{-- <div class="form-group col-md-3">
                                    <label for="">International Purchase Price <span
                                            class="required">*</span></label>
                                    {!! Form::number('international_purchase_price', null, [
                                        'class' => 'form-control',
                                        'id' => 'international_purchase_price',
                                        'step' => 'any',
                                        'required',
                                        'tabindex="10"',
                                    ]) !!}
                                </div> --}}
                                <div class="form-group col-md-3">
                                    <label for="warehouse_sale_price">Warehouse Sale Price <span
                                            class="required">*</span></label>
                                    {!! Form::number('warehouse_sale_price', null, [
                                        'class' => 'form-control',
                                        'id' => 'warehouse_sale_price',
                                        'step' => 'any',
                                        'min'=>0,
                                         'max'=>9999999999999999,
                                        'required',

                                    ]) !!}
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="minimum_warehouse_sale_price">Minimum Warehouse Sale Price <span
                                            class="required">*</span></label>
                                    {!! Form::number('minimum_warehouse_sale_price', null, [
                                        'class' => 'form-control',
                                        'id' => 'minimum_warehouse_sale_price',
                                        'step' => 'any',
                                        'min'=>0,
                                         'max'=>9999999999999999,
                                        'required',

                                    ]) !!}
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="local_sale_price">Local Sale Price <span class="required">*</span></label>
                                    {!! Form::number('local_sale_price', null, [
                                        'class' => 'form-control',
                                        'id' => 'local_sale_price',
                                        'step' => 'any',
                                        'min'=>0,
                                         'max'=>9999999999999999,
                                        'required',

                                    ]) !!}
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="minimum_local_sale_price">Minimum Local Sale Price <span
                                            class="required">*</span></label>
                                    {!! Form::number('minimum_local_sale_price', null, [
                                        'class' => 'form-control',
                                        'id' => 'minimum_local_sale_price',
                                        'step' => 'any',
                                        'min'=>0,
                                         'max'=>9999999999999999,
                                        'required',

                                    ]) !!}
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="outer_sale_price">Outer Sale Price <span class="required">*</span></label>
                                    {!! Form::number('outer_sale_price', null, [
                                        'class' => 'form-control',
                                        'id' => 'outer_sale_price',
                                        'step' => 'any',
                                        'min'=>0,
                                         'max'=>9999999999999999,
                                        'required',

                                    ]) !!}
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="minimum_outer_sale_price">Minimum Outer Sale Price <span
                                            class="required">*</span></label>
                                    {!! Form::number('minimum_outer_sale_price', null, [
                                        'class' => 'form-control',
                                        'id' => 'minimum_outer_sale_price',
                                        'step' => 'any',
                                        'min'=>0,
                                         'max'=>9999999999999999,
                                        'required',

                                    ]) !!}
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="image">Image</label>
                                    {!! Form::file('image', ['accept' => '.jpg,.jpeg,.png,.webp', 'id' => 'image', 'class' => 'form-control']) !!}

                                    <img id="preview"
                                        src="{{ 'https://www.sohibd.com/storage/app/files/shares/backend/not_found.webp' }}"
                                        alt="preview image" style="max-height:100px;">
                                </div>
                                <div class="form-group col-md-8">
                                    <label for="detail">Detail</label>
                                    <span class="required">*</span></label>
                                    {!! Form::textarea('detail', null, ['id' => 'detail', 'rows' => 6, 'class' => 'form-control', 'required']) !!}
                                </div>

                            </div>

                            <div class="row">
                                <div class="form-group col-md-4" id="unit_variant_div">
                                    <label for="unit_variant">Unit Variant <span class="required">*</span></label>
                                    {!! Form::select('unit_variant', [1 => 'YES', 0 => 'NO'], 0, [
                                        'id' => 'unit_variant',
                                        'class' => 'form-control',
                                        'required',

                                        'placeholder' => 'Select One',
                                    ]) !!}
                                </div>

                                <div class="mb-3 col-md-8 col-sm-12" id="subVariant">
                                    {!! Form::label('reviewdatatable', 'Sub Unit') !!}
                                    <table class="table" id="review_field">

                                        <tr>

                                            <td style="width: 40%"> {!! Form::select('unit_id[]', $units, 1, [
                                                'id' => 'sub_unit_name_1',
                                                'class' => 'form-control units',
                                                'required',
                                                'min'=>0,
                                               'max'=>999999999,
                                                'onchange' => 'getsubUnittval(1,this)',
                                                'placeholder' => 'Select One',
                                                'disabled',
                                            ]) !!}</select> </td>
                                            <td style="width:60%"><input type="number" name="unit_quantity[]" required
                                                    value="1" class="form-control" readonly /></td>

                                            <td><button type="button" disabled id="reviewadd" class="btn btn-success"><i
                                                        class="fas fa-plus-circle"></i></button></td>

                                        </tr>

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


        <!-- Department Modal-->
        <div id="department_modal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Department Create</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="departmentErrr3" class="alert hide"> </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-12">
                                <div class="panel panel-bd lobidrag">
                                    <div class="panel-body">
                                        {!! Form::open(['route' => Request::segment(1) . '.department.store.new', 'method' => 'POST', 'id'=> 'department_insert', 'files' => true]) !!}
                                            <div class="form-group row">
                                                <label class="control-label col-md-3 text-right">Name <small class="requiredCustom">*</small></label>
                                                <div class="col-md-8">
                                                    <input class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" type="text" placeholder="Department Name" name="name" autofocus />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="control-label col-md-3"></label>
                                                <div class="col-md-8">
                                                    <button class="btn btn-primary" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i>Save</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Section Modal-->
        <div id="section_modal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Product Section Create</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="sectionErrr3" class="alert hide"> </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-12">
                                <div class="panel panel-bd lobidrag">
                                    <div class="panel-body">
                                        {!! Form::open(['route' => Request::segment(1) . '.section.store.new', 'method' => 'POST', 'id'=> 'section_insert', 'files' => true]) !!}
                                            <div class="form-group row">
                                                <label class="control-label col-md-3 text-right">Name <small class="requiredCustom">*</small></label>
                                                <div class="col-md-8">
                                                    <input class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" type="text" placeholder="Section Name" name="name" autofocus />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="control-label col-md-3"></label>
                                                <div class="col-md-8">
                                                    <button class="btn btn-primary" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i>Save</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

         <!-- Section Modal-->
         <div id="category_modal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Category Modal Create</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="categoryErrr3" class="alert hide"> </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-12">
                                <div class="panel panel-bd lobidrag">
                                    <div class="panel-body">
                                        {!! Form::open(['route' => Request::segment(1) . '.category.store.new', 'method' => 'POST', 'id'=> 'category_insert', 'files' => true]) !!}
                                            <div class="form-group row">
                                                <label class="control-label col-md-3 text-right">Name <small class="requiredCustom">*</small></label>
                                                <div class="col-md-8">
                                                    <input class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" type="text" placeholder="Category Name" name="name" autofocus />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="control-label col-md-3"></label>
                                                <div class="col-md-8">
                                                    <button class="btn btn-primary" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i>Save</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Modal-->
        <div id="sub_category_modal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Sub Category Create</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="subCategoryErrr3" class="alert hide"> </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-12">
                                <div class="panel panel-bd lobidrag">
                                    <div class="panel-body">
                                        {!! Form::open(['route' => Request::segment(1) . '.sub.category.store.new', 'method' => 'POST', 'id'=> 'sub_category_insert', 'files' => true]) !!}
                                            <div class="form-group row">
                                                <label class="control-label col-md-3 text-right">Category Name <small class="requiredCustom">*</small></label>
                                                <div class="col-md-8">
                                                    {{-- <input class="form-control{{ $errors->has('category_name') ? ' is-invalid' : '' }}" type="text" placeholder="Category Name" name="category_name" autofocus /> --}}
                                                    <select class="form-control custom-select" name="modal_category_id" id="modal_category_id" required>
                                                        <option value="">Select One *</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="control-label col-md-3 text-right">Name <small class="requiredCustom">*</small></label>
                                                <div class="col-md-8">
                                                    <input class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" type="text" placeholder=" Sub Category Name" name="name" autofocus />
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="control-label col-md-3"></label>
                                                <div class="col-md-8">
                                                    <button class="btn btn-primary" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i>Save</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Modal-->
        <div id="brand_modal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Brand Create</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="brandErrr3" class="alert hide"> </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-12">
                                <div class="panel panel-bd lobidrag">
                                    <div class="panel-body">
                                        {!! Form::open(['route' => Request::segment(1) . '.brand.store.new', 'method' => 'POST', 'id'=> 'brand_insert', 'files' => true]) !!}

                                            <div class="form-group row">
                                                <label class="control-label col-md-3 text-right">Name <small class="requiredCustom">*</small></label>
                                                <div class="col-md-8">
                                                    <input class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" type="text" placeholder="Brand Name" name="name" autofocus />
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="control-label col-md-3"></label>
                                                <div class="col-md-8">
                                                    <button class="btn btn-primary" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i>Save</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </section>
    <!-- /.content -->
@stop

@push('js')
    <script>
        $(document).ready(function() {
            $('.demo-select2').select2();
        });

        $('#barcode').blur(function() {
            var barcode = $(this).val();

            $.ajax({
                url: "{{ URL(Request::segment(1) . '/check-barcode') }}",
                method: "get",
                data: {
                    barcode: barcode
                },
                success: function(res) {
                    console.log(res)
                    if (res.data == 'Found') {
                        $('#barcode').val('')
                        alert('Barcode already exists, please add another Barcode!')
                        return false
                    }
                },
                error: function(err) {
                    console.log(err)
                }
            })
        });

        $(document).ready(function() {
            $('#unit_variant').on('change', function() {

                if ($('#unit_variant').val() == 1) {
                    $('#reviewadd').prop("disabled", false);
                    $('#sub_unit_name_1').prop("disabled", false);
                } else {
                    $('#review_field').find("tr:gt(0)").remove();
                    $('#reviewadd').attr("disabled", true);
                    $('#sub_unit_name_1').prop('selectedIndex', 1);
                    $('#sub_unit_name_1').attr("disabled", true);
                }
            });
            var arr2 = @json(json_decode($units, true));
            arr = jQuery.map(arr2, function(n, i) {
                // if(i==0){
                //   return  '<option  value="">Select One</option>'
                // }
                return '<option  value="' + i + '">' + n + '</option>'
            });
            //   console.log('arr2',arr);
            var r = 1;
            $('#reviewadd').click(function() {
                r++;
                $('#review_field').append('<tr id="reviewrow' + r +
                    '" class="dynamic-added"><td style="width:40%"><select class="form-control units"  name="unit_id[]" id="sub_unit_name_' +
                    r + '" onchange="getsubUnittval(' + r +
                    ',this);"><option  value="">Select One</option>' + arr +
                    '</select><td style="width:40%"><input type="number" step="any"  name="unit_quantity[]" required  placeholder="Quantity" class="form-control" /></td> </td><td><button type="button" id="' +
                    r +
                    '" class="btn btn-danger reviewbtn_remove"><i class="fas fa-minus-circle"></i></button> </td></tr>'
                    );

            });

            $(document).on('click', '.reviewbtn_remove', function() {
                var button_id = $(this).attr("id");
                $('#reviewrow' + button_id + '').remove();

            });

            $('#category_id').change(function() {
                $('#subcategory_id').empty();
                $.ajax({
                    type: "GET",
                    url: "{{ url(Request::segment(1) . '/getsubcategory') }}" + '/' + $(this).val(),
                    data: {},
                    dataType: "JSON",
                    success: function(data) {
                        if (data) {

                            $.each(data, function(key, value) {
                                $('#subcategory_id').append('<option value="' + value
                                    .id + '">' + value.name + '</option>');

                            });
                        }
                    }
                });

            });

            // for random value
            var gRandLength = 9;
            $('#randnumber').click(function() {
                var num = Math.floor(1 + (Math.random() * Math.pow(10, gRandLength)));
                $('#barcode').val(num);
                $.ajax({
                    url: "{{ URL(Request::segment(1) . '/check-barcode') }}",
                    method: "get",
                    data: {
                        barcode: num
                    },
                    success: function(res) {
                        console.log(res)
                        if (res.data == 'Found') {
                            $('#barcode').val('')
                            alert('Barcode already exists, please add another Barcode!')
                            return false
                        }
                    },
                    error: function(err) {
                        console.log(err)
                    }
                })
            });
            // for image

            $('#image').change(function() {

                let reader = new FileReader();

                reader.onload = (e) => {

                    $('#preview').attr('src', e.target.result);
                }

                reader.readAsDataURL(this.files[0]);

            });

        });

        function findValueInArray(value,arr){
            var result = "Doesn't exist";

            for(var i=0; i<arr.length; i++){
                var name = arr[i];
                if(name == value){
                    console.log('aaa');
                    result = 'Exist';
                    break;
                }
            }
            return result;
        }


        function getsubUnittval(row, sel) {
            var subunit = [];

            var current_row = row;
            var current_product_id = sel.value;
            if (current_row > 1) {

                var pre = '';
                var cur = '';
                var products =[];

                $('.units').each(function(i,e){

                    if(i == 0){
                        pre = e.value;
                    }else{
                        cur = e.value;
                        if(pre === cur){
                             console.log(i, ': danger');
                             $('#sub_unit_name_' + (i+1)).val('');
                            alert('You selected same unit, Please selected another unit!');
                        }else{
                            console.log(i, ': no error');
                            pre = e.value;
                        }
                    }

                });
            }
        }

        // department modal start
        function modal_customer(){
            $('#department_modal').modal('show');
        }

        //new department insert
        $("#department_insert").submit(function(e){
            e.preventDefault();
            var departmentErrr    = $("#departmentErrr3");
            $.ajax({
                url: $(this).attr('action'),
                method: $(this).attr('method'),
                dataType: 'json',
                data: $(this).serialize(),
                beforeSend: function()
                {
                    departmentErrr.removeClass('hide');
                },
                success: function(data)
                {
                    console.log(data);
                    if (data.exception) {
                        departmentErrr.addClass('alert-danger').removeClass('alert-success').html(data.exception);
                    }else{
                        $('#product_department_id').append('<option value = "' + data.id + '"  selected> '+ data.name + ' </option>');
                        console.log(data.id);
                        $("#department_modal").modal('hide');
                    }
                },
                error: function(xhr)
                {
                    alert('failed!');
                }
            });
        });


        // section modal start
        function modal_section(){
            $('#section_modal').modal('show');
        }

        //new section insert
        $("#section_insert").submit(function(e){
            e.preventDefault();
            var sectionErrr3    = $("#sectionErrr3");
            $.ajax({
                url: $(this).attr('action'),
                method: $(this).attr('method'),
                dataType: 'json',
                data: $(this).serialize(),
                beforeSend: function()
                {
                    sectionErrr3.removeClass('hide');
                },
                success: function(data)
                {
                    //console.log('dsfsdaf');
                    if (data.exception) {
                        sectionErrr3.addClass('alert-danger').removeClass('alert-success').html(data.exception);
                    }else{
                        $('#product_section_id').append('<option value = "' + data.id + '"  selected> '+ data.name + ' </option>');
                        console.log(data.id);
                        $("#section_modal").modal('hide');
                    }
                },
                error: function(xhr)
                {
                    alert('failed!');
                }
            });
        });

        // category modal start
        function modal_category(){
            $('#category_modal').modal('show');
        }

        //new category insert
        $("#category_insert").submit(function(e){
            e.preventDefault();
            var categoryErrr3    = $("#categoryErrr3");
            $.ajax({
                url: $(this).attr('action'),
                method: $(this).attr('method'),
                dataType: 'json',
                data: $(this).serialize(),
                beforeSend: function()
                {
                    categoryErrr3.removeClass('hide');
                },
                success: function(data)
                {
                    //console.log('dsfsdaf');
                    if (data.exception) {
                        categoryErrr3.addClass('alert-danger').removeClass('alert-success').html(data.exception);
                    }else{
                        $('#category_id').append('<option value = "' + data.id + '"  selected> '+ data.name + ' </option>');
                        console.log(data.id);
                        $("#category_modal").modal('hide');
                    }
                },
                error: function(xhr)
                {
                    alert('failed!');
                }
            });
        });

         //sub category modal start
         function modal_sub_category(){
            $('#sub_category_modal').modal('show');
            $('#modal_category_id').empty();
            $("#sub_category_modal input[name='name']").empty();
            var sub_id = $('#category_id option:selected').val();
            var sub_text = $('#category_id option:selected').text();

            $('#modal_category_id').append('<option value = "' + sub_id + '"  selected> '+ sub_text + ' </option>');
        }

        //new sub category insert
        $("#sub_category_insert").submit(function(e){
            e.preventDefault();
            var subCategoryErrr3    = $("#subCategoryErrr3");
            $.ajax({
                url: $(this).attr('action'),
                method: $(this).attr('method'),
                dataType: 'json',
                data: $(this).serialize(),
                beforeSend: function()
                {
                    subCategoryErrr3.removeClass('hide');
                },
                success: function(data)
                {
                    //console.log('dsfsdaf');
                    if (data.exception) {
                        subCategoryErrr3.addClass('alert-danger').removeClass('alert-success').html(data.exception);
                    }else{
                        $('#subcategory_id').append('<option value = "' + data.id + '"  selected> '+ data.name + ' </option>');
                        $("#sub_category_modal").modal('hide');
                    }
                },
                error: function(xhr)
                {
                    alert('failed!');
                }
            });
        });


        // category modal start
        function modal_brand(){
            $('#brand_modal').modal('show');
        }

        //new category insert
        $("#brand_insert").submit(function(e){
            e.preventDefault();
            var brandErrr    = $("#brandErrr3");
            $.ajax({
                url: $(this).attr('action'),
                method: $(this).attr('method'),
                dataType: 'json',
                data: $(this).serialize(),
                beforeSend: function()
                {
                    brandErrr.removeClass('hide');
                },
                success: function(data)
                {
                    //console.log('dsfsdaf');
                    if (data.exception) {
                        brandErrr.addClass('alert-danger').removeClass('alert-success').html(data.exception);
                    }else{
                        $('#brand_id').append('<option value = "' + data.id + '"  selected> '+ data.name + ' </option>');
                        $("#brand_modal").modal('hide');
                    }
                },
                error: function(xhr)
                {
                    alert('failed!');
                }
            });
        });

        function showSuppliertForm() {
            var page = "{{ url(Request::segment(1) . '/suppliers/create') }}";
            var myWindow = window.open(page, "_blank", "scrollbars=yes,width=700,height=1000,top=30");
            // focus on the popup //
            myWindow.focus();
        }

    </script>
@endpush
