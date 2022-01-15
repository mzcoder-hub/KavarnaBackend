<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
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
            $menus = Menu::with(['categories', 'galleries'])->find($id);

            if ($menus) {
                return ResponseFormatter::success($menus, 'Data produk berhasil diambil');
            } else {
                return ResponseFormatter::error(null, 'Produk tidak ditemukan', 404);
            }
        }

        $menus = Menu::with(['categories', 'galleries']);


        if ($name) {
            $menus->where('name', 'like', '%' . $name . '%');
        }

        if ($description) {
            $menus->where('description', 'like', '%' . $description . '%');
        }

        if ($price_from && $price_to) {
            $menus->whereBetween('price', [$price_from, $price_to]);
        }

        if ($categories) {
            $menus->where('category_id', $categories);
        }

        return ResponseFormatter::success($menus->paginate($limit), 'Data produk berhasil diambil');
    }
}
