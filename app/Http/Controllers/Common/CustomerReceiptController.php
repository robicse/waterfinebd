<?php

namespace App\Http\Controllers\Common;

use App\Models\Customer;
use App\Models\User;
use App\Models\BankAccount;
use App\Models\PaymentType;
use Illuminate\Http\Request;
use App\Helpers\ErrorTryCatch;
use App\Models\PaymentReceipt;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use App\Models\Sale;
use DataTables;

class CustomerReceiptController extends Controller
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
        // $this->middleware('permission:customer-receipts-list', [
        //     'only' => ['index', 'show'],
        // ]);
        // $this->middleware('permission:customer-receipts-create', [
        //     'only' => ['create', 'store'],
        // ]);
        // $this->middleware('permission:customer-receipts-edit', [
        //     'only' => ['edit', 'update'],
        // ]);
        // $this->middleware('permission:customer-receipts-delete', [
        //     'only' => ['destroy'],
        // ]);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {

            $paymentReceipts = PaymentReceipt::whereorder_type('Sale')->whereorder_type_id(1)->orderBy('id', 'DESC');

            return Datatables::of($paymentReceipts)
                ->addIndexColumn()
                ->addColumn('action', function ($data) {
                    $btn =
                        '<a href=' .
                        route(
                            \Request::segment(1) . '.customer-receipts.show',
                            $data->id
                        ) .
                        ' class="btn btn-warning btn-sm waves-effect"><i class="fa fa-eye"></i></a>';
                    return $btn;
                })
                ->addColumn('store_name', function ($data) {
                    return @$data->store->name;
                })
                ->addColumn('customer_name', function ($data) {
                    return @$data->customer->name;
                })
                ->addColumn('payment_type_name', function ($data) {
                    return @$data->payment_type->name;
                })
                ->addColumn('created_by_user', function ($data) {
                    return $data->created_by_user->name;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('backend.common.customer_receipts.index');
    }

    public function create()
    {
        $stores = Store::wherestatus(1)->pluck('name', 'id');
        $customers = Customer::wherestatus(1)->get();

        $payment_types = PaymentType::wherestatus(1)->pluck('name', 'id');
        return view('backend.common.customer_receipts.create')
            ->with('customers', $customers)
            ->with('paymentTypes', $payment_types)
            ->with('stores', $stores);
    }
    public function store(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'date' => 'required',
            'customer_id' => 'required',
            'amount' => 'required|numeric|min:0|max:9999999999999999',
            // 'paid_amount.*' => 'required|numeric|min:0|max:9999999999999999',
        ]);
        try {
            // DB::beginTransaction();
            $row_count = count($request->paid_amount);

            $store_id = $request->store_id;
            $date = $request->date;
            $transaction_date_time = $request->date . ' ' . date('H:i:s');
            $month = date('m', strtotime($date));
            $year = date('Y', strtotime($date));
            $login_user_id = Auth::user()->id;
            $customer_id = $request->customer_id;
            $payment_type_id = $request->payment_type_id;
            $amount = $request->amount;

            for ($i = 0; $i < $row_count; $i++) {
                $due_amount = $request->due_amount[$i];
                $paid_amount = $request->paid_amount[$i];
                $invoice_no = $request->invoice_no[$i];
                if ($paid_amount !== null) {
                    $saleInfo = Sale::where('id','=',$invoice_no)->first();
                    $saleInfo->paid_amount = $saleInfo->paid_amount + $paid_amount;
                    $saleInfo->due_amount = $saleInfo->due_amount - $paid_amount;
                    $saleInfo->save();
                }

                // for paid amount > 0
                if($paid_amount > 0){
                    PaymentReceipt::whereorder_type('Sale')->whereorder_id($invoice_no)->whereorder_type_id(2)->delete();
                }

                // for due amount > 0
                if($due_amount > $paid_amount){
                    $payment_receipt = new PaymentReceipt();
                    $payment_receipt->date = date('Y-m-d');
                    $payment_receipt->store_id = $store_id;
                    $payment_receipt->order_type = 'Sale';
                    $payment_receipt->order_id = $invoice_no;
                    $payment_receipt->customer_id = $customer_id;
                    $payment_receipt->order_type_id = 2;
                    $payment_receipt->amount = $due_amount - $paid_amount;
                    $payment_receipt->created_by_user_id = Auth::User()->id;
                    $payment_receipt->save();
                }
                // for paid amount > 0
                if($paid_amount > 0){
                    $payment_receipt = new PaymentReceipt();
                    $payment_receipt->date = date('Y-m-d');
                    $payment_receipt->store_id = $store_id;
                    $payment_receipt->order_type = 'Sale';
                    $payment_receipt->order_id = $invoice_no;
                    $payment_receipt->customer_id = $customer_id;
                    $payment_receipt->order_type_id = 1;
                    $payment_receipt->payment_type_id = $payment_type_id;
                    $payment_receipt->amount = $paid_amount;
                    $payment_receipt->created_by_user_id = Auth::User()->id;
                    $payment_receipt->save();
                }
            }

            // DB::commit();
            Toastr::success('Customer Receive  Create Successfully', 'Success');
            return redirect()->route(
                \Request::segment(1) . '.customer-receipts.index'
            );
        } catch (\Exception $e) {
            // DB::rollBack();
            $response = ErrorTryCatch::createResponse(false,500,'Internal Server Error.',null);
            Toastr::error($response['message'], 'Error');
            return back();
        }
    }

    public function customerDue($id)
    {
        return PaymentReceipt::wherecustomer_id($id)->whereorder_type_id(2)->sum('amount');
    }
    public function CustomerBanks($id)
    {
        //
    }

    public function customerDueBalanceInfo($id)
    {
        return Sale::where('customer_id', $id)->where('due_amount', '!=', 0)->select('id', 'due_amount')->get();
    }

    public function show($id)
    {
        //
    }
}
