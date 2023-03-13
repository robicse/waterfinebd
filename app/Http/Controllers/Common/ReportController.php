<?php

namespace App\Http\Controllers\Common;
use App\Models\Van;
use App\Models\Sale;
use App\Models\User;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Store;
use App\Models\SaleProduct;
use App\Models\SaleReturn;
use Illuminate\Http\Request;
use App\Helpers\ErrorTryCatch;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Traits\CurrencyTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SaleReturnCustomerToVanExport;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;

class ReportController extends Controller
{
    use CurrencyTrait;
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
    }

    public function   index()
    {
        //return view('backend.common.reports.index');
    }

    //Sales Report Controller
    public function purchaseStoreWiseIndex()
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $stores = Store::wherestatus(1)->pluck('name', 'id');
            return view('backend.common.reports.purchase_store_wise_report.index', compact('stores', 'default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function purchaseStoreWiseShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $store_id = $request->store_id;

        $storeInfo = Store::where('id', $store_id)->first();
        $store = Store::find($store_id);
        $previewtype = $request->previewtype;
        $storeWisePurchaseReports = Purchase::where('store_id', '=', $store_id)->whereBetween('purchase_date', array($from, $to))->get();
        $stores = Store::wherestatus(1)->pluck('name', 'id');

        if ($previewtype == 'htmlview') {
            return view('backend.common.reports.purchase_store_wise_report.reports', compact('storeWisePurchaseReports', 'from', 'to', 'stores', 'store_id', 'storeInfo', 'previewtype', 'default_currency'));
        } elseif ($previewtype == 'pdfview') {
            $pdf = Pdf::loadView('backend.common.reports.purchase_store_wise_report.pdf_view', compact('storeWisePurchaseReports', 'from', 'to', 'store', 'store_id', 'storeInfo', 'previewtype', 'default_currency'));
            return $pdf->stream('store_purchase_report_' . now() . '.pdf');
        }
        elseif ($previewtype == 'excelview') {
            return  Excel::download(new SaleWarehouseWiseExport($storeWisePurchaseReports, $storeInfo), now() . '_purchase_store_wise.xlsx');
        } else {
            return view('backend.common.reports.purchase_store_wise_report.reports', compact('storeWisePurchaseReports', 'from', 'to', 'stores', 'store_id', 'storeInfo', 'previewtype', 'default_currency'));
        }


        /* if ($previewtype == 'excellview') {
            return  Excel::download(new SaleReturnCustomerToVanExport($request), now() . '_sale_return_customer.xlsx');
        } */
    }

    public function saleStoreWiseIndex()
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $stores = Store::wherestatus(1)->pluck('name', 'id');
            return view('backend.common.reports.sale_store_wise_report.index', compact('stores', 'default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function saleStoreWiseShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $store_id = $request->store_id;

        $storeInfo = Store::where('id', $store_id)->first();
        $store = Store::find($store_id);
        $previewtype = $request->previewtype;
        $storeWiseSaleReports = Sale::where('store_id', '=', $store_id)->whereBetween('voucher_date', array($from, $to))->get();

        $stores = Store::wherestatus(1)->pluck('name', 'id');

        if ($previewtype == 'htmlview') {
            return view('backend.common.reports.sale_store_wise_report.reports', compact('storeWiseSaleReports', 'from', 'to', 'stores', 'store_id', 'storeInfo', 'previewtype', 'default_currency'));
        } elseif ($previewtype == 'pdfview') {
            $pdf = Pdf::loadView('backend.common.reports.sale_store_wise_report.pdf_view', compact('storeWiseSaleReports', 'from', 'to', 'store', 'store_id', 'storeInfo', 'previewtype', 'default_currency'));
            return $pdf->stream('warehouse_sale_report_' . now() . '.pdf');
        }
        elseif ($previewtype == 'excelview') {
            return  Excel::download(new SaleWarehouseWiseExport($storeWiseSaleReports, $storeInfo), now() . '_sale_warehouse_wise.xlsx');
        } else {
            return view('backend.common.reports.sale_store_wise_report.reports', compact('storeWiseSaleReports', 'from', 'to', 'stores', 'store_id', 'storeInfo', 'previewtype', 'default_currency'));
        }


        /* if ($previewtype == 'excellview') {
            return  Excel::download(new SaleReturnCustomerToVanExport($request), now() . '_sale_return_customer.xlsx');
        } */
    }

    public function MultipleStoreCurrentStockIndex()
    {
        try {
            $products = Product::wherestatus(1)->pluck('name', 'id');
            $stores = Store::wherestatus(1)->pluck('name', 'id');
            return view('backend.common.reports.multiple_store_current_stock_reports.index', compact('stores', 'products'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function  MultipleStoreCurrentStockShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $store_ids = $request->store_id;
        $product_ids = $request->product_id;
        //dd($product_ids);
        try {
            // $storeReports = Stock::with('store')
            //     // ->selectRaw(DB::raw('SUM(current_stock_qty) as current_stock_qty, product_id'))
            //     ->select('stocks.product_id')
            //     ->whereIn('stocks.store_id',  $store_ids)
            //     ->whereIn('stocks.product_id', $product_ids)
            //     ->groupBy('stocks.product_id')
            //     ->get();

            $storeReports = Product::whereIn('id', $product_ids)->get();
                // dd($storeReports);
            $products = Product::wherestatus(1)->pluck('name', 'id');
            $stores = Store::wherestatus(1)->pluck('name', 'id');
            return view('backend.common.reports.multiple_store_current_stock_reports.reports', compact('storeReports', 'stores', 'products', 'store_ids', 'product_ids'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function lossProfitStoreWiseIndex()
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $stores = Store::wherestatus(1)->pluck('name', 'id');
            return view('backend.common.reports.loss_profit_store_wise_report.index', compact('stores', 'default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function lossProfitStoreWiseShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $store_id = $request->store_id;

        $storeInfo = Store::where('id', $store_id)->first();
        $store = Store::find($store_id);
        $previewtype = $request->previewtype;
        $storeWiseLossProfitReports = Sale::where('store_id', '=', $store_id)->whereBetween('voucher_date', array($from, $to))->get();
        $storeWiseLossProfitReportsReturn = SaleReturn::where('store_id', '=', $store_id)->whereBetween('return_date', array($from, $to))->get();
        $stores = Store::wherestatus(1)->pluck('name', 'id');

        if ($previewtype == 'htmlview') {
            return view('backend.common.reports.loss_profit_store_wise_report.reports', compact('storeWiseLossProfitReports', 'storeWiseLossProfitReportsReturn','from', 'to', 'stores', 'store_id', 'storeInfo', 'previewtype', 'default_currency'));
        } elseif ($previewtype == 'pdfview') {
            $pdf = Pdf::loadView('backend.common.reports.loss_profit_store_wise_report.pdf_view', compact('storeWiseLossProfitReports', 'storeWiseLossProfitReportsReturn','from', 'to', 'store', 'store_id', 'storeInfo', 'previewtype', 'default_currency'));
            return $pdf->stream('store_purchase_report_' . now() . '.pdf');
        }
        elseif ($previewtype == 'excelview') {
            return  Excel::download(new SaleWarehouseWiseExport($storeWiseLossProfitReports, $storeInfo), now() . '_purchase_store_wise.xlsx');
        } else {
            return view('backend.common.reports.loss_profit_store_wise_report.reports', compact('storeWisePurchaseReports', 'storeWiseLossProfitReportsReturn','from', 'to', 'stores', 'store_id', 'storeInfo', 'previewtype', 'default_currency'));
        }


        /* if ($previewtype == 'excellview') {
            return  Excel::download(new SaleReturnCustomerToVanExport($request), now() . '_sale_return_customer.xlsx');
        } */
    }
}
