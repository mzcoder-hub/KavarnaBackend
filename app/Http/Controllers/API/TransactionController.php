<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $status = $request->input('status');

        if ($id) {
            $transaction = Transaction::with(['items.menus'])->find($id);

            if ($transaction)
                return ResponseFormatter::success(
                    $transaction,
                    'Data transaksi berhasil diambil'
                );
            else
                return ResponseFormatter::error(
                    null,
                    'Data transaksi tidak ada',
                    404
                );
        }

        $transaction = Transaction::with(['items.menus'])->where('users_id', Auth::user()->id);

        if ($status)
            $transaction->where('status', $status);

        return ResponseFormatter::success(
            $transaction->paginate($limit),
            'Data list transaksi berhasil diambil'
        );
    }

    public function checkout(Request $request)
    {
	try{
        $request->validate([
            'items' => 'required|array',
            'seat_number' => 'required',
	    'items.*.id' => 'exists:menus,id',
            'total_price' => 'required',
            'status' => 'required',
        ]);
//        return ResponseFormatter::success($request->items,'Transaksi Berhasil');
        $transaction = Transaction::create([
            'users_id' => Auth::user()->id,
            'seat_number' => $request->seat_number,
            'total_price' => $request->total_price,
            'status' => $request->status
        ]);

//        return ResponseFormatter::success($transaction->id, 'Transaction Success');

        foreach ($request->items as $menu) {
            TransactionItem::create([
               'menus_id' => $menu['id'],
               'transactions_id' => $transaction->id,
               'quantity' => $menu['quantity']
            ]);
        }

	DB::commit();
        return ResponseFormatter::success($transaction, 'Transaksi berhasil');
	}catch(Exception $e){
        DB::rollback();
        return ResponseFormatter::error($e, 'Transaksi Gagal');
        }
    }
}
