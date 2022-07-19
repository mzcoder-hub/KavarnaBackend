<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Facades\Invoice;

class generatePDFInvoice extends Controller
{
    public function generateInvoice(Request $request)
    {
        $getTransactionById = Transaction::with(['items.menus'])->find($request->id);
        if ($getTransactionById) {

            $client = new Party([
                'name'          => 'Sewidji Cafe & Resto',
                'custom_fields' => [
                    'alamat'        => 'Jl. Raya Pekajangan No.135, Gendingan, Pekajangan, Kec. Kedungwuni, Kabupaten Pekalongan, Jawa Tengah 51173',
                ],
            ]);

            $customer = new Party([
                'name'          => 'Ashley Medina',
                'address'       => 'The Green Street 12',
                'code'          => '#22663214',
                'custom_fields' => [
                    'order number' => '> 654321 <',
                ],
            ]);


            $items = [];

            foreach ($getTransactionById->items as $data) {
                $items[] = (new InvoiceItem())
                    ->title($data->menus[0]->name)
                    ->pricePerUnit($data->menus[0]->price)
                    ->quantity($data->quantity);
            }

            $invoice = Invoice::make('receipt')
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
                ->filename($client->name . ' ' . $customer->name)
                ->addItems($items)
                ->save('local');

            $link = $invoice->url();

            return $invoice->stream();
        } else {
            $client = new Party([
                'name'          => 'Roosevelt Lloyd',
                'phone'         => '(520) 318-9486',
                'custom_fields' => [
                    'note'        => 'IDDQD',
                    'business id' => '365#GG',
                ],
            ]);

            $customer = new Party([
                'name'          => 'Ashley Medina',
                'address'       => 'The Green Street 12',
                'code'          => '#22663214',
                'custom_fields' => [
                    'order number' => '> 654321 <',
                ],
            ]);

            $items = [
                (new InvoiceItem())
                    ->title('Service 1')
                    ->description('Your product or service description')
                    ->pricePerUnit(47.79)
                    ->quantity(2)
                    ->discount(10)
            ];

            $notes = [
                'your multiline',
                'additional notes',
                'in regards of delivery or something else',
            ];
            $notes = implode("<br>", $notes);

            $invoice = Invoice::make('receipt')
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
                ->filename($client->name . ' ' . $customer->name)
                ->addItems($items)
                ->notes($notes)
                ->logo(public_path('vendor/invoices/sample-logo.png'))
                ->save('local');

            $link = $invoice->url();

            return $invoice->stream();
        }
    }
}
