<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
    	'provider_id','subscription_plan_id','start_date','end_date','is_active'
    ];

    protected $hidden = [
    	'created_at','updated_at'
    ];

    public function provider(){
    	return $this->belongsTo('App\Provider');
    }

    public function subscription_plan(){
    	return $this->hasMany('App\SubscriptionPlan');
    }
}
