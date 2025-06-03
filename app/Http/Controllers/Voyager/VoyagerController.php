<?php

namespace App\Http\Controllers\Voyager;

use TCG\Voyager\Http\Controllers\VoyagerController as BaseVoyagerController;
use TCG\Voyager\Facades\Voyager;
use Illuminate\Support\Facades\DB;

use App\Reservation;


class VoyagerController extends BaseVoyagerController
{
    public function index() {
    	$reservations = Reservation::orderBy('created_at','DESC')->take(5)->get();
    	$today_t = Reservation::select("typeresa",DB::raw("COUNT(*) as count"))
    		->where('date_resa',date('Y-m-d'))
		    ->groupBy(DB::raw("typeresa"))
		    ->get();

		$today = [0,0,0];
		foreach ($today_t as $key => $value) {
			switch ($value->typeresa) {
				case 'depart':
					$today[0] = $value->count;
					break;

				case 'arrive':
					$today[1] = $value->count;
					break;

				case 'excursioncircuit':
					$today[2] = $value->count;
					break;
				
			}
			//$today[] = ['type'=>$value->typeresa,"count"=>$value->count];
		}

        return Voyager::view('voyager::index',compact(['reservations','today']));
    }
}
