<?php

namespace App\Helpers;

use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Facades\Invoice;

class GeneratedPDF
{
    public static function letGeneratePDF($transaction)
    {

        $client = new Party([
            'name' => 'Ngabangan 1907 Coffee Bar',
            'custom_fields' => [
                'alamat' => 'Jl. Surabaya Sugihwaras Gg. 5A No.3, Sugihwaras, Kec. Pekalongan Tim., Kota Pekalongan, Jawa Tengah 51129',
            ],
        ]);

        $customer = new Party([
            'name' => $transaction->buyers->name,
            'custom_fields' => [
                'Nomor HP' => $transaction->buyers->phone_number,
            ],
        ]);

        $items = [];

        foreach ($transaction->items as $data) {
            $items[] = (new InvoiceItem())
                ->title($data->menus[0]->title)
                ->pricePerUnit($data->menus[0]->price_per_unit)
                ->quantity($data->quantity);
        }

        $data = [
            "items" => $transaction->items,
        ];

        // $invoice = Invoice::make('receipt')
        //     ->status(__('invoices::invoice.paid'))
        //     ->sequence(667)
        //     ->serialNumberFormat('{SEQUENCE}')
        //     ->seller($client)
        //     ->buyer($customer)
        //     ->date(now()->subWeeks(3))
        //     ->dateFormat('m/d/Y')
        //     ->currencySymbol('Rp.')
        //     ->currencyFormat('{SYMBOL}{VALUE}')
        //     ->currencyThousandsSeparator('.')
        //     ->currencyDecimalPoint(',')
        //     ->filename($transaction->invoice)
        //     ->addItems($items)
        //     ->save('public');

        // $link = $invoice->url();

        return $data;
    }

    public function letGeneratePDFAndSave($transaction)
    {
        $client = new Party([
            'name' => 'Sewidji Cafe & Resto',
            'custom_fields' => [
                'alamat' => 'Jl. Raya Pekajangan No.135, Gendingan, Pekajangan, Kec. Kedungwuni, Kabupaten Pekalongan, Jawa Tengah 51173',
            ],
        ]);

        $customer = new Party([
            'name' => $transaction->buyers->name,
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
            ->filename($transaction->invoice)
            ->addItems($items)
            ->save('public');

        $link = $invoice->url();
        // Then send email to party with link

        // And return invoice itself to browser or have a different view
        return $link;
    }
}
