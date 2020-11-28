<?php

namespace App;

use App\Scopes\SellerScope;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new SellerScope);
    }
    protected $table = 'users';
    
    public function products(){
        return $this->belongsTo('App\Product','id');
    }
}
