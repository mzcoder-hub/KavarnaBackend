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



        // // $notes = [
        // // 'your multiline',
        // // 'additional notes',
        // // 'in regards of delivery or something else',
        // // ];
        // // $notes = implode("<br>", $notes);

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
            ->save('public')
            // // ->notes($notes)
            ->logo(public_path('vendor/invoices/sample-logo.png'));
        // // You can additionally save generated invoice to configured disk

        $link = $invoice->url();
        // // Then send email to party with link

        // // And return invoice itself to browser or have a different view
        return $link;
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

        // $notes = [
        // 'your multiline',
        // 'additional notes',
        // 'in regards of delivery or something else',
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
        return $link;
    }
}
