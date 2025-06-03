<?php
namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Redirect;
use GuzzleHttp\Client;
use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataDeleted;
use TCG\Voyager\Events\BreadDataRestored;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Events\BreadImagesDeleted;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as BaseVoyagerBaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Chauffeur;
use App\Reservation;
use App\Commission;
use App\Destination;
use App\Tour;
use App\Agence;
use App\Mail\BookingConfirmationMail;

class ReservationController extends BaseVoyagerBaseController
{
    use BreadRelationshipParser;

    public function index(Request $request)
    {


        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $getter = $dataType->server_side ? 'paginate' : 'get';
        

        $search = (object) ['value' => $request->get('s'), 'key' => $request->get('key'), 'filter' => $request->get('filter')];
      
        $searchable = $dataType->server_side ? array_keys(SchemaManager::describeTable(app($dataType->model_name)->getTable())->toArray()) : '';
        $orderBy = $request->get('order_by', $dataType->order_column);
        $sortOrder = $request->get('sort_order', null);
        $mynextarrgument = '';
        if($orderBy == 'hour_start'){
            $mynextarrgument = 'date_resa';
         
        }
          
        $usesSoftDeletes = false;
        $showSoftDeleted = false;
        $orderColumn = [];
        if ($orderBy) {
            $index = $dataType->browseRows->where('field', $orderBy)->keys()->first() + 1;
            
            $orderColumn = [[$index, 'desc']];
            
            if (!$sortOrder && isset($dataType->order_direction)) {
                $sortOrder = $dataType->order_direction;
                $orderColumn = [[$index, $dataType->order_direction]];
             
            } else {
                $orderColumn = [[$index, 'desc']];
                   
            }
        }

        // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);

            if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope'.ucfirst($dataType->scope))) {
                $query = $model->{$dataType->scope}();
            } else {
                $query = $model::select('*');
                $query2 = $model::select('devises.symbol');
            }

            // Use withTrashed() if model uses SoftDeletes and if toggle is selected
            if ($model && in_array(SoftDeletes::class, class_uses($model)) && app('VoyagerAuth')->user()->can('delete', app($dataType->model_name))) {
                $usesSoftDeletes = true;

                if ($request->get('showSoftDeleted')) {
                    $showSoftDeleted = true;
                    $query = $query->onlyTrashed();
                }
            }

            // If a column has a relationship associated with it, we do not want to show that field
            $this->removeRelationshipField($dataType, 'browse');

            if ($search->value != '' && $search->key && $search->filter) {
                $search_filter = ($search->filter == 'equals') ? '=' : 'LIKE';
                $search_value = ($search->filter == 'equals') ? $search->value : '%'.$search->value.'%';
                $query->where($search->key, $search_filter, $search_value);
                $query2->where($search->key, $search_filter, $search_value);
            }

            // Filtration start from here
            $daterange = $request->get('date','');
            $type      = $request->get('type','');
            $chauffeur = $request->get('chauffeur','');
            $from = $request->get('from','');
            $tour = $request->get('tour','');
            $statut = $request->get('statut','');
            $agency = $request->get('agency','');
            $paiement = $request->get('paiement','');
            $ref = $request->get('ref','');

            
            $start = NULL;
            $end = NULL;
            if($daterange != "" && $ref == "") {
                $range = explode('|', $daterange);
                $start = trim($range[0]);
                $end = trim($range[1]);
            }
            
            // CHECK TEST
            if($type != '') {
                $query->where('typeresa',$type);
                
                $query2->where('typeresa',$type);
            }
            
            // CHECK TEST
            if($ref != '') {
                $query->where('ref_reservation',$ref);
                
                $query2->where('ref_reservation',$ref);
            }

            if($start != NULL && $end != NULL) {
                $query->where('date_resa','>=',$start);
                $query->where('date_resa','<=',$end);
                
                $query2->where('date_resa','>=',$start);
                $query2->where('date_resa','<=',$end);
            }
            
            if($chauffeur != '') {
                $query->where('chauffeur_id',$chauffeur);
                
                $query2->where('chauffeur_id',$chauffeur);
            }

