@extends('backend.layouts.master')
@section('title', 'Purchase Update')
@push('css')
    <link rel="stylesheet" href="{{ asset('backend/css/custom.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@10.10.1/dist/sweetalert2.min.css" rel="stylesheet">
@endpush
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Purchase Create </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route(Request::segment(1) . '.dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Purchase</li>
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
                            <h3 class="card-title">Purchase </h3>
                            <div class="float-right">
                                <a href="{{ route(Request::segment(1) . '.purchases.index') }}">
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
                            {!! Form::model($purchase, [
                                'route' => [Request::segment(1) . '.purchases.update', $purchase->id],
                                'method' => 'PATCH',
                                'files' => true,
                            ]) !!}
                            <div class="row">
                                @include('backend.common.purchases.form')

                                <div class="row">&nbsp;</div>
                                <div class="col-lg-12 col-md-12">
                                    <div id="dynamic" class="row bg-light">
                                        <table class="table table-responsive">
                                            <thead>
                                                <tr>
                                                    {{-- <th style="width: 12%">Barcode </th> --}}
                                                    {{-- <th style="width: 12%">Category <span class="required">*</span></th> --}}
                                                    <th style="width: 15%">
                                                        Product <span class="required">*</span>
                                                        {{-- <button type="button" class="btn btn-primary btn-sm"
                                                            title="Add New Product And Find Product By Type Product Name"
                                                            onclick="showProductForm()">
                                                            <i class="fa fa-plus"></i>
                                                        </button> --}}
                                                    </th>
                                                    <th style="width: 10%">Unit</th>
                                                    <th style="width: 10%">Qty</th>
                                                    <th style="width: 10%">Purchase Price</th>
                                                    <th style="width: 10%">Sell Price</th>
                                                    <th style="width: 10%">Vat(%)</th>
                                                    <th style="width: 10%">Vat Amount</th>
                                                    <th style="width: 15%">Sub Total</th>
                                                    <th style="width: 10%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="itemlist">
                                                @foreach($stocks as $stock)
                                                <tr>
                                                    {{-- <td>
                                                        <div>
                                                            <select class="form-control category_id select2"
                                                                name="category_id[]" required id="category_id_1"
                                                                onchange="getCategoryVal(1,this);">
                                                                <option value="">Select Category</option>
                                                                @if(count($categories) > 0)
                                                                    @foreach($categories as $category)
                                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </td> --}}
                                                    <td width="15%">
                                                        <select class="form-control product_id select2" name="product_id[]"
                                                            id="product_id_1" onchange="getval(1,this);" required style="width: 100%">
                                                            <option value="{{ $stock->product_id }}">
                                                                {{ $stock->product->name }}</option>
                                                        </select>
                                                    </td>
                                                    <td width="10%">
                                                        <div>
                                                            <select class="form-control unit_id select2" name="unit_id[]"
                                                                required id="unit_id_1" onchange="getUnitVal(1,this);" style="width: 100%">
                                                                {{-- <option value="">Select Unit</option> --}}
                                                                <option value="{{ $stock->unit_id }}">
                                                                    {{ $stock->unit->name }}</option>
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td width="10%">
                                                        <input class="input-sm text-right form-control quantity" type="number"
                                                            name="qty[]" onblur="getQty(1,this);" id='qty_id_1'
                                                            data-cell="D1" step="any"
                                                            min="0" max="9999999999999999" required
                                                            data-format="0[.]00" value="{{ $stock->qty }}">
                                                    </td>
                                                    <td width="10%">
                                                        <input type="number" onblur="CheckBuyPrice(1,this);"
                                                            class="input-sm text-right form-control purchase_price"
                                                            name="purchase_price[]" id='buy_price_id_1' step="any"
                                                            min="0" max="9999999999999999" required
                                                            data-format="0[.]00" data-cell="E1" value="{{ $stock->purchase_price }}">
                                                    </td>
                                                    <td width="10%">
                                                        <input type="number" onblur="CheckSalePrice(1,this);"
                                                            class="input-sm text-right form-control sale_price"
                                                            name="sale_price[]" id='sale_price_id_1' step="any"
                                                            min="0" max="9999999999999999" required
                                                            data-format="0[.]00" data-cell="C1" value="{{ $stock->sale_price }}">
                                                    </td>
                                                    <td width="10%">
                                                        <input type="number" class="form-control input-sm text-right"
                                                            name="product_vat[]" data-cell="V1"
                                                            id='product_vat_id_1' data-format="0[.]00" readonly value="{{ $stock->product_vat }}">
                                                    </td>
                                                    <td width="10%">
                                                        <input class="form-control input-sm text-right" type="number"
                                                            name="product_vat_amount[]" readonly
                                                            data-cell="K1" data-formula="(C1/100*V1)*D1"
                                                            id='product_vat_amount_id_1' data-format="0[.]00" value="{{ $stock->product_vat_amount }}">
                                                    </td>
                                                    <td width="15%">
                                                        <input type="text"
                                                            class="amount form-control input-sm text-right" name="total[]"
                                                            data-cell="F1" data-format="0[.]00"
                                                            data-formula="(E1*D1)" readonly step="any" min="0"
                                                            max="999999999999999" value="{{ $stock->product_total }}">
                                                    </td>
                                                    <td width="10%">
                                                        <input type="button" class="btn btn-success addProduct"
                                                            value="+">
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                            </tfoot>
                                        </table>
                                        <div class="row">&nbsp;</div>
                                        <div class="col-lg-12 col-md-12">
                                            <div class="row">
                                                <!-- accepted payments column -->
                                                <div class="col-lg-8 col-md-8">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p class="lead">Purchase Type:</p>
                                                            <p class="text-muted well well-sm shadow-none"
                                                                style="margin-top: 10px;">
                                                                <select class="form-control select2"
                                                                    name="sale_type_id" id="sale_type_id" readonly
                                                                    required>
                                                                    @if (count($order_types) > 0)
                                                                        @foreach ($order_types as $order_type)
                                                                            <option value="{{ $order_type->id }}">
                                                                                {{ $order_type->name }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="lead">Payment Type:</p>
                                                            <p class="text-muted well well-sm shadow-none"
                                                                style="margin-top: 10px;">
                                                                <select class="form-control select2"
                                                                    name="payment_type_id" id="payment_type_id" readonly
                                                                    required>
                                                                    {{-- <option value="">Select</option> --}}
                                                                    @if (count($payment_types) > 0)
                                                                        @foreach ($payment_types as $payment_type)
                                                                            <option value="{{ $payment_type->id }}" {{ $payment_type->id == '1' ? 'selected':''}}>
                                                                                {{ $payment_type->name }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                                <br/>
                                                                <span>&nbsp;</span>
                                                                <input type="text" name="bank_name" id="bank_name" class="form-control" placeholder="Bank Name">
                                                                <span>&nbsp;</span>
                                                                <input type="text" name="cheque_number" id="cheque_number" class="form-control" placeholder="Cheque Number">
                                                                <span>&nbsp;</span>
                                                                <input type="text" name="transaction_number" id="transaction_number" class="form-control" placeholder="Transaction Number">
                                                                <input type="text" name="note" id="note" class="form-control" placeholder="Note">
                                                                <span>&nbsp;</span>
                                                                <input type="text" name="cheque_date" id="cheque_date" class="datepicker form-control" placeholder="Issue Deposit Date ">
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-4">
                                                    <p class="lead">Amount Section</p>
                                                    <div class="table-responsive">
                                                        <table class="table">
                                                            <tr>
                                                                <th style="width:50%">Subtotal:</th>
                                                                <td>
                                                                    <input type="number" name="sub_total" id="amount"
                                                                        readonly data-cell="G1" data-format="0.00"
                                                                        data-formula="SUM(F1:F5000)" class="form-control"
                                                                        step="any" min="0" max="999999999999" value="{{ $purchase->sub_total }}">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Discount Type</th>
                                                                <td>
                                                                    <select class="form-control" name="discount_type"
                                                                        id="discount_type">
                                                                        <option value="Flat" {{ $purchase->discount_type == "Flat" ? "selected" : "" }}>Flat</option>
                                                                        <option value="Percent" {{ $purchase->discount_type == "Percent" ? "selected" : "" }}>Percent</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr id="discount_percent_div">
                                                                <th>Discount Percent:</th>
                                                                <td>
                                                                    <input type="text" name="discount_percent"
                                                                        id="discount_percent" class="form-control"
                                                                        onkeyup="priceCalculation('')"  value="{{ $purchase->discount_percent }}">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Total Vat:</th>
                                                                <td>
                                                                    <input type="number" name="total_vat" id="total_vat"
                                                                        readonly data-cell="T1" data-format="0.00"
                                                                        data-formula="SUM(K1:K5000)" class="form-control"
                                                                        step="any" min="0"
                                                                        max="9999999999999999"  value="{{ $purchase->total_vat }}">
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <table class="table table-sticky-bg table-responsive"
                                    style="position: sticky;
                                bottom: 0; z-index: 999;">
                                    <tr>
                                        <th>Total Qty</th>
                                        <td>
                                            <input class="input-sm text-right form-control" type="number"
                                                name="total_qty" id='total_qty'
                                                value="{{ $purchase->total_qty}}" data-cell="" step="any" min="0"
                                                max="99999999999999" required data-format="0[.]00" readonly>
                                        </td>

                                        <th>Discount:</th>
                                        <td>
                                            <input type="number" name="discount" id="discount_amount"
                                                class="form-control" onkeyup="priceCalculation('')" step="any"
                                                min="0" max="9999999999999999" value="{{ $purchase->discount}}">
                                        </td>
                                        <th>Total Buy:</th>
                                        <td>
                                            <input type="number" name="grand_total" id="grand_total"
                                                class="form-control" readonly step="any" min="0"
                                                max="9999999999999999" value="{{ $purchase->grand_total}}" />
                                        </td>
                                        <th>Total Sale:</th>
                                        <td>
                                            <input type="number" name="total_sale_price" id="total_sale_price"
                                                class="form-control" readonly step="any" min="0"
                                                max="9999999999999999" value="{{ $purchase->total_sale_price}}" />
                                        </td>
                                        <th>Paid:</th>
                                        <td id="PaidAmount">
                                            {!! Form::number('paid', $purchase->paid_amount, ['id' => 'paid', 'class' => 'form-control', 'step' => 'any']) !!}
                                        </td>
                                        <th>Due:</th>
                                        <td>
                                            {!! Form::number('due', $purchase->due_amount, [
                                                'id' => 'due',
                                                'class' => 'form-control',
                                                'step' => 'any',
                                                'readonly',
                                                'min' => '0',
                                                'max' => '9999999999999999',
                                            ]) !!}
                                        </td>
                                        <td>
                                            <button type="submit" class="btn btn-success"
                                                id="submitbtn SUBMIT_BTN">Submit</button>

                                        </td>
                                    </tr>
                                </table>

                                </form>
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
        function showCustomerForm() {

            var page = "{{ url(Request::segment(1) . '/customers/create') }}";
            var myWindow = window.open(page, "_blank", "scrollbars=yes,width=700,height=1000,top=30");
            // focus on the popup //
            myWindow.focus();
        }



        $(document).ready(function() {

            $('#discount_percent').prop("readonly", true);
            $('#discount_amount').prop("readonly", false);

            //$('.lc_div').hide();
            $('.select2').select2();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            //product fetch for purchase
            $('#product_id_1').select2({
                placeholder: 'Type Product Name',
                minimumInputLength: 1,
                ajax: {
                    type: "POST",
                    url: "{{ url(Request::segment(1) . '/find-product-info') }}",
                    dataType: "JSON",
                    delay: 250,
                    data: function(params) {
                        // console.log('params', params)
                        return {
                            q: params.term,
                            store_id: $('#store_id').val(),
                        };
                    },
                    //mark:select
                    processResults: function(data) {
                        // console.log(data)
                        return {
                            results: $.map(data, function(item) {
                                // console.log('item', item)
                                return {
                                    text: item.name,
                                    id: item.id
                                }
                            })
                        };

                    },
                    cache: true
                }
            });


            $(document).on('click', '.addProduct', function(event) {
                console.log('addProduct')
                // $('.addProduct').on('click', function(event) {
                var product = $('.product_id').html();
                var n = ($('#itemlist tr').length - 0) + 1;
                var tr =
                    '<tr>' +
                    // '<td width="12%"><div><select  class="form-control category_id select2" name="category_id[]" id="category_id_' +
                    // n + '" onchange="getCategoryVal(' + n + ',this);" required>' + category +
                    // '</select></div></td>' +
                    '<td><select class="form-control product_id select2" name="product_id[]" id="product_id_' +
                    n + '" onchange="getval(' + n +
                    ',this);" required  style="width: 100%"></select></td>' +

                    '<td width="12%"><div><select class="form-control unit_id select2" name="unit_id[]" id="unit_id_' +
                    n + '" onchange="getUnitVal(' + n +
                    ',this);" required  style="width: 100%"> required' +
                    '</select></div></td>' +

                    '<td width="12%"><input type="number" class="input-sm text-right form-control quantity" name="qty[]" id="qty_id_' +
                    n + '" required placeholder="0.00" data-cell="D' + n +
                    '" step="any" min="0" max="9999999999999999" data-format="0[.]00" onblur="getQty(' +
                    n + ',this);"></td>' +

                    '<td width="12%"><input type="number"  onblur="CheckBuyPrice(' + n +
                    ',this);" step="any" min="0" max="9999999999999999" class="input-sm text-right form-control purchase_price"  data-format="0[.]00" name="purchase_price[]" id="buy_price_id_' +
                    n + '" data-cell="E' + n + '"   value="" required></td>' +

                    '<td width="12%"><input type="number"  onblur="CheckSalePrice(' + n +
                    ',this);" step="any" min="0" max="9999999999999999" class="input-sm text-right form-control sale_price"  data-format="0[.]00" name="sale_price[]" id="sale_price_id_' +
                    n + '" data-cell="C' + n + '"   value="" required></td>' +

                    '<td width="12%"><input type="number" class="form-control input-sm text-right" placeholder="0.00" data-format="0[.]00" name="product_vat[]" id="product_vat_id_' +
                    n + '"  data-cell="V' + n + '" required readonly></td>' +

                    '<td><input type="number" class="form-control input-sm text-right" placeholder="0.00" name="product_vat_amount[]" id="product_vat_amount_id_' +
                    n + '" readonly data-cell="K' + n + '" data-formula="(C' + n + '/100*V' + n + ')*D' +
                    n + ' " data-format="0[.]00" required></td>' +
                    '<td style="widht:12px"><input class="form-control input-sm text-right" placeholder="0.00" readonly name="total[]"  data-cell="F' +
                    n + '" data-format="0[.]00" data-formula="(E' + n + '*D' + n + ') "></td>' +
                    '<td><span class="d-inline-flex"><input type="button" class="btn btn-success addProduct" value="+" title="Add New"> <input type="button" class="btn btn-danger delete float-left" style="margin-left: 5px" value="x" title="Remove This Product"></span></td>' +
                    '</tr>';

                $('#itemlist').append(tr);
                $form = $('#dynamic').calx();
                $form.calx('update');
                $form.calx('getCell', 'G1').setFormula('SUM(F1:F' + 5000 + ')');
                $form.calx('getCell', 'G1').calculate();

                // search product start
                $('#product_id_' + n).select2({
                    placeholder: 'Type Product Name',
                    minimumInputLength: 1,
                    ajax: {
                        type: "POST",
                        url: "{{ url(Request::segment(1) . '/find-product-info') }}",
                        dataType: "JSON",
                        delay: 250,
                        data: function(params) {
                            // console.log('params', params)
                            return {
                                q: params.term
                            };
                        },
                        processResults: function(data) {
                            //console.log('data2', data)
                            return {
                                results: $.map(data, function(item) {
                                    return {
                                        text: item.name,
                                        id: item.id
                                    }
                                })
                            };

                        },
                        cache: true
                    }
                });
                $('#product_id_' + n).select2('open').trigger('select2:open');
                // search product end
            });

            $(document).on('keydown', '#itemlist tr:last .purchase_price', function(e) {
                console.log('keydown');
                if (e.keyCode == 9) {
                    var product = $('.product_id').html();
                    var n = ($('#itemlist tr').length - 0) + 1;
                    var tr =
                        '<tr><td><select class="form-control product_id select2" name="product_id[]" id="product_id_' +
                        n + '" onchange="getval(' + n +
                        ',this);" required ></select></td>' +

                        '<td width="12%"><div><select class="form-control unit_id select2" name="unit_id[]" id="unit_id_' +
                        n + '" onchange="getUnitVal(' + n +
                        ',this);" required > required' +
                        '</select></div></td>' +

                        '<td width="12%"><input type="number" class="input-sm text-right form-control quantity" name="qty[]" id="qty_id_' +
                        n + '" required placeholder="0.00" data-cell="D' + n +
                        '" step="any" min="0" max="9999999999999999" data-format="0[.]00" onblur="getQty(' +
                        n + ',this);"><span id="show_stock_qty_' + n + '"></span></td>' +

                        '<td width="12%"><input type="number"  onblur="CheckSalePrice(' + n +
                        ',this);" step="any" min="0" max="9999999999999999" class="input-sm text-right form-control sale_price"  data-format="0[.]00" name="sale_price[]" id="sale_price_id_' +
                        n + '" data-cell="c' + n +
                        '"   value="" required>MSP <span id="show_min_sale_price_' +
                        n + '"></span></td>' +

                        '<td width="12%"><input type="number" class="form-control input-sm text-right" placeholder="0.00" data-format="0[.]00" name="product_vat[]" id="product_vat_id_' +
                        n + '"  data-cell="V' + n + '" required readonly></td>' +

                        '<td><input type="number" class="form-control input-sm text-right" placeholder="0.00" name="product_vat_amount[]" id="product_vat_amount_id_' +
                        n + '" readonly data-cell="K' + n + '" data-formula="(C' + n + '/100*V' + n +
                        ')*D' +
                        n + ' " data-format="0[.]00" required></td>' +
                        '<td style="widht:12px"><input class="form-control input-sm text-right" placeholder="0.00" readonly name="total[]"  data-cell="F' +
                        n + '" data-format="0[.]00" data-formula="(C' + n + '*D' + n + ') "></td>' +
                        '<td><span class="d-inline-flex"><input type="button" class="btn btn-success addProduct" value="+" title="Add New"> <input type="button" class="btn btn-danger delete float-left" style="margin-left: 5px" value="x" title="Remove This Product"></span></td>' +
                        '</tr>';

                    $('#itemlist').append(tr);
                    $form = $('#dynamic').calx();
                    $form.calx('update');
                    $form.calx('getCell', 'G1').setFormula('SUM(F1:F' + 5000 + ')');
                    $form.calx('getCell', 'G1').calculate();

                    // search product start
                    $('#product_id_' + n).select2({
                        placeholder: 'Type Product Name',
                        minimumInputLength: 1,
                        ajax: {
                            type: "POST",
                            url: "{{ url(Request::segment(1) . '/findproductforvansale') }}",
                            dataType: "JSON",
                            delay: 250,
                            data: function(params) {
                                // console.log('params', params)
                                return {
                                    q: params.term
                                };
                            },
                            processResults: function(data) {
                                //console.log('data2', data)
                                return {
                                    results: $.map(data, function(item) {
                                        return {
                                            text: item.name,
                                            id: item.id
                                        }
                                    })
                                };

                            },
                            cache: true
                        }
                    });

                    $('#product_id_' + n).select2('open').trigger('select2:open');
                }
            });


            //new item
            $('#itemlist').delegate('.delete', 'click', function() {
                $(this).parent().parent().parent().remove();
                $form = $('#dynamic').calx();
                $form.calx('update');
                $form.calx('getCell', 'G1').setFormula('SUM(F1:F' + 5000 + ')');
                $form.calx('getCell', 'G1').calculate();
                $form.calx('getCell', 'T1').setFormula('SUM(K1:K' + 5000 + ')');
                $form.calx('getCell', 'T1').calculate();

                var total_vat = $("#total_vat").val();
                var sub_total = $("#amount").val();
                var total_sale_price = parseFloat(sub_total) + parseFloat(total_vat);
                $('#total_sale_price').val(total_sale_price);
            });

            $('#name,#date').on("change", function() {
                if ($('#name').val() !== null) {
                    $.ajax({
                        type: "POST",
                        url: "{{ url(Request::segment(1) . '/check-customer-limit') }}",
                        data: {
                            customer_user_id: $('#name').val(),
                            date: $('#date').val(),
                        },
                        success: function(data) {
                            $('#customerDue').html(data.dueInfo);
                            if (data.status == 'credit_off') {
                                $('#submitbtn').prop("disabled", true);
                                Swal.fire({
                                    position: 'center',
                                    icon: 'info',
                                    title: data.message,
                                    showConfirmButton: false,
                                    showCloseButton: true
                                })

                            } else {
                                $('#submitbtn').prop("disabled", false);
                            }

                        }
                    });
                }

            });

        });

        function getval(row, sel) {
            console.log('getval')
            // alert(sel.value);
            var current_row = row;
            var store_id = $('#store_id').val();
            var current_product_id = sel.value;


            if (current_row > 1) {
                for (let index = 1; index < current_row; index++) {
                    var previous_product_id = $(('#product_id_' + index)).val();
                    var current_product_id = $('#product_id_' + current_row).val();
                    if (previous_product_id === current_product_id) {
                        $('#product_id_' + current_row).val('');
                        alert('You selected same product, Please selected another product!');
                        return false;
                    }
                }
            }

            $.ajax({
                url: "{{ URL(Request::segment(1) . '/purchase-relation-data') }}",
                method: "get",
                data: {
                    current_product_id: current_product_id
                },

                success: function(res) {
                    // console.log('res',res)
                    $(("#unit_id_" + current_row)).html(res.data.unitOptions);
                },
                error: function(err) {
                    console.log(err)
                }
            })

            //focus
        }

        function quantitySum(){
            console.log('quantitySum')
            var t = parseInt(0);
            $('.quantity').each(function(i,e){
                var amt = $(this).val();
                t += parseInt(amt);
            });
            $('#total_qty').val(t);
        }

        function buyPriceSum(){
            console.log('buyPriceSum')
            var t = parseFloat(0);
            $('.purchase_price').each(function(i,e){
                console.log('i',i)
                index = i + 1;
                var amt = $(this).val();
                t += parseFloat(amt) * parseFloat($('#qty_id_' + index).val());
            });
            console.log('t',t)
            $('#amount').val(t);
            $('#grand_total').val(t);
            $('#paid').val(t);
            $('#due').val(0);
        }

        //onkeyup
        function getQty(row, sel) {
            console.log('getQty')
            var inputValue = parseFloat(sel.value);
            var stock_qty = parseFloat($('#show_stock_qty_' + row).html());
            if (inputValue > stock_qty) {
                $('#qty_id_' + row).val('');
                Swal.fire('Out Of Stock', 'Try Again');

                $form = $('#dynamic').calx();
                $form.calx('update');
                $form.calx('getCell', 'G1').setFormula('SUM(F1:F' + 5000 + ')');
                $form.calx('getCell', 'G1').calculate();
                $form.calx('getCell', 'T1').setFormula('SUM(K1:K' + 5000 + ')');
                $form.calx('getCell', 'T1').calculate();
                var sub_total = $("#amount").val();
                var total_vat = $("#total_vat").val();
                var total_sale_price = parseFloat(sub_total) + parseFloat(total_vat);
                $('#total_sale_price').val(total_sale_price);
                return false
            }

            $form = $('#dynamic').calx();
            $form.calx('update');
            $form.calx('getCell', 'G1').setFormula('SUM(F1:F' + 5000 + ')');
            $form.calx('getCell', 'G1').calculate();
            $form.calx('getCell', 'T1').setFormula('SUM(K1:K' + 5000 + ')');
            $form.calx('getCell', 'T1').calculate();
            var sub_total = $("#amount").val();
            var total_vat = $("#total_vat").val();
            var total_sale_price = parseFloat(sub_total) + parseFloat(total_vat);
            $('#total_sale_price').val(total_sale_price);

            quantitySum();
        }

        $('#discount_type').on('change', function(event) {
            console.log('discount_type')
            //event.preventDefault();
            var discount_type = $('#discount_type').val();
            var total_vat = $("#total_vat").val();
            var sub_total = $("#amount").val();

            if (discount_type === 'Flat') {
                // $('#discount_percent_div').hide();
                $('#discount_percent').prop("readonly", true); // Element(s) are now enabled.
                $('#discount_amount').prop("readonly", false);
                $('#discount_percent').val('');
                $('#discount_amount').val('');
                $('#total_sale_price').val(total_sale_price);
            } else {
                // $('#discount_percent_div').show();
                $('#discount_percent').prop("readonly", false); // Element(s) are now enabled.
                $('#discount_amount').prop("readonly", true);
                $('#discount_amount').val('');
                $('#total_sale_price').val(total_sale_price);
            }

            var discount_amount = parseFloat($('#discount_amount').val());
            var total_sale_price = parseFloat(sub_total) + parseFloat(total_vat);
            if (sub_total > discount_amount) {
                alert('You Can Not Discount More than Subtotal Price !');
                return false;
            }
        })

        function CheckBuyPrice(row, sel) {
            console.log('CheckBuyPrice')
            buyPriceSum();
        }

        function sellPriceSum(){
            console.log('sellPriceSum')
            var t = parseFloat(0);
            $('.sale_price').each(function(i,e){
                // console.log('i',i)
                index = i + 1;
                var amt = $(this).val();
                t += parseFloat(amt) * parseFloat($('#qty_id_' + index).val());
            });
            // console.log('t',t)
            $('#total_sale_price').val(t);
        }

        function CheckSalePrice(row, sel) {
            console.log('check price');
            sellPriceSum();
            // var inputValue = parseFloat(sel.value);

            // $form = $('#dynamic').calx();
            // $form.calx('update');
            // $form.calx('getCell', 'G1').setFormula('SUM(F1:F' + 5000 + ')');
            // $form.calx('getCell', 'G1').calculate();
            // $form.calx('getCell', 'T1').setFormula('SUM(K1:K' + 5000 + ')');
            // $form.calx('getCell', 'T1').calculate();

            // var sub_total = $("#amount").val();
            // var total_vat = $("#total_vat").val();
            // var total_sale_price = parseFloat(sub_total) + parseFloat(total_vat);
            // $('#total_sale_price').val(total_sale_price);
        }


        // $(window).keydown(function(e) {
        //     priceCalculation();
        // });
        // $(window).click(function(e) {
        //     priceCalculation();
        // });

        function priceCalculation() {
            console.log('priceCalculation');
            var discount_type = $('#discount_type').val();

            var sub_total = $("#amount").val();
            var total_vat = $("#total_vat").val();
            var total_sale_price = parseFloat(sub_total) + parseFloat(total_vat);
            //total_sale_price = parseFloat(total_sale_price);

            if (discount_type == 'Flat') {
                var discount_amount = $('#discount_amount').val();
                if (discount_amount !== '') {
                    discount_amount = parseFloat(discount_amount);
                    var discount = total_sale_price - discount_amount;
                    var final_amount = discount;
                } else {
                    var final_amount = total_sale_price;
                }
                $('#discount_percent').val('');
            } else {
                var discount_percent = $('#discount_percent').val();
                if (discount_percent !== '') {
                    discount_percent = parseFloat(discount_percent);
                    var discount = (total_sale_price * discount_percent) / 100;
                    var final_amount = total_sale_price - discount;
                } else {
                    var final_amount = total_sale_price;
                }
                $('#discount_amount').val(discount);
            }

            $('#total_amount').val(total_sale_price);
            $('#total_sale_price').val(final_amount);
            if (sub_total < discount_amount) {
                alert('You Can Not Discount More than Subtotal Price !');
                $('#discount_amount').val(sub_total);
                priceCalculation();
            }

            var sale_type_id = $('#sale_type_id').val();
            if (sale_type_id === '2') {
                // console.log(2)
                var paid = $("#paid").val(0);
                $('#due').val(final_amount);
                $('#paid').prop("readonly", true);
                $("#payment_type_id").html('<option value="">NULL</option>');
                $('#payment_type_id').prop("readonly", true);
            } else {
                // console.log(1)
                var paid = $("#paid").val(final_amount);
                $('#due').val(0);
            }
        }

        $('#sale_type_id').change(function() {
            priceCalculation();
        });

        $('#payment_type_id').change(function() {
            priceCalculation();
        });

        //automatically call after two seconds/
        //tab index changing
        $('#date').on("keydown", function() {
            $('#product_id_1').select2('open').trigger('select2:open');
        });


        function showProductForm() {
            var page = "{{ url(Request::segment(1) . '/stock-transfer-warehouse-to-van') }}";
            var myWindow = window.open(page, "_blank", "scrollbars=yes,width=700,height=1000,top=30");
            // focus on the popup //
            myWindow.focus();
        }

        $(function() {
            $('#note').hide();
            $('#transaction_number').hide();
            $('#bank_name').hide();
            $('#cheque_number').hide();
            $('#cheque_date').hide();
            $('#payment_type_id').change(function(){
                if($('#payment_type_id').val() == '3') {
                    $('#bank_name').show();
                    $('#cheque_number').show();
                    $('#cheque_date').show();
                    $('#transaction_number').hide();
                    $('#note').hide();
                }else if($('#payment_type_id').val() == '2') {
                    $('#transaction_number').show();
                    $('#bank_name').hide();
                    $('#cheque_number').hide();
                    $('#cheque_date').hide();
                    $('#note').hide();
                }else if($('#payment_type_id').val() == '4') {
                    $('#note').show();
                    $('#bank_name').hide();
                    $('#cheque_number').hide();
                    $('#cheque_date').hide();
                    $('#transaction_number').hide();
                } else {
                    $('#note').val('');
                    $('#note').hide();
                    $('#transaction_number').hide();
                    $('#bank_name').val('');
                    $('#bank_name').hide();
                    $('#cheque_number').val('');
                    $('#cheque_number').hide();
                    $('#cheque_date').hide();
                }
            });
        });
    </script>
@endpush
