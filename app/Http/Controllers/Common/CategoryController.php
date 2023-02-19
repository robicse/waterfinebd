<?php

namespace App\Http\Controllers\Common;

use App\Helpers\ErrorTryCatch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use DataTables;

class CategoryController extends Controller
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
        $this->middleware('permission:categories-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:categories-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:categories-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:categories-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        try {
            $User=$this->User;
            if ($request->ajax()) {
                $categories = Category::orderBy('id', 'DESC');
                return Datatables::of($categories)
                    ->addIndexColumn()
                    ->addColumn('action', function ($category)use($User) {
                        $btn='';
                        if($User->can('categories-edit')){
                        $btn = '<a href=' . route(\Request::segment(1) . '.categories.edit', $category->id) . ' class="btn btn-info btn-sm waves-effect"><i class="fa fa-edit"></i></a>';
                        }
                        return $btn;
                    })
                    ->addColumn('status', function ($category) {
                        if ($category->status == 0) {
                            return '<span class="badge badge-danger"> <i class="fa fa-ban"></i> </span>';
                        } else {
                            return '<span class="badge badge-success"><i class="fa fa-check-square"></i></span>';
                        }
                    })
                    ->rawColumns(['action', 'status'])
                    ->make(true);
            }

            return view('backend.common.categories.index');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function create()
    {
        return view('backend.common.categories.create');
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'name' => 'required|min:1|max:190|unique:categories',
            'status' => 'required',
        ]);

        try {
            $category = new Category();
            $category->name = $request->name;
            $category->status = $request->status;
            $category->created_by_user_id = Auth::User()->id;
            $category->save();

            Toastr::success("Category Created Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.categories.index');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        return view('backend.common.categories.show', compact('category'));
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('backend.common.categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => "required|min:1|max:190|unique:categories,name,$id",
            'status' => 'required',
        ]);

        try {

            $category = Category::findOrFail($id);
            $category->name = $request->name;
            $category->status = $request->status;
            $category->updated_by_user_id = Auth::User()->id;
            $category->save();

            Toastr::success("Category Updated Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.categories.index');
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
