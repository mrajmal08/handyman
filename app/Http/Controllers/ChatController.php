<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\SendPushNotification;

use App\Chat;

class ChatController extends Controller
{
    public function save(Request $request)
    {
    	\Log::info($request->all());
    	
        $this->validate($request, [
                "user_id" => "required|integer",
                "provider_id" => "required|integer",
                "request_id" => "required|integer",
                "type" => "required|in:up,pu",
                "message" => "required",
            ]);
        if($request->type == "up"){
           (new SendPushNotification)->ProviderNotify($request->provider_id,$request->message,"chat"); 
        }
        if($request->type == "pu"){
         (new SendPushNotification)->UserNotify($request->provider_id,$request->message,"chat");
        }

        return Chat::create($request->all());
    }
}
