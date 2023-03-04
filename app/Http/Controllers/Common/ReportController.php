<?php

namespace App\Http\Controllers\Common;
use App\Models\Van;
use App\Models\Sale;
use App\Models\User;
use App\Models\Route;
use App\Models\Product;
use App\Models\Receive;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\BankAccount;
use App\Models\Requisition;
use App\Models\SaleDetails;
use Illuminate\Http\Request;
use App\Models\ReceiveDetail;
use App\Models\StockTransfer;
use App\Models\VanRouteStock;
use App\Helpers\ErrorTryCatch;
use App\Models\WarehouseStock;
use App\Models\BusinessSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Traits\CurrencyTrait;
use Illuminate\Support\Facades\DB;
use App\Charts\WarehouseStockChart;
use App\Http\Controllers\Controller;
use App\Models\StockTransferDetails;
use App\Models\VanRouteCurrentStock;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\WarehouseCurrentStock;
use App\Models\SaleReturnCustomerToVan;
use App\Models\WarehouseChartOfAccount;
use App\Models\SaleReturnVanToWarehouse;
use App\Models\ChartOfAccountTransaction;
use App\Models\RequisitionGoodsAndService;
use App\Models\SaleReturnCustomerToWarehouse;
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

        $this->middleware('permission:trial-balance-warehousewise-list', ['only' => ['trialBalanceWarehouseIndex', 'trialBalanceWarehouseshow']]);
        $this->middleware('permission:cash-flow-warehouse-wise-list', ['only' => ['cashFlowWarehouseIndex', 'cashFlowWarehouseShow']]);
        $this->middleware('permission:balance-sheet-warehousewise-list', ['only' => ['balanceSheetWarehouseIndex', 'balanceSheetWarehouseshow']]);
        $this->middleware('permission:income-statements-warehousewise-list', ['only' => ['lossProfitWarehouseIndex', 'lossProfitWarehouseshow']]);
        $this->middleware('permission:multiple-warehouse-current-stock-report-list', ['only' => ['MultipleWarehouseCurrentStockIndex', 'MultipleWarehouseCurrentStockShow']]);
        $this->middleware('permission:multiple-van-current-stock-report-list', ['only' => ['MultipleVanCurrentStockIndex', 'MultipleVanCurrentStockShow']]);
        $this->middleware('permission:multiple-route-current-stock-report-list', ['only' => ['MultipleRoutecurrentstockIndex', 'MultipleRoutecurrentstockshow']]);
        $this->middleware('permission:warehouse-previous-stock-report-list', ['only' => ['WarehousecurrentstockIndex', 'Warehousecurrentstockshow']]);
        $this->middleware('permission:van-previous-stock-report-list', ['only' => ['VancurrentstockIndex', 'Vancurrentstockshow']]);
        $this->middleware('permission:route-current-stock-report-list', ['only' => ['RoutecurrentstockIndex', 'Routecurrentstockshow']]);
        $this->middleware('permission:trading-account-report-list', ['only' => ['TradingaccountIndex', 'Tradingaccountshow']]);
        $this->middleware('permission:sale-warehouse-wise-report-list', ['only' => ['saleWarehouseWiseIndex', 'saleWarehouseWiseShow']]);
        $this->middleware('permission:sale-route-wise-report-list', ['only' => ['saleRouteWiseIndex', 'saleRouteWiseShow']]);
        $this->middleware('permission:sale-van-wise-report-list', ['only' => ['saleVanWiseIndex', 'saleVanWiseShow']]);
        $this->middleware('permission:sale-product-wise-report-list', ['only' => ['saleProductWiseIndex', 'saleProductWiseShow']]);
        $this->middleware('permission:sale-amount-wise-report-list', ['only' => ['saleAmountWiseIndex', 'saleAmountWiseShow']]);
        $this->middleware('permission:sale-details-report-list', ['only' => ['saleDetailsIndex', 'saleDetailsShow']]);
        $this->middleware('permission:sale-order-details-report-list', ['only' => ['saleOrderDetailsIndex', 'saleOrderDetailsShow']]);
        $this->middleware('permission:refund-order-report-list', ['only' => ['RefundOrderIndex', 'RefundOrderShow']]);
        $this->middleware('permission:fast-moving-items-report-list', ['only' => ['fastMovingItemsIndex', 'fastMovingItemsShow']]);
        $this->middleware('permission:slow-moving-items-report-list', ['only' => ['slowMovingItemsIndex', 'slowMovingItemsShow']]);
        $this->middleware('permission:non-moving-items-report-list', ['only' => ['nonMovingItemsIndex', 'nonMovingItemsShow']]);
        $this->middleware('permission:fast-moving-items-route-wise-report-list', ['only' => ['fastMovingItemsRouteWiseIndex', 'fastMovingItemsRouteWiseShow']]);
        $this->middleware('permission:slow-moving-items-route-wise-report-list', ['only' => ['slowMovingItemsRouteWiseIndex', 'slowMovingItemsRouteWiseShow']]);
        $this->middleware('permission:supplier-ageing-stock-report-list', ['only' => ['SupplierAgeingStockReportIndex', 'SupplierAgeingStockReportShow']]);
        $this->middleware('permission:customer-ageing-stock-report-list', ['only' => ['CustomerAgeingStockReportIndex', 'CustomerAgeingStockReportShow']]);
        $this->middleware('permission:sales-summary-quantity-wise-report-list', ['only' => ['saleSummaryQuentityWiseIndex', 'saleSummaryQuentityWiseShow']]);
        $this->middleware('permission:sales-summary-amount-wise-report-list', ['only' => ['saleSummaryAmountWiseIndex', 'saleSummaryAmountWiseShow']]);
        $this->middleware('permission:general-ledger-report-list', ['only' => ['generalLedgerIndex', 'generalLedgerShow']]);
        $this->middleware('permission:sales-summary-amount-and-quantity-wise-report-list', ['only' => ['saleSummaryAmountAndQuantityWiseIndex', 'saleSummaryAmountAndQuantityWiseShow']]);
        // $this->middleware('permission:item-lists-report-list', ['only' => ['itemListsIndex', 'itemListsShow']]);
        // $this->middleware('permission:bank-sales-report-list', ['only' => ['bankSalesIndex', 'bankSalesShow']]);
        /* $this->middleware('permission:bank-sales-product-wise-report-list', ['only' => ['bankSalesIndex', 'bankSalesShow']]); */
        $this->middleware('permission:best-route-report-list', ['only' => ['bestRouteIndex', 'bestRouteShow']]);
        $this->middleware('permission:warehouse-sale-return-report-list', ['only' => ['warehouseSaleReturnIndex', 'warehouseSaleReturnShow']]);
        $this->middleware('permission:van-sale-return-report-list', ['only' => ['vanSaleReturnIndex', 'vanSaleReturnShow']]);
        $this->middleware('permission:sales-analysis-report-list', ['only' => ['SalesAnalysisIndex', 'SalesAnalysisShow']]);
        $this->middleware('permission:requisition-report-list', ['only' => ['requisitionIndex', 'requisitionShow']]);
        $this->middleware('permission:purchase-order-report-list', ['only' => ['PurchaseOrderIndex', 'PurchaseOrderShow']]);
        $this->middleware('permission:purchase-vs-requistion-report-list', ['only' => ['PurchaseVsRequistionIndex', 'PurchaseVsRequistionShow']]);
        $this->middleware('permission:view-purchase-order-report-list', ['only' => ['ViewPurchaseOrderIndex', 'ViewPurchaseOrderShow']]);
        $this->middleware('permission:requisition-canceled-approved-report-list', ['only' => ['RequisitionCanceledApprovedIndex', 'RequisitionCanceledApprovedShow']]);
        $this->middleware('permission:po-canceled-approved-report-list', ['only' => ['POCanceledApprovedReportIndex', 'POCanceledApprovedReportIndexShow']]);
        $this->middleware('permission:bank-flow-warehouse-wise-list', ['only' => ['bankFlowWarehouseIndex', 'bankFlowWarehouseShow']]);
        $this->middleware('permission:fund-flow-warehouse-wise-list', ['only' => ['fundFlowWarehouseIndex', 'fundFlowWarehouseShow']]);
        $this->middleware('permission:van-to-warehouse-sale-return-list', ['only' => ['vanToWarehouseRetunIndex', 'vanToWarehouseRetunShow']]);
        $this->middleware('permission:customer-to-van-sale-return-list', ['only' => ['customerToVanRetunIndex', 'customerToVanRetunShow']]);
    }

    public function   index()
    {
        return view('backend.common.reports.index');
    }
    public function balanceSheetWarehouseIndex()
    {
        try {
            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.warehouse_report.index')->with('warehouses', $warehouses);
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function balanceSheetWarehouseshow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            if ($this->User->user_type === 'Super Admin') {
                $accounts = WarehouseChartOfAccount::where('warehouse_id', 1)->where('is_active', 1)->where('head_level', 1)->get();
            } elseif ($this->User->user_type == 'Admin' || $this->User->user_type == 'Accounts') {
                $accounts = WarehouseChartOfAccount::where('warehouse_id', $this->User->warehouse_id)->where('is_active', 1)->where('head_level', 1)->get();
            } else {
                // staff
                $accounts = WarehouseChartOfAccount::where('warehouse_id', $this->User->warehouse_id)->where('is_active', 1)->where('head_level', 1)->get();
            }

            $to = $request->asdate;
            $chart = ChartOfAccountTransaction::first();
            if (empty($from)) {
                $from = '2022-01-01';
            } else {
                $from = $chart->date;
            }

            $warehouse_id = $request->warehouse_id;
            $level = $request->level;
            $warehouseInfo = Warehouse::where('id', $warehouse_id)->first();

            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            $oResultAssets = '';
            $oResultIncomes = '';
            $oResultEquities = '';
            $oResultExpenses = '';
            $oResultLiabilities = '';
            if ($level == 'all') {
                $oResultAssets = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<=', $to)->where('warehouse_chart_of_account_type', 'A')
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();

                $oResultIncomes = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<=', $to)->where('warehouse_chart_of_account_type', 'I')
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();

                $oResultLiabilities = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<=', $to)->where('warehouse_chart_of_account_type', 'L')
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();

                $oResultExpenses = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<=', $to)->where('warehouse_chart_of_account_type', 'E')
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();
            } else {

                $oResultAssets = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'A')
                    ->where('warehouse_chart_of_head_level', $level)
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();

                $oResultIncomes = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'I')
                    ->where('warehouse_chart_of_head_level', $level)
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();

                $oResultLiabilities = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'L')
                    ->where('warehouse_chart_of_head_level', $level)
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();

                $oResultExpenses = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'E')
                    ->where('warehouse_chart_of_head_level', $level)
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();
            }

            return view('backend.common.reports.warehouse_report.reports', compact('warehouses', 'from', 'to', 'warehouse_id', 'warehouseInfo', 'level', 'oResultAssets', 'oResultIncomes', 'oResultExpenses', 'oResultEquities', 'oResultLiabilities', 'accounts','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function trialBalanceWarehouseIndex()
    {


        try {
            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            // $lavel=WarehouseChartOfAccount::distinct('head_level')->pluck('head_level','head_level');
            return view('backend.common.reports.warehouse_trial_balances.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function trialBalanceWarehouseshow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $warehouse_id = $request->warehouse_id;
            $level = $request->level;
            $warehouse = Warehouse::where('id', $warehouse_id)->first();
            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            $PreBalance = 0;
            $preDebCre = 'De/Cr';
            $PreResultAssets = '';
            $PreResultIncomes = '';
            $PreResultExpenses = '';
            $PreResultLiabilities = '';
            $PreResultEquities = '';
            $oResultAssets = '';
            $oResultIncomes = '';
            $oResultEquities = '';
            $oResultExpenses = '';
            $oResultLiabilities = '';

            if ($level == 'all') {
                $pre_sum_assets_debit = 0;
                $pre_sum_assets_credit = 0;
                $PreResultAssets = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<', $from)
                    ->where('warehouse_chart_of_account_type', 'A')

                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();

                if (count($PreResultAssets) > 0) {
                    foreach ($PreResultAssets as $PreResultAsset) {
                        $pre_sum_assets_debit += $PreResultAsset->debit;
                        $pre_sum_assets_credit += $PreResultAsset->credit;
                    }
                }
                $pre_sum_income_debit = 0;
                $pre_sum_income_credit = 0;
                $PreResultIncomes = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<', $from)->where('warehouse_chart_of_account_type', 'I')
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();
                if (count($PreResultIncomes) > 0) {
                    foreach ($PreResultIncomes as $PreResultIncome) {
                        $pre_sum_income_debit += $PreResultIncome->debit;
                        $pre_sum_income_credit += $PreResultIncome->credit;
                    }
                }

                $pre_sum_expense_debit = 0;
                $pre_sum_expense_credit = 0;

                $PreResultExpenses = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<', $from)->where('warehouse_chart_of_account_type', 'E')
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();
                if (count($PreResultExpenses) > 0) {
                    foreach ($PreResultExpenses as $PreResultExpense) {
                        $pre_sum_expense_debit += $PreResultExpense->debit;
                        $pre_sum_expense_credit += $PreResultExpense->credit;
                    }
                }


                $pre_sum_liability_debit = 0;
                $pre_sum_liability_credit = 0;

                $PreResultLiabilities = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<', $from)->where('warehouse_chart_of_account_type', 'L')
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();
                if (count($PreResultLiabilities) > 0) {
                    foreach ($PreResultLiabilities as $PreResultLiabilitie) {
                        $pre_sum_liability_debit += $PreResultLiabilitie->debit;
                        $pre_sum_liability_credit += $PreResultLiabilitie->credit;
                    }
                }

                $final_pre_sum_debit = $pre_sum_assets_debit + $pre_sum_income_debit + $pre_sum_expense_debit + $pre_sum_liability_debit;
                $final_pre_sum_credit = $pre_sum_assets_credit + $pre_sum_income_credit + $pre_sum_expense_credit + $pre_sum_liability_credit;
                if ($final_pre_sum_debit > $final_pre_sum_credit) {
                    $PreBalance = $final_pre_sum_debit - $final_pre_sum_credit;
                    $preDebCre = 'De';
                } else {
                    $PreBalance = $final_pre_sum_credit - $final_pre_sum_debit;
                    $preDebCre = 'Cr';
                }
                $oResultAssets = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'A')
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();

                $oResultIncomes = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'I')
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();

                $oResultLiabilities = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'L')
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();

                $oResultExpenses = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'E')
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();
            } else {


                $pre_sum_assets_debit = 0;
                $pre_sum_assets_credit = 0;
                $PreResultAssets = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<', $from)
                    ->where('warehouse_chart_of_account_type', 'A')
                    ->where('warehouse_chart_of_head_level', $level)
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();

                if (count($PreResultAssets) > 0) {
                    foreach ($PreResultAssets as $PreResultAsset) {
                        $pre_sum_assets_debit += $PreResultAsset->debit;
                        $pre_sum_assets_credit += $PreResultAsset->credit;
                    }
                }
                $pre_sum_income_debit = 0;
                $pre_sum_income_credit = 0;
                $PreResultIncomes = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<', $from)->where('warehouse_chart_of_account_type', 'I')
                    ->where('warehouse_chart_of_head_level', $level)
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();
                if (count($PreResultIncomes) > 0) {
                    foreach ($PreResultIncomes as $PreResultIncome) {
                        $pre_sum_income_debit += $PreResultIncome->debit;
                        $pre_sum_income_credit += $PreResultIncome->credit;
                    }
                }

                $pre_sum_expense_debit = 0;
                $pre_sum_expense_credit = 0;

                $PreResultExpenses = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<', $from)->where('warehouse_chart_of_account_type', 'E')
                    ->where('warehouse_chart_of_head_level', $level)
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();
                if (count($PreResultExpenses) > 0) {
                    foreach ($PreResultExpenses as $PreResultExpense) {
                        $pre_sum_expense_debit += $PreResultExpense->debit;
                        $pre_sum_expense_credit += $PreResultExpense->credit;
                    }
                }


                $pre_sum_liability_debit = 0;
                $pre_sum_liability_credit = 0;

                $PreResultLiabilities = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<', $from)->where('warehouse_chart_of_account_type', 'L')
                    ->where('warehouse_chart_of_head_level', $level)
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();
                if (count($PreResultLiabilities) > 0) {
                    foreach ($PreResultLiabilities as $PreResultLiabilitie) {
                        $pre_sum_liability_debit += $PreResultLiabilitie->debit;
                        $pre_sum_liability_credit += $PreResultLiabilitie->credit;
                    }
                }

                $final_pre_sum_debit = $pre_sum_assets_debit + $pre_sum_income_debit + $pre_sum_expense_debit + $pre_sum_liability_debit;
                $final_pre_sum_credit = $pre_sum_assets_credit + $pre_sum_income_credit + $pre_sum_expense_credit + $pre_sum_liability_credit;
                if ($final_pre_sum_debit > $final_pre_sum_credit) {
                    $PreBalance = $final_pre_sum_debit - $final_pre_sum_credit;
                    $preDebCre = 'De';
                } else {
                    $PreBalance = $final_pre_sum_credit - $final_pre_sum_debit;
                    $preDebCre = 'Cr';
                }
                $oResultAssets = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'A')
                    ->where('warehouse_chart_of_head_level', $level)
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();

                $oResultIncomes = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'I')
                    ->where('warehouse_chart_of_head_level', $level)
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();

                $oResultLiabilities = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'L')
                    ->where('warehouse_chart_of_head_level', $level)
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();

                $oResultExpenses = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'E')
                    ->where('warehouse_chart_of_head_level', $level)
                    ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                    ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                    ->get();
            }
            return view('backend.common.reports.warehouse_trial_balances.reports', compact('warehouses', 'from', 'to', 'warehouse_id', 'warehouse', 'level', 'oResultAssets', 'oResultIncomes', 'oResultExpenses', 'oResultEquities', 'oResultLiabilities', 'PreBalance', 'preDebCre','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function cashFlowWarehouseIndex()
    {
        try {
            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
                $warehouseChartOfAccounts = WarehouseChartOfAccount::wherehead_name('CASH IN HAND')->get();
            } else {
                $warehouse_id = $this->User->warehouse_id;
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
                $warehouseChartOfAccounts = WarehouseChartOfAccount::wherewarehouse_id($this->User->warehouse_id)->wherehead_name('CASH IN HAND')->get();
            }


            return view('backend.common.reports.warehouse_cash_flow.index', compact('warehouses', 'warehouseChartOfAccounts'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function cashFlowWarehouseShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            //dd($request->warehouse_id);
            $warehouse_id = $request->warehouse_id;
            $warehouse = Warehouse::find($warehouse_id);
            //$warehouse_chart_of_account_id = $request->warehouse_chart_of_account_id;
            $warehouse_chart_of_account_id = WarehouseChartOfAccount::wherehead_name('CASH IN HAND')->wherewarehouse_id($warehouse_id)->first()->id;
            $warehouse_chart_of_account = WarehouseChartOfAccount::find($warehouse_chart_of_account_id);
            $warehouseReports = ChartOfAccountTransaction::with('warehouse')->whereapproved_status('Approved')->where('warehouse_chart_of_account_id', $warehouse_chart_of_account_id)->where('warehouse_id', $warehouse_id)->whereBetween('date', array($from, $to))->get();

            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            /*  $warehouseChartOfAccounts = WarehouseChartOfAccount::wherewarehouse_id($warehouse_id)->wherehead_name('CASH IN HAND')->get(); */
            return view('backend.common.reports.warehouse_cash_flow.reports', compact('warehouseReports', 'warehouses', 'from', 'to', 'warehouse_id', 'warehouse_chart_of_account_id', 'warehouse_chart_of_account', 'warehouse','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function fundFlowWarehouseIndex()
    {
        try {
            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
                $warehouseChartOfAccounts = WarehouseChartOfAccount::wherehead_name('CASH IN HAND')->get();
            } else {
                $warehouse_id = $this->User->warehouse_id;
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
                $warehouseChartOfAccounts = WarehouseChartOfAccount::wherewarehouse_id($this->User->warehouse_id)->wherehead_name('CASH IN HAND')->get();
            }


            return view('backend.common.reports.warehouse_fund_flow.index', compact('warehouses', 'warehouseChartOfAccounts'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function fundFlowWarehouseShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        //dd($request->warehouse_id);
        $warehouse_id = $request->warehouse_id;
        $warehouse = Warehouse::find($warehouse_id);
        //$warehouse_chart_of_account_id = $request->warehouse_chart_of_account_id;
        $warehouse_chart_of_account_id = WarehouseChartOfAccount::wherehead_name('CASH AND BANK BALANCE')->wherewarehouse_id($warehouse_id)->first()->id;
        $warehouse_chart_of_account = WarehouseChartOfAccount::find($warehouse_chart_of_account_id);
        $warehouseReports = ChartOfAccountTransaction::with('warehouse')->whereapproved_status('Approved')->wherewarehouse_chart_of_account_parent_name('CASH AND BANK BALANCE')->where('warehouse_id', $warehouse_id)->whereBetween('date', array($from, $to))->get();

        if ($this->User->user_type === 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }
        
        return view('backend.common.reports.warehouse_fund_flow.reports', compact('warehouseReports', 'warehouses', 'from', 'to', 'warehouse_id', 'warehouse', 'warehouse_chart_of_account','default_currency'));
         } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        } 
    }

    public function bankFlowWarehouseIndex()
    {
        try {
            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouse_id = $this->User->warehouse_id;
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.warehouse_bank_flow.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function bankFlowWarehouseShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
       $warehouse_id = $request->warehouse_id;
        $warehouse = Warehouse::find($warehouse_id);
        //$warehouse_chart_of_account_id = $request->warehouse_chart_of_account_id;
        $warehouse_chart_of_account_id = WarehouseChartOfAccount::wherehead_name('CASH AND BANK BALANCE')->wherewarehouse_id($warehouse_id)->first()->id;
        $warehouse_chart_of_account = WarehouseChartOfAccount::find($warehouse_chart_of_account_id);
        $warehouseReports = ChartOfAccountTransaction::with('warehouse')->whereapproved_status('Approved')->wherewarehouse_chart_of_account_parent_name('CASH AND BANK BALANCE')
            ->where('warehouse_chart_of_account_name', '!=', 'CASH IN HAND')
            ->where('warehouse_chart_of_account_name', '!=', 'PETTY CASH')
            ->where('warehouse_id', $warehouse_id)->whereBetween('date', array($from, $to))->get();

        if ($this->User->user_type === 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }
        
        return view('backend.common.reports.warehouse_bank_flow.reports', compact('warehouseReports', 'warehouses', 'from', 'to', 'warehouse_id', 'warehouse_chart_of_account_id', 'warehouse_chart_of_account', 'warehouse','default_currency'));
         } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        } 
    }
    public function lossProfitWarehouseIndex()
    {
        try {
            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.warehouse_loss_profits.index')->with('warehouses', $warehouses);
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function  lossProfitWarehouseshow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $level = $request->level;
        $warehouse = Warehouse::where('id', $warehouse_id)->first();
        if ($this->User->user_type === 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }

        $PreBalance = 0;
        $preDebCre = 'De/Cr';
        $PreResultAssets = '';
        $PreResultIncomes = '';
        $PreResultExpenses = '';
        $PreResultLiabilities = '';
        $PreResultEquities = '';
        $oResultAssets = '';
        $oResultIncomes = '';
        $oResultEquities = '';
        $oResultExpenses = '';
        $oResultLiabilities = '';

        if (1) {
            $pre_sum_assets_debit = 0;
            $pre_sum_assets_credit = 0;
            $PreResultAssets = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<', $from)
                ->where('warehouse_chart_of_account_type', 'A')

                ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                ->get();

            if (count($PreResultAssets) > 0) {
                foreach ($PreResultAssets as $PreResultAsset) {
                    $pre_sum_assets_debit += $PreResultAsset->debit;
                    $pre_sum_assets_credit += $PreResultAsset->credit;
                }
            }
            $pre_sum_income_debit = 0;
            $pre_sum_income_credit = 0;
            $PreResultIncomes = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<', $from)->where('warehouse_chart_of_account_type', 'I')
                ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                ->get();
            if (count($PreResultIncomes) > 0) {
                foreach ($PreResultIncomes as $PreResultIncome) {
                    $pre_sum_income_debit += $PreResultIncome->debit;
                    $pre_sum_income_credit += $PreResultIncome->credit;
                }
            }

            $pre_sum_expense_debit = 0;
            $pre_sum_expense_credit = 0;

            $PreResultExpenses = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<', $from)->where('warehouse_chart_of_account_type', 'E')
                ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                ->get();
            if (count($PreResultExpenses) > 0) {
                foreach ($PreResultExpenses as $PreResultExpense) {
                    $pre_sum_expense_debit += $PreResultExpense->debit;
                    $pre_sum_expense_credit += $PreResultExpense->credit;
                }
            }


            $pre_sum_liability_debit = 0;
            $pre_sum_liability_credit = 0;

            $PreResultLiabilities = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->where('date', '<', $from)->where('warehouse_chart_of_account_type', 'L')
                ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                ->get();
            if (count($PreResultLiabilities) > 0) {
                foreach ($PreResultLiabilities as $PreResultLiabilitie) {
                    $pre_sum_liability_debit += $PreResultLiabilitie->debit;
                    $pre_sum_liability_credit += $PreResultLiabilitie->credit;
                }
            }

            $final_pre_sum_debit = $pre_sum_assets_debit + $pre_sum_income_debit + $pre_sum_expense_debit + $pre_sum_liability_debit;
            $final_pre_sum_credit = $pre_sum_assets_credit + $pre_sum_income_credit + $pre_sum_expense_credit + $pre_sum_liability_credit;
            if ($final_pre_sum_debit > $final_pre_sum_credit) {
                $PreBalance = $final_pre_sum_debit - $final_pre_sum_credit;
                $preDebCre = 'De';
            } else {
                $PreBalance = $final_pre_sum_credit - $final_pre_sum_debit;
                $preDebCre = 'Cr';
            }
            $oResultAssets = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'A')
                ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                ->get();

            $oResultIncomes = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'I')
                ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                ->get();

            $oResultLiabilities = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'L')
                ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                ->get();

            $oResultExpenses = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_id($warehouse_id)->whereBetween('date', array($from, $to))->where('warehouse_chart_of_account_type', 'E')
                ->selectRaw('chart_of_account_transactions.warehouse_chart_of_account_id,chart_of_account_transactions.warehouse_chart_of_account_name, COALESCE(sum(chart_of_account_transactions.debit),0) debit,COALESCE(sum(chart_of_account_transactions.credit),0) credit')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_id')
                ->groupBy('chart_of_account_transactions.warehouse_chart_of_account_name')
                ->get();
        }
        return view('backend.common.reports.warehouse_loss_profits.reports', compact('warehouses', 'from', 'to', 'warehouse_id', 'warehouse', 'level', 'oResultAssets', 'oResultIncomes', 'oResultExpenses', 'oResultEquities', 'oResultLiabilities', 'PreBalance', 'preDebCre','default_currency'));
         } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        } 
    }
    public function WarehousecurrentstockIndex()
    {
        try {
            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.warehouse_current_stock_reports.index')->with('warehouses', $warehouses);
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function  Warehousecurrentstockshow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $warehouse_id = $request->warehouse_id;
            $warehouseReports = WarehouseStock::with('warehouse')->where('warehouse_id', $warehouse_id)->whereBetween('created_at', array($from . " 00:00:00", $to . " 23:59:59"))->get();
            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            $warehouseInfo = Warehouse::where('id', $warehouse_id)->first();

            return view('backend.common.reports.warehouse_current_stock_reports.reports', compact('warehouseReports', 'warehouses', 'warehouseInfo', 'from', 'to', 'warehouse_id','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function MultipleWarehouseCurrentStockIndex()
    {

        try {
            $products = Product::wherestatus(1)->pluck('name', 'id');
            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.multiple_warehouse_current_stock_reports.index', compact('warehouses', 'products'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function  MultipleWarehouseCurrentStockShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $warehouse_ids = $request->warehouse_id;
        $product_ids = $request->product_id;
         try {

        $warehouseReports = WarehouseCurrentStock::with('warehouse')
            ->selectRaw(DB::raw('SUM(current_stock_qty) as current_stock_qty, product_id'))
            ->whereIn('warehouse_current_stocks.warehouse_id',  $warehouse_ids)
            ->whereIn('warehouse_current_stocks.product_id', $product_ids)
            ->groupBy('warehouse_current_stocks.product_id')
            ->get();
        $products = Product::wherestatus(1)->pluck('name', 'id');
        if ($this->User->user_type === 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }

        return view('backend.common.reports.multiple_warehouse_current_stock_reports.reports', compact('warehouseReports', 'warehouses', 'products', 'warehouse_ids', 'product_ids'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function MultipleVanCurrentStockIndex()
    {
        try {
            $products = Product::wherestatus(1)->pluck('name', 'id');
            if ($this->User->user_type === 'Super Admin') {
                $vans = Van::wherestatus(1)->pluck('name', 'id');
            } else {
                $vans = Van::wherewarehouse_id($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.multiple_van_current_stock_reports.index', compact('vans', 'products'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function  MultipleVanCurrentStockShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $van_ids = $request->van_id;
        $product_ids = $request->product_id;
        try {

        $vanReports = VanRouteCurrentStock::with('van')
            ->whereIn('van_route_current_stocks.van_id',  $van_ids)
            ->whereIn('van_route_current_stocks.product_id', $product_ids)
            ->groupBy('van_route_current_stocks.product_id')
            ->get();

        $products = Product::wherestatus(1)->pluck('name', 'id');
        if ($this->User->user_type === 'Super Admin') {
            $vans = Van::wherestatus(1)->pluck('name', 'id');
        } else {
            $vans = Van::wherewarehouse_id($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }

        return view('backend.common.reports.multiple_van_current_stock_reports.reports', compact('vanReports', 'vans', 'products', 'van_ids', 'product_ids','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function MultipleRouteCurrentStockIndex()
    {
        try {
            $products = Product::wherestatus(1)->pluck('name', 'id');
            if ($this->User->user_type === 'Super Admin') {
                $routes = Route::wherestatus(1)->pluck('name', 'id');
            } else {
                $routes = Route::wherewarehouse_id($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.multiple_route_current_stock_reports.index', compact('routes', 'products'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function  MultipleRouteCurrentStockShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $route_ids = $request->route_id;
        $product_ids = $request->product_id;
     try {

       
        $routeReports = VanRouteCurrentStock::with('route')
            ->whereIn('van_route_current_stocks.route_id',  $route_ids)
            ->whereIn('van_route_current_stocks.product_id', $product_ids)
            ->groupBy('van_route_current_stocks.product_id')
            ->get();
        // dd($routeReports);

        $products = Product::wherestatus(1)->pluck('name', 'id');
        if ($this->User->user_type === 'Super Admin') {
            $routes = Route::wherestatus(1)->pluck('name', 'id');
        } else {
            $routes = Route::wherewarehouse_id($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }

        return view('backend.common.reports.multiple_route_current_stock_reports.reports', compact('routeReports', 'routes', 'products', 'route_ids', 'product_ids','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function VancurrentstockIndex()
    {
        try {
            if ($this->User->user_type === 'Super Admin') {
                $vans = Van::wherestatus(1)->pluck('name', 'id');
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $vans = Van::wherewarehouse_id($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.van_current_stock_reports.index', compact('vans', 'warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function  Vancurrentstockshow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $warehouse_id = $request->warehouse_id;
            $route_id = $request->route_id;
            $van_id = $request->van_id;
            $vanWiseSaleReports = Sale::where('van_id', '=', $van_id)->get();
            $warehouseInfo = Warehouse::where('id', $warehouse_id)->first();
            $routeInfo = Route::findOrFail($route_id);
            $vanInfo = Van::findOrFail($van_id);
            $business_setting = BusinessSetting::all();

            if (empty($van_id)) {
                $vanReports = VanRouteStock::with('van')->whereBetween('created_at', array($from . " 00:00:00", $to . " 23:59:59"))->get();
            } else {
                $vanReports = VanRouteStock::with('van')->where('van_id', $van_id)->whereBetween('created_at', array($from . " 00:00:00", $to . " 23:59:59"))->get();
            }
            if ($this->User->user_type === 'Super Admin') {
                $vans = Van::wherestatus(1)->pluck('name', 'id');
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $vans = Van::wherewarehouse_id($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.van_current_stock_reports.reports', compact('vanReports', 'warehouseInfo', 'warehouseInfo', 'routeInfo', 'vanInfo', 'warehouses', 'warehouse_id', 'van_id', 'route_id', 'from', 'to', 'business_setting','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function RoutecurrentstockIndex()
    {
        try {
            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
                $routes = Route::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
                $routes = Route::wherewarehouse_id($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.route_current_stock_reports.index', compact('routes', 'warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function  Routecurrentstockshow(Request $request)
    {

        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
         try {
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        $warehouseInfo = Warehouse::where('id', $warehouse_id)->first();
        $routeInfo = Route::findOrFail($route_id);
        $business_setting = BusinessSetting::all();

        if (empty($route_id)) {
            $routeReports = VanRouteStock::with('route')->whereBetween('created_at', array($from . " 00:00:00", $to . " 23:59:59"))->get();
        } else {
            $routeReports = VanRouteStock::with('route')->where('route_id', $route_id)->whereBetween('created_at', array($from . " 00:00:00", $to . " 23:59:59"))->get();
        }
        if ($this->User->user_type === 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            $routes = Route::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            $routes = Route::wherewarehouse_id($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }

        return view('backend.common.reports.route_current_stock_reports.reports', compact('routeReports', 'warehouses', 'routes', 'from', 'to', 'warehouse_id', 'route_id', 'warehouseInfo', 'routeInfo','default_currency'));

        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function TradingaccountIndex()
    {

        try {
            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.trading_report.index')->with('warehouses', $warehouses);
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function Tradingaccountshow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $warehouse_id = $request->warehouse_id;

            if (empty($warehouse_id)) {
                $warehouseReports = ChartOfAccountTransaction::with('warehouse')->whereapproved_status('Approved')->where('warehouse_id', '!=', 'NULL')->whereBetween('date', array($from, $to))->get();
            } else {
                // dd($warehouse_id);
                $warehouseReports = ChartOfAccountTransaction::with('warehouse')->whereapproved_status('Approved')->where('warehouse_id', $warehouse_id)->whereBetween('date', array($from, $to))->get();
            }
            if ($this->User->user_type === 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            $warehouseInfo = Warehouse::where('id', $warehouse_id)->first();

            return view('backend.common.reports.trading_report.reports', compact('warehouseReports', 'warehouses', 'from', 'to', 'warehouse_id', 'warehouseInfo','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    //Sales Report Controller
    public function saleWarehouseWiseIndex()
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.sale_warehouse_wise_report.index', compact('warehouses','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function saleWarehouseWiseShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $business_setting = BusinessSetting::all();

        $warehouseInfo = Warehouse::where('id', $warehouse_id)->first();
        $warehouse = Warehouse::find($warehouse_id);
        $previewtype = $request->previewtype;
        $warehouseWiseSaleReports = Sale::where('warehouse_id', '=', $warehouse_id)->whereBetween('date', array($from, $to))->get();

        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }
        if ($previewtype == 'htmlview') {
            return view('backend.common.reports.sale_warehouse_wise_report.reports', compact('warehouseWiseSaleReports', 'from', 'to', 'warehouses', 'warehouse_id', 'warehouseInfo', 'business_setting', 'previewtype','default_currency'));
        } elseif ($previewtype == 'pdfview') {
            $pdf = Pdf::loadView('backend.common.reports.sale_warehouse_wise_report.pdf_view', compact('warehouseWiseSaleReports', 'from', 'to', 'warehouse', 'warehouse_id', 'warehouseInfo', 'business_setting', 'previewtype','default_currency'));
            return $pdf->stream('warehouse_sale_report_' . now() . '.pdf');
        } else {
            return view('backend.common.reports.sale_warehouse_wise_report.reports', compact('warehouseWiseSaleReports', 'from', 'to', 'warehouses', 'warehouse_id', 'warehouseInfo', 'business_setting', 'previewtype','default_currency'));
        }
    }

    public function saleRouteWiseIndex()
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.sale_route_wise_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function saleRouteWiseShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();

        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        $routeWiseSaleReports = Sale::where('route_id', '=', $route_id)->get();
        $routeInfo = Route::findOrFail($route_id);
        $business_setting = BusinessSetting::all();
        $previewtype = $request->previewtype;
        $warehouse = Warehouse::find($warehouse_id);
        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }
        if ($previewtype == 'htmlview') {
            return view('backend.common.reports.sale_route_wise_report.reports', compact('routeWiseSaleReports', 'warehouses', 'warehouse_id', 'route_id', 'from', 'to', 'routeInfo', 'business_setting', 'previewtype','default_currency'));
        } elseif ($previewtype == 'pdfview') {
            $pdf = Pdf::loadView('backend.common.reports.sale_route_wise_report.pdf_view', compact('routeWiseSaleReports', 'warehouse', 'warehouse_id', 'route_id', 'from', 'to', 'routeInfo', 'business_setting', 'previewtype','default_currency'));
            return $pdf->stream('warehouse_sale_report_' . now() . '.pdf');
        } else {
            return view('backend.common.reports.sale_route_wise_report.reports', compact('routeWiseSaleReports', 'warehouses', 'warehouse_id', 'route_id', 'from', 'to', 'routeInfo', 'business_setting', 'previewtype','default_currency'));
        }
    }

    public function saleVanWiseIndex()
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.sale_van_wise_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function saleVanWiseShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        $van_id = $request->van_id;
        $vanWiseSaleReports = Sale::where('van_id', '=', $van_id)->get();
        $routeInfo = Route::findOrFail($route_id);
        $vanInfo = Van::findOrFail($van_id);
        $business_setting = BusinessSetting::all();
        $warehouse = Warehouse::find($warehouse_id);
        $previewtype = $request->previewtype;

        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }

        if ($previewtype == 'htmlview') {
            return view('backend.common.reports.sale_van_wise_report.reports', compact('vanWiseSaleReports', 'routeInfo', 'vanInfo', 'warehouses', 'warehouse_id', 'van_id', 'route_id', 'from', 'to', 'business_setting','default_currency'));
        } elseif ($previewtype == 'pdfview') {
            $pdf = Pdf::loadView('backend.common.reports.sale_van_wise_report.pdf_view', compact('vanWiseSaleReports', 'from', 'to', 'warehouse', 'warehouse_id', 'van_id', 'route_id', 'routeInfo', 'vanInfo', 'business_setting', 'previewtype','default_currency'));
            return $pdf->stream('sale_van_wise_report_' . now() . '.pdf');
        } else {
            return view('backend.common.reports.sale_van_wise_report.reports', compact('vanWiseSaleReports', 'routeInfo', 'vanInfo', 'warehouses', 'warehouse_id', 'van_id', 'route_id', 'from', 'to', 'business_setting','default_currency'));
        }
    }

    public function saleProductWiseIndex()
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
                $products = Product::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
                $products = Product::wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.sale_product_wise_report.index', compact('warehouses', 'products'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function saleProductWiseShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        $van_id = $request->van_id;
        $product_id = $request->product_id;
        $routeInfo = Route::find($route_id);
        $vanInfo = Van::find($van_id);
        $business_setting = BusinessSetting::all();
        $warehouse = Warehouse::find($warehouse_id);
        $previewtype = $request->previewtype;
        if ($route_id == !null && $van_id == null) {
            $vanWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
                ->wherewarehouse_id($warehouse_id)
                ->whereroute_id($route_id)
                ->whereBetween('sales.date', array($from, $to))
                ->where('sale_details.product_id', $product_id)
                ->select('sales.*')->get();
        } elseif ($route_id == !null && $van_id == !null) {
            $vanWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
                ->wherewarehouse_id($warehouse_id)
                ->whereroute_id($route_id)
                ->wherevan_id($van_id)
                ->whereBetween('sales.date', array($from, $to))
                ->where('sale_details.product_id', $product_id)
                ->select('sales.*')->get();
        } else {
            $vanWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
                ->wherewarehouse_id($warehouse_id)
                ->whereBetween('sales.date', array($from, $to))
                ->where('sale_details.product_id', $product_id)
                ->select('sales.*')->get();
        }

        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            $products = Product::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            $products = Product::wherestatus(1)->pluck('name', 'id');
        }

        if ($previewtype == 'htmlview') {

            return view('backend.common.reports.sale_product_wise_report.reports', compact('vanWiseSaleReports', 'warehouses', 'warehouse_id', 'product_id', 'products', 'route_id', 'van_id', 'routeInfo', 'vanInfo', 'business_setting', 'from', 'to','default_currency'));
        } elseif ($previewtype == 'pdfview') {
            $pdf = Pdf::loadView('backend.common.reports.sale_product_wise_report.pdf_view', compact('vanWiseSaleReports', 'from', 'to', 'warehouse', 'warehouse_id', 'product_id', 'products', 'van_id', 'route_id', 'routeInfo', 'vanInfo', 'business_setting', 'previewtype','default_currency'));
            return $pdf->stream('sale_product_wise_report_' . now() . '.pdf');
        } else {
            return view('backend.common.reports.sale_product_wise_report.reports', compact('vanWiseSaleReports', 'warehouses', 'warehouse_id', 'product_id', 'products', 'route_id', 'van_id', 'routeInfo', 'vanInfo', 'business_setting', 'from', 'to','default_currency'));
        }
    }

    public function saleAmountWiseIndex()
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.sale_amount_wise_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function saleAmountWiseShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        $van_id = $request->van_id;
        $amount = $request->amount;
        $amount_status = $request->amount_status;
        if ($amount_status == 'Equal') {
            $condition = '=';
        } elseif ($amount_status == 'Greater') {
            $condition = '>=';
        } else {
            $condition = '<=';
        }
        $routeInfo = Route::find($route_id);
        $vanInfo = Van::find($van_id);
        $business_setting = BusinessSetting::all();
        $warehouse = Warehouse::find($warehouse_id);
        $previewtype = $request->previewtype;
        if ($route_id == !null && $van_id == null) {
            $vanWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
                ->wherewarehouse_id($warehouse_id)
                ->whereroute_id($route_id)
                ->whereBetween('sales.date', array($from, $to))
                ->where('grand_total', $condition, $amount)
                ->select('sales.*')->get();
        } elseif ($route_id == !null && $van_id == !null) {
            $vanWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
                ->wherewarehouse_id($warehouse_id)
                ->whereroute_id($route_id)
                ->wherevan_id($route_id)
                ->whereBetween('sales.date', array($from, $to))
                ->where('grand_total', $condition, $amount)
                ->select('sales.*')->get();
        } else {
            $vanWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
                ->wherewarehouse_id($warehouse_id)
                ->whereBetween('sales.date', array($from, $to))
                ->where('grand_total', $condition, $amount)
                ->select('sales.*')->get();
        }

        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            $products = Product::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            $products = Product::wherestatus(1)->pluck('name', 'id');
        }

        if ($previewtype == 'htmlview') {
            return view('backend.common.reports.sale_amount_wise_report.reports', compact('vanWiseSaleReports', 'warehouses', 'warehouse_id', 'amount', 'route_id', 'van_id', 'routeInfo', 'vanInfo', 'business_setting', 'from', 'to', 'amount_status','default_currency'));
        } elseif ($previewtype == 'pdfview') {
            $pdf = Pdf::loadView('backend.common.reports.sale_amount_wise_report.pdf_view', compact('vanWiseSaleReports', 'from', 'to', 'warehouse', 'warehouse_id', 'van_id', 'route_id', 'routeInfo', 'vanInfo', 'business_setting', 'previewtype','default_currency'));
            return $pdf->stream('sale_amount_wise_report_' . now() . '.pdf');
        } else {
            return view('backend.common.reports.sale_amount_wise_report.reports', compact('vanWiseSaleReports', 'warehouses', 'warehouse_id', 'amount', 'route_id', 'van_id', 'routeInfo', 'vanInfo', 'business_setting', 'from', 'to', 'amount_status','default_currency'));
        }
    }

    public function fastMovingItemsIndex()
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.fast_moving_items_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function fastMovingItemsShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        $routeInfo = Route::find($route_id);
        $warehouseInfo = Warehouse::find($warehouse_id);
        if ($route_id == !null) {
            $fastMovingItemsReports = SaleDetails::join('sales', 'sales.id', 'sale_details.sale_id')
                ->where('sales.warehouse_id', $warehouse_id)
                ->where('sales.route_id', $route_id)
                ->whereBetween('sales.date', array($from, $to))
                ->selectRaw('sale_details.product_id, COALESCE(sum(sale_details.qty),0) total_qty')
                ->groupBy('sale_details.product_id')
                ->orderBy('total_qty', 'desc')
                ->limit(10)
                ->get();
        } else {
            $fastMovingItemsReports = SaleDetails::join('sales', 'sales.id', 'sale_details.sale_id')
                ->where('sales.warehouse_id', $warehouse_id)
                ->whereBetween('sales.date', array($from, $to))
                ->selectRaw('sale_details.product_id, COALESCE(sum(sale_details.qty),0) total_qty')
                ->groupBy('sale_details.product_id')
                ->orderBy('total_qty', 'desc')
                ->limit(10)
                ->get();
        }

        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }

        return view('backend.common.reports.fast_moving_items_report.reports', compact('fastMovingItemsReports', 'warehouses', 'warehouse_id', 'route_id', 'from', 'to', 'routeInfo', 'warehouseInfo','default_currency'));
    }

    public function slowMovingItemsIndex()
    {
        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
                $products = Product::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
                $products = Product::wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.slow_moving_items_report.index', compact('warehouses', 'products'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function slowMovingItemsShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        $routeInfo = Route::find($route_id);
        $warehouseInfo = Warehouse::find($warehouse_id);

        if ($route_id == !null) {
            $slowMovingItemsReports = SaleDetails::join('sales', 'sales.id', 'sale_details.sale_id')
                ->where('sales.warehouse_id', $warehouse_id)
                ->where('sales.route_id', $route_id)
                ->whereBetween('sales.date', array($from, $to))
                ->selectRaw('sale_details.product_id, COALESCE(sum(sale_details.qty),0) total_qty')
                ->groupBy('sale_details.product_id')
                ->orderBy('total_qty', 'asc')
                ->limit(10)
                ->get();
        } else {
            $slowMovingItemsReports = SaleDetails::join('sales', 'sales.id', 'sale_details.sale_id')
                ->where('sales.warehouse_id', $warehouse_id)
                ->whereBetween('sales.date', array($from, $to))
                ->selectRaw('sale_details.product_id, COALESCE(sum(sale_details.qty),0) total_qty')
                ->groupBy('sale_details.product_id')
                ->orderBy('total_qty', 'asc')
                ->limit(10)
                ->get();
        }

        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }

        return view('backend.common.reports.slow_moving_items_report.reports', compact('slowMovingItemsReports', 'warehouses', 'warehouse_id', 'route_id', 'from', 'to', 'routeInfo', 'warehouseInfo','default_currency'));
    }

    public function nonMovingItemsIndex()
    {
        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.non_moving_items_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function nonMovingItemsShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        $routeInfo = Route::find($route_id);
        $warehouseInfo = Warehouse::find($warehouse_id);

        if ($route_id == !null) {
            $stockTransferDetails = StockTransferDetails::join('stock_transfers', 'stock_transfers.id', 'stock_transfer_details.stock_transfer_id')
                ->where('stock_transfers.to_warehouse_id', $warehouse_id)
                ->where('stock_transfers.to_route_id', $route_id)
                ->whereBetween('stock_transfers.receive_datetime', array($from, $to))
                ->selectRaw('stock_transfer_details.product_id, COALESCE(sum(stock_transfer_details.received_qty),0) total_qty')
                ->groupBy('stock_transfer_details.product_id')
                ->orderBy('total_qty', 'asc')
                ->limit(10)
                ->get();

            $nonMovingItemsReports = [];
            if (count($stockTransferDetails) > 0) {
                foreach ($stockTransferDetails as $stockTransferDetail) {
                    $saleProductQty = SaleDetails::join('sales', 'sales.id', 'sale_details.sale_id')
                        ->where('sales.warehouse_id', $warehouse_id)
                        ->where('sales.route_id', $route_id)
                        ->whereproduct_id($stockTransferDetail->product_id)
                        ->whereBetween('sales.date', array($from, $to))
                        ->selectRaw('sale_details.product_id, COALESCE(sum(sale_details.qty),0) total_qty')
                        ->groupBy('sale_details.product_id')
                        ->first();

                    if (($stockTransferDetail->total_qty > 0) && (empty($saleProductQty))) {
                        $nested_data['product_id'] = $stockTransferDetail->product_id;
                        $nested_data['transfer_total_qty'] = $stockTransferDetail->total_qty;
                        $nested_data['sale_total_qty'] = 0;

                        array_push($nonMovingItemsReports, $nested_data);
                    }
                }
            }
        } else {
            $stockTransferDetails = StockTransferDetails::join('stock_transfers', 'stock_transfers.id', 'stock_transfer_details.stock_transfer_id')
                ->where('stock_transfers.to_warehouse_id', $warehouse_id)
                ->whereBetween('stock_transfers.receive_datetime', array($from, $to))
                ->selectRaw('stock_transfer_details.product_id, COALESCE(sum(stock_transfer_details.received_qty),0) total_qty')
                ->groupBy('stock_transfer_details.product_id')
                ->orderBy('total_qty', 'asc')
                ->limit(10)
                ->get();

            $nonMovingItemsReports = [];
            if (count($stockTransferDetails) > 0) {
                foreach ($stockTransferDetails as $stockTransferDetail) {
                    $saleProductQty = SaleDetails::join('sales', 'sales.id', 'sale_details.sale_id')
                        ->where('sales.warehouse_id', $warehouse_id)
                        ->whereproduct_id($stockTransferDetail->product_id)
                        ->whereBetween('sales.date', array($from, $to))
                        ->selectRaw('sale_details.product_id, COALESCE(sum(sale_details.qty),0) total_qty')
                        ->groupBy('sale_details.product_id')
                        ->first();

                    if (($stockTransferDetail->total_qty > 0) && (empty($saleProductQty))) {
                        $nested_data['product_id'] = $stockTransferDetail->product_id;
                        $nested_data['transfer_total_qty'] = $stockTransferDetail->total_qty;
                        $nested_data['sale_total_qty'] = 0;

                        array_push($nonMovingItemsReports, $nested_data);
                    }
                }
            }
        }


        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }
        // dd($nonMovingItemsReports);

        return view('backend.common.reports.non_moving_items_report.reports', compact('nonMovingItemsReports', 'warehouses', 'warehouse_id', 'route_id', 'from', 'to', 'routeInfo', 'warehouseInfo','default_currency'));
    }

    public function fastMovingItemsRouteWiseIndex()
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.fast_moving_items_route_wise_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function fastMovingItemsRouteWiseShow(Request $request)
    {

        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();

        $routeWiseSaleDetailReports = SaleDetails::join('sales', 'sales.id', 'sale_details.sale_id')
            ->where('sales.warehouse_id', $warehouse_id)
            ->where('sales.route_id', $route_id)
            ->whereBetween('sales.date', array($from, $to))
            ->selectRaw('sale_details.product_id, COALESCE(count(sale_details.product_total),0) total')
            ->groupBy('sale_details.product_id')
            ->orderBy('total', 'desc')
            ->get();

        $routeInfo = Route::find($route_id);
        $warehouse = Warehouse::find($warehouse_id);

        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }

        return view('backend.common.reports.fast_moving_items_route_wise_report.reports', compact('routeWiseSaleDetailReports', 'warehouses', 'warehouse_id', 'route_id', 'from', 'to', 'routeInfo', 'warehouse','default_currency'));
    }

    public function slowMovingItemsRouteWiseIndex()
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.slow_moving_items_route_wise_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function slowMovingItemsRouteWiseShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        $routeWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
            ->wherewarehouse_id($warehouse_id)
            ->whereBetween('sales.date', array($from, $to))
            ->selectRaw('sales.*, COALESCE(count(sale_details.product_id),0) total')
            ->groupBy('sales.id')
            ->orderBy('total', 'asc')
            ->get();

        $routeInfo = Route::find($route_id);
        $warehouse = Warehouse::find($warehouse_id);


        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }

        return view('backend.common.reports.slow_moving_items_route_wise_report.reports', compact('routeWiseSaleReports', 'warehouses', 'warehouse_id', 'route_id', 'from', 'to', 'routeInfo', 'warehouse','default_currency'));
    }
    public function saleDetailsIndex()
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.sale_details_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function saleDetailsShow(Request $request)
    {  
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        if ($route_id == null) {
            $routeWiseSaleReports = Sale::where('warehouse_id', '=', $warehouse_id)->first();
            $routeWiseSaleDetailReports = SaleDetails::whereid($routeWiseSaleReports->id)->get();
        } else {
            $routeWiseSaleReports = Sale::where('route_id', '=', $route_id)->first();
            if ($routeWiseSaleReports) {
                $routeWiseSaleDetailReports = SaleDetails::whereid($routeWiseSaleReports->id)->get();
            } else {
                Toastr::error('No data found', "Error");
                return back();
            }
        }
        $routeInfo = Route::find($route_id);
        $warehouse = Warehouse::find($warehouse_id);
        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }

        return view('backend.common.reports.sale_details_report.reports', compact('routeWiseSaleReports', 'routeWiseSaleDetailReports', 'routeInfo', 'warehouses', 'warehouse_id', 'route_id', 'from', 'to', 'warehouse','default_currency'));
    }

    public function saleOrderDetailsIndex()
    {
        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.sale_order_details_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function saleOrderDetailsShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        $order_id = $request->id;
        if ($route_id == null) {
            $routeWiseSaleReports = Sale::where('warehouse_id', '=', $warehouse_id)->whereinvoice_no($order_id)->first();
            if ($routeWiseSaleReports) {
                $routeWiseSaleDetailReports = SaleDetails::whereid($routeWiseSaleReports->id)->get();
            } else {
                $routeWiseSaleDetailReports = collect([]);
            }
        } else {
            $routeWiseSaleReports = Sale::where('route_id', '=', $route_id)->whereinvoice_no($order_id)->first();
            if ($routeWiseSaleReports) {
                $routeWiseSaleDetailReports = SaleDetails::whereid($routeWiseSaleReports->id)->get();
            } else {
                $routeWiseSaleDetailReports = collect([]);
            }
        }

        $routeInfo = Route::find($route_id);
        $warehouse = Warehouse::find($warehouse_id);

        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }

        return view('backend.common.reports.sale_order_details_report.reports', compact('routeWiseSaleReports', 'routeWiseSaleDetailReports', 'routeInfo', 'warehouses', 'warehouse_id', 'route_id', 'from', 'to', 'warehouse', 'order_id','default_currency'));
    }

    public function RefundOrderIndex()
    {

        try {

            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.refund_order_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function RefundOrderShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        if ($route_id == null) {
            $routeWiseSaleReports = SaleReturnCustomerToWarehouse::where('warehouse_id', '=', $warehouse_id)->get();
        } else {
            $routeWiseSaleReports = SaleReturnCustomerToVan::where('warehouse_id', '=', $warehouse_id)->get();
        }

        $routeInfo = Route::find($route_id);
        $warehouse = Warehouse::find($warehouse_id);
        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }
        return view('backend.common.reports.refund_order_report.reports', compact('routeWiseSaleReports', 'routeInfo', 'warehouses', 'warehouse_id', 'route_id', 'from', 'to', 'warehouse','default_currency'));
    }
    public function saleSummaryQuentityWiseIndex(Request $request)
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.sale_summary_quantity_wise_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function saleSummaryQuentityWiseShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        $van_id = $request->van_id;
        $quantity = $request->quantity;
        $quantity_status = $request->quantity_status;
        if ($quantity_status == 'Equal') {
            $condition = '=';
        } elseif ($quantity_status == 'Greater') {
            $condition = '>=';
        } else {
            $condition = '<=';
        }
        $warehouseInfo = Warehouse::find($warehouse_id);
        $routeInfo = Route::find($route_id);
        $vanInfo = Van::find($van_id);
        $business_setting = BusinessSetting::all();
        $warehouse = Warehouse::find($warehouse_id);
        $previewtype = $request->previewtype;

        if ($route_id == !null && $van_id == null) {
            $vanWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
                ->wherewarehouse_id($warehouse_id)
                ->whereroute_id($route_id)
                ->whereBetween('sales.date', array($from, $to))
                ->where('sale_details.qty', $condition, $quantity)
                ->select('sales.*')->get();
        } elseif ($route_id == !null && $van_id == !null) {
            $vanWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
                ->wherewarehouse_id($warehouse_id)
                ->whereroute_id($route_id)
                ->wherevan_id($route_id)
                ->whereBetween('sales.date', array($from, $to))
                ->where('sale_details.qty', $condition, $quantity)
                ->select('sales.*')->get();
        } else {
            $vanWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
                ->wherewarehouse_id($warehouse_id)
                ->whereBetween('sales.date', array($from, $to))
                ->where('sale_details.qty', $condition, $quantity)
                ->select('sales.*')->get();
        }

        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            $products = Product::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            $products = Product::wherestatus(1)->pluck('name', 'id');
        }

        if ($previewtype == 'htmlview') {
            return view('backend.common.reports.sale_summary_quantity_wise_report.reports', compact('vanWiseSaleReports', 'warehouses', 'warehouse_id', 'quantity', 'products', 'route_id', 'van_id', 'warehouseInfo', 'routeInfo', 'vanInfo', 'business_setting', 'from', 'to', 'quantity_status','default_currency'));
        } elseif ($previewtype == 'pdfview') {
            $pdf = Pdf::loadView('backend.common.reports.sale_summary_quantity_wise_report.pdf_view', compact('vanWiseSaleReports', 'from', 'to', 'warehouse', 'warehouse_id', 'van_id', 'route_id', 'routeInfo', 'vanInfo', 'business_setting', 'previewtype','default_currency'));
            return $pdf->stream('sale_summary_quantity_wise_report_' . now() . '.pdf');
        } else {
            return view('backend.common.reports.sale_summary_quantity_wise_report.reports', compact('vanWiseSaleReports', 'warehouses', 'warehouse_id', 'quantity', 'products', 'route_id', 'van_id', 'warehouseInfo', 'routeInfo', 'vanInfo', 'business_setting', 'from', 'to', 'quantity_status','default_currency'));
        }
    }
    public function saleSummaryAmountWiseIndex(Request $request)
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.sale_summary_amount_wise_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }


    public function saleSummaryAmountWiseShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        $van_id = $request->van_id;
        $amount = $request->amount;
        $amount_status = $request->amount_status;
        if ($amount_status == 'Equal') {
            $condition = '=';
        } elseif ($amount_status == 'Greater') {
            $condition = '>=';
        } else {
            $condition = '<=';
        }
        $routeInfo = Route::find($route_id);
        $vanInfo = Van::find($van_id);
        $business_setting = BusinessSetting::all();
        $warehouse = Warehouse::find($warehouse_id);
        $previewtype = $request->previewtype;

        if ($route_id == !null && $van_id == null) {
            $vanWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
                ->wherewarehouse_id($warehouse_id)
                ->whereroute_id($route_id)
                ->whereBetween('sales.date', array($from, $to))
                ->where('grand_total', $condition, $amount)
                ->select('sales.*')->get();
        } elseif ($route_id == !null && $van_id == !null) {
            $vanWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
                ->wherewarehouse_id($warehouse_id)
                ->whereroute_id($route_id)
                ->wherevan_id($route_id)
                ->whereBetween('sales.date', array($from, $to))
                ->where('grand_total', $condition, $amount)
                ->select('sales.*')->get();
        } else {
            $vanWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
                ->wherewarehouse_id($warehouse_id)
                ->whereBetween('sales.date', array($from, $to))
                ->where('grand_total', $condition, $amount)
                ->select('sales.*')->get();
        }

        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            $products = Product::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            $products = Product::wherestatus(1)->pluck('name', 'id');
        }

        if ($previewtype == 'htmlview') {
            return view('backend.common.reports.sale_summary_amount_wise_report.reports', compact('vanWiseSaleReports', 'warehouses', 'warehouse_id', 'amount', 'products', 'route_id', 'van_id', 'routeInfo', 'vanInfo', 'business_setting', 'from', 'to', 'amount_status','default_currency'));
        } elseif ($previewtype == 'pdfview') {
            $pdf = Pdf::loadView('backend.common.reports.sale_summary_amount_wise_report.pdf_view', compact('vanWiseSaleReports', 'from', 'to', 'warehouse', 'warehouse_id', 'van_id', 'route_id', 'routeInfo', 'vanInfo', 'business_setting', 'previewtype','default_currency'));
            return $pdf->stream('sale_summary_amount_wise_report_' . now() . '.pdf');
        } else {
            return view('backend.common.reports.sale_summary_amount_wise_report.reports', compact('vanWiseSaleReports', 'warehouses', 'warehouse_id', 'amount', 'products', 'route_id', 'van_id', 'routeInfo', 'vanInfo', 'business_setting', 'from', 'to', 'amount_status','default_currency'));
        }
    }

    public function generalLedgerIndex(Request $request)
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.general_ledger_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function generalLedgerShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $warehouse_chart_of_account_id = $request->warehouse_chart_of_account_id;
        $warehouse_chart_of_account = WarehouseChartOfAccount::find($warehouse_chart_of_account_id);
        $route_id = $request->route_id;
        if ($route_id == null) {
            $pre_balance_debit = ChartOfAccountTransaction::where('warehouse_id', '=', $warehouse_id)->wherewarehouse_chart_of_account_id($warehouse_chart_of_account_id)->where('date', '<', $from)->whereapproved_status('Approved')->sum('debit');
            $pre_balance_credit = ChartOfAccountTransaction::where('warehouse_id', '=', $warehouse_id)->wherewarehouse_chart_of_account_id($warehouse_chart_of_account_id)->where('date', '<', $from)->whereapproved_status('Approved')->sum('credit');
            $routeWiseSaleReports = ChartOfAccountTransaction::where('warehouse_id', '=', $warehouse_id)->wherewarehouse_chart_of_account_id($warehouse_chart_of_account_id)->whereBetween('date', array($from, $to))->whereapproved_status('Approved')->get();
        } else {
            $pre_balance_debit = ChartOfAccountTransaction::where('warehouse_id', '=', $warehouse_id)->where('route_id', '=', $route_id)->wherewarehouse_chart_of_account_id($warehouse_chart_of_account_id)->where('date', '<', $from)->whereapproved_status('Approved')->sum('debit');
            $pre_balance_credit = ChartOfAccountTransaction::where('warehouse_id', '=', $warehouse_id)->where('route_id', '=', $route_id)->wherewarehouse_chart_of_account_id($warehouse_chart_of_account_id)->where('date', '<', $from)->whereapproved_status('Approved')->sum('credit');
            $routeWiseSaleReports = ChartOfAccountTransaction::where('warehouse_id', '=', $warehouse_id)->wherewarehouse_chart_of_account_id($warehouse_chart_of_account_id)->where('route_id', '=', $route_id)->whereBetween('date', array($from, $to))->whereapproved_status('Approved')->get();
        }

        if ($pre_balance_debit > $pre_balance_credit) {
            $pre_balance = $pre_balance_debit - $pre_balance_credit;
            $pre_balance_deb_cre = 'De';
        } else {
            $pre_balance = $pre_balance_credit - $pre_balance_debit;
            $pre_balance_deb_cre = 'Cr';
        }
        $business_setting = BusinessSetting::all();
        $routeInfo = Route::find($route_id);
        $warehouse = Warehouse::find($warehouse_id);
        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }
        return view('backend.common.reports.general_ledger_report.reports', compact('routeWiseSaleReports', 'routeInfo', 'warehouses', 'warehouse_id', 'route_id', 'from', 'to', 'warehouse', 'business_setting', 'pre_balance', 'pre_balance_deb_cre', 'warehouse_chart_of_account_id', 'warehouse_chart_of_account','default_currency'));
    }

    public function saleSummaryAmountAndQuantityWiseIndex(Request $request)
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.sale_summary_amount_and_quantity_wise_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function saleSummaryAmountAndQuantityWiseShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        $van_id = $request->van_id;
        $amount = $request->amount;
        $amount_status = $request->amount_status;
        if ($amount_status == 'Equal') {
            $amount_condition = '=';
        } elseif ($amount_status == 'Greater') {
            $amount_condition = '>=';
        } else {
            $amount_condition = '<=';
        }

        $quantity = $request->quantity;
        $quantity_status = $request->quantity_status;
        if ($quantity_status == 'Equal') {
            $quantity_condition = '=';
        } elseif ($quantity_status == 'Greater') {
            $quantity_condition = '>=';
        } else {
            $quantity_condition = '<=';
        }

        $routeInfo = Route::find($route_id);
        $vanInfo = Van::find($van_id);
        $business_setting = BusinessSetting::all();
        $warehouse = Warehouse::find($warehouse_id);
        $previewtype = $request->previewtype;
        if ($route_id == !null && $van_id == null) {
            $vanWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
                ->wherewarehouse_id($warehouse_id)
                ->whereroute_id($route_id)
                ->whereBetween('sales.date', array($from, $to))
                ->where('sales.grand_total', $amount_condition, $amount)
                ->where('sale_details.qty', $quantity_condition, $quantity)
                ->select('sales.*')->get();
        } elseif ($route_id == !null && $van_id == !null) {
            $vanWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
                ->wherewarehouse_id($warehouse_id)
                ->whereroute_id($route_id)
                ->wherevan_id($route_id)
                ->whereBetween('sales.date', array($from, $to))
                ->where('sales.grand_total', $amount_condition, $amount)
                ->where('sale_details.qty', $quantity_condition, $quantity)
                ->select('sales.*')->get();
        } else {
            $vanWiseSaleReports = Sale::join('sale_details', 'sales.id', 'sale_details.sale_id')
                ->wherewarehouse_id($warehouse_id)
                ->whereBetween('sales.date', array($from, $to))
                ->where('sales.grand_total', $amount_condition, $amount)
                ->where('sale_details.qty', $quantity_condition, $quantity)
                ->select('sales.*')->get();
        }

        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            $products = Product::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            $products = Product::wherestatus(1)->pluck('name', 'id');
        }

        if ($previewtype == 'htmlview') {
            return view('backend.common.reports.sale_summary_amount_and_quantity_wise_report.reports', compact('vanWiseSaleReports', 'warehouses', 'warehouse_id', 'amount', 'products', 'route_id', 'van_id', 'routeInfo', 'vanInfo', 'business_setting', 'from', 'to', 'amount_status', 'quantity', 'quantity_status','default_currency'));
        } elseif ($previewtype == 'pdfview') {
            $pdf = Pdf::loadView('backend.common.reports.sale_summary_amount_and_quantity_wise_report.pdf_view', compact('vanWiseSaleReports', 'warehouse', 'warehouse_id', 'route_id', 'from', 'to', 'routeInfo', 'van_id', 'vanInfo', 'business_setting', 'previewtype','default_currency'));
            return $pdf->stream('warehouse_sale_report_' . now() . '.pdf');
        } else {
            return view('backend.common.reports.sale_summary_amount_and_quantity_wise_report.reports', compact('vanWiseSaleReports', 'warehouses', 'warehouse_id', 'amount', 'products', 'route_id', 'van_id', 'routeInfo', 'vanInfo', 'business_setting', 'from', 'to', 'amount_status', 'quantity', 'quantity_status','default_currency'));
        }
    }

    public function itemListsIndex(Request $request)
    {

        try {
            return view('backend.common.reports.item_list_report.index');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }


    public function itemListsShow(Request $request)
    {
        try {
            $default_currency = $this->getCurrencyInfoByDefaultCurrency();
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $status = $request->status;
            $business_setting = BusinessSetting::all();
            $productReport = Product::with('productprice', 'product_department', 'product_section', 'brand', 'category')->wherestatus($status)->whereBetween('created_at', array($from . " 00:00:00", $to . " 23:59:59"))->select('id', 'name', 'created_at', 'status', 'product_department_id', 'product_section_id', 'unit_variant', 'brand_id', 'category_id', 'subcategory_id', 'average_purchase_price')->get();

            return view('backend.common.reports.item_list_report.reports', compact('productReport', 'business_setting', 'from', 'to', 'status','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function bankSalesIndex(Request $request)
    {
        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.bank_sales_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }


    public function bankSalesShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $bank_id = $request->bank_id;
        $prefix = 'Bank' . $bank_id;
        //$route_id = $request->route_id;

        $coaInfo = WarehouseChartOfAccount::where('prefix', $prefix)->first();
        if ($coaInfo) {
            $bankReports = ChartOfAccountTransaction::whereapproved_status('Approved')->wherewarehouse_chart_of_account_id($coaInfo->id)->wherewarehouse_id($warehouse_id)
                ->wheretransaction_type('Customer Receive')
                ->whereBetween('date', array($from, $to))->get();
        } else {
            $bankReports = collect([]);
        }

        //$routeInfo = Route::find($route_id);
        $warehouse = Warehouse::find($warehouse_id);
        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }
        return view('backend.common.reports.bank_sales_report.reports', compact('bankReports', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'bank_id','default_currency'));



          } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        } 
    }
    public function bestRouteIndex(Request $request)
    {
        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.best_route_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }



    public function bestRouteShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $warehouse_id = $request->warehouse_id;
            $routeWiseSaleReports = Sale::with('warehouse', 'route')->where('warehouse_id', '=', $warehouse_id)->where('route_id', '!=', null)->whereBetween('date', array($from, $to))
                ->selectRaw('sales.route_id,sales.warehouse_id, COALESCE(sum(sales.grand_total),0) grand_total')
                ->groupBy('sales.route_id')
                ->groupBy('sales.warehouse_id')
                ->orderBy('grand_total', 'desc')
                ->get();
            $business_setting = BusinessSetting::all();
            $warehouse = Warehouse::find($warehouse_id);
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.best_route_report.reports', compact('routeWiseSaleReports',  'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }


    public function getBanks($id)
    {
        $info = BankAccount::wherestatus(1)->wherewarehouse_id($id)->select('bank_name', 'id')->get();
        return ($info);
    }

    public function warehouseSaleReturnIndex(Request $request)
    {
        
        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.warehouse_sales_return_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }



    public function warehouseSaleReturnShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $warehouse_id = $request->warehouse_id;
            $warehouseSaleReturnReports = SaleReturnCustomerToWarehouse::where('warehouse_id', '=', $warehouse_id)->whereBetween('date', array($from, $to))->get();
            $business_setting = BusinessSetting::all();
            $warehouse = Warehouse::find($warehouse_id);
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.warehouse_sales_return_report.reports', compact('warehouseSaleReturnReports', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function vanSaleReturnIndex(Request $request)
    {
        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.van_sales_return_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }



    public function vanSaleReturnShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $warehouse_id = $request->warehouse_id;
            $van_id = $request->van_id;
            $vanSaleReturnReports = SaleReturnCustomerToVan::where('van_id', '=', $van_id)->whereBetween('date', array($from, $to))->get();
            $business_setting = BusinessSetting::all();
            $warehouse = Warehouse::find($warehouse_id);
            $van = Van::find($van_id);
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.van_sales_return_report.reports', compact('vanSaleReturnReports', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'van_id', 'van','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function SalesAnalysisIndex(Request $request)
    {


        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.sales_analysis_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }


    public function SalesAnalysisShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $warehouse_id = $request->warehouse_id;
            $van_id = $request->van_id;
            $business_setting = BusinessSetting::all();
            $warehouse = Warehouse::find($warehouse_id);
            $vanInfo = Van::find($van_id);
            if ($van_id == null) {
                $chart_saleanalysis = [
                    'chart_title' => $warehouse->name . ' Sales Analysis',
                    'report_type' => 'group_by_date',
                    'model' => 'App\Models\Sale',
                    'conditions' => [
                        ['warehouse_id' => $warehouse_id, 'van_id' => NULL, 'route_id' => NULL, 'color' => 'black', 'fill' => true],
                    ],
                    'group_by_field' => 'created_at',
                    'filter_field' => 'date',
                    'range_date_start' => $from,
                    'range_date_end' => $to,
                    'aggregate_function' => 'sum',
                    'aggregate_field' => 'grand_total',
                    'group_by_period' => 'day',
                    'chart_type' => 'bar',
                ];
            } else {

                $chart_saleanalysis = [
                    'chart_title' => $vanInfo->name . ' Sales Analysis',
                    'report_type' => 'group_by_date',
                    'model' => 'App\Models\Sale',
                    'conditions' => [
                        ['van_id' => $van_id, 'color' => 'black', 'fill' => false],
                    ],
                    'group_by_field' => 'created_at',
                    'filter_field' => 'date',
                    'range_date_start' => $from,
                    'range_date_end' => $to,
                    'aggregate_function' => 'sum',
                    'aggregate_field' => 'grand_total',
                    'group_by_period' => 'day',
                    'chart_type' => 'bar',
                ];
            }


            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            $saleAnalysis_chart = new LaravelChart($chart_saleanalysis);
            return view('backend.common.reports.sales_analysis_report.reports', compact('saleAnalysis_chart', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'van_id', 'vanInfo','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }




    public function oldSalesAnalysisIndex(Request $request)
    {


        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            $warehousestock = WarehouseStock::with('warehouse')->groupBy('warehouse_id')->get();

            $chart = new WarehouseStockChart;
            $warehousestock = WarehouseStock::with('warehouse')->wherewarehouse_id()->pluck('stock', 'date');
            // $warehousestock=WarehouseStock::with('warehouse')->groupBy('warehouse_id')->pluck('stock','date');

            $chart = new WarehouseStockChart;
            $chart->labels($warehousestock->keys());
            $chart->dataset('store', 'line', $warehousestock->values());
            $chart->loaderColor("#FF5733");
            $chart->width(100);
            $chart->height(300);

            return view('backend.common.reports.sales_analysis_report.index', compact('warehouses', 'chart','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }


    public function PurchaseOrderIndex(Request $request)
    {


        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.purchase_order_report.index', compact('warehouses','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }


    public function findWarehouseSuplier(Request $request)
    {
        return User::wherewarehouse_id($request->warehouse_id)->whereuser_type('supplier')->select('id', 'name')->get();
    }

    public function PurchaseOrderShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $warehouse_id = $request->warehouse_id;
            $supplier_id = $request->supplier_id;
            if ($supplier_id == null) {
                $purchsesReport = Receive::where('warehouse_id', '=', $warehouse_id)->whererequisition_id(null)->whereBetween('date', array($from, $to))->get();
            } else {
                $purchsesReport = Receive::where('warehouse_id', '=', $warehouse_id)->wheresupplier_user_id($supplier_id)->whererequisition_id(null)->whereBetween('date', array($from, $to))->get();
            }
            $business_setting = BusinessSetting::all();
            $warehouse = Warehouse::find($warehouse_id);
            $supplier = User::find($supplier_id);

            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.purchase_order_report.reports', compact('purchsesReport', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'supplier_id', 'supplier','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function PurchaseVsRequistionIndex(Request $request)
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.purchase_vs_requistion_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function PurchaseVsRequistionShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $warehouse_id = $request->warehouse_id;
            //$supplier_id = $request->supplier_id;

            $requisitionReport = RequisitionGoodsAndService::with('details')->where('warehouse_id', '=', $warehouse_id)->whereBetween('requisition_date', array($from, $to))->get();

            $business_setting = BusinessSetting::all();
            $warehouse = Warehouse::find($warehouse_id);

            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.purchase_vs_requistion_report.reports', compact('warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'requisitionReport','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function requisitionIndex(Request $request)
    {
        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.requisition_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function requisitionShow(Request $request)
    {
         try {
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $requisitionReport = RequisitionGoodsAndService::with('details')->where('warehouse_id', '=', $warehouse_id)->whereBetween('requisition_date', array($from, $to))->get();

        $business_setting = BusinessSetting::all();
        $warehouse = Warehouse::find($warehouse_id);

        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }
        return view('backend.common.reports.requisition_report.reports', compact('requisitionReport', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting','default_currency'));
          } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        } 
    }

    public function ViewPurchaseOrderIndex(Request $request)
    {
        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.view_purchase_order_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }


    public function ViewPurchaseOrderShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $warehouse_id = $request->warehouse_id;
            $purchsesReport = Purchase::where('warehouse_id', '=', $warehouse_id)->whereis_viewed(1)->whereBetween('date', array($from, $to))->get();

            $business_setting = BusinessSetting::all();
            $warehouse = Warehouse::find($warehouse_id);

            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.view_purchase_order_report.reports', compact('purchsesReport', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function POCanceledApprovedReportIndex(Request $request)
    {
        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.purchase_order_canceled_approved_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }


    public function POCanceledApprovedReportIndexShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $warehouse_id = $request->warehouse_id;
            $is_received = $request->is_received;
            $purchsesReport = Purchase::where('warehouse_id', '=', $warehouse_id)->whereis_received($is_received)->whereBetween('date', array($from, $to))->get();

            $business_setting = BusinessSetting::all();
            $warehouse = Warehouse::find($warehouse_id);

            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.purchase_order_canceled_approved_report.reports', compact('purchsesReport', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'is_received','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function RequisitionCanceledApprovedIndex(Request $request)
    {
        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.requisition_canceled_approved_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }


    public function RequisitionCanceledApprovedShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $status = $request->status;
        $purchsesReport = Requisition::where('to_warehouse_id', '=', $warehouse_id)->wherestatus($status)->whereBetween('date', array($from, $to))->get();

        $business_setting = BusinessSetting::all();
        $warehouse = Warehouse::find($warehouse_id);

        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }
        return view('backend.common.reports.requisition_canceled_approved_report.reports', compact('purchsesReport', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'status','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function SupplierAgeingStockReportIndex()
    {
        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.supplier_ageing_stock_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function SupplierAgeingStockReportShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        //$route_id = $request->route_id;
        $warehouseInfo = Warehouse::findOrFail($warehouse_id);
        $ageingReports = ReceiveDetail::join('receives', 'receives.id', 'receive_details.receive_id')
            ->join('users', 'receives.supplier_user_id', 'users.id')
            ->where('receives.payment_type_id', 2)
            ->where('receives.warehouse_id', $warehouse_id)
            ->whereBetween('receives.date', array($from, $to))
            ->selectRaw('receives.invoice_no,receives.supplier_user_id, users.name, COALESCE(sum(receive_details.qty),0) total_qty, COALESCE(sum(receive_details.product_total),0) total_amount')
            ->groupBy('receives.invoice_no')
            ->groupBy('receives.supplier_user_id')
            ->get();

        $business_setting = BusinessSetting::all();

        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }

        return view('backend.common.reports.supplier_ageing_stock_report.reports', compact('ageingReports', 'warehouses', 'warehouse_id',  'from', 'to',  'warehouseInfo', 'business_setting','default_currency'));
    }

    public function CustomerAgeingStockReportIndex()
    {
        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.customer_ageing_stock_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function CustomerAgeingStockReportShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $route_id = $request->route_id;
        $warehouseInfo = Warehouse::findOrFail($warehouse_id);
        $routeInfo = '';

        if (empty($route_id)) {
            $ageingReports = SaleDetails::join('sales', 'sales.id', 'sale_details.sale_id')
                ->join('users', 'sales.customer_user_id', 'users.id')
                ->where('sales.payment_type_id', 2)
                ->where('sales.warehouse_id', $warehouse_id)
                ->whereBetween('sales.date', array($from, $to))
                ->selectRaw('sales.invoice_no,sales.customer_user_id, users.name, COALESCE(sum(sale_details.qty),0) total_qty, COALESCE(sum(sales.grand_total),0) total_amount')
                ->groupBy('sales.invoice_no')
                ->groupBy('sales.customer_user_id')
                ->get();
        } else {
            $ageingReports = SaleDetails::join('sales', 'sales.id', 'sale_details.sale_id')
                ->join('users', 'sales.customer_user_id', 'users.id')
                ->where('sales.payment_type_id', 2)
                ->where('sales.warehouse_id', $warehouse_id)
                ->where('sales.route_id', $route_id)
                ->whereBetween('sales.date', array($from, $to))
                ->selectRaw('sales.invoice_no,sales.customer_user_id, users.name, COALESCE(sum(sale_details.qty),0) total_qty, COALESCE(sum(sales.grand_total),0) total_amount')
                ->groupBy('sales.invoice_no')
                ->groupBy('sales.customer_user_id')
                ->get();
            $ageingReports = Sale::where('route_id', '=', $route_id)->get();
            $routeInfo = Route::findOrFail($route_id);
        }

        $business_setting = BusinessSetting::all();

        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }

        return view('backend.common.reports.customer_ageing_stock_report.reports', compact('ageingReports', 'warehouses', 'warehouse_id', 'route_id', 'from', 'to', 'routeInfo', 'warehouseInfo', 'business_setting','default_currency'));
    }



    public function vanToWarehouseRetunIndex()
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.van_to_warehouse_sales_return_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function vanToWarehouseRetunShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        try {
            $from = date('Y-m-d', strtotime($request->start_date));
            $to = date('Y-m-d', strtotime($request->end_date));
            $warehouse_id = $request->warehouse_id;
            $van_id = $request->van_id;
            $vanSaleReturnReports = SaleReturnVanToWarehouse::where('van_id', '=', $van_id)->whereBetween('date', array($from, $to))->get();
            $business_setting = BusinessSetting::all();
            $warehouse = Warehouse::find($warehouse_id);
            $van = Van::find($van_id);
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }
            return view('backend.common.reports.van_to_warehouse_sales_return_report.reports', compact('vanSaleReturnReports', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'van_id', 'van','default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function customerToVanRetunIndex()
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.customer_to_van_sales_return_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }
    public function customerToVanRetunShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
         try {
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $van_id = $request->van_id;
        $previewtype = $request->previewtype;
        $vanSaleReturnReports = SaleReturnCustomerToVan::where('van_id', '=', $van_id)->whereBetween('date', array($from, $to))->get();
        $business_setting = BusinessSetting::all();
        $warehouse = Warehouse::find($warehouse_id);
        $van = Van::find($van_id);
        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }
        if ($previewtype == 'excellview') {


            return  Excel::download(new SaleReturnCustomerToVanExport($request), now() . '_sale_return_customer.xlsx');
           

        } elseif ($previewtype == 'printview') {
            $pdf = Pdf::loadView('backend.common.reports.customer_to_van_sales_return_report.pdf_view', compact('vanSaleReturnReports', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'van_id', 'van','default_currency'));
            return $pdf->stream('vantotwarehouseprint_' . now() . '.pdf');
        } else {
            return view('backend.common.reports.customer_to_van_sales_return_report.reports', compact('vanSaleReturnReports', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'van_id', 'van', 'previewtype','default_currency'));
        }
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }

    }

    public function purchaseReportIndex()
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.purchase_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    //mark: purchaseReportShow
    public function purchaseReportShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
         try {
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $supplier_id = $request->supplier_id;
        $previewtype = $request->previewtype;
        if ($supplier_id == null) {
            $supplier=[];
       $purchaseReports = Purchase::with('purchasedetails')->wherewarehouse_id($warehouse_id )->whereBetween('date', array($from . " 00:00:00", $to . " 23:59:59"))->get();
        }else{
            $supplier=User::find($supplier_id);
            $purchaseReports = Purchase::with('purchasedetails')->wherewarehouse_id($warehouse_id )->wheresupplier_user_id($supplier_id)->whereBetween('date', array($from . " 00:00:00", $to . " 23:59:59"))->get();
           
        }
        $business_setting = BusinessSetting::all();
        $warehouse = Warehouse::find($warehouse_id);
        
        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }
        if ($previewtype == 'excellview') {
            return  Excel::download(new SaleReturnCustomerToVanExport($request), now() . '_purchase_report.xlsx');           

        } elseif ($previewtype == 'pdfview') {
            $pdf = Pdf::loadView('backend.common.reports.purchase_report.pdf_view', compact('purchaseReports', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'supplier_id', 'supplier', 'previewtype','default_currency'));
            return $pdf->stream('purchase_report_' . now() . '.pdf');
        } else {
            return view('backend.common.reports.purchase_report.reports', compact('purchaseReports', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'supplier_id', 'supplier', 'previewtype','default_currency'));
        }
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }

    }


    public function purchaseOrderReportIndex()
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.purchases_order_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }



    public function purchaseOrderReportShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
          try {
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $supplier_id = $request->supplier_id;
        $previewtype = $request->previewtype;
        if ($supplier_id == null) {
            $supplier=[];
       $purchaseReports = Requisition::with('requisitiondetail')->wherewarehouse_id($warehouse_id )->whereBetween('date', array($from . " 00:00:00", $to . " 23:59:59"))->get();
        }else{
            $supplier=User::find($supplier_id);
            $purchaseReports = Requisition::with('requisitiondetail')->wherewarehouse_id($warehouse_id )->wheresupplier_user_id($supplier_id)->whereBetween('date', array($from . " 00:00:00", $to . " 23:59:59"))->get();
           
        }
        $business_setting = BusinessSetting::all();
        $warehouse = Warehouse::find($warehouse_id);
        
        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }
        if ($previewtype == 'excellview') {
            return  Excel::download(new SaleReturnCustomerToVanExport($request), now() . '_purchase_order_report.xlsx');           

        } elseif ($previewtype == 'pdfview') {
            $pdf = Pdf::loadView('backend.common.reports.purchases_order_report.pdf_view', compact('purchaseReports', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'supplier_id', 'supplier', 'previewtype','default_currency'));
            return $pdf->stream('purchase_report_' . now() . '.pdf');
        } else {
            return view('backend.common.reports.purchases_order_report.reports', compact('purchaseReports', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'supplier_id', 'supplier', 'previewtype','default_currency'));
        }
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }

    }

    public function SalesmanSalesReportIndex()
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.salessman_sale_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function SalesmanSalesReportShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
          try {
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $user_id = $request->user_id;
        $previewtype = $request->previewtype;
         $User=User::find($user_id);
  
        $saleReports =Sale::wherewarehouse_id($warehouse_id)->wherecreated_by_user_id($user_id)
        ->whereBetween('created_at', array($from . " 00:00:00", $to . " 23:59:59"))->get();
           
     
        $business_setting = BusinessSetting::all();
        $warehouse = Warehouse::find($warehouse_id);
        
        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }
        if ($previewtype == 'excellview') {
            return  Excel::download(new SaleReturnCustomerToVanExport($request), now() . '_purchase_order_report.xlsx');           

        } elseif ($previewtype == 'pdfview') {
            $pdf = Pdf::loadView('backend.common.reports.salessman_sale_report.pdf_view', compact('saleReports', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'user_id', 'User', 'previewtype','default_currency'));
            return $pdf->stream('purchase_report_' . now() . '.pdf');
        } else {
            return view('backend.common.reports.salessman_sale_report.reports', compact('saleReports', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'user_id', 'User', 'previewtype','default_currency'));
        }
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }

    }



    public function StockTransferReportIndex()
    {

        try {
            if ($this->User->user_type == 'Super Admin') {
                $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
            } else {
                $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
            }

            return view('backend.common.reports.stock_transfers_report.index', compact('warehouses'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function StockTransferReportShow(Request $request)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        //   try {
        $from = date('Y-m-d', strtotime($request->start_date));
        $to = date('Y-m-d', strtotime($request->end_date));
        $warehouse_id = $request->warehouse_id;
        $transfer_type = $request->transfer_type;
        $van_id = $request->van_id;
        $previewtype = $request->previewtype;
        if ($van_id == null) {
            $van=[];
        $stockTransferReports = StockTransfer::with('stocktransferdetails')->wheretransfer_type($transfer_type)->whereis_received(1)
        ->whereBetween('created_at', array($from . " 00:00:00", $to . " 23:59:59"))
        ->where(function ($query) use($request){
            $query->whereto_warehouse_id($request->warehouse_id)
                ->orWhere('from_warehouse_id', $request->warehouse_id);
                })->get();
       }
       else{
        $van = Van::find($van_id);
       $stockTransferReports = StockTransfer::with('stocktransferdetails')->wheretransfer_type($transfer_type)->whereis_received(1)
        ->whereBetween('created_at', array($from . " 00:00:00", $to . " 23:59:59"))
        ->where(function ($query) use($request){$query->whereto_van_id($request->van_id)
                ->orWhere('from_van_id', $request->van_id);
                })->get();
            }
     
        $business_setting = BusinessSetting::all();
        $warehouse = Warehouse::find($warehouse_id);
        
        if ($this->User->user_type == 'Super Admin') {
            $warehouses = Warehouse::wherestatus(1)->pluck('name', 'id');
        } else {
            $warehouses = Warehouse::whereid($this->User->warehouse_id)->wherestatus(1)->pluck('name', 'id');
        }
        if ($previewtype == 'excellview') {
            return  Excel::download(new SaleReturnCustomerToVanExport($request), now() . '_stock_transfers_report.xlsx');           

        } elseif ($previewtype == 'pdfview') {
            $pdf = Pdf::loadView('backend.common.reports.stock_transfers_report.pdf_view', compact('stockTransferReports', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'transfer_type', 'van','van_id', 'previewtype','default_currency'));
            return $pdf->stream('purchase_report_' . now() . '.pdf');
        } else {
            return view('backend.common.reports.stock_transfers_report.reports', compact('stockTransferReports', 'warehouses', 'warehouse_id', 'from', 'to', 'warehouse', 'business_setting', 'transfer_type', 'van','van_id', 'previewtype','default_currency'));
        }
        // } catch (\Exception $e) {
        //     $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
        //     Toastr::error($response['message'], "Error");
        //     return back();
        // }

    }




}
