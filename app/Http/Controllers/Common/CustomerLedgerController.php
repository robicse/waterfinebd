<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables;
use Carbon\Carbon;
use App\Models\PaymentReceipt;
use App\Models\Customer;
use App\Models\Store;
use App\Models\User;
use App\Helpers\ErrorTryCatch;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Models\ChartOfAccountTransaction;
use App\Models\Warehouse;

class CustomerLedgerController extends Controller
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
        // $this->middleware('permission:customer-ledgers-list', ['only' => ['index', 'show']]);
        // $this->middleware('permission:customer-ledgers-create', ['only' => ['create', 'store']]);
        // $this->middleware('permission:customer-ledgers-edit', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:customer-ledgers-delete', ['only' => ['destroy']]);
    }
    public function index()
    {
        try {

            $customers = Customer::wherestatus(1)->get();
            $stores = Store::wherestatus(1)->pluck('name', 'id');
            return view('backend.common.customer_ledgers.index', compact('customers','stores'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
        try {
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $customer_id = $request->customer_id;
            $store_id = $request->store_id;
            $customers = Customer::wherestatus(1)->get();
            $stores = Store::wherestatus(1)->pluck('name', 'id');
            $customerReports = PaymentReceipt::whereorder_type('Sale')->wherecustomer_id($customer_id)->whereBetween('date', array($from, $to))->get();
            $preBalance = PaymentReceipt::whereorder_type('Sale')->whereorder_type_id(2)->wherecustomer_id($customer_id)->where('date', '<', $from)->sum('amount');

            return view('backend.common.customer_ledgers.reports', compact('customerReports', 'preBalance', 'customers', 'from', 'to', 'customer_id','stores','store_id'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function show($id)
    {
    }

    public function edit($id)
    {
    }


    public function update(Request $request, $id)
    {
    }

    public function destroy($id)
    {
    }
}
