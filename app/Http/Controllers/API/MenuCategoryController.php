<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $show_category = $request->input('show_category');

        if ($id) {
            $category = MenuCategory::with(['menus'])->find($id);

            if ($category) {
                return ResponseFormatter::success($category, 'Data kategori berhasil diambil');
            } else {
                return ResponseFormatter::error(null, 'Data kategori tidak ditemukan', 404);
            }
        }

        $category = MenuCategory::query();


        if ($name) {
            $category->where('name', 'like', '%' . $name . '%');
        }

        if ($show_category) {
            $category->with('menus');
        }

        return ResponseFormatter::success($category->paginate($limit), 'Data List kategori berhasil diambil');
    }

    public function update(Request $request)
    {
        $id = $request->input('id');

        $menus = MenuCategory::find($id)->update($request->all());

        if ($menus) {
            return ResponseFormatter::success($menus, 'Data menu berhasil di update');
        } else {
            return ResponseFormatter::error(null, 'Menu tidak ditemukan', 404);
        }
    }
}
