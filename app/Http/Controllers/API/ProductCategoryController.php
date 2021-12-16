<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $show_category = $request->input('show_category');

        if ($id) {
            $category = ProductCategory::with(['products'])->find($id);

            if ($category) {
                return ResponseFormatter::success($category, 'Data kategori berhasil diambil');
            } else {
                return ResponseFormatter::error(null, 'Data kategori tidak ditemukan', 404);
            }
        }

        $category = ProductCategory::query();


        if ($name) {
            $category->where('name', 'like', '%' . $name . '%');
        }

        if ($show_category) {
            $category->with('products');
        }

        return ResponseFormatter::success($category->paginate($limit), 'Data List kategori berhasil diambil');
    }
}
