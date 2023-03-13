<?php

namespace App\Http\Controllers\Common;

use DB;
use DataTables;
use Carbon\Carbon;
use App\Models\PaymentReceipt;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ErrorTryCatch;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Models\ChartOfAccountTransaction;
use App\Models\Store;
use NumberFormatter;

class SupplierLedgerController extends Controller
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
        // $this->middleware('permission:supplier-ledgers-list', ['only' => ['index', 'show']]);
        // $this->middleware('permission:supplier-ledgers-create', ['only' => ['create', 'store']]);
        // $this->middleware('permission:supplier-ledgers-edit', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:supplier-ledgers-delete', ['only' => ['destroy']]);
    }
    public function index()
    {
        try {

            $suppliers = Supplier::wherestatus(1)->get();
            $stores = Store::wherestatus(1)->pluck('name', 'id');
            return view('backend.common.supplier_ledgers.index', compact('suppliers','stores'));
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
            $digit = new NumberFormatter("en", NumberFormatter::SPELLOUT);
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $supplier_id = $request->supplier_id;
            $store_id = $request->store_id;
            $store = Store::find($store_id);
            $supplier = Supplier::find($supplier_id);
            $previewtype = $request->previewtype;
            $suppliers = Supplier::wherestatus(1)->get();
            $stores = Store::wherestatus(1)->pluck('name', 'id');
            $supplierReports = PaymentReceipt::whereorder_type('Purchase')->wheresupplier_id($supplier_id)->whereBetween('date', array($from, $to))->get();
            $preBalance = PaymentReceipt::whereorder_type('Purchase')->whereorder_type_id(2)->wheresupplier_id($supplier_id)->where('date', '<', $from)->sum('amount');

            if ($previewtype == 'htmlview') {
                return view('backend.common.supplier_ledgers.reports', compact('supplierReports', 'preBalance', 'suppliers', 'from', 'to', 'supplier_id','stores','store_id','store','supplier','digit'));
            }else{
                $pdf = Pdf::loadView('backend.common.supplier_ledgers.pdf_view', compact('supplierReports', 'preBalance', 'suppliers', 'from', 'to', 'supplier_id','stores','store_id','store','supplier','digit'));
                return $pdf->stream('store_purchase_report_' . now() . '.pdf');
            }
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
