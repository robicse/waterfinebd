<?php
$status = '1';
?>
<div class="row">
    @include('backend.common.component.name')
    @include('backend.common.component.status',['id'=>$status])
</div>
@if($category)
@include('backend.common.component.update')
@else
@include('backend.common.component.submit')
@endif
