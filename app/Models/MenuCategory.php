<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    public function menus()
    {
        return $this->hasMany(Menu::class, 'categories_id', 'id');
    }

    public function galleries()
    {
        return $this->hasOne(CategoryGallery::class, 'categories_id', 'id');
    }
}
