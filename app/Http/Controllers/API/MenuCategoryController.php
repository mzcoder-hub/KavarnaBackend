<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Exception;

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

    public function store(Request $request)
    {
        $category = new MenuCategory();
        $category->name = $request->name;

        try {
            $category->save();
            return ResponseFormatter::success($category, 'Data kategori berhasil ditambahkan');
        } catch (Exception $e) {
            return ResponseFormatter::error(null, $e->getMessage(), 400);
        }
    }

    public function update(Request $request)
    {

        //return ResponseFormatter::success($request->name, 'Data menu berhasil di update');

        try {
            $menuCategoryUpdate =  MenuCategory::find($request->id)->update(['name' => $request->name]);
            DB::commit();
            return ResponseFormatter::success($menuCategoryUpdate, 'Data menu berhasil di update');
        } catch (Exception $e) {
            DB::rollback();
            return ResponseFormatter::error($e, 'Menu tidak ditemukan', 404);
        }
    }
}
