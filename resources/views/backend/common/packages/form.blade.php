<?php
$email_required = '';
$status = '1';
?>
<div class="row">
    @include('backend.common.component.name')
    @include('backend.common.component.amount')
    @include('backend.common.component.status')
</div>
{{-- @if($package)
@include('backend.common.component.update')
@else
@include('backend.common.component.submit')
@endif --}}
