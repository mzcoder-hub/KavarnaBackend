<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicesAttachment extends Model
{
    use HasFactory;

    protected $table = 'invoices_attachment';

    protected $fillable = [
        'transactions_id',
        'url',
    ];

    public function transactions()
    {
        return $this->belongsTo(Transactions::class, 'transactions_id', 'id');
    }
}
