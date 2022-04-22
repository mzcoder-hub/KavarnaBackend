<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuGallery;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

    public function store(Request $request)
    {
        try {

            $requested = request()->all();

            $image = $request->image;  // your base64 encoded
            $imageName = 'menu_' . time() . '.png';
            $uploadImage = Storage::disk('public')->put($imageName, base64_decode($image));
            $path = 'public/' . $imageName;

            if ($uploadImage) {
                $menu = Menu::create($requested);
                if ($menu) {
                    MenuGallery::create([
                        'menus_id' => $menu->id,
                        'url' => $path,
                    ]);
                    return ResponseFormatter::success($menu, 'Menu berhasil ditambahkan');
                } else {
                    return ResponseFormatter::error(null, 'Menu gagal ditambahkan', 500);
                }
            } else {
                return ResponseFormatter::error(null, 'Gambar tidak dapat diupload', 500);
            }
        } catch (Exception $e) {
            return ResponseFormatter::error($e, 'Tambah Menu Gagal');
        }
    }

    public function update(Request $request)
    {
        $getMenuById = Menu::find($request->id);

        if ($getMenuById) {
            $updateMenu = $getMenuById->update($request->all());
            if ($updateMenu) {
                $getMenuGalleriesById = MenuGallery::where('menus_id', $request->id)->first();
                if (!$getMenuGalleriesById) {
                    $image = $request->image;  // your base64 encoded
                    $imageName = 'menu_' . time() . '.png';
                    $uploadImage = Storage::disk('public')->put($imageName, base64_decode($image));
                    $path = 'public/' . $imageName;
                    if ($uploadImage) {
                        MenuGallery::create([
                            'menus_id' => $request->id,
                            'url' => $path,
                        ]);
                        return ResponseFormatter::success($getMenuById, 'Menu berhasil diupdate');
                    } else {
                        return ResponseFormatter::error(null, 'Gambar tidak dapat diupload', 500);
                    }
                } else {
                    $image = $request->image;  // your base64 encoded
                    $imageName = 'menu_' . time() . '.png';
                    $uploadImage = Storage::disk('public')->put($imageName, base64_decode($image));
                    $path = 'public/' . $imageName;
                    if ($path) {
                        $getMenuGalleriesById->update([
                            'url' => $path
                        ]);
                        return ResponseFormatter::success($getMenuById, 'Menu berhasil diupdate');
                    } else {
                        return ResponseFormatter::error(null, 'Gambar tidak dapat diupload', 500);
                    }
                }
            } else {
                return ResponseFormatter::error(null, 'Gambar tidak dapat diupload', 500);
            }
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
