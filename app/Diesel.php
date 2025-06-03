<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Diesel extends Model
{
    
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    
    public function devise_symbol(){
        $symbol =  $this->hasOne('App\Devise','id','devise_id')->first();

        if(empty($symbol))
            return NULL;

        return $symbol->symbol;
        
    }
}
