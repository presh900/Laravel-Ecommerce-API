<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    

    const UNAVAILABLE_PRODUCT = 'false';
    const AVAILABLE_PRODUCT = 'true';
    // 

    public function categories(){
        return $this->belongsTo('App\Category','id');
    }
}
