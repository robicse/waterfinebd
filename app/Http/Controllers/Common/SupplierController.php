<?php

namespace App\Http\Controllers\Common;

use DataTables;
use App\Models\User;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Helpers\ErrorTryCatch;
use App\Imports\SupplierImport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class SupplierController extends Controller
{
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->User = Auth::user();
            if ($this->User->status == 0) {
                $request->session()->flush();
                return redirect('login');
            }
            return $next($request);
        });
        $this->middleware('permission:suppliers-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:suppliers-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:suppliers-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:suppliers-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $User=$this->User;
        try {
            if ($request->ajax()) {
                $suppliers = Supplier::latest();
                return Datatables::of($suppliers)
                    ->addIndexColumn()
                    ->addColumn('action', function ($supplier) use($User) {
                        $btn='';
                        //$btn = '<span  class="d-inline-flex"><a href=' . route(\Request::segment(1) . '.suppliers.show', $supplier->id) . ' class="btn btn-warning btn-sm waves-effect"><i class="fa fa-eye"></i></a>';
                        if($User->can('suppliers-edit')){
                        $btn .= '<a href=' . route(\Request::segment(1) . '.suppliers.edit', $supplier->id) . ' class="btn btn-info waves-effect btn-sm float-left" style="margin-left: 5px"><i class="fa fa-edit"></i></a>';
                        }
                        $btn .= '</span>';
                        return $btn;
                    })
                    ->addColumn('status', function ($supplier) {
                        if ($supplier->status == 0) {
                            return '<span class="badge badge-danger"> <i class="fa fa-ban"></i> </span>';
                        } else {
                            return '<span class="badge badge-success"><i class="fa fa-check-square"></i></span>';
                        }
                    })
                    ->rawColumns(['action', 'status'])
                    ->make(true);
            }

            return view('backend.common.suppliers.index');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function create()
    {
        return view('backend.common.suppliers.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'phone' => 'required|unique:users'
        ]);

        try {
            DB::beginTransaction();
            $supplier = new Supplier();
            $supplier->name = $request->name;
            $supplier->phone = $request->phone;
            $supplier->email = $request->email;
            $supplier->start_date = $request->start_date;
            $supplier->address = $request->address;
            $supplier->created_by_user_id = $this->User->id;
            $supplier->save();
            DB::commit();
            Toastr::success('Store Update Successfully', 'Success');
            return redirect()->route(request()->segment(1) . '.suppliers.index');
        }catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('backend.common.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'phone' => 'required|unique:users,phone,' . $id,
        ]);

        try {
        $supplier = Supplier::find($id);
        $supplier->name = $request->name;
        $supplier->phone = $request->phone;
        $supplier->email = $request->email;
        $supplier->address = $request->address;
        $supplier->updated_by_user_id = Auth::User()->id;
        $supplier->save();

        Toastr::success("Supplier Updated Successfully", "Success");
        return redirect()->route(\Request::segment(1) . '.suppliers.index');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function destroy($id)
    {
        //
    }

    public function supplierExcelStore(Request $request){
        Excel::import(new SupplierImport, $request->file('supplier'));

        Toastr::success("Supplier Created", "Success");
        return redirect()->back();
    }
}
