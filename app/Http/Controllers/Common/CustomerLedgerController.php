<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables;
use Carbon\Carbon;
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
        $this->middleware('permission:customer-ledgers-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:customer-ledgers-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:customer-ledgers-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:customer-ledgers-delete', ['only' => ['destroy']]);
    }
    public function index()
    {
        try {
            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
                $customers = User::wherestatus(1)->whereuser_type('Customer')->get();
            } elseif ($this->User->user_type == 'Admin' || $this->User->user_type == 'Accounts') {
                $warehouses = Warehouse::wherestatus(1)->whereid($this->User->warehouse_id)->pluck('name', 'id');
                $warehouse_id = $this->User->warehouse_id;
                $customers = User::wherestatus(1)->whereuser_type('Customer')->wherewarehouse_id($warehouse_id)->get();
            } else {
                // staff
                $warehouses = Warehouse::wherestatus(1)->wherecreated_by_user_id($this->User->id)->pluck('name', 'id');
                $warehouse_id = $this->User->warehouse_id;
                $customers = User::wherestatus(1)->whereuser_type('Customer')->wherewarehouse_id($warehouse_id)->get();
            }
            return view('backend.common.customer_ledgers.index', compact('warehouses', 'customers'));
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
            $customer_user_id = $request->customer_user_id;
            $warehouse_id = $request->warehouse_id;
            if ($this->User->user_type === 'Super Admin') {
                $customers = User::wherestatus(1)->whereuser_type('Customer')->wherewarehouse_id($warehouse_id)->get();
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } elseif ($this->User->user_type == 'Admin' || $this->User->user_type == 'Accounts') {
                $warehouses = Warehouse::wherestatus(1)->whereid($this->User->warehouse_id)->pluck('name', 'id');
                $customers = User::wherestatus(1)->whereuser_type('Customer')->wherewarehouse_id($this->User->warehouse_id)->get();
            } else {
                // staff
                $warehouses = Warehouse::wherestatus(1)->wherecreated_by_user_id($this->User->id)->pluck('name', 'id');
                $customers = User::wherestatus(1)->whereuser_type('Customer')->wherewarehouse_id($this->User->warehouse_id)->get();
            }
            $customerReports = ChartOfAccountTransaction::whereapproved_status('Approved')->whereuser_id($customer_user_id)->where('user_id', '!=', 'NULL')->whereBetween('date', array($from, $to))->get();

            $preBalanceDebit = ChartOfAccountTransaction::whereapproved_status('Approved')->whereuser_id($customer_user_id)->where('user_id', '!=', 'NULL')->where('date', '<', $from)->sum('debit');
            $preBalanceCredit = ChartOfAccountTransaction::whereapproved_status('Approved')->whereuser_id($customer_user_id)->where('user_id', '!=', 'NULL')->where('date', '<', $from)->sum('credit');
            $preBalance = $preBalanceDebit - $preBalanceCredit;

            return view('backend.common.customer_ledgers.reports', compact('customerReports', 'customers', 'preBalance', 'from', 'to', 'customer_user_id', 'warehouses', 'warehouse_id'));
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
