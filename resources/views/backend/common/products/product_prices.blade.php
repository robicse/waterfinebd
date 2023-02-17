@if(count($unit_sets) > 0)
    <div class="form-group row">
        <label for="unit_set_package_ids">Unit Set</label><br/>
        <select class="form-control demo-select2" name="unit_set_ids[]" id="unit_set_ids" multiple>
            @foreach($unit_sets as $unit_set)
                <option value="{{$unit_set->id}}">{{$unit_set->name}}
                    => {{@$unit_set->variant_unit_qty}} {{@$unit_set->unit->name}}</option>
            @endforeach
        </select>
    </div>
@endif
