<?php
$email_required = '';
$status = '1';
?>
<div class="row">
    @include('backend.common.component.category')
    @include('backend.common.component.unit')
    @include('backend.common.component.name')
    @include('backend.common.component.status')
</div>
@if($product)
@include('backend.common.component.update')
@else
@include('backend.common.component.submit')
@endif
