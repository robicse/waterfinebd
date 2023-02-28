<?php
$email_required = '';
?>
<div class="row">
    @include('backend.common.component.category')
    @include('backend.common.component.unit')
    @include('backend.common.component.name')
</div>
@if($product)
@include('backend.common.component.update')
@else
@include('backend.common.component.submit')
@endif
