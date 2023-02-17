@extends('backend.layouts.master')
@section("title","Product Create")
@push('css')
<link rel="stylesheet" href="{{asset('backend/css/custom.css')}}">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@10.10.1/dist/sweetalert2.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" integrity="sha512-aOG0c6nPNzGk+5zjwyJaoRUgCdOrfSDhmMID2u4+OIslr0GjpLKo7Xm0Ao3xmpM4T8AmIouRkqwj1nrdVsLKEQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
                        <li class="breadcrumb-item"><a href="{{route(Request::segment(1).'.dashboard')}}">Home</a>
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
                            <h3 class="card-title">Product Barcode</h3>
                            <div class="float-right">
                                <a href="{{ route(Request::segment(1).'.products.index') }}">
                                    <button class="btn btn-success">
                                        <i class="fa fa-plus-circle"></i>
                                        Back
                                    </button>
                                </a>
                            </div>
                        </div>
                        {!! Form::open(array('url' => Request::segment(1).'/barcode-prints','method'=>'POST')) !!}
                        
                        <div class="row">
                            <div class="col-lg-12 col-md-12">
                                <div class="well pb-3 mt-1">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px; font-size: 40px">
                                            <i class="fa fa-barcode addIcon"></i></div>
                                            <input type="text" name="add_item"   value=""  style="padding: 20px;font-size:2em;color:blueviolet" class="form-control ui-autocomplete-input" id="add_item" placeholder="Please add products to order list" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            <div class="col-lg-12 col-md-12">
                                <div id="dynamic" class="row bg-light">
                                    <table class="table table-responsive">
                                            <thead>
                                                <tr>
                                                    <th style="width: 60%">Product <span class="required">*</span></th>
                                                   <th>Barcode Qty</th>
                                                   <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="itemlist">


                                            </tbody>
                                            
                                        </table>

                        </div>
                        </div>


                            <div class="card-footer float-right">
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

@push('js')
  
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('backend/jquery-calx-sample-2.2.8.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
   
    $("#add_item").autocomplete({
        source: function (request, response) {
            
            $.ajax({
                type: 'get',
                url: "{{ url(Request::segment(1)) }}" + '/findproductforbarcode',
                data: {
                    term: request.term,
                },
                success: function (data) {
                    $(this).removeClass('ui-autocomplete-loading');
                    response(data);
                }
            });
        },
        minLength: 1,
        autoFocus: false,
        delay: 250,
        response: function (event, ui) {
            if ($(this).val().length >= 10 && ui.content== 0) {
                Swal.fire({
                title:'No matching result found!',
                text:'Sorry',
                timer: 2000,
                showConfirmButton: false});
            }
            else if (ui.content.length == 1 && ui.content != 0) {
                ui.item = ui.content[0];
                $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                $(this).autocomplete('close');
                $(this).removeClass('ui-autocomplete-loading');
            }
        },
        select: function (event, ui) {
            event.preventDefault();
            if (ui.item.id !== 0) {
                var row = ui.item;
                $(this).val('');
                console.log('row',row)
                if($('#id' + row.id).serializeArray().length){
                    $("#id"+row.id).val(parseInt($("#id"+row.id).val())+1);
                    $itemlist   = $('#itemlist');
                    $counter    = 0;
                    $counter = $("#itemlist tr").length;
                    var i = ++$counter;

                    $form = $('#dynamic').calx();
                    $form.calx('update');
                    $form.calx('getCell', 'G1').setFormula('SUM(F1:F'+i+')');
                    $form.calx('getCell', 'G1').calculate();
                }
                else{
                    $itemlist   = $('#itemlist');
                    $counter    = 0;
                    $counter = $("#itemlist tr").length;
                    var i = ++$counter;
                    $itemlist.append( '<tr>\<td> <input class="form-control input-sm text-right" type="hidden"  name="product_barcode[]" value="'+row.barcode+'" data-format="0" >\ '+row.value+' \</td>\<input name="product_name[]" type="hidden"  value="'+row.value+'"></td>\
                        <td><input type="number" class="form-control input-sm text-left" name="barcodequentity[]" data-cell="C'+i+'" id="id'+row.id+'" data-format=""></td>\
                    <td class="text-center"><button class="btn-remove btn btn-sm btn-danger"><i class="fa fa-times fa-fw"></i></button></td>\
                    </tr>');
                    $("#id"+row.id).val(1);
                   
                }
            }
            else {
                Swal.fire('No matching result found! Product might be out of stock in the selected warehouse.');
            }
            $('#itemlist').on('click', '.btn-remove', function(){
                $(this).parent().parent().parent().remove();
              
            });
        }
    });
});



    </script>
@endpush
