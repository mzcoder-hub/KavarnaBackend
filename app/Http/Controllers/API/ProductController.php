<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $description = $request->input('description');
        $categories = $request->input('categories');

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        if ($id) {
            $products = Product::with(['categories', 'galleries'])->find($id);

            if ($products) {
                return ResponseFormatter::success($products, 'Data produk berhasil diambil');
            } else {
                return ResponseFormatter::error(null, 'Produk tidak ditemukan', 404);
            }
        }

        $products = Product::with(['categories', 'galleries']);


        if ($name) {
            $products->where('name', 'like', '%' . $name . '%');
        }

        if ($description) {
            $products->where('description', 'like', '%' . $description . '%');
        }

        if ($price_from && $price_to) {
            $products->whereBetween('price', [$price_from, $price_to]);
        }

        if ($categories) {
            $products->where('category_id', $categories);
        }

        return ResponseFormatter::success($products->paginate($limit), 'Data produk berhasil diambil');
    }
}
