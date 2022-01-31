<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function all(Request $request)
    {

        try {
            $id = $request->transaction_id;
            $limit = $request->limit;
            $status = $request->status;

            if ($id) {
                $transaction = Transaction::with(['items.menus'])->find($id);

                if ($transaction) {
                    return ResponseFormatter::success(
                        $transaction,
                        'Data transaksi berhasil diambil'
                    );
                } else {
                    return ResponseFormatter::error(
                        null,
                        'Data transaksi tidak ada',
                        404
                    );
                }
            }

            $transaction = Transaction::with(['items.menus']);

            if ($status)
                $transaction->where('status', $status);

            return ResponseFormatter::success(
                $transaction->paginate($limit),
                'Data list transaksi berhasil diambil'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error($error, 'Ambil Data Transaksi Gagal');
        }
    }

    public function checkout(Request $request)
    {
        try {
            $request->validate([
                'invoice' => 'required',
                'items' => 'required|array',
                'seat_number' => 'required',
                'queue' => 'required',
                'items.*.id' => 'exists:menus,id',
                'total_price' => 'required',
                'status' => 'required',
            ]);
            //        return ResponseFormatter::success($request->items,'Transaksi Berhasil');
            $transaction = Transaction::create([
                'users_id' => Auth::user()->id,
                'invoice' => $request->invoice,
                'seat_number' => $request->seat_number,
                'queue' => $request->queue,
                'payment_method' => $request->payment_method,
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
        } catch (Exception $e) {
            DB::rollback();
            return ResponseFormatter::error($e, 'Transaksi Gagal');
        }
    }

    public function transactionQueue()
    {
        try {
            $transaction = Transaction::orderBy('created_at', 'desc')->first();
            // print_r($transaction);
            if ($transaction) {
                $newNumberQueue = $transaction->queue + 1;
                return ResponseFormatter::success($newNumberQueue, 'Nomor Antrian didapatkan', 200);
            }

            return ResponseFormatter::success(1, 'Nomor Antrian Baru dibuat', 404);
        } catch (Exception $error) {
            return ResponseFormatter::error($error, 'Ambil Nomor Antrian Gagal');
        }
    }
}
