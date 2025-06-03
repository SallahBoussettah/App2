<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use App\Reservation;
use App\Paiment;
use App\Devise;
use DateTime;
use App\Expense;
use App\Chauffeur;
use App\EtatChauffeur;
use App\Fournisseur;
use App\Reclamation;
use App\Accountant;
use App\Diesel;
use File;
use ZipArchive;

class HomeController extends Controller
{
    public function index(Request $request) {
    	return view('statistiques');
    }

    public function get(Request $request) {
    	$range = explode('|', $request->date);
        $start = trim($range[0]);
        $end = trim($range[1]);

        // CHART 1 NB/TYPE
        $nb_reservations_tmp = Reservation::select("typeresa",DB::raw("COUNT(*) as count"))
    		->where('date_resa', '>=' , $start)
    		->where('date_resa', '<=' , $end)
		    ->groupBy(DB::raw("typeresa"))
		    ->get();

		$nb_reservations = [0,0,0,0];
		foreach ($nb_reservations_tmp as $key => $value) {
			switch ($value->typeresa) {
				case 'depart':
					$nb_reservations[0] = $value->count;
					break;

				case 'arrive':
					$nb_reservations[1] = $value->count;
					break;

				case 'excursioncircuit':
					$nb_reservations[2] = $value->count;
					break;
				case 'intertransfert':
					$nb_reservations[3] = $value->count;
					break;
				
			}
		}

		// CHART 2 PROGRESS/MOIS
		$start = new DateTime($start);
        $end = new DateTime($end);

        $diff = $end->diff($start);

        $labels = [];

        if($diff->y > 0) {
            $data = Reservation::selectRaw('YEAR(reservations.date_resa) as date_resa_n,sum(reservations.prixtotal*devises.value) as sum,count(*) as count')
                ->join('devises', 'devises.id', '=', 'reservations.devise_id')
                ->where('date_resa','>=',$start->format('Y-m-d'))
                ->where('date_resa','<=',$end->format('Y-m-d'))
                ->where('status','!=','pending')
                ->groupBy('date_resa_n')
                ->get();

            for($i = 0;$i <= $diff->y ; $i++) {
                $old = new DateTime($start->format('Y-m-d'));
                $labels[] = $old->modify('+'.$i.' year')->format('Y');
            }

            $duree = ($diff->y+1)." année(s)";
        }
        elseif($diff->m > 0) {
            $data = Reservation::selectRaw('CONCAT(YEAR(reservations.date_resa),"-",MONTH(reservations.date_resa)) as date_resa_n,sum(reservations.prixtotal*devises.value) as sum,count(*) as count')
                ->join('devises', 'devises.id', '=', 'reservations.devise_id')
                ->where('date_resa','>=',$start->format('Y-m-d'))
                ->where('date_resa','<=',$end->format('Y-m-d'))
                ->where('status','!=','pending')
                ->groupBy('date_resa_n')
                ->get();

            for($i = 0;$i <= $diff->m ; $i++) {
                $old = new DateTime($start->format('Y-m-d'));
                $labels[] = $old->modify('+'.$i.' month')->format('Y-n');
            }

            $duree = ($diff->m+1)." mois";
        }
        else {
            $data = Reservation::selectRaw('reservations.date_resa as date_resa_n,sum(reservations.prixtotal*devises.value) as sum,count(*) as count')
                ->join('devises', 'devises.id', '=', 'reservations.devise_id')
                ->where('date_resa','>=',$start->format('Y-m-d'))
                ->where('date_resa','<=',$end->format('Y-m-d'))
                ->where('status','!=','pending')
                ->groupBy('date_resa_n')
                ->get();

            for($i = 0;$i <= $diff->d ; $i++) {
                $old = new DateTime($start->format('Y-m-d'));
                $labels[] = $old->modify('+'.$i.' day')->format('Y-m-d');
            }

            $duree = ($diff->d+1)." jour(s)";
        }

        // GET DEFAULT CURRENCY
        $currency = Devise::where('value',1)->first();
        if(empty($currency)) {
            $currency = "Undefined";
        }
        else {
            $currency = $currency->symbol;
        }

    	return json_encode([
            "currency"=>$currency,
    		"nb_reservations" => $nb_reservations,
    		"progress"=>array("labels"=>$labels,"data"=>$data,"duree"=>$duree)
    	]);
    }

    // RAPPORTS
    public function index_rapports(Request $request) {
        
        return view('rapports');
    }