            if($from != '') {
                $query->where('from_id',$from);
                
                $query2->where('from_id',$from);
            }
            if($tour != '') {
                $query->where('tour_id',$tour);
                
                $query2->where('tour_id',$tour);
            }
            
            if($agency != '') {
                $query->where('agence_id',$agency);
                
                $query2->where('agence_id',$agency);
            }
            
            
            if($statut != '') {
                $query->where('status',$statut);
                $query2->where('status',$statut);
            }else{
                $query2->where('status','confirmed');
            }
            
            if($paiement != '') {
                $query->where('paiement',$paiement);
                $query2->where('paiement',$paiement);
            }else{
                $query2->where('paiement','confirmed');
            }
            
            

            $totals = $query2->groupBy('devises.symbol')   
                           ->selectRaw('devises.symbol , sum(prixtotal) as total')
                           ->join('devises', 'reservations.devise_id', '=', 'devises.id')
                           ->get();
           
            if ($orderBy && in_array($orderBy, $dataType->fields())) {
                $querySortOrder = (!empty($sortOrder)) ? $sortOrder : 'desc';
                if($mynextarrgument != ""){
                    
                    $dataTypeContent = call_user_func([
                      $query->orderBy($mynextarrgument, $querySortOrder)
                            ->orderBy($orderBy, $querySortOrder),
                     $getter,
                     
                 ]);
                // dd('hello');
                }else{ 
                    $dataTypeContent = call_user_func([
                     $query->orderBy($orderBy, $querySortOrder),
                     $getter,
                    ]);
                    
                    //dd($dataTypeContent);
                }
               
               // dd($dataTypeContent , $orderBy);
            } elseif ($model->timestamps) {
                $dataTypeContent = call_user_func([$query->latest($model::CREATED_AT), $getter]);
            } else {
                $dataTypeContent = call_user_func([$query->orderBy($model->getKeyName(), 'DESC'), $getter]);
            }

