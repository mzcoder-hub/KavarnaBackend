<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buyers extends Model
{
    use HasFactory;

    protected $table = 'buyers';

    protected $fillable = [
        'transactions_id',
        'name',
        'phone_number',
    ];

    public function transactions()
    {
        return $this->belongsTo(Transactions::class, 'transactions_id');
    }
}
