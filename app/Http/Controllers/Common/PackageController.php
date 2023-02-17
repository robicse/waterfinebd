<?php

namespace App\Http\Controllers\Common;

use DataTables;

use Carbon\Carbon;
use App\Models\Unit;
use App\Models\User;
use App\Models\Brand;
use App\Models\Country;
use App\Models\Product;
use App\Models\UnitSet;
use App\Models\Category;
use App\Models\VatSetting;
use App\Models\SubCategory;
use Illuminate\Support\Str;
use App\Models\ProductPrice;
use Illuminate\Http\Request;
use App\Helpers\ErrorTryCatch;
use App\Models\ProductSection;
use App\Models\UnitSetPackage;
use App\Models\SupplierProduct;
use Sohibd\Laravelslug\Generate;
use App\Http\Traits\ProductTrait;
use App\Models\ProductDepartment;
use App\Http\Traits\CurrencyTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    use ProductTrait;
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

        $this->middleware('permission:products-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:products-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:products-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:products-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        // no delete (Robiul)
        // $check_test_trait = $this->check_trait();
        // $check_product_trait = $this->getProductNameByProductId(1);
        // dd($check_product_trait);
        // no delete (Robiul)

        /* try {
            $products = Product::latest()->paginate(5);
            return view('backend.common.products.index', compact('products'))
                ->with('i', (request()->input('page', 1) - 1) * 5);
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        } */


        try {
            $User = $this->User;
            $default_currency = $this->getCurrencyInfoByDefaultCurrency();
            if ($request->ajax()) {
                $products = Product::latest();
                return Datatables::of($products)
                    ->addIndexColumn()
                    ->addColumn('action', function ($products) use ($User) {
                        $btn = '';
                        $btn = '<span  class="d-inline-flex"><a href=' . route(\Request::segment(1) . '.products.show', $products->id) . ' class="btn btn-warning btn-sm waves-effect"><i class="fa fa-eye"></i></a>';
                        if ($User->can('products-edit')) {
                            $btn .= '<a href=' . route(\Request::segment(1) . '.products.edit', $products->id) . ' class="btn btn-info btn-sm waves-effect" style="margin-left: 5px"><i class="fa fa-edit"></i></a> <a href=' . url(\Request::segment(1) . '/duplicate-product/' . $products->id) . ' class="btn btn-info btn-sm waves-effect float-left" style="margin-left: 5px"><i class="fa fa-plus-square"></i></a>';
                        }
                        $btn .= '</span>';
                        return $btn;
                    })
                    ->addColumn('status', function ($products) {
                        if ($products->status == 0) {
                            return '<span class="badge badge-danger"> <i class="fa fa-ban"></i> </span>';
                        } else {
                            return '<span class="badge badge-success"><i class="fa fa-check-square"></i></span>';
                        }
                    })
                    ->addColumn('unit_variant', function ($products) {
                        if ($products->unit_variant == 0) {
                            return '<span class="badge badge-danger"> No </span>';
                        } else {
                            return '<span class="badge badge-success"> Yes </span>';
                        }
                    })
                    ->addColumn('unit', function ($products) {
                        return $products?->unit?->name;
                    })
                    ->addColumn('local_purchase_price', function ($products) {
                        $price = getProductPrices($products->id, $products->unit_id);
                        return $price['local_purchase_price'];
                    })
                    ->addColumn('international_purchase_price', function ($products) {
                        $price = getProductPrices($products->id, $products->unit_id);
                        return $price['international_purchase_price'];
                    })
                    ->addColumn('warehouse_sale_price', function ($products) {
                        $price = getProductPrices($products->id, $products->unit_id);
                        return $price['warehouse_sale_price'];
                    })
                    ->addColumn('min_warehouse_sale_price', function ($products) {
                        $price = getProductPrices($products->id, $products->unit_id);
                        return $price['minimum_warehouse_sale_price'];
                    })
                    ->addColumn('local_sale_price', function ($products) {
                        $price = getProductPrices($products->id, $products->unit_id);
                        return $price['local_sale_price'];
                    })
                    ->addColumn('minimum_local_sale_price', function ($products) {
                        $price = getProductPrices($products->id, $products->unit_id);
                        return $price['minimum_local_sale_price'];
                    })
                    ->addColumn('outer_sale_price', function ($products) {
                        $price = getProductPrices($products->id, $products->unit_id);
                        return $price['outer_sale_price'];
                    })
                    ->addColumn('minimum_outer_sale_price', function ($products) {
                        $price = getProductPrices($products->id, $products->unit_id);
                        return $price['minimum_outer_sale_price'];
                    })

                    ->rawColumns(['action', 'status', 'unit_variant', 'unit'])
                    ->make(true);
            }

            return view('backend.common.products.index', compact('default_currency'));
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function create()
    {
        $suppliers = User::wherestatus(1)->whereuser_type('Supplier')->pluck('name', 'id');
        $countries = Country::pluck('name', 'name');
        $categories = Category::wherestatus(1)->pluck('name', 'id');
        $brands = Brand::wherestatus(1)->pluck('name', 'id');
        $units = Unit::wherestatus(1)->pluck('name', 'id');
        $productdepartment = ProductDepartment::wherestatus(1)->pluck('name', 'id');
        $productsection = ProductSection::wherestatus(1)->pluck('name', 'id');
        $vatPercents = VatSetting::wheredefault_status(1)->wherestatus(1)->pluck('vat_percent', 'id');
        return view('backend.common.products.create', compact('suppliers', 'countries', 'categories', 'brands', 'units', 'productdepartment', 'productsection', 'vatPercents'));
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'name' => 'required|min:1|max:190',
            'unit_measurement' => 'required|min:1|max:190',
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'brand_id' => 'required',
            'product_department_id' => 'required',
            'product_section_id' => 'required',
            'barcode' => 'required|min:7|max:15|unique:products',
            'status' => 'required',
            'local_purchase_price' => 'required|numeric|min:0|max:9999999999999999',
            // 'international_purchase_price' => 'required|min:1',
            'local_sale_price' => 'required|numeric|min:0|max:9999999999999999',
            'minimum_local_sale_price' => 'required|numeric|min:0|max:9999999999999999',
            'outer_sale_price' => 'required|numeric|min:0|max:9999999999999999',
            'minimum_outer_sale_price' => 'required|numeric|min:0|max:9999999999999999',
            'warehouse_sale_price' => 'required|numeric|min:0|max:9999999999999999',
            'minimum_warehouse_sale_price' => 'required|numeric|min:0|max:9999999999999999',

        ]);
        $check = Product::wherename($request->name)->whereunit_measurement($request->unit_measurement)->first();
        if ($check) {
            return back()
                ->withErrors([
                    'name' => 'Product Name and  Measurement Are Unique',
                ]);
        }
        try {
            DB::beginTransaction();
            $login_user_id = Auth::user()->id;
            $unit_variant = $request->unit_variant;
            $get_product_code = Product::orderBy('id', 'desc')->pluck('code')->first();
            if (!empty($get_product_code)) {
                $get_product_code_after_replace = str_replace("PRO", "", $get_product_code);
                $product_code = (int)$get_product_code_after_replace + 1;
            } else {
                $product_code = 1;
            }
            $final_product_code = 'PRO' . $product_code;
            $product = new Product();
            $product->name = $request->name;
            $product->arabic_name = $request->arabic_name;
            $product->code = $final_product_code;
            $product->unit_measurement = $request->unit_measurement;
            $product->name_unit_measurement = $request->name . ' ' . $request->unit_measurement;
            $product->slug = Generate::Slug($product->name_unit_measurement);
            $product->average_purchase_price = 0;
            $product->category_id = $request->category_id;
            $product->subcategory_id = $request->subcategory_id;
            $product->product_department_id = $request->product_department_id;
            $product->product_section_id = $request->product_section_id;
            $product->brand_id = $request->brand_id;
            $product->barcode = $request->barcode;
            $product->country_of_origin = $request->country_of_origin;
            $product->expire_date = $request->expire_date;
            $product->low_inventory_alert = $request->low_inventory_alert;
            $product->image = $request->image;
            $product->detail = $request->detail;
            $product->status = $request->status;
            $product->product_barcode = $request->product_barcode;
            $product->vat_id = $request->vat_id;
            if ($request->has('unit_id')) {
                $product->unit_id =  $request->unit_id[0];
            } else {
                $product->unit_id = '1';
            }

            //  $product->unit_id = $request->unit_id[0][0]?:'1';
            $product->unit_variant = $unit_variant;
            // $product->unit_set_ids = $unit_set_ids;
            $product->created_by_user_id = $login_user_id;
            $product->updated_by_user_id = $login_user_id;


            $image = $request->file('image');
            if (isset($image)) {
                if (!is_dir(public_path() . "/uploads/product")) {
                    mkdir(public_path() .  "/uploads/product", 0777, true);
                }

                $currentDate = Carbon::now()->toDateString();
                $imagename = $currentDate . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
                $proImage = Image::make($image)->resize(200, 200)->save($image->getClientOriginalExtension());
                Storage::disk('public')->put('uploads/product/' . $imagename, $proImage);
                $product->image = 'uploads/product/' . $imagename;
            } else {
                $product->image = "uploads/product/default.png";
            }
            $product->save();

            $insert_id = $product->id;

            if ($unit_variant == 1) {
                $row_count = count($request->unit_id);
                for ($i = 0; $i < $row_count; $i++) {
                    $first_unit_quantity = $request->unit_quantity[0];
                    $first_local_purchase_price = $request->local_purchase_price;
                    $first_international_purchase_price = $request->local_purchase_price;
                    $first_local_sale_price = $request->local_sale_price;
                    $first_minimum_local_sale_price = $request->minimum_local_sale_price;
                    $first_outer_sale_price = $request->outer_sale_price;
                    $first_minimum_outer_sale_price = $request->minimum_outer_sale_price;
                    $first_warehouse_sale_price = $request->warehouse_sale_price;
                    $first_minimum_warehouse_sale_price = $request->minimum_warehouse_sale_price;

                    if ($i == 0) {
                        $unit_quantity = $first_unit_quantity;
                        $per_unit_quantity = $first_unit_quantity;
                        $local_purchase_price = $first_local_purchase_price;
                        $international_purchase_price = $first_international_purchase_price;
                        $local_sale_price = $first_local_sale_price;
                        $minimum_local_sale_price = $first_minimum_local_sale_price;
                        $outer_sale_price = $first_outer_sale_price;
                        $minimum_outer_sale_price = $first_minimum_outer_sale_price;
                        $warehouse_sale_price = $first_warehouse_sale_price;
                        $minimum_warehouse_sale_price = $first_minimum_warehouse_sale_price;
                    } else {
                        $unit_quantity = $request->unit_quantity[$i];
                        $per_unit_quantity = $first_unit_quantity / $request->unit_quantity[$i];
                        $local_purchase_price = $first_local_purchase_price / $request->unit_quantity[$i];
                        $international_purchase_price = $first_international_purchase_price / $request->unit_quantity[$i];
                        $local_sale_price = $first_local_sale_price / $request->unit_quantity[$i];
                        $minimum_local_sale_price = $first_minimum_local_sale_price / $request->unit_quantity[$i];
                        $outer_sale_price = $first_outer_sale_price / $request->unit_quantity[$i];
                        $minimum_outer_sale_price = $first_minimum_outer_sale_price / $request->unit_quantity[$i];
                        $warehouse_sale_price = $first_warehouse_sale_price / $request->unit_quantity[$i];
                        $minimum_warehouse_sale_price = $first_minimum_warehouse_sale_price / $request->unit_quantity[$i];
                    }


                    $unit_id = $request->unit_id[$i];
                    $product_prices = new ProductPrice();
                    $product_prices->unit_id = $unit_id;
                    $product_prices->unit_quantity = $unit_quantity;
                    $product_prices->per_unit_quantity = $per_unit_quantity;
                    $product_prices->product_id = $insert_id;
                    $product_prices->local_purchase_price = $local_purchase_price;
                    $product_prices->international_purchase_price = $international_purchase_price;
                    $product_prices->warehouse_sale_price = $warehouse_sale_price;
                    $product_prices->minimum_warehouse_sale_price = $minimum_warehouse_sale_price;
                    $product_prices->local_sale_price = $local_sale_price;
                    $product_prices->minimum_local_sale_price = $minimum_local_sale_price;
                    $product_prices->outer_sale_price = $outer_sale_price;
                    $product_prices->minimum_outer_sale_price = $minimum_outer_sale_price;
                    $product_prices->created_by_user_id = $login_user_id;
                    $product_prices->updated_by_user_id = $login_user_id;
                    $product_prices->save();
                }
            } else {

                $product_prices = new ProductPrice();
                $product_prices->unit_id = 1;
                $product_prices->unit_quantity = 1;
                $product_prices->per_unit_quantity = 1;
                $product_prices->product_id = $insert_id;
                $product_prices->local_purchase_price = $request->local_purchase_price;
                $product_prices->international_purchase_price = $request->local_purchase_price;
                $product_prices->warehouse_sale_price = $request->warehouse_sale_price;
                $product_prices->minimum_warehouse_sale_price = $request->minimum_warehouse_sale_price;
                $product_prices->local_sale_price = $request->local_sale_price;
                $product_prices->minimum_local_sale_price = $request->minimum_local_sale_price;
                $product_prices->outer_sale_price = $request->outer_sale_price;
                $product_prices->minimum_outer_sale_price = $request->minimum_outer_sale_price;
                $product_prices->created_by_user_id = $login_user_id;
                $product_prices->updated_by_user_id = $login_user_id;
                $product_prices->save();
            }

            // supplier product
            if ($insert_id) {
                $supplier_user_ids = $request->supplier_user_ids;
                $row_count = count($supplier_user_ids);
                if ($row_count > 0) {
                    for ($j = 0; $j < $row_count; $j++) {
                        $supplier_product = new SupplierProduct();
                        $supplier_product->product_id = $insert_id;
                        $supplier_product->supplier_user_id = $supplier_user_ids[$j];
                        $supplier_product->created_by_user_id = $login_user_id;
                        $supplier_product->updated_by_user_id = $login_user_id;
                        $supplier_product->save();
                    }
                }
            }

            DB::commit();

            Toastr::success("Product Created Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.products.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function show($id)
    {
        $vatPercents = VatSetting::wherestatus(1)->pluck('vat_percent', 'id');
        $product = Product::findOrFail($id);
        $product_price = ProductPrice::where('product_id', $id)->get();
        return view('backend.common.products.show', compact('product', 'product_price', 'vatPercents'));
    }

    public function edit($id)
    {
        $suppliers = User::wherestatus(1)->whereuser_type('Supplier')->pluck('name', 'id');
        $supplierIds = SupplierProduct::whereproduct_id($id)->pluck('supplier_user_id');
        $countries = Country::pluck('name', 'name');

        $vatPercents = VatSetting::wheredefault_status(1)->wherestatus(1)->pluck('vat_percent', 'id');
        $product = Product::with('productprice')->findOrFail($id);
        // dd($product);
        $product_price = ProductPrice::where('product_id', $id)->first();
        $categories = Category::wherestatus(1)->pluck('name', 'id');
        $subCategories = SubCategory::wherestatus(1)->pluck('name', 'id');
        $brands = Brand::wherestatus(1)->pluck('name', 'id');
        $units = Unit::wherestatus(1)->pluck('name', 'id');
        $productdepartment = ProductDepartment::wherestatus(1)->pluck('name', 'id');
        $productsection = ProductSection::wherestatus(1)->pluck('name', 'id');
        return view('backend.common.products.edit', compact('suppliers', 'supplierIds', 'countries', 'product', 'product_price', 'categories', 'brands', 'units', 'productdepartment', 'productsection', 'vatPercents', 'subCategories'));
    }
    public function duplicateProduct($id)
    {
        $suppliers = User::wherestatus(1)->whereuser_type('Supplier')->pluck('name', 'id');
        $supplierIds = SupplierProduct::whereproduct_id($id)->pluck('supplier_user_id');
        $countries = Country::pluck('name', 'name');

        //$vatPercents = VatSetting::wherestatus(1)->pluck('vat_percent', 'id');
        $vatPercents = VatSetting::wheredefault_status(1)->wherestatus(1)->pluck('vat_percent', 'id');
        $product = Product::findOrFail($id);
        $product_price = ProductPrice::where('product_id', $id)->first();
        $categories = Category::wherestatus(1)->pluck('name', 'id');
        $subCategories = SubCategory::wherestatus(1)->pluck('name', 'id');
        $brands = Brand::wherestatus(1)->pluck('name', 'id');
        $units = Unit::wherestatus(1)->pluck('name', 'id');
        $productdepartment = ProductDepartment::wherestatus(1)->pluck('name', 'id');
        $productsection = ProductSection::wherestatus(1)->pluck('name', 'id');
        return view('backend.common.products.duplicate', compact('suppliers', 'supplierIds', 'countries', 'product', 'product_price', 'categories', 'brands', 'units', 'productdepartment', 'productsection', 'vatPercents', 'subCategories'));
    }

    public function update(Request $request, $id)
    {
        //        dd($request->all());
        request()->validate([
            'name' => 'required|min:1|max:198',
            'barcode' => 'required|min:7|max:15|unique:products,barcode,' . $id,
            'unit_measurement' => 'required|min:1|max:190',
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'brand_id' => 'required',
            'product_department_id' => 'required',
            'product_section_id' => 'required',
            'status' => 'required',
            'local_purchase_price' => 'required|numeric|min:0|max:9999999999999999',
            // 'international_purchase_price' => 'required|min:1',
            'warehouse_sale_price' => 'required|numeric|min:0|max:9999999999999999',
            'minimum_warehouse_sale_price' => 'required|numeric|min:0|max:9999999999999999',
            'local_sale_price' => 'required|numeric|min:0|max:9999999999999999',
            'minimum_local_sale_price' => 'required|numeric|min:0|max:9999999999999999',
            'outer_sale_price' => 'required|numeric|min:0|max:9999999999999999',
            'minimum_outer_sale_price' => 'required|numeric|min:0|max:9999999999999999',
            'unit_id' => 'required',
        ]);
        $check = Product::where('id', '!==', $id)->wherename($request->name)->whereunit_measurement($request->unit_measurement)->first();
        if ($check) {
            return back()
                ->withErrors([
                    'name' => 'Product Name and  Measurement Are Unique',
                ]);
        }
        try {
            DB::beginTransaction();
            $product = Product::findOrFail($id);

            $image = $request->file('image');
            if (isset($image)) {
                if (!is_dir(public_path() . "/uploads/product")) {
                    mkdir(public_path() .  "/uploads/product", 0777, true);
                }
                // $imagpath=storage_path() . '/app/files/productimages/'.$product->image;
                // $imagpaththumb=storage_path() . '/app/files/productimages/thumbs/'.$product->image;
                //  unlink($imagpath);
                //  unlink($imagpaththumb);
                $currentDate = Carbon::now()->toDateString();
                $imagename = $currentDate . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
                $proImage = Image::make($image)->resize(200, 200)->save($image->getClientOriginalExtension());
                Storage::disk('public')->put('uploads/product/' . $imagename, $proImage);
                $name = 'uploads/product/' . $imagename;
            } else {
                $name = $product->image;
            }


            $login_user_id = Auth::user()->id;

            if ($request->has('unit_id')) {
                $unit_id =  (int) $request->unit_id[0];
            } else {
                $unit_id = 1;
            }

            $unit_variant = $request->unit_variant;
            //dd($unit_variant);


            $product->name = $request->name;
            $product->arabic_name = $request->arabic_name;
            $product->category_id = $request->category_id;
            $product->subcategory_id = $request->subcategory_id;
            $product->product_department_id = $request->product_department_id;
            $product->product_section_id = $request->product_section_id;
            $product->brand_id = $request->brand_id;
            $product->barcode = $request->barcode;
            $product->country_of_origin = $request->country_of_origin;
            $product->low_inventory_alert = $request->low_inventory_alert;
            $product->image = $request->image;
            $product->detail = $request->detail;
            $product->unit_measurement = $request->unit_measurement;
            $product->name_unit_measurement = $request->name . ' ' . $request->unit_measurement;
            $product->slug = Generate::Slug($product->name_unit_measurement);
            // $product->product_barcode = $request->product_barcode;
            $product->vat_id = $request->vat_id;
            $product->status = $request->status;
            $product->expire_date = $request->expire_date;
            $product->unit_id = $unit_id;
            $product->unit_variant = $unit_variant;
            //$product->unit_set_ids = $unit_set_ids;
            $product->updated_by_user_id = $login_user_id;
            $product->image = $name;
            $update = $product->save();
            if ($update) {

                $first_product_price_id = $request->product_price_id[0];
                if ($unit_variant == 1) {
                    $product_prices = ProductPrice::findOrFail($request->product_price_id);
                    $previous_row_count = count($product_prices);
                    $row_count = count($request->unit_id);
                    //dd($previous_row_count);

                    for ($i = 0; $i < $row_count; $i++) {
                        $first_unit_quantity = $request->unit_quantity[0];
                        $first_local_purchase_price = $request->local_purchase_price;
                        $first_international_purchase_price = $request->local_purchase_price;
                        $first_warehouse_sale_price = $request->warehouse_sale_price;
                        $first_minimum_warehouse_sale_price = $request->minimum_warehouse_sale_price;
                        $first_local_sale_price = $request->local_sale_price;
                        $first_minimum_local_sale_price = $request->minimum_local_sale_price;
                        $first_outer_sale_price = $request->outer_sale_price;
                        $first_minimum_outer_sale_price = $request->minimum_outer_sale_price;

                        if ($i == 0) {
                            $unit_quantity = $first_unit_quantity;
                            $per_unit_quantity = $first_unit_quantity;
                            $local_purchase_price = $first_local_purchase_price;
                            $international_purchase_price = $first_international_purchase_price;
                            $warehouse_sale_price = $first_warehouse_sale_price;
                            $minimum_warehouse_sale_price = $first_minimum_warehouse_sale_price;
                            $local_sale_price = $first_local_sale_price;
                            $minimum_local_sale_price = $first_minimum_local_sale_price;
                            $outer_sale_price = $first_outer_sale_price;
                            $minimum_outer_sale_price = $first_minimum_outer_sale_price;
                        } else {
                            $unit_quantity = $request->unit_quantity[$i];
                            $per_unit_quantity = $first_unit_quantity / $request->unit_quantity[$i];
                            $local_purchase_price = $first_local_purchase_price / $request->unit_quantity[$i];
                            $international_purchase_price = $first_international_purchase_price / $request->unit_quantity[$i];
                            $warehouse_sale_price = $first_warehouse_sale_price / $request->unit_quantity[$i];
                            $minimum_warehouse_sale_price = $first_minimum_warehouse_sale_price / $request->unit_quantity[$i];
                            $local_sale_price = $first_local_sale_price / $request->unit_quantity[$i];
                            $minimum_local_sale_price = $first_minimum_local_sale_price / $request->unit_quantity[$i];
                            $outer_sale_price = $first_outer_sale_price / $request->unit_quantity[$i];
                            $minimum_outer_sale_price = $first_minimum_outer_sale_price / $request->unit_quantity[$i];
                        }

                        $unit_id = $request->unit_id[$i];
                        if ($i < $previous_row_count) {
                            $product_price_id = $request->product_price_id[$i];
                            $product_prices = ProductPrice::findOrFail($product_price_id);
                            $product_prices->unit_id = $unit_id;
                            $product_prices->unit_quantity = $unit_quantity;
                            $product_prices->per_unit_quantity = $per_unit_quantity;
                            $product_prices->local_purchase_price = $local_purchase_price;
                            $product_prices->international_purchase_price = $international_purchase_price;
                            $product_prices->warehouse_sale_price = $warehouse_sale_price;
                            $product_prices->minimum_warehouse_sale_price = $minimum_warehouse_sale_price;
                            $product_prices->local_sale_price = $local_sale_price;
                            $product_prices->minimum_local_sale_price = $minimum_local_sale_price;
                            $product_prices->outer_sale_price = $outer_sale_price;
                            $product_prices->minimum_outer_sale_price = $minimum_outer_sale_price;
                            $product_prices->updated_by_user_id = $login_user_id;
                            $product_prices->save();
                        } else {
                            $product_prices = new ProductPrice();
                            $product_prices->product_id = $id;
                            $product_prices->unit_id = $unit_id;
                            $product_prices->unit_quantity = $unit_quantity;
                            $product_prices->per_unit_quantity = $per_unit_quantity;
                            $product_prices->local_purchase_price = $local_purchase_price;
                            $product_prices->international_purchase_price = $international_purchase_price;
                            $product_prices->warehouse_sale_price = $warehouse_sale_price;
                            $product_prices->minimum_warehouse_sale_price = $minimum_warehouse_sale_price;
                            $product_prices->outer_sale_price = $outer_sale_price;
                            $product_prices->minimum_outer_sale_price = $minimum_outer_sale_price;
                            $product_prices->local_sale_price = $local_sale_price;
                            $product_prices->minimum_local_sale_price = $minimum_local_sale_price;
                            $product_prices->created_by_user_id = $login_user_id;
                            $product_prices->updated_by_user_id = $login_user_id;
                            //                            dd($product_prices);
                            $product_prices->save();
                        }
                    }
                } else {
                    $product_prices = ProductPrice::findOrFail($first_product_price_id);
                    $product_prices->local_purchase_price = $request->local_purchase_price;
                    $product_prices->international_purchase_price = $request->local_purchase_price;
                    $product_prices->warehouse_sale_price = $request->warehouse_sale_price;
                    $product_prices->minimum_warehouse_sale_price = $request->minimum_warehouse_sale_price;
                    $product_prices->local_sale_price = $request->local_sale_price;
                    $product_prices->minimum_local_sale_price = $request->minimum_local_sale_price;
                    $product_prices->outer_sale_price = $request->outer_sale_price;
                    $product_prices->minimum_outer_sale_price = $request->minimum_outer_sale_price;
                    $product_prices->updated_by_user_id = $login_user_id;
                    $product_prices->save();
                }

                // supplier product
                $supplier_user_ids = $request->supplier_user_ids;
                $row_count = count($supplier_user_ids);
                //dd(SupplierProduct::whereproduct_id($id)->count());
                /* if (($row_count > 0) && SupplierProduct::whereproduct_id($id)->count() != $row_count) {
                    dd('1');
                    SupplierProduct::whereproduct_id($id)->delete();
                    for ($j = 0; $j < $row_count; $j++) {
                        $supplier_product = new SupplierProduct();
                        $supplier_product->product_id = $id;
                        $supplier_product->supplier_user_id = $supplier_user_ids[$j];
                        $supplier_product->created_by_user_id = $login_user_id;
                        $supplier_product->updated_by_user_id = $login_user_id;
                        $supplier_product->save();
                    }
                } */
                if (($row_count > 0)) {
                    
                    SupplierProduct::whereproduct_id($id)->delete();
                    for ($j = 0; $j < $row_count; $j++) {
                        $supplier_product = new SupplierProduct();
                        $supplier_product->product_id = $id;
                        $supplier_product->supplier_user_id = $supplier_user_ids[$j];
                        $supplier_product->created_by_user_id = $login_user_id;
                        $supplier_product->updated_by_user_id = $login_user_id;
                        $supplier_product->save();
                    }
                }
            }


            DB::commit();

            Toastr::success("Product Updated Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.products.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function destroy(Product $product)
    {
        //$product->delete();

        //        return redirect()->route('products.index')
        //            ->with('success','Product deleted successfully');
        Toastr::success("Product Deleted Successfully", "Success");
        return redirect()->route(\Request::segment(1) . '.products.index');
    }

    public function productUnit(Request $request)
    {
        $unit_variant = $request->unit_variant;
        if ($unit_variant === '0') {
            $units = Unit::where('id', '1')->get();
        } else {
            $units = Unit::where('id', '!=', '1')
                ->whereIn('id', function ($query) {
                    $query->from('unit_sets')->groupBy('output_unit_id')->selectRaw('output_unit_id');
                })
                ->get();
        }
        return $units;
    }

    public function productPrice(Request $request)
    {

        $unit_id = $request->unit_id;
        $unit_sets = UnitSet::where('output_unit_id', $unit_id)->get();
        return view('backend.common.products.product_prices', compact('unit_sets'));
    }

    public function productPriceEdit(Request $request)
    {
        //$unit_variant = $request->unit_variant;
        $unit_id = $request->unit_id;
        $product_id = $request->product_id;
        $product = Product::find($product_id);
        $unit_set_ids = $product->unit_set_ids;
        $unit_sets = UnitSet::where('output_unit_id', $unit_id)->get();
        return view('backend.common.products.product_prices_edit', compact('unit_sets', 'unit_set_ids'));
    }

    public function checkBarcode(Request $request)
    {
        $barcode = $request->barcode;
        $exists_barcode = Product::where('barcode', $barcode)->get();
        if (count($exists_barcode) > 0) {
            $barcode_check = 'Found';
        } else {
            $barcode_check = 'Not Found';
        }
        return response()->json(['success' => true, 'data' => $barcode_check]);
    }

    public function checkBarcodeEdit(Request $request)
    {
        $barcode = $request->barcode;
        $product_id = $request->product_id;
        $exists_barcode = Product::where('id', '!=', $product_id)->where('barcode', $barcode)->get();
        if (count($exists_barcode) > 0) {
            $barcode_check = 'Found';
        } else {
            $barcode_check = 'Not Found';
        }
        return response()->json(['success' => true, 'data' => $barcode_check]);
    }

    public function findsubcategory($id)
    {
        return SubCategory::wherestatus(1)->wherecategory_id($id)->get();
    }


    public function findProductForRequisition(Request $request)
    {
        if ($request->has('term')) {
            if ($request->purchase_from == 'Local') {
                $data = DB::table('products')
                    ->join('product_prices', 'products.id', '=', 'product_prices.product_id')
                    ->where('products.name', 'like', '%' . $request->term . '%')
                    ->where('products.status', '=', 1)
                    ->orwhere('products.barcode', $request->term)
                    ->select('products.name', 'products.barcode', 'products.id', 'products.status', 'products.unit_id', 'products.unit_set_ids', 'products.vat_id', 'products.average_purchase_price', 'product_prices.local_purchase_price')
                    ->get();
                $results = array();
                foreach ($data as  $v) {
                    $vat = VatSetting::find($v->vat_id)->vat_percent;

                    $unitList = Unit::find($v->unit_id);
                    $unitSetList = [];
                    $unitSets = json_decode($v->unit_set_ids);
                    if (count($unitSets) > 0) {
                        $nestedData = [];

                        foreach ($unitSets as $unitSet) {
                            $nestedData[] = ['id' => $unitSet, 'name' => getUnitNameFromUnitSet($unitSet), 'unit_id' => getUnitIdFromUnitSet($unitSet)];
                        }
                        array_push($unitSetList, $nestedData);
                    }

                    //$results[]=['id'=>$v->id,'value'=>$v->name,'price'=>$v->local_purchase_price,'unit_sets'=>$unitSetList,'unitList'=>$unitList,'average_purchase_price'=>$v->average_purchase_price,'variant_unit_qty'=>getVariantUnitQtyFromUnitSet($v->unit_id)];
                    $results[] = ['id' => $v->id, 'value' => $v->name, 'price' => $v->local_purchase_price, 'unit_sets' => $unitSetList, 'unitList' => $unitList, 'average_purchase_price' => $v->average_purchase_price, 'vat' => $vat];
                }
            } else {
                $data = DB::table('products')
                    ->join('product_prices', 'products.id', '=', 'product_prices.product_id')
                    ->where('products.name', 'like', '%' . $request->term . '%')
                    ->where('products.status', '=', 1)
                    ->orwhere('products.barcode', $request->term)
                    ->select('products.name', 'products.barcode', 'products.id', 'products.status', 'products.unit_id', 'products.unit_set_ids', 'products.vat_id', 'products.average_purchase_price', 'product_prices.local_purchase_price')
                    ->get();
                $results = array();
                foreach ($data as  $v) {
                    $vat = VatSetting::find($v->vat_id)->vat_percent;
                    $unitList = Unit::find($v->unit_id);
                    $unitSetList = [];
                    $unitSets = json_decode($v->unit_set_ids);

                    if (count($unitSets) > 0) {
                        foreach ($unitSets as $unitSet) {
                            $nestedData[] = ['id' => $unitSet, 'name' => getUnitNameFromUnitSet($unitSet), 'unit_id' => getUnitIdFromUnitSet($unitSet)];
                        }
                        array_push($unitSetList, $nestedData);
                    }

                    //                    $results[]=['id'=>$v->id,'value'=>$v->name,'price'=>$v->local_purchase_price,'unit_sets'=>json_encode($unitSetList),'unitList'=>$unitList,'average_purchase_price'=>$v->average_purchase_price,'variant_unit_qty'=>getVariantUnitQtyFromUnitSet($v->unit_id)];
                    /* $results[]=['id'=>$v->id,'value'=>$v->name,'price'=>$v->local_purchase_price,'unit_sets'=>json_encode($unitSetList),'unitList'=>$unitList,'average_purchase_price'=>$v->average_purchase_price]; */

                    $results[] = ['id' => $v->id, 'value' => $v->name, 'price' => $v->local_purchase_price, 'unit_sets' => $unitSetList, 'unitList' => $unitList, 'average_purchase_price' => $v->average_purchase_price, 'vat' => $vat];
                }
            }
            return response()->json($results);
        }
    }


    public function barcodeLists()
    {
        return view('backend.common.products.barcode_index');
    }

    public function findproductforbarcode(Request $request)
    {
        if ($request->has('term')) {
            $data = DB::table('products')
                ->where('products.name', 'like', '%' . $request->term . '%')
                ->where('products.status', '=', 1)
                ->orwhere('products.barcode', $request->term)
                ->select('products.name', 'products.barcode', 'products.id')
                ->get();
            $results = array();

            foreach ($data as  $v) {
                $results[] = ['id' => $v->id, 'value' => $v->name, 'barcode' => $v->barcode];
            }
            return response()->json($results);
        }
    }

    public function barcodePrintView(Request $request)
    {
        // dd($request->all());
        $barcode = $request->product_barcode;
        $barcodequantity = $request->barcodequentity;
        $name = $request->product_name;
        return view('backend.common.products.barcode_show')->with('barcodes', $barcode)
            ->with('barcodequantity', $barcodequantity)
            ->with('productname', $name);
    }

    public function newDepartment(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);
        $productDepartment = new ProductDepartment();
        $productDepartment->name = $request->name;
        $productDepartment->created_by_user_id = Auth::user()->id;
        $productDepartment->updated_by_user_id = Auth::user()->id;
        $productDepartment->status = 1;
        $productDepartment->save();
        $insert_id = $productDepartment->id;
        if ($insert_id) {
            $sdata['id'] = $insert_id;
            $sdata['name'] = $productDepartment->name;
            echo json_encode($sdata);
        } else {
            $data['exception'] = 'Some thing mistake !';
            echo json_encode($data);
        }
    }

    public function newSection(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);
        $productSection = new ProductSection();
        $productSection->name = $request->name;
        $productSection->created_by_user_id = Auth::user()->id;
        $productSection->updated_by_user_id = Auth::user()->id;
        $productSection->status = 1;
        $productSection->save();
        $insert_id = $productSection->id;
        if (1) {
            $sdata['id'] = $insert_id;
            $sdata['name'] = $productSection->name;
            echo json_encode($sdata);
        } else {
            $data['exception'] = 'Some thing mistake !';
            echo json_encode($data);
        }
    }

    public function newCategory(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);
        $productCategory = new Category();
        $productCategory->name = $request->name;
        $productCategory->created_by_user_id = Auth::user()->id;
        $productCategory->updated_by_user_id = Auth::user()->id;
        $productCategory->status = 1;
        $productCategory->save();
        $insert_id = $productCategory->id;
        if (1) {
            $sdata['id'] = $insert_id;
            $sdata['name'] = $productCategory->name;
            echo json_encode($sdata);
        } else {
            $data['exception'] = 'Some thing mistake !';
            echo json_encode($data);
        }
    }


    public function newSubCategory(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);
        $productSubCategory = new SubCategory();
        $productSubCategory->name = $request->name;
        $productSubCategory->category_id = $request->modal_category_id;
        $productSubCategory->created_by_user_id = Auth::user()->id;
        $productSubCategory->updated_by_user_id = Auth::user()->id;
        $productSubCategory->status = 1;
        $productSubCategory->save();
        $insert_id = $productSubCategory->id;
        if ($insert_id) {
            $sdata['id'] = $insert_id;
            $sdata['name'] = $productSubCategory->name;
            echo json_encode($sdata);
        } else {
            $data['exception'] = 'Some thing mistake !';
            echo json_encode($data);
        }
    }

    public function newBrand(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);
        $productBrand = new Brand();
        $productBrand->name = $request->name;
        $productBrand->created_by_user_id = Auth::user()->id;
        $productBrand->updated_by_user_id = Auth::user()->id;
        $productBrand->status = 1;
        $productBrand->save();
        $insert_id = $productBrand->id;
        if ($insert_id) {
            $sdata['id'] = $insert_id;
            $sdata['name'] = $productBrand->name;
            echo json_encode($sdata);
        } else {
            $data['exception'] = 'Some thing mistake !';
            echo json_encode($data);
        }
    }
}
