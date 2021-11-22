<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $description = $request->input('description');
        $tags = $request->input('tags');
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');
        $categories = $request->input('categories');
        $limit = $request->input('limit');

        if ($id) {
            $product = Product::with(['category', 'galleries'])->find($id);
            if ($product) {
                return ApiResponse::success($product, 'Data Produk Berhasil Diambil');
            } else {
                return ApiResponse::error(null, 'Data Produk Tidak Ada', 404);
            }
        }

        $product = Product::with(['category', 'galleries']);

        if ($name) {
            $product->where('name', 'like', "%$name%");
        }

        if ($description) {
            $product->where('description', 'like', "%$description%");
        }

        if ($tags) {
            $product->where('tags', 'like', "%$tags%");
        }

        if ($price_from) {
            $product->where('price', '>=', $price_from);
        }

        if ($price_to) {
            $product->where('price', '<=', $price_to);
        }

        if ($categories) {
            $product->where('categories_id', $categories);
        }

        return ApiResponse::success($product->paginate($limit), 'Data Produk Berhasil Diambil');
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric',
            'description' => 'required',
            'tags' => 'required',
            'categories_id' => 'required|exists:product_categories,id',
        ]);

        if ($validate->fails()) {
            return ApiResponse::error([], $validate->getMessageBag());
        }

        $product = Product::create($request->all());

        return ApiResponse::success($product, 'Produk Berhasil Ditambah');
    }

    public function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:products,id',
            'name' => 'string',
            'price' => 'numeric',
            'description' => 'string',
            'tags' => 'string',
            'categories_id' => 'numeric|exists:product_categories,id',
        ]);

        if ($validate->fails()) {
            return ApiResponse::error([], $validate->getMessageBag());
        }

        $product =  Product::find($request->id);
        $product->update($request->all());

        return ApiResponse::success($product, 'Produk Berhasil Diubah');
    }
}
