<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'categories_id',
    ];

    public function categories()
    {
        return $this->belongsTo(MenuCategory::class, 'categories_id', 'id');
    }

    public function galleries()
    {
        return $this->hasMany(MenuGallery::class, 'menus_id', 'id');
    }
}
