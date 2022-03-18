<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
    	'name','amount','validity','description','is_active','is_deleted'
    ];

    protected $hidden = [
    	'created_at','updated_at','is_deleted'
    ];

    
}
