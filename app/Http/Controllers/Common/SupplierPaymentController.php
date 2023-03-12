<?php

namespace App\Http\Controllers\Common;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Models\PaymentType;
use App\Models\PaymentReceipt;
use App\Models\VoucherType;
use Illuminate\Http\Request;
use App\Helpers\ErrorTryCatch;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use DataTables;
use App\Helpers\Helper;

class SupplierPaymentController extends Controller
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
        $this->middleware('permission:supplier-payments-list', ['only' => ['index', 'show']]);
        // $this->middleware('permission:supplier-payments-create', ['only' => ['create', 'store']]);
        // $this->middleware('permission:supplier-payments-edit', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:supplier-payments-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        try {

            if ($request->ajax()) {

                $paymentReceipts = PaymentReceipt::whereorder_type('Purchase')->whereorder_type_id(1)->orderBy('id', 'DESC');

                return Datatables::of($paymentReceipts)
                    ->addIndexColumn()
                    ->addColumn('action', function ($data) {
                        $btn =
                            '<a href=' .
                            route(
                                \Request::segment(1) . '.supplier-payments.show',
                                $data->id
                            ) .
                            ' class="btn btn-warning btn-sm waves-effect"><i class="fa fa-eye"></i></a>';
                        return $btn;
                    })
                    ->addColumn('store_name', function ($data) {
                        return @$data->store->name;
                    })
                    ->addColumn('supplier_name', function ($data) {
                        return @$data->supplier->name;
                    })
                    ->addColumn('payment_type_name', function ($data) {
                        $transactions = Helper::getSalePaymentInfo($data->order_id);
                        $payment_info = $data->payment_type->name;
                        if(count($transactions) > 0){
                            foreach($transactions as $transaction){
                                $payment_info .= '<span>';
                                if($transaction->payment_type_id == 3){
                                    $payment_info .= '( Bank Name:'. $transaction->bank_name . ')<br/>';
                                    $payment_info .= '( Cheque Number:'. $transaction->cheque_number . ')<br/>';
                                    $payment_info .= '( Cheque Date:'. $transaction->cheque_date . ')<br/>';
                                }elseif($transaction->payment_type_id == 2){
                                    $payment_info .= '( Transaction Number:'. $transaction->transaction_number . ')<br/>';
                                }elseif($transaction->payment_type_id == 2){
                                    $payment_info .= '( Note:'. $transaction->note . ')<br/>';
                                }else{
                                }
                                $payment_info .= 'Tk.'. $transaction->amount .'</span>';
                            }
                        }
                        return $payment_info;
                    })
                    ->addColumn('created_by_user', function ($data) {
                        return $data->created_by_user->name;
                    })
                    ->rawColumns(['action','payment_type_name'])
                    ->make(true);
            }
            return view('backend.common.supplier_payments.index');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function create()
    {
        //dd('ff');
        $stores = Store::wherestatus(1)->pluck('name', 'id');
        $suppliers = Supplier::wherestatus(1)->get();

        $payment_types = PaymentType::wherestatus(1)->pluck('name', 'id');
        return view('backend.common.supplier_payments.create')
            ->with('suppliers', $suppliers)
            ->with('paymentTypes', $payment_types)
            ->with('stores', $stores);

    }
    public function store(Request $request)
    {
        $this->validate($request, [
            'date' => 'required',
            'supplier_id' => 'required',
            'amount' => 'required|numeric|min:0|max:9999999999999999',
        ]);
        try {
            DB::beginTransaction();
            $row_count = count($request->paid_amount);

            $store_id = $request->store_id;
            $date = $request->date;
            $transaction_date_time = $request->date . ' ' . date('H:i:s');
            $month = date('m', strtotime($date));
            $year = date('Y', strtotime($date));
            $login_user_id = Auth::user()->id;
            $supplier_id = $request->supplier_id;
            $payment_type_id = $request->payment_type_id;
            $amount = $request->amount;

            for ($i = 0; $i < $row_count; $i++) {
                $due_amount = $request->due_amount[$i];
                $paid_amount = $request->paid_amount[$i];
                $invoice_no = $request->invoice_no[$i];
                if ($paid_amount !== null) {
                    $purchaseInfo = Purchase::where('id','=',$invoice_no)->first();
                    $purchaseInfo->paid_amount = $purchaseInfo->paid_amount + $paid_amount;
                    $purchaseInfo->due_amount = $purchaseInfo->due_amount - $paid_amount;
                    $purchaseInfo->save();
                }

                // for paid amount > 0
                if($paid_amount > 0){
                    PaymentReceipt::whereorder_type('Purchase')->whereorder_id($invoice_no)->whereorder_type_id(2)->delete();
                }

                // for due amount > 0
                if($due_amount > $paid_amount){
                    $payment_receipt = new PaymentReceipt();
                    $payment_receipt->date = date('Y-m-d');
                    $payment_receipt->store_id = $store_id;
                    $payment_receipt->order_type = 'Purchase';
                    $payment_receipt->order_id = $invoice_no;
                    $payment_receipt->supplier_id = $supplier_id;
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
                    $payment_receipt->order_type = 'Purchase';
                    $payment_receipt->order_id = $invoice_no;
                    $payment_receipt->supplier_id = $supplier_id;
                    $payment_receipt->order_type_id = 1;
                    $payment_receipt->payment_type_id = $payment_type_id;
                    $payment_receipt->bank_name = $request->bank_name ? $request->bank_name : '';
                    $payment_receipt->cheque_number = $request->cheque_number ? $request->cheque_number : '';
                    $payment_receipt->cheque_date = $request->cheque_date ? $request->cheque_date : '';
                    $payment_receipt->card_number = $request->card_number ? $request->card_number : '';
                    $payment_receipt->note = $request->note ? $request->note : '';
                    $payment_receipt->amount = $paid_amount;
                    $payment_receipt->created_by_user_id = Auth::User()->id;
                    $payment_receipt->save();
                }
            }

            DB::commit();
            Toastr::success('Customer Receive  Create Successfully', 'Success');
            return redirect()->route(
                \Request::segment(1) . '.supplier-payments.index'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            $response = ErrorTryCatch::createResponse(false,500,'Internal Server Error.',null);
            Toastr::error($response['message'], 'Error');
            return back();
        }
    }

    public function show($id)
    {
        //
    }

    public function supplierDueBalanceInfo($id)
    {
        return Purchase::where('supplier_id', $id)->where('due_amount', '!=', 0)->select('id', 'due_amount')->get();
    }
}
