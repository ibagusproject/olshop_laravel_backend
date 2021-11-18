<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductCategoryController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit');
        $show_product = $request->input('show_product');

        if ($id) {
            $category = ProductCategory::with(['products'])->find($id);
            if ($category) {
                return ApiResponse::success($category, 'Data Kategori Berhasil Diambil');
            } else {
                return ApiResponse::error(null, 'Data Kategori Tidak Ada', 404);
            }
        }

        $category = ProductCategory::query();

        if ($name) {
            $category->where('name', 'like', "%$name%");
        }

        if ($show_product) {
            $category->with('products');
        }

        return ApiResponse::success($category->paginate($limit), 'Data Kategori Berhasil Diambil');
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|unique:product_categories',
        ]);

        if ($validate->fails()) {
            return ApiResponse::error([], $validate->getMessageBag());
        }

        $product = ProductCategory::create($request->all());

        return ApiResponse::success($product, 'Kategori Berhasil Ditambah');
    }

    public function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:product_categories,id',
            'name' => 'required',
        ]);

        if ($validate->fails()) {
            return ApiResponse::error([], $validate->getMessageBag());
        }

        $category =  ProductCategory::find($request->id);
        $category->update($request->all());

        return ApiResponse::success($category);
    }
}
