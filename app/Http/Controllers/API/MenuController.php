<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use Exception;
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
                return ResponseFormatter::success($menus, 'Data menu berhasil diambil');
            } else {
                return ResponseFormatter::error(null, 'Menu tidak ditemukan', 404);
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
            $menus->where('categories_id', $categories);
        }

        return ResponseFormatter::success($menus->paginate($limit), 'Data menu berhasil diambil');
    }

    public function store()
    {
	try{

        $request = request()->all();
	//print_r($request);
        $menu = Menu::create($request);

       	if ($menu) {
         	return ResponseFormatter::success($menu, 'Menu berhasil ditambahkan');
       	} else {
       	        return ResponseFormatter::error(null, 'Menu gagal ditambahkan', 500);
       	}

	}catch(Exception $e){
	return ResponseFormatter::error($e, 'Tambah Menu Gagal');
	}

    }

    public function update()
    {
        $request = request()->all();

        $id = $request['id'];

        $menu = Menu::find($id);

        if ($menu) {
            $menu->update($request);

            return ResponseFormatter::success($menu, 'Menu berhasil diubah');
        } else {
            return ResponseFormatter::error(null, 'Menu tidak ditemukan', 404);
	}
    }

    public function delete()
    {
        $request = request()->id;

        $menu = Menu::find($request);

        if ($menu) {
            $menu->delete();

            return ResponseFormatter::success(null, 'Menu berhasil dihapus');
        } else {
            return ResponseFormatter::error(null, 'Menu tidak ditemukan', 404);
        }
    }
}
