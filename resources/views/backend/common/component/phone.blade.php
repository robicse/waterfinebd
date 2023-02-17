<div class="form-group col-md-4">
    <label for="phone" class="form-control-label">Phone * </label>
    {!! Form::text('phone', null, ['id' => 'phone', 'class' => 'form-control', 'required']) !!}
    @if ($errors->has('phone'))
        <span class="text-danger alert">{{ $errors->first('phone') }}</span>
    @endif
</div>