    public function get_rapports(Request $request) {
        //dd($request->all());
        $range = explode('|', $request->date);
        $start = trim($range[0]);
        $end = trim($range[1]);
        //dd($range);
        $start = new DateTime($start);
        $end = new DateTime($end);

        $reservations = DB::table('reservations')
            ->selectRaw('reservations.* ,(reservations.prixtotal*devises.value) as prix,devises.symbol as devise,agences.nom as agence,chauffeurs.nom as chauffeur , tours.title as tour')
            ->leftJoin('agences', 'agences.id', '=', 'reservations.agence_id')
            ->leftJoin('chauffeurs', 'chauffeurs.id', '=', 'reservations.chauffeur_id')
            ->leftJoin('devises', 'devises.id', '=', 'reservations.devise_id')
            ->leftJoin('tours', 'tours.id', '=', 'reservations.tour_id')
            ->leftJoin('reservation_fournisseurs', 'reservation_fournisseurs.reservation_id', '=', 'reservations.id')
            ->where('reservations.date_resa','>=',$start->format('Y-m-d'))
            ->where('reservations.date_resa','<=',$end->format('Y-m-d'))
            ->whereRaw('1 = 1');

        if($request->reclamation != ""){
            /* $reservations = $reservations->rightJoin('reclamations', 'reclamations.reservation_id', '=', 'reservations.id');
            if($request->chauffeur != ""){
                  $reservations = $reservations->where('reclamations.chauffeur_id',$request->chauffeur);
            }*/
            dd($request->reclamation);
            $reservations = $reservations->where('reservations.reclamation',$request->reclamation);
        }
        if($request->agence != "") {
            $reservations = $reservations->where('reservations.agence_id',$request->agence);
        }
        $totaldepance = 0 ; 
		$totaletat = 0 ;
        $totalgaz = 0 ;
        if($request->chauffeur != "") {
            $reservations = $reservations->where('reservations.chauffeur_id',$request->chauffeur);
            
            $totaldepance = Expense::join('devises', 'devises.id', '=', 'devise_id')
            ->where('chauffeur_id',$request->chauffeur)
            ->where('date','>=',$start->format('Y-m-d'))
            ->where('date','<=',$end->format('Y-m-d'))
            ->sum(\DB::raw('cost *devises.value'));

            $totaletat = EtatChauffeur::join('devises', 'devises.id', '=', 'devise_id')
            ->where('chauffeur_id',$request->chauffeur)
            ->where('etat_chauffeurs.day','>=',$start->format('Y-m-d'))
            ->where('etat_chauffeurs.day','<=',$end->format('Y-m-d'))
            ->sum(\DB::raw('total_day*devises.value'));

            $totalgaz = Diesel::join('devises', 'devises.id', '=', 'devise_id')
            ->where('chauffeur_id',$request->chauffeur)
            ->where('date','>=',$start->format('Y-m-d'))
            ->where('date','<=',$end->format('Y-m-d'))
            ->sum(\DB::raw('cost*devises.value'));

            
 
        }
        else if($request->tour != "") 
        {
            $totalgaz = DB::table('diesels')
                        ->Join('devises', 'devises.id', '=', 'diesels.devise_id')
                        ->where('date','>=',$start->format('Y-m-d'))
                        ->where('date','<=',$end->format('Y-m-d'))
                        ->where('tour_id', '=', $request->tour)
                        ->sum(\DB::raw('cost*devises.value'));
            $totaldepance = Expense::join('devises', 'devises.id', '=', 'devise_id')
                            ->where('date','>=',$start->format('Y-m-d'))
                            ->where('date','<=',$end->format('Y-m-d'))
                            ->where('tour_id', '=', $request->tour)
                            ->sum(\DB::raw('cost *devises.value'));
            $totaletat = EtatChauffeur::join('devises', 'devises.id', '=', 'devise_id')
                            ->where('etat_chauffeurs.day','>=',$start->format('Y-m-d'))
                            ->where('etat_chauffeurs.day','<=',$end->format('Y-m-d'))
                            ->sum(\DB::raw('total_day*devises.value'));
        }
        else
        {
            
            $totaletat = EtatChauffeur::join('devises', 'devises.id', '=', 'devise_id')
                        ->where('etat_chauffeurs.day','>=',$start->format('Y-m-d'))
                        ->where('etat_chauffeurs.day','<=',$end->format('Y-m-d'))
                        ->sum(\DB::raw('total_day*devises.value'));

            $totalgaz = DB::table('diesels')
                        ->Join('devises', 'devises.id', '=', 'diesels.devise_id')
                        ->where('date','>=',$start->format('Y-m-d'))
                        ->where('date','<=',$end->format('Y-m-d'))
                        ->sum(\DB::raw('cost*devises.value'));
            $totaldepance = Expense::join('devises', 'devises.id', '=', 'devise_id')
                        ->where('date','>=',$start->format('Y-m-d'))
                        ->where('date','<=',$end->format('Y-m-d'))
                        ->sum(\DB::raw('cost *devises.value'));

        }

        if($request->tour != "") 
        {
            $reservations = $reservations->where('reservations.tour_id',$request->tour);
        }
        
        if($request->type != "") {
            $reservations = $reservations->where('reservations.typeresa',$request->type);
        }
        if($request->forni != ""){
             $reservations = $reservations->where('reservation_fournisseurs.fournisseur_id',$request->forni);
        }


        $reservations = $reservations->get();
        // GET DEFAULT CURRENCY
        $currency = Devise::where('value',1)->first();
        if(empty($currency)) {
            $currency = "Undefined";
        }
        else {
            $currency = $currency->symbol;
        }

       // dd($reservations);

       // dd($totalgaz , $totaletat , $totaldepance);
        return json_encode(["reservations"=>$reservations,"currency"=>$currency , "cost" => $totaldepance, "total_day"=>$totaletat , "totalgaz" => $totalgaz]);
    }

