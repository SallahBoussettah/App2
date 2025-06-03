<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Reclamation extends Model
{

	public function reservations(){
        return $this->hasOne('App\Reservation','id','reservation_id')->first();
    }    
}
