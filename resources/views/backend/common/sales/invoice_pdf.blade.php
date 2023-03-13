@php
    use Salla\ZATCA\GenerateQrCode;
    use Salla\ZATCA\Tags\InvoiceDate;
    use Salla\ZATCA\Tags\InvoiceTaxAmount;
    use Salla\ZATCA\Tags\InvoiceTotalAmount;
    use Salla\ZATCA\Tags\Seller;
    use Salla\ZATCA\Tags\TaxNumber;

    $totalVat = @$sale->total_vat;
    $totalAmount = @$sale->grand_total;
    // $displayQRCodeAsBase64 = GenerateQrCode::fromArray([new Seller(getSalesmanNameById(@$sale->salesman_user_id)), new TaxNumber(@SalePrintSetting()->vat_number), new InvoiceDate($sale->date), new InvoiceTotalAmount($totalAmount), new InvoiceTaxAmount($totalVat)])->render();

@endphp
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice</title>
    <style media="all">
        @font-face {
            font-family: 'zahidularabic';
            font-weight: normal;
            font-style: normal;
            font-variant: normal;
            src: url('{{ storage_path('fonts/Adobe_Arabic_Regular.ttf') }}');


        }

        @font-face {
            font-family: 'bangla';
            font-weight: normal;
            font-style: normal;
            font-variant: normal;
            /* src: url('{{ storage_path('fonts/Potro_Sans_Bangla_Bold.ttf') }}'); */
            src: '{{ storage_path('fonts/Potro_Sans_Bangla_Bold.ttf') }}';


        }

        * {
            margin: 0;
            padding: 0;
            line-height: 1.3;
            color: #333542;
        }

        body {
            font-size: .875rem;
            font-family: "dejavu sans mono, helvetica";
            color: #000000 !important
        }

        .arabic {
            direction: inherit !important;
            font-family: "zahidularabic" !important
        }

        .gry-color *,
        .gry-color {
            color: #878f9c;
        }

        table {
            width: 100%;
        }

        table th {
            font-weight: normal;
        }

        table.padding th {
            padding: .5rem .7rem;
        }

        table.padding td {
            padding: .7rem;
        }

        table.sm-padding td {
            padding: .2rem .7rem;
        }

        .border-bottom td,
        .border-bottom th {
            border-bottom: 1px solid #eceff4;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .small {
            font-size: .85rem;
        }

        .currency {}
    </style>
</head>

<body>
    <div>
        <div style="background: #eceff4;padding: 1.5rem;">
            {{-- <div style="width: 100%"> --}}
                <div  style="width: 50%:float:left;">
                    <table>
                        <tr>
                            <td>
                                <img loading="lazy" src="{{ asset(@$sale->store->logo)}}" height="40"
                                    style="display:inline-block;">
                            </td>
                            {{-- <td class="text-right strong"><img src="{{ $displayQRCodeAsBase64 }}" alt="QR Code"
                                    style="width:70px;" /></td> --}}
                        </tr>
                    </table>
                </div>
                <div  style="width: 50%:float:right;">
                    <table>
                        <tr>
                            <td style="font-size: 1.2rem;" class="strong"> sss</td>
                            <td class="text-right"></td>
                        </tr>
                        <tr>
                            <td class="strong small">
                                Store:  {{ Helper::getStoreName(@$sale->store_id) }}<br>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right small"><span class=" small">Inovice ID: {{ @$sale->invoice_no }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-right small strong"><span class="small">Invoice Date: {{ @$sale->created_at }}
                                </span></td>
                        </tr>
                    </table>
                </div>
            {{-- </div> --}}



        </div>

        <div style="padding: 1.5rem;padding-bottom: 0">
            <table>
                <tr>
                    <td class="strong smallstrong">Customer: {{ @$sale->customer->name }}
                        </td>
                </tr>
                <tr>
                    <td class="strong small">Address: {{ @$sale->customer->address ?: $sale->customer->email }},
                        {{ @$sale->customer->phone }}</td>
                </tr>
                <tr>
                    <td class="strong small">Customer Phone: {{ @$sale->customer->phone }}</td>
                </tr>
            </table>
        </div>

        <div style="padding: 1.5rem;">
            <table class="padding text-left small border-bottom">
                <thead>
                    <tr class="strong" style="background: #eceff4;">
                        <th width="35%">Name</th>
                        <th width="10%"> U/M</th>
                        <th width="15%"> Qty </th>
                        <th width="10%"> UN P</th>
                        <th width="15%" class="text-right"> Total Price</th>
                    </tr>
                </thead>
                <tbody class="strong">
                    @foreach ($saleProducts as $key => $sales_info)
                        <tr class="">
                            <td>{{ Str::limit(@$sales_info->product->name, 50, '..') }} </td>
                            <td>
                                {{ @$sales_info->product->unit->name }}
                            </td>
                            <td class="strong">{{ @$sales_info->qty }}</td>
                            <td class="strong currency">{{ number_format(@$sales_info->sale_price, 2) }}</td>
                            <td class="text-right currency">
                                {{ number_format(@$sales_info->qty * @$sales_info->sale_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding:0 1.5rem;">
            <table style="width: 40%;margin-left:auto;" class="text-right sm-padding small strong">
                <tbody>
                    <tr>
                        <th class="strong text-left">Sub Total</th>
                        <td class="currency">{{ number_format(@$sale->sub_total, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="strong text-left">VAT:</th>
                        <td class="currency">{{ number_format(@$sale->total_vat, 2) }}</td>
                    </tr>
                    <tr class="border-bottom">
                        <th class="strong text-left">Discount:</th>
                        <td class="currency">{{ number_format(@$sale->discount, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="text-left strong">Grand Total:</th>
                        <td class="currency">{{ number_format(@$sale->grand_total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="padding:0 1.5rem;">
            <div class="col-md-12" style="text-align:right;float:right;">
                <span>Print Date: {{date('Y-m-d H:i:s')}} Computer Generated Invoice</span>
            </div>
        </div>

    </div>
</body>

</html>
