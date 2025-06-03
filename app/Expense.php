<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Expense extends Model
{
 
 	public function devise_symbol(){
        $symbol =  $this->hasOne('App\Devise','id','devise_id')->first();

        if(empty($symbol))
            return NULL;

        return $symbol->symbol;
    }
    public function devise_value(){
        $valueof =  $this->hasOne('App\Devise','id','devise_id')->first();

        if(empty($valueof))
            return NULL;

        return $valueof->value;
    }
    

}