            // Replace relationships' keys for labels and create READ links if a slug is provided.
            $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType);
        } else {
            // If Model doesn't exist, get data from table name
            $dataTypeContent = call_user_func([DB::table($dataType->name), $getter]);
            $model = false;
        }

        // Check if BREAD is Translatable
        if (($isModelTranslatable = is_bread_translatable($model))) {
            $dataTypeContent->load('translations');
        }

        // Check if server side pagination is enabled
        $isServerSide = isset($dataType->server_side) && $dataType->server_side;

        // Check if a default search key is set
        $defaultSearchKey = $dataType->default_search_key ?? null;

        $view = 'voyager::bread.browse';

        if (view()->exists("voyager::$slug.browse")) {
            $view = "voyager::$slug.browse";
        }
        
        $chauffeurs = Chauffeur::orderBy('nom')->get();
        $froms = Destination::all();
        $tours = Tour::all();
        $agences = Agence::all();
        return Voyager::view($view, compact(
            'dataType',
            'dataTypeContent',
            'isModelTranslatable',
            'search',
            'orderBy',
            'orderColumn',
            'sortOrder',
            'searchable',
            'isServerSide',
            'defaultSearchKey',
            'usesSoftDeletes',
            'showSoftDeleted',
            'chauffeurs',
            'froms',
            'tours',
            'totals',
            'agences'
        ));
    }

    public function store(Request $request){

          $total_prs = $request->input('pax_adulte') + $request->input('pax_enfant3') + $request->input('pax_enfant12');
          $reservation = new Reservation();
          $reservation->client_nom =  $request->input('client_nom');
          $reservation->client_email =  $request->input('client_email');
          $reservation->client_pays =  $request->input('client_pays');
          $reservation->client_telephone =  $request->input('client_telephone');
          //$reservation->client_commentaire =  $request->input('client_commentaire');
          $reservation->admin_commentaire =  $request->input('admin_commentaire');
          $reservation->pax_adulte =  $request->input('pax_adulte');
          $reservation->pax_enfant12 =  $request->input('pax_enfant12');
          $reservation->pax_enfant3 =  $request->input('pax_enfant3');
          $reservation->pax_total =  $total_prs;
          $reservation->hotel_nom =  $request->input('hotel_nom');
          $reservation->hotel_adresse =  $request->input('hotel_adresse');
          $reservation->hotel_tel =  $request->input('hotel_tel');
         // $reservation->vol_heur =  $request->input('vol_heur');
         // $reservation->vol_num =  $request->input('vol_num');
         // $reservation->vol_date =  $request->input('vol_date');
          $reservation->ref_reservation =  $request->input('ref_reservation');
          $reservation->date_resa =  $request->input('date_resa');
          $reservation->prixtotal =  $request->input('prixtotal');
          $reservation->devise_id =  $request->input('reservation_belongsto_devise_relationship');
          $reservation->paiement =  $request->input('paiement');
          $reservation->typeresa =  $request->input('typeresa');
          $reservation->status =  $request->input('status');
          $reservation->commission =  $request->input('commission');
          $reservation->hour_start =  $request->input('hour_start');
         // $reservation->langue =  $request->input('langue');
          $reservation->vol_cmt =  $request->input('vol_cmt');
         // dd($request->input('vol_cmt'));


        $reservation->prix_avence =  $request->input('prix_avence');
        $reservation->paiement =  $request->input('paiement');
        $reservation->bank_avence =  $request->input('bank_avence');
        $reservation->prix_rest =  $request->input('prix_rest');
        $reservation->paiement_rest =  $request->input('paiement_rest');
        $reservation->bank_rest =  $request->input('bank_rest');
        $reservation->flight_status =  $request->input('flight_status');
        $reservation->payment_statut =  $request->input('payment_statut');
        $reservation->reclamation =  $request->input('reclamation');
        $reservation->rembo =  $request->input('rembo');


          

          $reservation->agence_id =  $request->input('reservation_belongsto_agence_relationship');
          $reservation->commercial_id =  $request->input('reservation_belongsto_chauffeur_relationship_1');
          $reservation->chauffeur_id =  !empty($request->input('reservation_belongsto_chauffeur_relationship')) ? $request->input('reservation_belongsto_chauffeur_relationship') : 13;
          $reservation->vehicule_id =  $request->input('reservation_belongsto_vehicule_relationship');
          $reservation->from_id =  $request->input('reservation_belongsto_destination_relationship');
          $reservation->to_id =  $request->input('reservation_belongsto_destination_relationship_1');
          $reservation->tour_id =  $request->input('reservation_belongsto_tour_relationship');
          $reservation->ville_id =  $request->input('reservation_belongsto_ville_relationship');
          
          
          if($reservation->save()){
              \LogActivity::addToLog('Ajouter Reservation');
              if(!empty($request->input('reservation_belongsto_chauffeur_relationship_1'))) {
               
                $commission = new Commission();
                $commission->commercial_id = $reservation->commercial_id ; 
                $commission->reservation_id =$reservation->id  ; 
                $commission->commission = $reservation->prixtotal * setting('admin.comi_pourcentage') /100 ; 
                $commission->devise_id = $reservation->devise_id ; 
                 
                if($commission->save()){
                    //dd('im here');
                    return redirect('/reservations/create');
                }
              }
              //dd('im here 2');
              return redirect('/reservations/create');
          }
          
          
    }
    public function update(Request $request , $id){
        //dd($request->all());
        $total_pax = $request->input('pax_adulte') + $request->input('pax_enfant3') + $request->input('pax_enfant12'); 
        $request->merge(['pax_total' => $total_pax]);

        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Compatibility with Model binding.
        $id = $id instanceof \Illuminate\Database\Eloquent\Model ? $id->{$id->getKeyName()} : $id;

        $model = app($dataType->model_name);
        if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope'.ucfirst($dataType->scope))) {
            $model = $model->{$dataType->scope}();
        }
        if ($model && in_array(SoftDeletes::class, class_uses($model))) {
            $data = $model->withTrashed()->findOrFail($id);
        } else {
            $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);
        }

        // Check permission
        $this->authorize('edit', $data);

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->editRows, $dataType->name, $id)->validate();
        $this->insertUpdateData($request, $slug, $dataType->editRows, $data);
        
        \LogActivity::addToLog('Modifier Reservation');
        
        event(new BreadDataUpdated($dataType, $data));

        if (auth()->user()->can('browse', $model)) {
            $redirect = redirect()->route("voyager.{$dataType->slug}.index");
        } else {
            $redirect = redirect()->back();
        }

        return $redirect->with([
            'message'    => __('voyager::generic.successfully_updated')." {$dataType->getTranslatedAttribute('display_name_singular')}",
            'alert-type' => 'success',
        ]);
    }
    
    
    //***************************************
    //                _____
    //               |  __ \
    //               | |  | |
    //               | |  | |
    //               | |__| |
    //               |_____/
    //
    //         Delete an item BREA(D)
    //
    //****************************************

    public function destroy(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Init array of IDs
        $ids = [];
        if (empty($id)) {
            // Bulk delete, get IDs from POST
            $ids = explode(',', $request->ids);
        } else {
            // Single item delete, get ID from URL
            $ids[] = $id;
        }
        foreach ($ids as $id) {
            $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);

            // Check permission
            $this->authorize('delete', $data);

            $model = app($dataType->model_name);
            if (!($model && in_array(SoftDeletes::class, class_uses_recursive($model)))) {
                $this->cleanup($dataType, $data);
            }
        }

        $displayName = count($ids) > 1 ? $dataType->getTranslatedAttribute('display_name_plural') : $dataType->getTranslatedAttribute('display_name_singular');

        $res = $data->destroy($ids);
        \LogActivity::addToLog('Supprimer reservation');
        $data = $res
            ? [
                'message'    => __('voyager::generic.successfully_deleted')." {$displayName}",
                'alert-type' => 'success',
            ]
            : [
                'message'    => __('voyager::generic.error_deleting')." {$displayName}",
                'alert-type' => 'error',
            ];

        if ($res) {
            event(new BreadDataDeleted($dataType, $data));
        }
        
        
        return redirect()->back()->with($data);
    }
    
    public function index_calendar(Request $request) {
        // GET THE SLUG,
        $slug = 'reservations';

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $view = "voyager::reservations.browse-calendar";
        $usesSoftDeletes = false;
        $showSoftDeleted = false;
        $isModelTranslatable = false;

        return Voyager::view($view,compact(
                'dataType',
                'isModelTranslatable',
                'usesSoftDeletes',
                'showSoftDeleted'
            )
        );
    }

    public function getResa(Request $request) {
        // GET THE SLUG,
        $slug = 'reservations';

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $model = app($dataType->model_name);

        $dataTypeContent = $model::select('id','date_resa AS start',DB::raw('CONCAT(client_nom) AS title'),'status')
            ->whereMonth('date_resa',$request->month)
            ->whereYear('date_resa',$request->year);

        if(isset($request->status)){
            if(count($request->status) > 0)
                $dataTypeContent->whereIn('status',$request->status);
        }

        $dataTypeContent = $dataTypeContent->get();
        return json_encode($dataTypeContent);
    }

    public function getSearch(Request $request) {
        // GET THE SLUG,
        $slug = 'reservations';

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $model = app($dataType->model_name);

        $dataTypeContent = $model::whereRaw('1 = 1');

        $test = false;
        if($request->id != ""){
            $test = true;
            $dataTypeContent->where('ref','like','%'.$request->id.'%');
        }

        if($request->first_name != ""){
            $test = true;
            $dataTypeContent->where('client_nom','like','%'.$request->first_name.'%');
        }

        if($request->last_name != ""){
            $test = true;
            $dataTypeContent->where('client_prenom','like','%'.$request->last_name.'%');
        }

        if($request->email != "") {
            $test = true;
            $dataTypeContent->where('email','like','%'.$request->email.'%');
        }

        if(!$test)
            return json_encode(array());

        $dataTypeContent = $dataTypeContent->get();
        return json_encode($dataTypeContent);

    }
    
    // ADDIONAL
    public function imprimer_pancarte(Request $request,$id) {
        $reservation = Reservation::where('id',$id)->first();
        if(empty($reservation))
            abort(404);
            
        return view('voyager::reservations.imprimer-pancarte',compact('reservation'));
    }
    
    public function imprimer_liste(Request $request,$with,$date,$type,$chauffeur , $ids_res) {
        
        //$reservations = Reservation::whereIn('id' , json_decode($ids_res))->get();
        //dd($reservations);
        
        
        //dd($with , $date , $type , $chauffeur , $ids_res);
        $reservations = Reservation::whereRaw("1 = 1");
        $chauffeur_t  = NULL;
        
        //dd(json_decode($ids_res))
        // Filtration start from here

        $daterange = $date;

        $start = NULL;
        $end = NULL;
        
        if($daterange != "null") {
            $range = explode('|', $daterange);
            $start = trim($range[0]);
            $end = trim($range[1]);
        }
        
        // CHECK TEST
        if($type != 'null') {
            $reservations->where('typeresa',$type);
        }

         if($start != NULL && $end != NULL) {
            $reservations->where('date_resa','>=',$start);
            $reservations->where('date_resa','<=',$end);
        }
        
        
        if($chauffeur != 'null') {
            $reservations->where('chauffeur_id',$chauffeur);
            $chauffeur_t = Chauffeur::where('id',$chauffeur)->first();
        }
      if($ids_res != 'null') {
            $reservations->whereIn('id' , json_decode($ids_res));
            //dd('im here');
        }
        $reservations = $reservations->get();
        //dd($reservations);
        //return dd($reservations);
        
        return view('voyager::reservations.imprimer-liste',compact('reservations','with','date','chauffeur_t'));
    }
    
    //Edit Commercial 
    public function editCommercial(Request $request){
        $reservation = Reservation::where('id',$request->mybookingid)->first();
         //dd($reservation);
        if($request->commission > 0){
            $mycomm = Commission::where('reservation_id' , $reservation->id)->first();
            if($mycomm){
             $mycomm->commercial_id = $request->commercial ;
            
             $mycomm->save();
            }else{
             $commisssion = new Commission();
             $commission->commercial_id = $request->commercial; 
             $commission->reservation_id = $reservation->id;
             $commission->commission = $reservation->prixtotal * setting('admin.comi_pourcentage') /100 ; 
             $commission->devise_id = $reservation->devise_id ; 
            }
        }
        else{
            $mycomm = Commission::where('reservation_id' , $reservation->id);
            if($mycomm){
                $mycomm->delete();
            }
        }
        
        $reservation->commercial_id = $request->commercial;
        $reservation->commission = $request->commission; 
        $reservation->save();
        \LogActivity::addToLog('Modifier Commerciale');
        return Redirect::to('reservations');
    }
    
    public function editChauffeur(Request $request) {
        $reservation = Reservation::where('id',$request->mybookingid)->first();
        $reservation->chauffeur_id = $request->chauffeur_id; 
        $reservation->save();
        \LogActivity::addToLog('Modifier chauffeur');
        
        return response()->json(['success' => true]);
    }

    public function editStatus(Request $request) {
        $reservation = Reservation::where('id',$request->id)->first();
        $reservation->status = $request->status; 
        $reservation->save();
        \LogActivity::addToLog('Modifier Statut');
        return response()->json(['success' => true]);
    }
    public function sendBookingConfirmation($id)
    {
        try {
            
            $reservation = Reservation::findOrFail($id);
            
                $customerName = $reservation->client_nom;
                $pickupTime = $reservation->hour_start;
                $pickupLocation = $reservation->hotel_nom;
                $maps = $reservation->maps ;
                $pick_up = $reservation->pick_up;
                $contactNumber = '+212 666 887333'; // Set dynamically if needed
        
                // Send the email using the gathered data
    
                Mail::send('emails.booking_confirmation', [
                    'customerName' => $customerName,
                    'pickupTime' => $pickupTime,
                    'pickupLocation' => $pickupLocation,
                    'pick_up'=>$pick_up,
                    'maps'=>$maps,
                    'contactNumber' => $contactNumber,
                ], function($message) use ($reservation) {
                    $message->to($reservation->client_email);
                    $message->subject('🚨🚨🚨🚨 CONFIRMATION PICK UP EXCURSIONS MARRAKECH');
                });
            
            $reservation->active_auto_notify = true;
            $reservation->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Booking confirmation email sent successfully.'
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function sendmultiemail(Request $request){
    
        $ids = $request->get('ids', []); 
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No reservation IDs provided.'
                ], 400);
            }
        
            $failedEmails = [];
            $successfulEmails = [];
        
            foreach (json_decode($ids) as $id) {
                try {
                    $reservation = Reservation::findOrFail($id);
        
                    $customerName = $reservation->client_nom;
                    $pickupTime = $reservation->hour_start;
                    $pickupLocation = $reservation->hotel_nom;
                    $maps = $reservation->maps;
                    $pick_up = $reservation->pick_up;
                    $contactNumber = '+212 666 887333'; // Set dynamically if needed
        
                    // Send the email using the gathered data
                    Mail::send('emails.booking_confirmation', [
                        'customerName' => $customerName,
                        'pickupTime' => $pickupTime,
                        'pickupLocation' => $pickupLocation,
                        'pick_up' => $pick_up,
                        'maps' => $maps,
                        'contactNumber' => $contactNumber,
                    ], function ($message) use ($reservation) {
                        $message->to($reservation->client_email);
                        $message->subject('🚨🚨🚨🚨 CONFIRMATION PICK UP EXCURSIONS MARRAKECH');
                    });
        
                    $reservation->active_auto_notify = true;
                    $reservation->save();
        
                    $successfulEmails[] = $reservation->client_email;
        
                } catch (\Exception $e) {
                    $failedEmails[] = [
                        'id' => $id,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            return response()->json([
                'success' => count($failedEmails) === 0,
                'message' => count($failedEmails) === 0
                    ? 'Tous les e-mails ont été envoyés avec succès.'
                    : "Certains courriels n'ont pas été envoyés.",
                'successfulEmails' => $successfulEmails,
                'failedEmails' => $failedEmails,
            ]);
    }



    public function isWhatsAppNumber($phone)
    {
        $client = new \GuzzleHttp\Client();
    
        $url = "https://graph.facebook.com/v19.0/" . env('META_PHONE_ID') . "/contacts";
    
        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('META_WA_TOKEN'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'blocking' => 'wait',
                    'contacts' => [$phone],
                    'force_check' => true,
                ]
            ]);
    
            $data = json_decode($response->getBody(), true);
            return $data['contacts'][0]['status'] === 'valid';
    
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    public function sendMetaWhatsAppReminder($reservation)
    {
        $client = new Client();
    
        $url = 'https://graph.facebook.com/v19.0/' . env('META_PHONE_ID') . '/messages';

        try {
            $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('META_WA_TOKEN'),
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'to' => '212603145383', // $reservation->tel_customer, // Ex: 212603145383
                    'type' => 'template',
                    'template' => [
                        'name' => 'event_details_reminder_1',
                        'language' => ['code' => 'en_US'], // or 'fr_FR'
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => [
                                    ['type' => 'text', 'text' => $reservation->client_nom],
                                    ['type' => 'text', 'text' => $reservation->hour_start],
                                    ['type' => 'text', 'text' => $reservation->hotel_nom],
                                    ['type' => 'text', 'text' => $reservation->pick_up],
                                    ['type' => 'text', 'text' => $reservation->pick_up],
                                    ['type' => 'text', 'text' => $reservation->maps],
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    
            return response()->json(['success' => true, 'msg' => 'WhatsApp reminder sent!']);
    
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()], 500);
        }
    }


    public function sendWhatsApp(Request $request)
    {
        $reservation = Reservation::findOrFail($request->id);
     
        if (! $this->isWhatsAppNumber($reservation->client_telephone)) {
            return response()->json([
                'success' => false,
                'msg' => 'This number does not have WhatsApp!'.$reservation->client_telephone
            ], 400);
        }

        return $this->sendMetaWhatsAppReminder($reservation);
    }


}
