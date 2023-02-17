@extends('backend.layouts.master')
@section("title","Product Details")
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Product Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">{{request()->path()}}</li>
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
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Product Details</h3>
                            <div class="float-right">
                                <a href="{{ route(Request::segment(1).'.products.index') }}">
                                    <button class="btn btn-success">
                                        <i class="fa fa-plus-circle"></i>
                                        Back
                                    </button>
                                </a>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <h6><strong>Name: </strong> {{$product->name}}</h6>
                                    <h6><strong>Code: </strong> {{$product->code}}</h6>
                                    <h6><strong>Unit / Measurement : </strong> {{@$product->unit_measurement}}</h6>
                                    <h6><strong>Average Purchase Price: </strong> {{$product->average_purchase_price}}
                                    </h6>
                                    <h6><strong>Category: </strong> {{$product->category->name}}</h6>
                                    <h6><strong>Brand: </strong> {{$product->brand->name}}</h6>
                                    <h6><strong>Created By: </strong> {{$product->created_by_user->name}}</h6>
                                </div>
                                <div class="col-lg-6">
                                    <h6><strong>Barcode: </strong> {{$product->barcode}}</h6>
                                    <h6><strong>Low Inventory Alert: </strong> {{$product->low_inventory_alert}}</h6>
                                    <h6><strong>Status: </strong> {{$product->status == 0 ? 'Inactive' : 'Active'}}</h6>
                                    <h6><strong>Unit Variant: </strong> {{$product->unit_variant == 0 ? 'NO' : 'YES'}}
                                    </h6>
                                    <h6><strong>Unit: </strong> {{$product->unit->name}}</h6>
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-body">
                                    <table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th>#Id</th>
                                             <th>Unit</th>
                                            <th>Local Purchase</th>
                                            <th>International Purchase</th>
                                            <th>Local Sale Price</th>
                                            <th>Minimum Local Sale Price</th>
                                            <th>Outer Sale Price</th>
                                            <th>Minimum Outer Sale Price</th>
                                            <th>Warehouse Sale Price</th>
                                            <th>Minimum Warehouse Sale Price</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($product_price as $key => $price)
                                            <tr>

                                                <td>{{$key + 1}}</td>
                                                <td> {{@$price->unit->name}}</td>
                                                <td> {{@$price->local_purchase_price}}</td>
                                                <td> {{@$price->international_purchase_price}}</td>
                                                <td> {{@$price->local_sale_price}}</td>
                                                <td> {{@$price->minimum_local_sale_price}}</td>
                                                <td> {{@$price->outer_sale_price}}</td>
                                                <td> {{@$price->minimum_outer_sale_price}}</td>
                                                <td> {{@$price->warehouse_sale_price}}</td>
                                                <td> {{@$price->minimum_warehouse_sale_price}}</td>
                                                
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
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
