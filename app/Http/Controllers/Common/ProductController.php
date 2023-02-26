<?php

namespace App\Http\Controllers\Common;

use App\Helpers\ErrorTryCatch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use DataTables;

class ProductController extends Controller
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
        $this->middleware('permission:products-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:products-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:products-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:products-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        try {
            $User=$this->User;
            if ($request->ajax()) {
                $products = Product::orderBy('id', 'DESC');
                return Datatables::of($products)
                    ->addIndexColumn()
                    ->addColumn('category', function ($products) {
                        return $products?->category?->name;
                    })
                    ->addColumn('status', function ($data) {
                        if ($data->status == 0) {
                            return '<div class="form-check form-switch"><input type="checkbox" id="flexSwitchCheckDefault" onchange="updateStatus(this,\'products\')" class="form-check-input"  value=' . $data->id . ' /></div>';
                        } else {
                            return '<div class="form-check form-switch"><input type="checkbox" id="flexSwitchCheckDefault" checked="" onchange="updateStatus(this,\'products\')" class="form-check-input"  value=' . $data->id . ' /></div>';
                        }
                    })
                    ->addColumn('action', function ($product)use($User) {
                        $btn='';
                        if($User->can('products-edit')){
                        $btn = '<a href=' . route(\Request::segment(1) . '.products.edit', $product->id) . ' class="btn btn-info btn-sm waves-effect"><i class="fa fa-edit"></i></a>';
                        }
                        return $btn;
                    })
                    ->rawColumns(['category','action', 'status'])
                    ->make(true);
            }

            return view('backend.common.products.index');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function create()
    {
        $categories = Category::pluck('name','id');
        $units = Unit::pluck('name','id');
        return view('backend.common.products.create', compact('categories','units'));
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'name' => 'required|min:1|max:190|unique:products',
            'category_id' => 'required',
        ]);

        try {
            $product = new Product();
            $product->category_id = $request->category_id;
            $product->unit_id = $request->unit_id;
            $product->name = $request->name;
            //$product->status = $request->status;
            $product->created_by_user_id = Auth::User()->id;
            $product->save();

            Toastr::success("Product Created Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.products.index');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('backend.common.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::pluck('name','id');
        $units = Unit::pluck('name','id');
        return view('backend.common.products.edit', compact('product','categories','units'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => "required|min:1|max:190|unique:products,name,$id",
            'status' => 'required',
        ]);

        try {
            $product = Product::findOrFail($id);
            $product->category_id = $request->category_id;
            $product->unit_id = $request->unit_id;
            $product->name = $request->name;
            $product->status = $request->status;
            $product->updated_by_user_id = Auth::User()->id;
            $product->save();
            Toastr::success("Product Updated Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.products.index');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function destroy($id)
    {
        //
    }
}
