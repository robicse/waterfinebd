<div class="row">
    <div class="form-group col-md-4">
        <label for="name" class="form-control-label">FUll Name * </label>
        {!! Form::text('name', null, ['id' => 'name', 'class' => 'form-control', 'required']) !!}
        @if ($errors->has('name'))
            <span class="text-danger alert">{{ $errors->first('name') }}</span>
        @endif
    </div>
    <div class="form-group col-md-4">
        <label for="username" class="form-control-label">User Name * </label>
        {!! Form::text('username', null, ['id' => 'username', 'class' => 'form-control', 'required']) !!}
        @if ($errors->has('username'))
            <span class="text-danger alert">{{ $errors->first('username') }}</span>
        @endif
    </div>
    @include('backend.common.component.email')
    @include('backend.common.component.phone')
    <div class="form-group col-md-4">
        <label for="role">User Type  <span class="required">*</span></label>
        @if(@isset($user))
            {!! Form::select('roles',$roles,@$user->getRoleNames(), ['class'=>'form-control','id'=>'roles','required']) !!}
        @else
            {!! Form::select('roles',$roles,null, ['class'=>'form-control','id'=>'roles','required']) !!}
        @endif
    </div>
    <div class="form-group col-md-4">
        <label for="example-text-input" class="form-control-label">Image *</label>
        @if(@isset($user))
        <span> <img src="{{asset($user->image)}}" alt=""  height="30px" width="30px"></span>
        @endif
        <input class="form-control" type="file" name="image" accept="image/png, image/jpeg,image/webp">
        @if ($errors->has('image'))
            <span class="text-danger alert">{{ $errors->first('image') }}</span>
        @endif
    </div>
</div>
@if(@isset($user))
@else
<div class="row">
    @include('backend.common.component.password')
    @include('backend.common.component.confirm_password')
</div>
@endif
