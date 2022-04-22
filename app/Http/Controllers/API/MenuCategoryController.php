<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\CategoryGallery;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MenuCategoryController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $show_category = $request->input('show_category');

        if ($id) {
            $category = MenuCategory::with(['menus.galleries', 'galleries'])->find($id);

            if ($category) {
                return ResponseFormatter::success($category, 'Data kategori berhasil diambil');
            } else {
                return ResponseFormatter::error(null, 'Data kategori tidak ditemukan', 404);
            }
        }

        $category = MenuCategory::query();


        if ($name) {
            $category->where('name', 'like', '%' . $name . '%')->with(['galleries', 'menus.galleries']);
        }

        if ($show_category) {
            $category->with('menus.galleries', 'galleries');
        }

        return ResponseFormatter::success($category->with(['menus.galleries', 'galleries'])->paginate($limit), 'Data List kategori berhasil diambil');
    }

    public function store(Request $request)
    {
        try {
            $category = new MenuCategory();
            $category->name = $request->name;
            $image = $request->image;  // your base64 encoded
            $imageName = 'menu_' . time() . '.png';
            $uploadImage = Storage::disk('public')->put($imageName, base64_decode($image));
            $path = 'public/' . $imageName;


            if ($uploadImage) {
                if ($path) {
                    $category->save();

                    CategoryGallery::create([
                        'categories_id' => $category->id,
                        'url' => $path,
                    ]);
                    return ResponseFormatter::success($category, 'Data kategori berhasil ditambahkan');
                } else {
                    return ResponseFormatter::error(null, 'Gagal menyimpan gambar', 500);
                }
            } else {
                return ResponseFormatter::error(null, 'Gagal menyimpan gambar', 500);
            }
        } catch (Exception $e) {
            return ResponseFormatter::error(null, $e->getMessage(), 400);
        }
    }

    public function update(Request $request)
    {

        try {
            $menuCategoryUpdate =  MenuCategory::find($request->id)->update(['name' => $request->name]);

            if ($menuCategoryUpdate) {
                $getCategoryGalleriesById = CategoryGallery::where('categories_id', $request->id)->first();
                if (!$getCategoryGalleriesById) {

                    $image = $request->image;  // your base64 encoded
                    $imageName = 'menu_' . time() . '.png';
                    $uploadImage = Storage::disk('public')->put($imageName, base64_decode($image));
                    $path = 'public/' . $imageName;
                    if ($uploadImage) {
                        if ($path) {
                            CategoryGallery::create([
                                'categories_id' => $request->id,
                                'url' => $path,
                            ]);
                            return ResponseFormatter::success($menuCategoryUpdate, 'Menu berhasil diupdate');
                        } else {
                            return ResponseFormatter::error(null, 'Gambar tidak dapat diupload', 500);
                        }
                    } else {
                        return ResponseFormatter::error(null, 'Gagal menyimpan gambar', 500);
                    }
                } else {
                    $imageFolderPath = 'public/images/category';
                    $path = $request->file('image')->store($imageFolderPath);

                    if ($path) {
                        $getCategoryGalleriesById->update([
                            'url' => $path
                        ]);
                        DB::commit();
                        return ResponseFormatter::success($menuCategoryUpdate, 'Data menu berhasil di update');
                    } else {
                        return ResponseFormatter::error(null, 'Gambar tidak dapat diupload', 500);
                    }
                }
            }
        } catch (Exception $e) {
            DB::rollback();
            return ResponseFormatter::error($e, 'Menu Category tidak ditemukan', 404);
        }
    }

    public function delete(Request $request)
    {
        try {
            $menuCategoryDelete =  MenuCategory::find($request->id)->delete();
            DB::commit();
            return ResponseFormatter::success($menuCategoryDelete, 'Data menu berhasil di delete');
        } catch (Exception $e) {
            DB::rollback();
            return ResponseFormatter::error($e, 'Menu tidak ditemukan', 404);
        }
    }
}
