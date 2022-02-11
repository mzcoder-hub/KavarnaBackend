<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Buyers;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Facades\Invoice;

class TransactionController extends Controller
{
    public function all(Request $request)
    {

        try {
            $id = $request->transaction_id;
            $limit = $request->limit;
            $status = $request->status;

            if ($id) {
                $transaction = Transaction::with(['items.menus.galleries'])->find($id);

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

            $transaction = Transaction::with(['items.menus.galleries']);

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

    public function changeStatus(Request $request)
    {
        try {
            $transaction = Transaction::find($request->transaction_id);
            // print_r($request->all());
            $transaction->status = $request->status;
            $transaction->save();

            return ResponseFormatter::success(
                $transaction,
                'Status transaksi berhasil diubah'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error($error, 'Ubah Status Transaksi Gagal');
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

            $addToCustTable = Buyers::create([
                'transactions_id' => $transaction->id,
                'name' => $request->name_customer,
                'phone_number' => $request->phone_customer,
            ]);

            //        return ResponseFormatter::success($transaction->id, 'Transaction Success');

            foreach ($request->items as $menu) {
                TransactionItem::create([
                    'menus_id' => $menu['id'],
                    'transactions_id' => $transaction->id,
                    'quantity' => $menu['quantity']
                ]);
            }

            $client = new Party([
                'name'          => 'Sewidji Cafe & Resto',
                'custom_fields' => [
                    'alamat'        => 'Jl. Raya Pekajangan No.135, Gendingan, Pekajangan, Kec. Kedungwuni, Kabupaten Pekalongan, Jawa Tengah 51173',
                ],
            ]);

            $customer = new Party([
                'name'          => $transaction->buyers->name,
                'custom_fields' => [
                    'Nomor HP' => $transaction->buyers->phone_number,
                ],
            ]);


            $items = [];

            foreach ($transaction->items as $data) {
                $items[] = (new InvoiceItem())
                    ->title($data->menus[0]->name)
                    ->pricePerUnit($data->menus[0]->price)
                    ->quantity($data->quantity);
            }

            // $notes = [
            //     'your multiline',
            //     'additional notes',
            //     'in regards of delivery or something else',
            // ];
            // $notes = implode("<br>", $notes);

            $invoice = Invoice::make('receipt')
                // ability to include translated invoice status
                // in case it was paid
                ->status(__('invoices::invoice.paid'))
                ->sequence(667)
                ->serialNumberFormat('{SEQUENCE}')
                ->seller($client)
                ->buyer($customer)
                ->date(now()->subWeeks(3))
                ->dateFormat('m/d/Y')
                ->currencySymbol('Rp.')
                ->currencyCode('Rupiah')
                ->currencyFormat('{SYMBOL}{VALUE}')
                ->currencyThousandsSeparator('.')
                ->currencyDecimalPoint(',')
                ->filename($transaction->invoice)
                ->addItems($items)
                // ->notes($notes)
                // ->logo(public_path('vendor/invoices/sample-logo.png'))
                // You can additionally save generated invoice to configured disk
                ->save('public');

            $link = $invoice->url();
            // Then send email to party with link

            // And return invoice itself to browser or have a different view
            $transaction->pdf = $link;
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

    public function transactionRange(Request $request)
    {

        try {
            $request->validate([
                'typeRange' => 'required',
            ]);

            // print_r($request->typeRange);

            if ($request->typeRange == 'today') {
                $transaction = Transaction::where('status', '=', 'SUCCESS')->where('created_at', ">=", Carbon::today())->with(['items.menus'])->get();

                if ($transaction) {
                    return ResponseFormatter::success($transaction, 'Data transaksi berhasil diambil');
                } else {
                    return ResponseFormatter::error(
                        null,
                        'Data transaksi tidak ada',
                        404
                    );
                }
            }

            if ($request->typeRange == 'weeks') {

                $transaction = Transaction::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where('status', 'SUCCESS')->with(['items.menus'])->get();

                if ($transaction) {
                    return ResponseFormatter::success($transaction, 'Data transaksi berhasil diambil');
                } else {
                    return ResponseFormatter::error(
                        null,
                        'Data transaksi tidak ada',
                        404
                    );
                }
            }
            if ($request->typeRange == 'years') {

                $transaction = Transaction::whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])->where('status', 'SUCCESS')->with(['items.menus'])->get();

                if ($transaction) {
                    return ResponseFormatter::success($transaction, 'Data transaksi berhasil diambil');
                } else {
                    return ResponseFormatter::error(
                        null,
                        'Data transaksi tidak ada',
                        404
                    );
                }
            }
        } catch (Exception $error) {
            return ResponseFormatter::error($error, 'Ambil Data Transaksi Gagal');
        }
    }
}
