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
        // try {
            if ($request->ajax()) {
                $suppliers = Supplier::latest();
                return Datatables::of($suppliers)
                    ->addIndexColumn()
                    ->addColumn('action', function ($supplier) use($User) {
                        $btn='';
                        $btn = '<span  class="d-inline-flex"><a href=' . route(\Request::segment(1) . '.suppliers.show', $supplier->id) . ' class="btn btn-warning btn-sm waves-effect"><i class="fa fa-eye"></i></a>';
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
        // } catch (\Exception $e) {
        //     $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
        //     Toastr::error($response['message'], "Error");
        //     return back();
        // }
    }

    public function create()
    {
        if ($this->User->user_type === 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            $routes = Route::wherestatus(1)->select('name', 'id')->get();
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            $routes = Route::wherewarehouse_id($this->User->warehouse_id)->wherestatus(1)->select('name', 'id')->get();
        }


        return view('backend.common.suppliers.create', compact('warehouses', 'routes'));
    }

    public function store(Request $request)
    {
        //dd($request->all());

        $this->validate($request, [
            'name' => 'required',
            'phone' => 'required|unique:users',
            'country_code' => 'required',
            'type' => 'required',
            /* 'address' => 'required', */
            'email' => 'required|email|unique:users',
            'credit_limit' => 'required|numeric|min:0|max:9999999999999999',
            'days_limit' => 'required|numeric|min:0|max:9999999999999999',
            'previous_balance' => 'required|numeric|min:0|max:9999999999999999',
            'status' => 'required',
        ]);

        //try {
        DB::beginTransaction();
        $get_supplier_code = Supplier::orderBy('id', 'desc')->pluck('code')->first();
        if (!empty($get_supplier_code)) {
            $get_supplier_code_after_replace = str_replace("SUP", "", $get_supplier_code);
            $supplier_code = (int)$get_supplier_code_after_replace + 1;
        } else {
            $supplier_code = 1;
        }
        $final_supplier_code = 'SUP' . $supplier_code;

        // user table data start
        $user = userDataInsert($request->all(), 'Supplier');
        $warehouse_id = $request->warehouse_id;
        // user table data end

        if ($user) {

            $supplier = new Supplier();
            $supplier->user_id = $user->id;
            $supplier->type = $request->type;
            $supplier->code = $final_supplier_code;
            $supplier->vat_no = $request->vat_no;
            $supplier->credit_limit = $request->credit_limit;
            $supplier->days_limit = $request->days_limit;
            $supplier->payment_terms = $request->payment_terms;
            //$supplier->payment_type_id = $request->payment_type_id;
            $supplier->supplier_location = $request->supplier_location;
            $supplier->comercial_registration_no = $request->comercial_registration_no;
            $supplier->bank_accounts_details = $request->bank_accounts_details;
            $supplier->product_groups = $request->product_groups;
            $supplier->created_by_user_id = Auth::User()->id;
            $supplier->updated_by_user_id = Auth::User()->id;
            $supplier->save();
            $insert_id = $supplier->id;
            $warehouse_id = $request->warehouse_id;
            if ($insert_id) {
                $headAccountDetail  = WarehouseChartOfAccount::wherewarehouse_id($warehouse_id)->where('head_name', 'ACCOUNT PAYABLES')->orderBy('id', 'desc')->first();
                if (empty($headAccountDetail)) {
                    Toastr::error('Sorry Chart Of Account Not Ready To Create Account Payble', "Error");
                    return back();
                }

                $parentHeadAccountDetail = WarehouseChartOfAccount::wherewarehouse_id($warehouse_id)->where('parent_head_name', 'ACCOUNT PAYABLES')->orderBy('id', 'desc')->first();
                if (empty($parentHeadAccountDetail)) {
                    $head_code = $headAccountDetail->head_code . '00000001';
                } else {
                    $head_code = $parentHeadAccountDetail->head_code + 1;
                    // dd($head_code); exit;

                }

                $head_name = $request->name;
                $parent_head_name = 'ACCOUNT PAYABLES';
                $head_level = $headAccountDetail->head_level + 1;
                $head_type = $headAccountDetail->head_type;
                $supplier_chart_of_account = new WarehouseChartOfAccount();
                $supplier_chart_of_account->prefix = 'SUP' . $user->id;
                $supplier_chart_of_account->user_id = $user->id;
                $supplier_chart_of_account->warehouse_id = $warehouse_id;
                $supplier_chart_of_account->warehouse_name = $headAccountDetail->name;
                $supplier_chart_of_account->head_code = $head_code;
                $supplier_chart_of_account->head_name =  $head_name;
                $supplier_chart_of_account->parent_head_name = $parent_head_name;
                $supplier_chart_of_account->head_type =   $head_type;
                $supplier_chart_of_account->head_level =  $head_level;
                $supplier_chart_of_account->created_by_user_id = Auth::User()->id;
                $supplier_chart_of_account->updated_by_user_id = Auth::User()->id;
                $supplier_chart_of_account->save();
            }
            $previous_balance = $request->previous_balance;
            if ($previous_balance > 0) {
                // posting
                $month = date('m');
                $year = date('Y');
                $transaction_date_time = date(' H:i:s');
                $date = date("Y-m-d");
                $login_user_id = Auth::user()->id;
                $supplier_user_id = $user->id;

                // Cash In Hand For Opening Balance
                $get_voucher = VoucherType::where('name', 'Opening Balance')->first();
                $voucher_type_id = $get_voucher->id;
                $voucher_type_name = $get_voucher->name;
                $get_voucher_no = ChartOfAccountTransaction::where('voucher_type_id', $voucher_type_id)->latest()->pluck('voucher_no')->first();
                if (!empty($get_voucher_no)) {
                    $get_voucher_name_str = $voucher_type_name . "-";
                    $get_voucher = str_replace($get_voucher_name_str, "", $get_voucher_no);
                    $voucher_no = (int)$get_voucher + 1;
                } else {
                    $voucher_no = 2000;
                }
                $final_voucher_no = $voucher_type_name . '-' . $voucher_no;

                // supplier head
                $supplier_chart_of_account_info = WarehouseChartOfAccount::wherewarehouse_id($warehouse_id)->where('user_id', $supplier_user_id)->first();
                if (empty($supplier_chart_of_account_info)) {
                    Toastr::error('Sorry Chart Of Account Not Ready To Create Account Payble', "Error");
                    return back();
                }
                // Supplier Credit
                $description = $supplier_chart_of_account_info->head_name . ' Credited For Supplier Create';
                chartOfAccountTransactions($insert_id, NULL, $login_user_id, $voucher_type_id, $final_voucher_no, 'Supplier Credited', $date, $transaction_date_time, $year, $month, $warehouse_id, NULL, NULL, $supplier_user_id, NULL, NULL, NULL, NULL, $supplier_chart_of_account_info->id, $supplier_chart_of_account_info->head_code, $supplier_chart_of_account_info->parent_head_name, $supplier_chart_of_account_info->head_type, 0, $previous_balance, $description, 'Pending');
            }
            DB::commit();

            Toastr::success("Supplier Created Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.suppliers.index');
        }
        /*  } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        } */
    }

    public function show($id)
    {
        $supplier = User::findOrFail($id);
        return view('backend.common.suppliers.show', compact('supplier'));
    }

    public function edit($id)
    {
        if ($this->User->user_type === 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }

        $supplier = User::findOrFail($id);
        return view('backend.common.suppliers.edit', compact('supplier', 'warehouses'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'phone' => 'required|unique:users,phone,' . $id,
            'type' => 'required',
            'country_code' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            /* 'address' => 'required', */
            'credit_limit' => 'required|numeric|min:0|max:9999999999999999',
            'days_limit' => 'required|numeric|min:0|max:9999999999999999',
            'status' => 'required',
        ]);
        //dd($request->all());

        //try {
        // user table data start
        userDataUpdate($request->all(), $id);
        // user table data end

        $supplier = Supplier::where('user_id', $id)->first();
        if ($supplier) {
            $supplier->type = $request->type;
            $supplier->vat_no = $request->vat_no;
            $supplier->credit_limit = $request->credit_limit;
            $supplier->days_limit = $request->days_limit;
            $supplier->payment_terms = $request->payment_terms;
            //$supplier->payment_type_id = $request->payment_type_id;

            $supplier->supplier_location = $request->supplier_location;
            $supplier->comercial_registration_no = $request->comercial_registration_no;
            $supplier->bank_accounts_details = $request->bank_accounts_details;
            $supplier->product_groups = $request->product_groups;
            $supplier->updated_by_user_id = Auth::User()->id;
            $update_supplier = $supplier->save();
            /* if ($update_supplier) {
                    WarehouseChartOfAccount::whereIn('user_id', [$update_supplier->user_id])
                    ->update([
                        'head_name' =>$request->name,
                        'updated_by_user_id' => Auth::id()
                    ]);

                } */

            if ($update_supplier) {
                $coa = WarehouseChartOfAccount::where('user_id', $id)->first();
                $coa->head_name = $request->name;
                $coa->updated_by_user_id = Auth::User()->id;
                $coa->warehouse_id = $request->warehouse_id;
                $coa->save();
            }
        }

        Toastr::success("Supplier Updated Successfully", "Success");
        return redirect()->route(\Request::segment(1) . '.suppliers.index');
        /* } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        } */
    }

    public function destroy($id)
    {
        //
    }

    public function localSupplier(Request $request)
    {

        try {
            if ($request->ajax()) {
                // $suppliers = User::where('user_type', 'Supplier')->orderBy('id', 'DESC');
                /* $suppliers = User::join('suppliers', 'users.id', '=', 'suppliers.user_id')
                    ->where('suppliers.type', '=', 'Local')
                    ->orderBy('id', 'DESC')
                    ->get(['users.*']); */
                if ($this->User->user_type == 'Super Admin') {
                    $suppliers = User::join('suppliers', 'users.id', '=', 'suppliers.user_id')
                        ->where('suppliers.type', '=', 'Local')
                        ->orderBy('id', 'DESC')
                        ->get(['users.*']);
                } elseif ($this->User->user_type == 'Admin' || $this->User->user_type == 'Accounts') {
                    $suppliers = User::join('suppliers', 'users.id', '=', 'suppliers.user_id')
                        ->where('suppliers.type', '=', 'Local')
                        ->wherewarehouse_id($this->User->warehouse_id)
                        ->orderBy('id', 'DESC')
                        ->get(['users.*']);
                } else {
                    $suppliers = User::join('suppliers', 'users.id', '=', 'suppliers.user_id')
                        ->where('suppliers.type', '=', 'Local')
                        ->wherecreated_by_user_id($this->User->id)
                        ->orderBy('id', 'DESC')
                        ->get(['users.*']);
                }

                return Datatables::of($suppliers)
                    ->addIndexColumn()
                    ->addColumn('action', function ($supplier) {
                        $btn = '<a href=' . route(\Request::segment(1) . '.suppliers.show', $supplier->id) . ' class="btn btn-warning waves-effect"><i class="fa fa-eye"></i></a> <a href=' . route(\Request::segment(1) . '.suppliers.edit', $supplier->id) . ' class="btn btn-info waves-effect"><i class="fa fa-edit"></i></a>';
                        return $btn;
                    })
                    ->addColumn('type', function ($supplier) {

                        return $supplier->supplier->type;
                    })

                    ->addColumn('warehouse_name', function ($supplier) {
                        return getWarehouseName($supplier->warehouse_id);
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

            return view('backend.common.suppliers.local_supplier');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function internationalSupplier(Request $request)
    {
        try {
            if ($request->ajax()) {
                // $suppliers = User::where('user_type', 'Supplier')->orderBy('id', 'DESC');
                /* $suppliers = User::join('suppliers', 'users.id', '=', 'suppliers.user_id')
                    ->where('suppliers.type', '=', 'International')
                    ->orderBy('id', 'DESC')
                    ->get(['users.*']); */

                if ($this->User->user_type == 'Super Admin') {
                    $suppliers = User::join('suppliers', 'users.id', '=', 'suppliers.user_id')
                        ->where('suppliers.type', '=', 'International')
                        ->orderBy('id', 'DESC')
                        ->get(['users.*']);
                } elseif ($this->User->user_type == 'Admin' || $this->User->user_type == 'Accounts') {
                    $suppliers = User::join('suppliers', 'users.id', '=', 'suppliers.user_id')
                        ->where('suppliers.type', '=', 'International')
                        ->wherewarehouse_id($this->User->warehouse_id)
                        ->orderBy('id', 'DESC')
                        ->get(['users.*']);
                } else {
                    $suppliers = User::join('suppliers', 'users.id', '=', 'suppliers.user_id')
                        ->where('suppliers.type', '=', 'International')
                        ->wherecreated_by_user_id($this->User->id)
                        ->orderBy('id', 'DESC')
                        ->get(['users.*']);
                }


                return Datatables::of($suppliers)
                    ->addIndexColumn()
                    ->addColumn('action', function ($supplier) {
                        $btn = '<a href=' . route(\Request::segment(1) . '.suppliers.show', $supplier->id) . ' class="btn btn-warning waves-effect"><i class="fa fa-eye"></i></a> <a href=' . route(\Request::segment(1) . '.suppliers.edit', $supplier->id) . ' class="btn btn-info waves-effect"><i class="fa fa-edit"></i></a>';
                        return $btn;
                    })
                    ->addColumn('type', function ($supplier) {

                        return $supplier->supplier->type;
                    })
                    ->addColumn('warehouse_name', function ($supplier) {
                        return getWarehouseName($supplier->warehouse_id);
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

            return view('backend.common.suppliers.international_supplier');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function supplierExcelStore(Request $request){
        Excel::import(new SupplierImport, $request->file('supplier'));

        Toastr::success("Supplier Created", "Success");
        return redirect()->back();
    }
}