    // PAIMENTS
    public function paiments_status(Request $request) {
        // INTIAL VARIABLE
        $start = explode('/',$request->mois);
        $date = new DateTime($start[1].'-'.$start[0].'-01');

        $devises = Devise::all();
        // GET RESERVATION
        $reservations = Reservation::where('agence_id',$request->agence)
            ->where('date_resa','>=',$date->format('Y-m-d'))
            ->where('date_resa','<=',$date->format('Y-m-t'))
            ->get();

        $sum_resa = 0;
        foreach ($reservations as $key => $reservation) {
            $sum_resa += $this->convert_to_currency($devises,$reservation->devise_id,$reservation->prixtotal);
        }
        $sum_resa = number_format((float)$sum_resa, 2, '.', '');

        // GEt PAIMENTS
        $paiments = Paiment::where('agence_id',$request->agence)
            ->where('mois',$request->mois)
            ->get();

        $sum_paiment = 0;
        foreach ($paiments as $key => $paiment) {
            $sum_paiment += $this->convert_to_currency($devises,$paiment->devise_id,$paiment->total);
        }
        $sum_paiment = number_format((float)$sum_paiment, 2, '.', '');

        // GET DEFAULT CURRENCY
        $currency = Devise::where('value',1)->first();
        if(empty($currency)) {
            $currency = "Undefined";
        }
        else {
            $currency = $currency->symbol;
        }

        // GET DIFFERENCE
        $diff = "danger";
        if($sum_paiment != 0) {
            if($sum_paiment >= $sum_resa) {
                $diff = "success";
            }
            else {
                $diff = "warning";
            }            
        }


        return json_encode([
            "sum_paiment"=>$sum_paiment,
            "sum_resa"=>$sum_resa,
            "currency"=>$currency,
            "diff"=>$diff,
            "date"=> $date->format('Y-m-d').' | '.$date->format('Y-m-t')
        ]);
    }

    public function convert_to_currency($devises,$devise,$value) {
        foreach ($devises as $k => $v) {
            if($v->id == $devise) {
                return number_format((float)($value*$v->value), 2, '.', '');
            }
        }
        return number_format((float)$value, 2, '.', '');
    }

    public function get_paiments(Request $request) {
        $range = explode('|', $request->date);
        
        $start = trim($range[0]);
        $end = trim($range[1]);

        $start = new DateTime($start);
        $end = new DateTime($end);

        /*$reservations = DB::table('reservations')
            ->selectRaw('reservations.*,agences.nom as agence,chauffeurs.nom as chauffeur,vehicules.titre as vehicule')
            ->join('agences', 'agences.id', '=', 'reservations.agence_id')
            ->join('chauffeurs', 'chauffeurs.id', '=', 'reservations.chauffeur_id')
            ->join('vehicules', 'vehicules.id', '=', 'reservations.vehicule_id')
            ->where('date_resa','>=',$start->format('Y-m-d'))
            ->where('date_resa','<=',$end->format('Y-m-d'))
            ->get();*/

        return json_encode($reservations);
    }



     public function chartofdriver(Request $request) {
       
        $driver = Chauffeur::find($request->id);
        // dd($driver);
        return view('chartbydrivers' , compact('driver'));
    }

