<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    
    protected $perPage = 300;
    public function vehicule(){
    	return $this->hasOne('App\Vehicule','id','vehicule_id')->first();
    }

    public function chauffeur(){
 
        if($this->chauffeur_id == null){
            return '-------';
        }else {    
            //return $this->hasOne('App\Tour','id','tour_id')->first()->nom;
            return $this->hasOne('App\Chauffeur','id','chauffeur_id')->withTrashed()->first()->nom;
        }
    	
    }

    public function devise_symbol(){
        $symbol =  $this->hasOne('App\Devise','id','devise_id')->first();

        if(empty($symbol))
            return NULL;

        return $symbol->symbol;
    }

    public function agence(){
        return $this->hasOne('App\Agence','id','agence_id')->first();
    }
     public function nam_of_agence(){
        return $this->hasOne('App\Agence','id','agence_id')->select('nom')->first();
    }

    public function departure(){
        return  $this->hasOne('App\Destination','id','from_id')->first();
        
    }
    
    
    public function departureacc(){
        $departure =  $this->hasOne('App\Destination','id','from_id')->first();
        if(empty($departure))
            return NULL;

        return $departure->titre;
    }

    public function arrival(){
        return $this->hasOne('App\Destination','id','to_id')->first();
    }
    public function devi(){
        return $this->hasOne('App\Devise','id','devise_id')->first();
    }
    public function to_tour(){
        if ($this->typeresa == 'excursioncircuit') {
            if($this->tour_id == null){
                return '-------';
            }else {
                $tour = $this->hasOne('App\Tour','id','tour_id')->first();
                  if(is_null($tour)){
                     return '-------2';
                 }else {
                    
                    return $tour->title;
                 } 
            }
            
            
        }else {
            if($this->to_id != null){
                return $this->hasOne('App\Destination','id','to_id')->first()->titre;
            }else {
                return '-------';
            }
             
        }
       
    }
}
