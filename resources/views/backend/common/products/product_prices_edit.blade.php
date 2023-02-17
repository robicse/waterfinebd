@php
    //dd($unit_set_package_ids);
    //$price = \App\Model\InsurancePrice::where('insurance_type_id',$insuranceType->id)->where('insurance_package_id',$insurancePackage->id)->first();
@endphp

@if(count($unit_sets) > 0)
    <div class="form-group row">
        <label for="unit_set_package_ids">Unit Set</label><br/>
        <select class="form-control demo-select2" name="unit_set_ids[]" id="unit_set_ids" multiple>
            @foreach($unit_sets as $unit_set)
                @php
                    $unit_set_ids_arr = json_decode($unit_set_ids);
                @endphp
                <option
                    value="{{$unit_set->id}}" {{ in_array($unit_set->id,$unit_set_ids_arr) ? 'selected' : ''}}>{{$unit_set->name}}
                    => {{@$unit_set->variant_unit_qty}} {{@$unit_set->unit->name}}</option>
            @endforeach
        </select>
    </div>
@endif
