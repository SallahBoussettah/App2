<!doctype html>
<html>
    <head>
        <title>Pancarte</title>
        
        <!-- CSS -->
        <link rel="stylesheet" href="{{ voyager_asset('css/app.css') }}">
        
        <style type="text/css">
            @media print{
                @page {size:A4 landscape}
                body
                {
                  margin: 25mm 25mm 25mm 25mm;
                }
            }
            html, body{margin: 0; padding: 0;}
            .page{
                box-sizing: border-box; 
                height: 100%; 
                width: 100%; 
                border: 1px solid transparent; 
                page-break-after: always;
            }
            .page-middle{
                height: 100%;
                width: 100%; 
                display: table;
            }
            .page-middle-inner{
                height: 100%; 
                width: 100%; 
                display: table-cell; 
                vertical-align: middle;
                position:relative;
            }
            .name {
                margin-left:20mm;
                margin-right:20mm;
                text-align:center;
                font-size:95pt;
                line-height:100pt;
                color:#000;
                font-family: "Open Sans", sans-serif;
                text-transform: capitalize;
                font-weight: 300;
            }
            .panneau__footer {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                text-align:center;
            }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="page-middle">
                <div class="page-middle-inner">
                    <p class="name">{{ $reservation->client_nom }}</p>
                    <p class="name">{{ !empty($reservation->agence()) ? $reservation->agence()->nom : ''}}</p>
                    <table class="panneau__footer">
            			<tr>
            			    <td style="width:25%;">
                				<small>Date : {{ $reservation->date_resa }}</small><br>
                				<small>Heur : {{ $reservation->hour_start }}</small><br>
            			    </td>
            			    <td style="width:25%;">
                				<small>Hotel : {{ $reservation->hotel_nom }}</small><br>
                				<small>Adresse : {{ $reservation->hotel_adresse }}</small><br>
                				<small>Téléphone : {{ $reservation->hotel_tel }}</small>
            			    </td>
            			    <td style="width:25%;">
                				<small>Pax : {{ $reservation->pax_adulte+$reservation->pax_enfant12+$reservation->pax_enfant3 }} personne(s)</small><br>
                				<small>Chauffeur : {{ !empty($reservation->chauffeur())?$reservation->chauffeur():'' }}</small>
                			</td>
                			<td style="width:25%;">
                				<small>Prix : {{ $reservation->prixtotal}} {{$reservation->devise_symbol()}}</small><br>
                				<small>Vol / Cmt : {{ $reservation->vol_cmt }}</small>
                			</td>
            			</tr>
            		</table>
                </div>
            </div>
        </div>
        <!-- JS -->
        <script type="text/javascript" src="{{ voyager_asset('js/app.js') }}"></script>
        <script type="text/javascript">
            window.print();
        </script>
    </body>
</html>