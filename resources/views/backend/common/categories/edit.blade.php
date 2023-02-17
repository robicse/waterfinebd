@extends('backend.layouts.master')
@section("title","Category Edit")
@push('css')
    <link rel="stylesheet" href="{{asset('backend/css/custom.css')}}">
@endpush
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Category</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route(Request::segment(1).'.dashboard')}}">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Categories</li>
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
                            <h3 class="card-title">Category Edit</h3>
                            <div class="float-right">
                                <a href="{{ route(Request::segment(1).'.categories.index') }}">
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
                            {!! Form::model($category, array('route' =>[Request::segment(1).'.categories.update',$category->id],'method'=>'PUT','files'=>true)) !!}
                                <div class="form-group">
                                    <label for="name">Name <span class="required">*</span></label>
                                    {!! Form::text('name', $category->name, [
                                            'id' => 'name',
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => 'Enter Name',
                                        ]) !!}
                                </div>
                                <div class="form-group">
                                    <label for="inputStatus">Status <span
                                        class="required">*</span></label>
                                    {!! Form::select('status', [1 => 'Active', 0 => 'In-Active'], $category->status, ['id' => 'status', 'class' => 'form-control', 'required','placeholder' => 'Select One']) !!}
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            {!! Form::close()!!}
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