    public function getbydriver(Request $request) {
        $range = explode('|', $request->date);
        $start = trim($range[0]);
        $end = trim($range[1]);

        $start = new DateTime($start);
        $end = new DateTime($end);

        $diff = $end->diff($start);
        $labels = [];

        if($diff->y > 0) {
            $data = Expense::selectRaw('YEAR(expenses.date) as date_n , sum(expenses.cost*devises.value) as cost ,count(*) as count')
                ->join('devises', 'devises.id', '=', 'expenses.devise_id')
                ->where('date','>=',$start->format('Y-m-d'))
                ->where('date','<=',$end->format('Y-m-d'))
                ->groupBy('date_n')
                ->get();
               // dd($data);

            for($i = 0;$i <= $diff->y ; $i++) {
                $old = new DateTime($start->format('Y-m-d'));
                $labels[] = $old->modify('+'.$i.' year')->format('Y');
            }

            $duree = ($diff->y+1)." année(s)";
        }
        elseif($diff->m > 0) {
            $data = Expense::selectRaw('CONCAT(YEAR(expenses.date),"-",MONTH(expenses.date)) as date_n , sum(expenses.cost*devises.value) as cost ,count(*) as count')
                ->join('devises', 'devises.id', '=', 'expenses.devise_id')
                ->where('date','>=',$start->format('Y-m-d'))
                ->where('date','<=',$end->format('Y-m-d'))
                ->groupBy('date_n')
                ->get();

            for($i = 0;$i <= $diff->m ; $i++) {
                $old = new DateTime($start->format('Y-m-d'));
                $labels[] = $old->modify('+'.$i.' month')->format('Y-n');
            }

            $duree = ($diff->m+1)." mois";
        }
        else {
            $data = Expense::selectRaw('expenses.date as date_n , sum(expenses.cost*devises.value) as cost  ,count(*) as count')
                 ->join('devises', 'devises.id', '=', 'expenses.devise_id')
                ->where('date','>=',$start->format('Y-m-d'))
                ->where('date','<=',$end->format('Y-m-d'))
                ->where('chauffeur_id','=',$request->id)
                ->groupBy('date_n')
                ->get();
//dd($data);
            for($i = 0;$i <= $diff->d ; $i++) {
                $old = new DateTime($start->format('Y-m-d'));
                $labels[] = $old->modify('+'.$i.' day')->format('Y-m-d');
            }

            $duree = ($diff->d+1)." jour(s)";
        }

        // GET DEFAULT CURRENCY
        $currency = Devise::where('value',1)->first();
        if(empty($currency)) {
            $currency = "Undefined";
        }
        else {
            $currency = $currency->symbol;
        }

        return json_encode([
            "currency"=>$currency,
            "progress"=>array("labels"=>$labels,"data"=>$data,"duree"=>$duree)
        ]);
    }


    public function index_archived()
    {
       $chauffeurs = Chauffeur::onlyTrashed()->get();
        return view('archivechauffeur' , compact('chauffeurs'));

    }
    public function update_archived($id)
    {
        $chauffeur = Chauffeur::withTrashed()
                     ->where('id', $id)
                     ->restore();
        return redirect('archive-chauffeurs');
    }

     public function downloadZip(Request $request, $ids_res) {
        $zip = new ZipArchive;
        
        $files = Accountant::whereIn('id' , json_decode($ids_res))->get();

        $date = new DateTime('NOW');
        $fileName = $date->format('Y-m-d-H-i-s').'.zip';
   
        if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE)
        {
            //$files = json_decode($ids_res);
            //dd($files);

            foreach ($files as $key => $value) {
               //    echo json_decode($value->files)[0]->download_link;
                $relativeNameInZipFile = basename("storage/".json_decode($value->files)[0]->download_link);
                $zip->addFile("storage/".json_decode($value->files)[0]->download_link, $relativeNameInZipFile);
            }
         //   dd('done');
             
            $zip->close();
        }
    
        return response()->download(public_path($fileName));
    }
    
public function getReservations()
    {
        
        $client = new Client();

        // Replace 'your_sandbox_username' and 'your_sandbox_password' with your sandbox credentials
        $sandboxUrl = 'https://supplier-api.getyourguide.com/sandbox/1/deals';

        try {
            $response = $client->get($sandboxUrl, [
                'auth' => ['eurekadigital', '371019d76edbd414e2a698181d3bc8ce'],
                'headers' => [
                    'Accept' => 'application/json',
                    // Add other required headers
                ],
                'query' => [
                    'externalProductId' => 'PPYM1U',
                ],
            ]);


            $data = json_decode($response->getBody(), true);

            // Handle the data as needed
            \Log::info('Notify Availability Update Response: ' . json_encode($data));

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Notify Availability Update Error: ' . $e->getMessage());

            // Handle the error, return a custom response, or rethrow the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
        


dd('gg');
        $reservations = json_decode($response->getBody(), true);

        return view('supplier.reservations', ['reservations' => $reservations]);
    }

    public function getBookings()
    {
        // Create a Guzzle client
        $client = new Client();

        // Make API request to retrieve bookings
        $response = $client->get('https://api.getyourguide.com/supplier/v1/bookings', [
            'auth' => ['web@eureka-creation.ma', '0524458999']
        ]);

        $bookings = json_decode($response->getBody(), true);

        return view('supplier.bookings', ['bookings' => $bookings]);
    }


}
