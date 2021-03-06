<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transactions';

    protected $fillable = [
        'users_id',
        'seat_number',
        'queue',
        'total_price',
        'invoice',
        'payment_method',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transactions_id', 'id');
    }

    public function buyers()
    {
        return $this->hasOne(Buyers::class, 'transactions_id', 'id');
    }

    public function invoice_attachment()
    {
        return $this->hasOne(InvoicesAttachment::class, 'transactions_id', 'id');
    }
}
