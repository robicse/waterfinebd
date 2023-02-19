<?php
$email_required = '';
$status = '1';
?>
<div class="row">
    @include('backend.common.component.name')
    @include('backend.common.component.phone')
    @include('backend.common.component.email',['required'=>$email_required])
    @include('backend.common.component.start_date')
    @include('backend.common.component.status',['id'=>$status])
    @include('backend.common.component.address')
</div>
@if($supplier)
@include('backend.common.component.update')
@else
@include('backend.common.component.submit')
@endif
