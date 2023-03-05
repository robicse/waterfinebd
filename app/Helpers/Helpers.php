<?php

namespace App\Helpers;


use App\Models\Module;
use App\Models\Store;
use App\Models\Product;
use App\Models\User;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\PurchaseReturn;
use App\Models\Unit;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Request;
use Intervention\Image\ImageManagerStatic as Image;


class Helper
{


    public static function make_slug($string)
    {
        return Str::slug($string, '-');
    }

    public static function getCollapseAndParentModuleList()
    {
        return Module::where('status', 1)
            ->where(function ($query) {
                $query
                    ->where('parent_menu', 'Collapse')
                    ->orWhere('parent_menu', 'Parent');
            })
            ->orderBy('serial', 'asc')
            ->get();
    }

    public static function getChildModuleList($parent)
    {
        return Module::where('parent_menu', $parent)
            ->where('status', 1)
            ->orderBy('serial', 'asc')
            ->get();
    }

    public static function getChildModuleSlugList($parent, $role)
    {
        $childModules = Module::where('parent_menu', $parent)
            ->where('status', 1)
            ->orderBy('serial', 'asc')
            ->get();
        $slugs = [];
        if (count($childModules) > 0) {
            foreach ($childModules as $key => $childModule) {
                $slugs[] = $childModule->slug;
            }
            $slugs_array = implode(',', $slugs);
            $slugList = explode(',', $slugs_array);
        } else {
            $slugList = [];
        }
        return $slugList;
    }

    public static function collapseChildMenuPermission($module_ids)
    {
        return DB::table('permissions')
            ->whereIn('module_id', $module_ids)
            ->pluck('name')
            ->first();
    }

    public static function getParentAndChildModuleList()
    {
        return Module::where('parent_menu', '!=', 'Collapse')
            ->where('status', 1)
            ->orderBy('serial', 'asc')
            ->get();
    }

    public static function getModulePermissionActionByModuleId($module_id)
    {
        return DB::table('permissions')
            ->where('module_id', $module_id)
            ->orderBy('serial', 'asc')
            ->get();
    }

    public static function getStoreList()
    {
        return Store::where('status', 1)
            ->orderBy('id', 'asc')
            ->get();
    }

    public static function getReportCount()
    {
        $reportCount = [
            'productCount' => Product::count(),
            'userCount' => User::count(),
            'customerCount' => Customer::count(),
            'supplierCount' => Supplier::count(),
        ];
        return $reportCount;
    }

    public static function getStoreReportCount($store_id)
    {
        $storeReportCount = [
            'purchaseAmount' => Purchase::wherestore_id($store_id)->sum('paid_amount'),
            'saleAmount' => Sale::wherestore_id($store_id)->sum('grand_total'),
            'purchaseReturnAmount' => PurchaseReturn::wherestore_id($store_id)->sum('total_buy_amount'),
            'saleReturnAmount' => 0,
        ];
        return $storeReportCount;
    }


    public static function getUnitName($id)
    {
        return Unit::where('id', $id)
            ->pluck('name')
            ->first();
    }



    public static function getStoreName($id)
    {
        return Store::where('id', $id)
            ->pluck('name')
            ->first();
    }

    public static function ledgerCurrentBalance($ledgers)
    {
        $balance = 0;
        foreach ($ledgers as $data) {
            $amount = $data->amount;
            if ($data->order_type_id == 2) {
                $balance += $amount;
            }
        }
        return $balance;
    }



}
