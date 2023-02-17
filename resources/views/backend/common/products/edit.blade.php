@extends('backend.layouts.master')
@section('title', 'Product Edit')
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
                            <h3 class="card-title">Product Create</h3>
                            <div class="float-right">
                                <a href="{{ route(Request::segment(1) . '.products.index') }}">
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
                            {!! Form::model($product, [
                                'route' => [Request::segment(1) . '.products.update', $product->id],
                                'method' => 'PATCH',
                                'files' => true,
                            ]) !!}
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="product_department_id">Product Department <span
                                            class="required">*</span></label>
                                    {!! Form::select('product_department_id', $productdepartment, null, [
                                        'required',
                                        'id' => 'product_department_id ',
                                        'class' => 'form-control',
                                        'placeholder' => 'Select One',
                                        'autofocus'

                                    ]) !!}

                                </div>

                                <div class="form-group col-md-4">
                                    <label for="product_section_id">Product Section <span class="required">*</span></label>
                                    {!! Form::select('product_section_id', $productsection, null, [
                                        'required',
                                        'id' => 'product_section_id ',
                                        'class' => 'form-control',
                                        'placeholder' => 'Select One',

                                    ]) !!}

                                </div>
                                <div class="form-group col-md-4">
                                    <label for="category_id">Category <span class="required">*</span></label>
                                    {!! Form::select('category_id', $categories, null, [
                                        'required',
                                        'id' => 'category_id',
                                        'class' => 'form-control',
                                        'placeholder' => 'Select One',

                                    ]) !!}

                                </div>
                                <div class="form-group col-md-4">
                                    <label for="subcategory_id">Sub-Category <span class="required">*</span></label>

                                    {!! Form::select('subcategory_id', $subCategories, null, [
                                        'required',
                                        'id' => 'subcategory_id',
                                        'class' => 'form-control',
                                        'placeholder' => 'Select One',

                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="brand_id">Brand <span class="required">*</span></label>
                                    {!! Form::select('brand_id', $brands, null, [
                                        'required',
                                        'id' => 'brand_id',
                                        'class' => 'form-control',
                                        'placeholder' => 'Select One',

                                    ]) !!}

                                </div>
                                <div class="form-group col-md-4">
                                    <label for="supplier_user_ids">Suppliers <span class="required">*</span></label>
                                    {!!Form::select('supplier_user_ids[]',$suppliers,$supplierIds, array('id'=>'supplier_user_ids','required','class'=>'form-control demo-select2','multiple'=>true))!!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="name">Name <span class="required">*</span></label>
                                    {!! Form::text('name', null, ['id' => 'name', 'class' => 'form-control', 'required']) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="arabic_name">Arabic Name <span class="required">*</span></label>
                                    {!! Form::text('arabic_name', null, [
                                        'id' => 'arabic_name',
                                        'class' => 'form-control',
                                        'required',

                                    ]) !!}
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="barcode">Barcode <span class="required">*</span></label>
                                    {!! Form::text('barcode', null, ['class' => 'form-control', 'id' => 'barcode', 'required']) !!}
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-primary" id="randnumber"><i title="Click Here"
                                                class="fa fa-random"></i></button>
                                    </span>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="unit_measurement">Unit / Measurement <span class="required">*</span> <span class="input-group-btn">
                                    </span></label>
                                    {!! Form::text('unit_measurement', null, ['class' => 'form-control', 'id' => 'unit_measurement', 'required']) !!}

                                </div>
                                <div class="form-group col-md-4">
                                    <label for="country_of_origin">Country Of Origin <span class="required">*</span></label>
                                    {!! Form::select('country_of_origin', $countries, null, [
                                        'required',
                                        'id' => 'country_of_origin ',
                                        'class' => 'form-control demo-select2',
                                        'placeholder' => 'Select One',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="low_inventory_alert">Low Inventory Alert</label>
                                    {!! Form::number('low_inventory_alert', null, ['class' => 'form-control', 'id' => 'low_inventory_alert']) !!}

                                </div>
                                <div class="form-group col-md-4">
                                    <label for="status">Status <span class="required">*</span></label>
                                    {!! Form::select('status', [1 => 'Active', 0 => 'Inactive'], null, [
                                        'id' => 'status',
                                        'class' => 'form-control',
                                        'required',
                                    ]) !!}
                                </div>
                                {{-- <div class="form-group col-md-6">
                                    <label for="expire_date">Expire Date </label>
                                    {!! Form::date('expire_date', null, ['id' => 'vat_id',
                                        'class' => 'form-control',
                                        'placeholder' => 'Select Expire Date',
                                    ]) !!}
                                </div> --}}
                                <div class="form-group col-md-6">
                                    <label for="vat_id">Vat Percent <span class="required">*</span></label>
                                    {!! Form::select('vat_id', $vatPercents, null, [
                                        'id' => 'vat_id',
                                        'class' => 'form-control',
                                        'placeholder' => 'Select One',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="local_purchase_price">Local Purchase Price <span
                                            class="required">*</span></label>
                                    {!! Form::number('local_purchase_price', $product_price->local_purchase_price, [
                                        'class' => 'form-control',
                                        'id' => 'local_purchase_price',
                                        'step' => 'any',
                                        'min'=>0,
                                         'max'=>9999999999999999,
                                        'required',

                                    ]) !!}
                                </div>
                                {{-- <div class="form-group col-md-4">
                                    <label for="">International Purchase Price <span
                                            class="required">*</span></label>
                                    {!! Form::number('international_purchase_price', $product_price->international_purchase_price, [
                                        'class' => 'form-control',
                                        'id' => 'international_purchase_price',
                                        'step' => 'any',
                                        'required',
                                        'tabindex="10"',
                                    ]) !!}
                                </div> --}}
                                <div class="form-group col-md-4">
                                    <label for="warehouse_sale_price">Warehouse Sale Price <span
                                            class="required">*</span></label>
                                    {!! Form::number('warehouse_sale_price', $product_price->warehouse_sale_price, [
                                        'class' => 'form-control',
                                        'id' => 'warehouse_sale_price',
                                        'step' => 'any',
                                        'min'=>0,
                                            'max'=>9999999999999999,
                                        'required',

                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="minimum_warehouse_sale_price">Minimum Warehouse Sale Price <span
                                            class="required">*</span></label>
                                    {!! Form::number('minimum_warehouse_sale_price', $product_price->minimum_warehouse_sale_price, [
                                        'class' => 'form-control',
                                        'id' => 'minimum_warehouse_sale_price',
                                        'step' => 'any',
                                        'min'=>0,
                                       'max'=>9999999999999999,
                                        'required',

                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="local_sale_price">Local Sale Price <span class="required">*</span></label>
                                    {!! Form::number('local_sale_price', $product_price->local_sale_price, [
                                        'class' => 'form-control',
                                        'id' => 'local_sale_price',
                                        'step' => 'any',
                                        'min'=>0,
                                            'max'=>9999999999999999,
                                        'required',

                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="minimum_local_sale_price">Minimum Local Sale Price <span
                                            class="required">*</span></label>
                                    {!! Form::number('minimum_local_sale_price', $product_price->minimum_local_sale_price, [
                                        'class' => 'form-control',
                                        'id' => 'minimum_local_sale_price',
                                        'step' => 'any',
                                        'min'=>0,
                                            'max'=>9999999999999999,
                                        'required',

                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="outer_sale_price">Outer Sale Price <span class="required">*</span></label>
                                    {!! Form::number('outer_sale_price', $product_price->outer_sale_price, [
                                        'class' => 'form-control',
                                        'id' => 'outer_sale_price',
                                        'step' => 'any',
                                        'min'=>0,
                                        'max'=>9999999999999999,
                                        'required',

                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="minimum_outer_sale_price">Minimum Outer Sale Price <span
                                            class="required">*</span></label>
                                    {!! Form::number('minimum_outer_sale_price', $product_price->minimum_outer_sale_price, [
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
                                    {!! Form::textarea('detail', null, ['id' => 'detail', 'rows' => 6, 'class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-4" id="unit_variant_div">
                                    <label for="unit_variant">Unit Variant <span class="required">*</span></label>
                                    {!! Form::select('unit_variant', [1 => 'YES', 0 => 'NO'], null, [
                                        'id' => 'unit_variant',
                                        'class' => 'form-control',
                                        'required',
                                        'readonly',

                                        'placeholder' => 'Select One',
                                    ]) !!}
                                </div>

                                <div class="mb-3 col-md-8 col-sm-12" id="subVariant">

                                    <table class="table" id="review_field">
                                        @foreach ($product->productprice as $key => $info)
                                            <tr id="reviewrow{{ $loop->index }}">
                                                <input type="hidden" name="product_price_id[]"
                                                    value="{{ $info->id }}" class="form-control" />

                                                <td style="width: 40%">
                                                    @php
                                                        $sl = $key + 1;
                                                        $sub_unit_name = 'sub_unit_name_' . $sl;
                                                    @endphp
                                                    {!! Form::select('unit_id[]', $units, $info->unit_id, [
                                                        'id' => $sub_unit_name,
                                                        'class' => 'form-control',
                                                        'required',
                                                        'min'=>0,
                                                        'max'=>9999999999999999,
                                                        'onchange' => 'getsubUnittval(' . $sl . ',this)',
                                                        'placeholder' => 'Select One',
                                                    ]) !!}
                                                </td>
                                                <td style="width:60%">
                                                    <input type="number" id="sub_unit_quentity" name="unit_quantity[]"
                                                        required value="{{ $info->unit_quantity }}"
                                                        class="form-control" />
                                                </td>

                                                @if ($loop->first)
                                                    <td><button type="button" id="reviewadd" class="btn btn-success"><i
                                                                class="fas fa-plus-circle"></i></button></td>
                                                @else
                                                    {{--                                                <td><button type="button" id="{{$loop->index}}" class="btn btn-danger productbtn_remove"><i class="fas fa-minus-circle"></i></button></td> --}}
                                                @endif

                                            </tr>
                                        @endforeach
                                    </table>

                                </div>
                            </div>


                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
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
    <script>
        $(document).ready(function() {
            $('.demo-select2').select2();
        });

        $('#barcode').blur(function() {
            var barcode = $(this).val();

            $.ajax({
                url: "{{ URL(Request::segment(1) . '/check-barcode-edit') }}",
                method: "get",
                data: {
                    barcode: barcode,
                    product_id: {{ $product->id }}
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
        })

        $(document).ready(function() {
            $("#unit_variant_div").focus(function() {
                alert();
                $(this).blur();
            });
            $('#unit_variant').on('change', function() {
                if ($('#unit_variant').val() == 1) {
                    $('#reviewadd').prop("disabled", false);
                    $('#sub_unit_name_1').prop("disabled", false);
                } else {
                    $('#review_field').find("tr:gt(0)").remove();
                    $('#reviewadd').attr("disabled", true);
                    $('#sub_unit_name_1').prop('selectedIndex', 1);
                    $('#sub_unit_name_1').attr("disabled", true);
                    $('#sub_unit_quentity').attr("readonly", true);
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
            var r = @json(count(json_decode($product->productprice, true)));
            console.log('arr2', r);
            $('#reviewadd').click(function() {
                r++;
                $('#review_field').append('<tr id="reviewrow' + r +
                    '" class="dynamic-added"><td style="width:40%"><select class="form-control"  name="unit_id[]" id="sub_unit_name_' +
                    r + '" onchange="getsubUnittval(' + r +
                    ',this);"><option  value="">Select One</option>' + arr +
                    '</select><td style="width:40%"><input type="number" min="0" max="9999999999999999" name="unit_quantity[]" required  placeholder="Quantity" class="form-control" /></td> </td><td><button type="button" id="' +
                    r +
                    '" class="btn btn-danger reviewbtn_remove"><i class="fas fa-minus-circle"></i></button> </td></tr>'
                );

            });

            $(document).on('click', '.reviewbtn_remove', function() {
                var button_id = $(this).attr("id");
                $('#reviewrow' + button_id + '').remove();

            });


            $id = {!! $product->category_id !!};
            $.ajax({
                type: "GET",
                url: "{{ url(Request::segment(1) . '/getsubcategory') }}" + '/' + $id,
                data: {},
                dataType: "JSON",
                success: function(data) {
                    if (data) {
                        $.each(data.division, function(key, value) {
                            // console.log(districtid);
                            if (value._id == divisionid) {
                                $('#division_id').append('<option value="' + value.id +
                                    '" selected>' + value.division + '</option>');
                            } else {
                                $('#division_id').append('<option value="' + value.id + '">' +
                                    value.division + '</option>');
                            }
                        });
                        $.each(data, function(key, value) {
                            if (value.id == $id) {
                                $('#subcategory_id').append('<option value="' + value.id +
                                    '" selected>' + value.name + '</option>');
                            } else {
                                $('#subcategory_id').append('<option value="' + value.id +
                                    '">' + value.name + '</option>');
                            }
                        });

                    }
                }
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
            var gRandLength = 7;
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

        function getsubUnittval(row, sel) {
            var subunit = [];
            var current_row = row;
            var current_product_id = sel.value;
            if (current_row > 1) {
                var previous_row = current_row - 1;
                var previous_product_id = $('#sub_unit_name_' + previous_row).val();
                if (previous_product_id === current_product_id) {
                    $('#sub_unit_name_' + current_row).val('');
                    alert('You selected same product, Please selected another product!');
                    return false
                }
            }

        }
    </script>
@endpush
