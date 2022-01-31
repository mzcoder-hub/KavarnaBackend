<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $table = 'transaction_items';

    protected $fillable = [
        'menus_id',
        'transactions_id',
        'quantity',
    ];

    public function menus()
    {
        return $this->hasOne(Menu::class, 'id', 'menus_id');
    }
}
