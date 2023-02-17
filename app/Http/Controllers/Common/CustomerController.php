<?php

namespace App\Http\Controllers\Common;
use DataTables;
use App\Models\User;
use App\Models\Route;
use App\Models\Customer;
use App\Models\RouteArea;
use App\Models\Warehouse;
use App\Models\PaymentType;
use App\Models\VoucherType;
use Illuminate\Http\Request;
use App\Helpers\ErrorTryCatch;
use App\Models\ChartOfAccount;
use App\Imports\CustomerImport;
use App\Http\Traits\CurrencyTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\WarehouseChartOfAccount;
use App\Models\ChartOfAccountTransaction;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CustomerController extends Controller
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

        $this->middleware('permission:customers-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:customers-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:customers-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:customers-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        // customer QR Code
        //        $qrcode = '1_qr_code.png';
        //        $text = 'Test Customer';
        //        QrCode::size(500)
        //            ->format('png')
        //            ->generate($text, public_path('uploads/customer_qrcode/'.$qrcode));

        //        QrCode::size(500)
        //            ->format('png')
        //            ->generate('codingdriver.com', public_path('uploads/customer_qrcode/qr_code.png'));
        //        $text = 'Cus Name:'.'<br/>'.'Cus Store Name:';
        //        echo "One line after\n another line"."</br>";
        //        //echo "</br>";
        //        echo "One line after\r\n another line";
        //  
        $User=$this->User;
        try {

            if ($request->ajax()) {

                if ($this->User->user_type == 'Super Admin') {
                    $customers = User::with('warehouse','customer')->where('user_type', 'Customer')->orderBy('id', 'DESC');
                } elseif ($this->User->user_type == 'Admin' || $this->User->user_type == 'Accounts') {
                    $customers = User::with('warehouse','customer')->wherewarehouse_id($this->User->warehouse_id)->where('user_type', 'Customer')->orderBy('id', 'DESC');
                } else {
                    $customers = User::with('warehouse','customer')->wherewarehouse_id($this->User->warehouse_id)->where('user_type', 'Customer')->orderBy('id', 'DESC');
                }
                return Datatables::of($customers)
                    ->addIndexColumn()
                    ->addColumn('action', function ($customer)use($User) {
                        $btn='';
                        $btn = '<span  class="d-inline-flex"><a href=' . route(\Request::segment(1) . '.customers.show', $customer->id) . ' class="btn btn-warning btn-sm waves-effect"><i class="fa fa-eye"></i></a>';
                        if($User->can('customers-edit')){
                        $btn .= '<a href=' . route(\Request::segment(1) . '.customers.edit', $customer->id) . ' class="btn btn-info waves-effect btn-sm float-left" style="margin-left: 5px"><i class="fa fa-edit"></i></a>';
                        }
                        $btn .= '</span>';
                        return $btn;
                    })
                    ->addColumn('status', function ($customer) {
                        if ($customer->status == 0) {
                            return '<span class="badge badge-danger"> <i class="fa fa-ban"></i> </span>';
                        } else {
                            return '<span class="badge badge-success"><i class="fa fa-check-square"></i></span>';
                        }
                    })
                    ->rawColumns(['action', 'status'])
                    ->make(true);
            }

            return view('backend.common.customers.index');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function create()
    {
        $route_areas = RouteArea::wherestatus(1)->select('name', 'id')->get();
        //dd($route_areas);
        if ($this->User->user_type === 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            //$routes = Route::wherestatus(1)->select('name', 'id')->get();
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            //$routes = Route::wherewarehouse_id($this->User->warehouse_id)->wherestatus(1)->select('name', 'id')->get();
        }
        return view('backend.common.customers.create', compact('route_areas','warehouses'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $this->validate($request, [
            'name' => 'required',
            'phone' => 'required|unique:users',
            'country_code' => 'required',
            'email' => 'required|email|unique:users',
            /* 'address' => 'required', */
            'credit_limit' => 'required|numeric|min:0|max:9999999999999999',
            'days_limit' => 'required|numeric|min:0|max:9999999999999999',
            'previous_balance' => 'required|numeric|min:0|max:9999999999999999',
            'status' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $get_customer_code = Customer::orderBy('id', 'desc')->pluck('code')->first();
            if (!empty($get_customer_code)) {
                $get_customer_code_after_replace = str_replace("CUS", "", $get_customer_code);
                $customer_code = (int)$get_customer_code_after_replace + 1;
            } else {
                $customer_code = 1;
            }
            $final_customer_code = 'CUS' . $customer_code;
            $auto_generate_code = 'CUS-AUTO-' . $customer_code;

            // user table data start
            $user = userDataInsert($request->all(), 'Customer');
            // user table data end

            if ($user) {
                // customer QR Code
                 $qrcode = 'uploads/customer_qrcode/' . $user->id . '_qr_code.png';
                // $text = 'Cus Name:' . $user->name . ' ' . 'Cus Store Name:' . $request->store_name;
                // QrCode::size(300)
                //     ->format('png')
                //     ->generate($text, public_path($qrcode));

                $customer = new Customer();
                $customer->user_id = $user->id;
                $customer->code = $final_customer_code;
                $customer->store_name = $request->store_name;
                $customer->vat_no = $request->vat_no;
                $customer->type = $request->type;
                $customer->nid = $request->nid;
                $customer->contact_person = $request->contact_person;
                $customer->contact_person_no = $request->contact_person_no;
                $customer->auto_generate_code = $auto_generate_code;
                $customer->qr_code = $qrcode;
                $customer->credit_limit = $request->credit_limit;
                $customer->days_limit = $request->days_limit;
                $customer->stublish_name = $request->stublish_name;
                //$customer->route_area_ids = json_encode($request->route_area_ids);
                $customer->created_by_user_id = Auth::User()->id;
                $customer->updated_by_user_id = Auth::User()->id;
                $customer->save();
                $insert_id = $customer->id;
                $warehouse_id = $request->warehouse_id;
                if ($insert_id) {
                    $headAccountDetail  = WarehouseChartOfAccount::wherewarehouse_id($warehouse_id)->where('head_name', 'ACCOUNT RECEIVABLES')->orderBy('id', 'desc')->first();
                    if (empty($headAccountDetail)) {
                        Toastr::error('Sorry Chart Of Account Not Ready To Create Account Payble', "Error");
                        return back();
                    }

                    $parentHeadAccountDetail = WarehouseChartOfAccount::wherewarehouse_id($warehouse_id)->where('parent_head_name', 'ACCOUNT RECEIVABLES')->orderBy('id', 'desc')->first();
                    if (empty($parentHeadAccountDetail)) {
                        $head_code = $headAccountDetail->head_code . '00000001';
                    } else {
                        $head_code = $parentHeadAccountDetail->head_code + 1;
                    }
                    $head_name =$request->name;
                    $parent_head_name = 'ACCOUNT RECEIVABLES';
                    $head_level = $headAccountDetail->head_level + 1;
                    $head_type = $headAccountDetail->head_type;
                    $customer_chart_of_account = new WarehouseChartOfAccount();
                    $customer_chart_of_account->prefix='CUS' . $user->id ;
                    $customer_chart_of_account->user_id=$user->id ;
                    $customer_chart_of_account->warehouse_id=$warehouse_id;
                    $customer_chart_of_account->warehouse_name=$headAccountDetail->name;
                    $customer_chart_of_account->head_code = $head_code;
                    $customer_chart_of_account->head_name =  $head_name;
                    $customer_chart_of_account->parent_head_name = $parent_head_name;
                    $customer_chart_of_account->head_type =   $head_type;
                    $customer_chart_of_account->head_level =  $head_level;
                    $customer_chart_of_account->created_by_user_id=Auth::User()->id;
                    $customer_chart_of_account->updated_by_user_id=Auth::User()->id;
                    $customer_chart_of_account->save();

                }
                $previous_balance = $request->previous_balance;
                if ($previous_balance > 0) {

                //coa id for user(supplier)
                $coa_id = $customer_chart_of_account->id;

                // posting
                $month = date('m');
                $year = date('Y');
                $transaction_date_time = date(' H:i:s');
                $date = date("Y-m-d");
                //$payment_type_id = $request->payment_type_id;
                $previous_balance = $request->previous_balance;
                $login_user_id = Auth::user()->id;

                //check it user id or supplier table user id(Tomorrow)
                $customer_user_id = $user->id;

                // Cash In Hand For Previous Balance
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

                // supplcustomerier head
                //$code = Supplier::where('id', $insert_id)->pluck('code')->first();
                $customer_chart_of_account_info = WarehouseChartOfAccount::wherewarehouse_id($warehouse_id)->where('id', $coa_id)->first();


                // Customer Credit
                $description = $customer_chart_of_account_info->head_name . ' Credited For Customer Create';
                chartOfAccountTransactions($insert_id, NULL, $login_user_id, $voucher_type_id, $final_voucher_no, 'Customer Credited', $date, $transaction_date_time, $year, $month, $warehouse_id, NULL, NULL, $customer_user_id, NULL, NULL, NULL, NULL, $customer_chart_of_account_info->id, $customer_chart_of_account_info->head_code, $customer_chart_of_account_info->parent_head_name, $customer_chart_of_account_info->head_type, $previous_balance, 0, $description, 'Pending');
            }
                DB::commit();
                Toastr::success("Customer Created Successfully", "Success");
                return redirect()->route(\Request::segment(1) . '.customers.index');
            }
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }  
    }

    public function show($id)
    {
        $customer = User::findOrFail($id);
        return view('backend.common.customers.show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = User::findOrFail($id);
       if ($this->User->user_type === 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
          } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
           }

        return view('backend.common.customers.edit', compact('customer', 'warehouses'));
    }

    public function update(Request $request, $id)
    {
         try {
            $this->validate($request, [
                'name' => 'required',
                'country_code' => 'required',
                'phone' => 'required',
                'email' => 'required',
                /* 'address' => 'required', */
                'credit_limit' => 'required|numeric|min:0|max:9999999999999999',
                'days_limit' => 'required|numeric|min:0|max:9999999999999999',
               'status' => 'required',
            ]);

            // user table data start
            userDataUpdate($request->all(),$id);
            // user table data end

            $customer = Customer::where('user_id', $id)->first();
            if ($customer) {
                $customer->store_name = $request->store_name;
                $customer->vat_no = $request->vat_no;
                $customer->type = $request->type;
                $customer->nid = $request->nid;
                $customer->contact_person = $request->contact_person;
                $customer->contact_person_no = $request->contact_person_no;
                //$customer->qr_code = $request->qr_code;
                $customer->credit_limit = $request->credit_limit;
                $customer->days_limit = $request->days_limit;
                $customer->stublish_name = $request->stublish_name;
                //$customer->route_area_ids = json_encode($request->route_area_ids);
                $customer->updated_by_user_id = Auth::User()->id;
                $update_customer = $customer->save();

                if ($update_customer) {
                    // customer QR Code
                    $user = User::findOrFail($id);
                    $qrcode = 'uploads/customer_qrcode/' . $id . '_qr_code.png';
                    $text = 'Cus Name:' . $user->name . ' ' . 'Cus Store Name:' . $customer->store_name;
                    QrCode::size(300)
                        ->format('png')
                        ->generate($text, public_path($qrcode));
                    $customerQRCode = Customer::where('user_id', $id)->first();
                    $customerQRCode->qr_code = $qrcode;
                    $customerQRCode->save();
                    $coa = WarehouseChartOfAccount::where('user_id', $id)->first();
                    $coa->head_name = $request->name;
                    $coa->updated_by_user_id = Auth::User()->id;
                    $coa->warehouse_id = $request->warehouse_id;
                    $coa->save();
                }
            }

            Toastr::success("Customer Updated Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.customers.index');
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

    public function customerExcelStore(Request $request)
    {
        Excel::import(new CustomerImport, $request->file('customer'));

        Toastr::success("Customer Created", "Success");
        return redirect()->back();
    }
}
