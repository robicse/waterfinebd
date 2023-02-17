<div class="form-group col-md-4">
    <label for="email" class="form-control-label">Email * </label>
    {!! Form::text('email', null, ['id' => 'email', 'class' => 'form-control', 'required']) !!}
    @if ($errors->has('email'))
        <span class="text-danger alert">{{ $errors->first('email') }}</span>
    @endif
</div>
