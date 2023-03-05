<html>

<head>
    <meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Store Purchases Report </title>
    <style media="all">
        @font-face {
            font-family: 'zahidularabic';
            font-weight: normal;
            font-style: normal;
            font-variant: normal;
            src: url('{{ storage_path('fonts/Adobe_Arabic_Regular.ttf') }}');


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
            <table>
                <tr>
                    <td>
                        <img loading="lazy" src="{{ asset(@$store->logo) }}" height="40" style="display:inline-block;">
                    </td>
                    {{-- <td  class="text-right strong"><img  src="data:image/png;base64,{{DNS2D::getBarcodePNG(Request::url(), 'QRCODE')}}" alt="QR Code" style="width:70px;" /></td> --}}
                </tr>
            </table>
            <table>
                <tr>
                    <td style="font-size: 1.2rem" class="strong"> Store Purchases Report </td>
                    <td class="text-right">at {{ date('Y-m-d H:i A') }}</td>
                </tr>
                <tr>
                    <td class="strong small">
                        Store : {{ @$store->name }}<br>
                    </td>
                </tr>
                <tr>
                    <td class="text-right small"><span class=" small">Address : {{ @$store->address }}</span></td>
                </tr>
                <tr>
                    <td class="strong small">Phone: {{ @$store->phone }}</td>
                    <td class="text-right small strong"><span class="small">Date: {!! @$from !!} To
                            {{ @$to }} </span></td>
                </tr>
            </table>
        </div>
        @if ($storeWisePurchaseReports->isNotEmpty())
            <div style="padding: 1.5rem;">
                <table class="padding text-left small border-bottom">
                    <thead>
                        <tr class="strong" style="background: #eceff4;">
                            <th>SL</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Invoice Number</th>
                            <th>Vat</th>
                            <th>Dis</th>
                            <th>Sub Total</th>
                            <th class="text-right">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody class="strong">
                        @foreach ($storeWisePurchaseReports as $sale)
                            <tr class="">
                                <td>{{ $loop->index + 01 }}</td>
                                <td>{{ @$sale->entry_date }}</td>
                                <td>{{ @$sale->supplier->name }}</td>
                                <td>{{ @$sale->id }}</td>
                                <td class="text-right"> {{ @$sale->total_vat }}</td>
                                <td class="text-right"> {{ @$sale->discount ? $sale->discount : 0 }}</td>
                                <td class="text-right"> {{ @$sale->sub_total }}</td>
                                <td class="text-right">{{ $sale->grand_total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div>
                    <h2 class="text-center">No Result found</h2>
                </div>
        @endif
    </div>

    <div style="padding:0 1.5rem;">
        <table style="width: 40%;margin-left:auto;" class="text-right sm-padding small strong">
            <tbody>

                <tr>
                    <th class="text-left strong">Total</th>
                    <td class="currency">{{ $storeWisePurchaseReports->sum('grand_total') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    </div>
</body>

</html>
