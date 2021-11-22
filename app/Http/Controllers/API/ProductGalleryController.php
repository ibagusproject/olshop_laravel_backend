<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\ProductGallery;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductGalleryController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $products_id = $request->input('products_id');
        $url = $request->input('url');
        $show_product = $request->input('show_product');
        $limit = $request->input('limit');

        if ($id) {
            $category = ProductGallery::with(['product'])->find($id);
            if ($category) {
                return ApiResponse::success($category, 'Data Kategori Berhasil Diambil');
            } else {
                return ApiResponse::error(null, 'Data Kategori Tidak Ada', 404);
            }
        }

        $category = ProductGallery::query();

        if ($url) {
            $category->where('url', 'like', "%$url%");
        }

        if ($products_id) {
            $category->where('products_id', $products_id);
        }

        if ($show_product) {
            $category->with('product');
        }

        return ApiResponse::success($category->paginate($limit), 'Data Galeri Berhasil Diambil');
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'products_id' => 'required|numeric|exists:products,id',
            'url' => 'required',
        ]);

        if ($validate->fails()) {
            return ApiResponse::error([], $validate->getMessageBag());
        }

        if ($request->hasFile('url')) {
            $url = $request->file('url')->store('img/products');
        } else {
            $url = $request->url;
        }

        $product = ProductGallery::create([
            'products_id' => $request->products_id,
            'url' => $url,
        ]);

        return ApiResponse::success($product, 'Galeri Berhasil Ditambah');
    }

    public function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:product_galleries,id',
            'products_id' => 'required|numeric|exists:products,id',
            'url' => 'required',
        ]);

        if ($validate->fails()) {
            return ApiResponse::error([], $validate->getMessageBag());
        }

        if ($request->hasFile('url')) {
            $url = $request->file('url')->store('public/img/products');
        } else {
            $url = $request->url;
        }

        $category =  ProductGallery::find($request->id);
        $category->update([
            'products_id' => $request->products_id,
            'url' => $url,
        ]);

        return ApiResponse::success($category);
    }
}
