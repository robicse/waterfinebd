<?php

namespace App\Http\Controllers\Common;


use Illuminate\Http\Request;
use App\Helpers\ErrorTryCatch;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

class CommonController extends Controller
{
    public function updateStatus(Request $request)
    {
        $data = DB::table($request->tableName)->where('id', $request->id)
        ->update(['status'=>$request->status]);
        if ($data) {
            return 1;
        }
        return 0;
    }
}
