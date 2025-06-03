<!doctype html>
<html>
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Liste</title>
        
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
            
            /* LISTE */
            .header-logos td{vertical-align: middle;padding: 15px 15px 0px 15px;}
    		.header-logos td img{width: 15%;}
    		.liste-title h3{font-weight: 900;text-align: center;font-size: 14px;border: 2px solid #000;padding: 10px;width: 20%;margin-left: 40%;}
    		.date-section {width: 95%;border-collapse: collapse;margin-right: 5%;}
    		.date-section td {border:1px solid #000;width: 100%;padding: 5px;font-size: 10px}
    
    		.content {width: 96%;border-collapse: collapse;margin-left: 2%;margin-right: 2%;margin-bottom: 25px;}
    		.content th {text-align: left;background-color: #e8e8e8}
    		.content tr {border-bottom: 1px solid #000;}
    		.content th , .content td {font-size: 11px;padding: 8px 4px 8px 2px;}
    		table { page-break-inside:auto }
            tr    { page-break-inside:avoid; page-break-after:auto }
            
            /* Pancarte */
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
        <!-- LISTE -->
        <table style="width: 100%;margin-bottom: 25px;">
    		<tr class="header-logos">
    			<td colspan="3" style="text-align: center;"><img src="{{ url('storage/'.setting('admin.icon_image')) }}"></td>
    		</tr>
    		<tr>
    			<td colspan="3" class="liste-title"><h3>Liste des réservations</h3></td>
    		</tr>
    		<tr>
    			<td></td><td></td>
    			<td style="width: 33%;">
    				<table class="date-section" border="0">
    					<tr>
    						<td style="background-color: #e8e8e8;width: 50%;">Date Debut</td>
    						<td>{{ $date }}</td>
    					</tr>
    					<tr>
    						<td style="background-color: #e8e8e8;width: 50%;">Chauffeur</td>
    						<td>{{ !empty($chauffeur_t) ? $chauffeur_t->nom : '' }}</td>
    					</tr>
    					<tr>
    						<td style="background-color: #e8e8e8;width: 50%;">Nombre des réservations</td>
    						<td>{{ count($reservations) }}</td>
    					</tr>
    				</table>
    			</td>
    		</tr>
    	</table>
    
    	<table class="content">
    		<tr>
    			<th style="width:5%;">Numero</th>
    			<th style="width:10%;">Client</th>
    			<th style="width:15%;">Date</th>
    			<th style="width:10%;">Hotel</th>
    			<th style="width:15%;">Addresse</th>
    			<th style="width:10%;">Téléphone</th>
    			<th style="width:10%;">Pax</th>
    			<th style="width:10%;">Chauffeur</th>
    			<th style="width:15%;">Observation</th>
    		</tr>
    		@foreach($reservations as $key => $reservation)
    		<tr>
    			<td>{{ $reservation->id }}</td>
    			<td>{{ $reservation->client_nom.' '.$reservation->client_prenom }}</td>
    			<td>
                    <small>Date : {{ $reservation->date_resa }}</small><br>
                    <small>Heur : {{ $reservation->hour_start }}</small><br>
			    </td>
    			<td>{{ $reservation->hotel_nom }}</td>
    			<td>{{ $reservation->hotel_adresse }}</td>
    			<td>{{ $reservation->hotel_tel }}</td>
    			<td>
                    Adult : {{ $reservation->pax_adulte }}<br/>
                    Enfant (-12) : {{ $reservation->pax_enfant12 }}<br/>
                    Enfant (3): {{ $reservation->pax_enfant3 }}
                </td>
    			<td>{{ !empty($reservation->chauffeur()) ? $reservation->chauffeur() : '' }}</td>
    			<td>{{ $reservation->client_commentaire }}</td>
    		</tr>
    		@endforeach
    	</table>
    	
    	<div style="page-break-before: always;"></div>
    	
    	@if($with == "oui")
    	    <!--PANCARTE -->
        	@foreach($reservations as $key => $reservation)
            <div class="page">
                <div class="page-middle">
                    <div class="page-middle-inner">
                        <p class="name">{{ $reservation->client_nom.' '.$reservation->client_prenom }}</p>
                        <table class="panneau__footer">
                			<tr>
                			    <td style="width:25%;">
                    				<small>Date : {{ $reservation->date_resa }}</small><br>
                    				<small>Heur : {{ $reservation->hour_start }}</small><br>
                    				<small>Vol / Cmt : {{ $reservation->vol_cmt }}</small>
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
                    				<small>Observation : {{ $reservation->client_commentaire }}</small>
                    			</td>
                			</tr>
                		</table>
                    </div>
                </div>
            </div>
        	@endforeach
        @endif
        
        <!-- JS -->
        <script type="text/javascript" src="{{ voyager_asset('js/app.js') }}"></script>
        <script type="text/javascript">
            window.print();
        </script>
    </body>
</html>