<?php

namespace App\Helpers;


use App\Models\Module;
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
            ->get();
    }


}
