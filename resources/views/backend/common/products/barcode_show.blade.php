<html>

<head>
    <title>Barcode Print</title>
    <style>
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: #FAFAFA;
            font: 12pt "Tahoma";
        }


        * {
            box-sizing: border-box;
            -moz-box-sizing: border-box;
        }

        .page {
            width: 50mm;
            min-height: 25mm;
            /*padding: 1mm 1mm;*/
            /*margin: 1mm auto;*/
            border: 1px #D3D3D3 solid;
            border-radius: 5px;
            background: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .subpage {
            padding: 1px;
            /*border: 1px red solid;*/
            height: 24mm;
            /*outline: 2cm #FFEAEA solid;*/
        }

        @page {
            size: 50mm 25mm;
            /*size: 50mm;*/
            margin: 0;
        }

        @media print {

            html,
            body {
                width: 50mm;
                height: 25mm;
            }

            .page {
                margin: 0;
                border: initial;
                border-radius: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
                /*page-break-after: always;*/
            }

        }
    </style>
</head>

<body>
    <div>
        {{-- @dd($barcodequantity); --}}
        @for ($i = 0; $i < count($productname); $i++)
            @for ($l = 0; $l < $barcodequantity[$i]; $l++)
                {{-- @dd($barcodes[$i]); --}}
                <div class="page">
                    <div class="subpage">
                        <div style="text-align: center;page-break-after:always;">
                            <span style="font-size: 11px;font-weight: bold;">{{ $productname[$i] }}</span> <br>
                            <img width="125mm" height="30mm" src="data:image/png;base64,{!! DNS1D::getBarcodePNG($barcodes[$i], 'C39') !!}" /><br>
                            <span style="font-size: 10px;">{{ $barcodes[$i] }}</span><br />
                        </div>
                    </div>
                </div>
            @endfor

        @endfor

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script type="text/javascript">
        var url = "{{ URL::to('/') }}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        window.print();

        var start = new Date;
        setInterval(function() {

        }, 1000);

        setTimeout(function() {
            document.location.href = "{{ url(Request::segment(1) . '/barcode-prints') }}";

        }, 1000);
    </script>

</body>

</html>
