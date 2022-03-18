<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\ProviderDevice;
use Exception;

use Edujugon\PushNotification\PushNotification;

class SendPushNotification extends Controller
{
    /**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function RideAccepted($request){

        return $this->sendPushToUser($request->user_id, trans('api.push.request_accepted'));
    }

	/**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function RideScheduled($request){

    	$this->sendPushToUser($request->user_id, trans('api.push.ride_scheduled'));
    }

    /**
     * Driver Arrived at your location.
     *
     * @return void
     */
    public function Arrived($request){

        return $this->sendPushToUser($request->user_id, trans('api.push.arrived'));
    }

     /**
     * Notify driver message push.
     *
     * @return void
     */
    public function UserNotify($user,$message,$type){

        return $this->sendPushToUser($user,$message,$type);
    }

   /**
     * Notify user message push.
     *
     * @return void
     */
    public function ProviderNotify($provider,$message,$type){

        return $this->sendPushToProvider($provider,$message,$type);

    }

     /**
     * Driver Arrived at your location.
     *
     * @return void
     */
    public function user_schedule($user){

        return $this->sendPushToUser($user, trans('api.push.schedule_start'));
    }

    /**
     * New Incoming request
     *
     * @return void
     */
    public function provider_schedule($provider){

        return $this->sendPushToProvider($provider, trans('api.push.schedule_start'));

    }

    /**
     * New Incoming request
     *
     * @return void
     */
    public function IncomingRequest($provider){

        return $this->sendPushToProvider($provider, trans('api.push.incoming_request'));

    }

    /**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function UserCancellRide($request){

        return $this->sendPushToProvider($request->provider_id, trans('api.push.user_cancelled'));
    }

    /**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function ProviderCancellRide($request){

        return $this->sendPushToUser($request->user_id, trans('api.push.provider_cancelled'));
    }


     /**
     * Money added to user wallet.
     *
     * @return void
     */
    public function ProviderNotAvailable($user_id){

        return $this->sendPushToUser($user_id,trans('api.push.provider_not_available'));
    }
    

    /**
     * Driver Documents verfied.
     *
     * @return void
     */
    public function DocumentsVerfied($provider_id){

        return $this->sendPushToProvider($provider_id, trans('api.push.document_verfied'));
    }


    /**
     * Money added to user wallet.
     *
     * @return void
     */
    public function WalletMoney($user_id, $money){

        return $this->sendPushToUser($user_id, $money.' '.trans('api.push.added_money_to_wallet'));
    }

    /**
     * Money charged from user wallet.
     *
     * @return void
     */
    public function ChargedWalletMoney($user_id, $money){

        return $this->sendPushToUser($user_id, $money.' '.trans('api.push.charged_from_wallet'));
    }

    /**
     * Sending Push to a user Device.
     *
     * @return void
     */
    public function sendPushToUser($user_id, $push_message){

        try{

           

            $user = User::findOrFail($user_id);

            if($user->device_token != ""){


               \Log::info('sending push for user : '. $user->first_name);
                \Log::info($push_message);

                if($user->device_type == 'ios'){
                     if(env('IOS_USER_ENV')=='development'){
                        $crt_user_path=app_path().'/apns/user/HandymanUser.pem';
                        $crt_provider_path=app_path().'/apns/provider/HandyManProvider.pem';
                        $dry_run = true;
                    }
                    else{
                        $crt_user_path=app_path().'/apns/user/HandymanUser.pem';
                        $crt_provider_path=app_path().'/apns/provider/HandyManProvider.pem';
                        $dry_run = false;
                    }
                    
                   $push = new PushNotification('apn');

                    $push->setConfig([
                            'certificate' => $crt_user_path,
                            'passPhrase' => env('IOS_USER_PUSH_PASS', 'apple'),
                            'dry_run' => $dry_run
                        ]);

                   $send=  $push->setMessage([
                            'aps' => [
                                'alert' => [
                                    'body' => $push_message
                                ],
                                'sound' => 'default',
                                'badge' => 1

                            ],
                            'extraPayLoad' => [
                                'custom' => $push_message
                            ]
                        ])
                        ->setDevicesToken($user->device_token)->send();
                        \Log::info('sent');
                    
                    return $send;

                }elseif($user->device_type == 'android'){

                   $push = new PushNotification('fcm');
                   $send=  $push->setMessage(['message'=>$push_message])
                        ->setApiKey('AAAA2a_CYvc:APA91bHesVLkb7JJ4kXcE0DDrv3ocqAE3xSYHkjWke0ch5UdtaIGSMOGEq4t7dME0AH11N0iAkpemI5BeHRxF4YVmbflVEKdptecnCbeZZ1oKRVxAXT6uxQn8bNxobb9VrQ95K6c2W7H')
                        ->setDevicesToken($user->device_token)->send();
                    
                    return $send;
                       


                }
            }

        } catch(Exception $e){
            return $e;
        }

    }


    /**
     * Sending Push to a user Device.
     *
     * @return void
     */
    public function sendPushToProvider($provider_id, $push_message){


        try{          

            $provider = ProviderDevice::where('provider_id',$provider_id)->first();           


            if($provider->token != ""){


                if($provider->type == 'ios'){

                    if(env('IOS_USER_ENV')=='development'){
                        $crt_user_path=app_path().'/apns/user/HandymanUser.pem';
                        $crt_provider_path=app_path().'/apns/provider/HandyManProvider.pem';
                        $dry_run = true;
                    }
                    else{
                        $crt_user_path=app_path().'/apns/user/HandymanUser.pem';
                        $crt_provider_path=app_path().'/apns/provider/HandyManProvider.pem';
                        $dry_run = false;
                    }

                   $push = new PushNotification('apn');
                   $push->setConfig([
                            'certificate' => $crt_provider_path,
                            'passPhrase' => env('IOS_PROVIDER_PUSH_PASS', 'apple'),
                            'dry_run' => $dry_run
                        ]);
                   $send=  $push->setMessage([
                            'aps' => [
                                'alert' => [
                                    'body' => $push_message
                                ],
                                'sound' => 'default',
                                'badge' => 1

                            ],
                            'extraPayLoad' => [
                                'custom' => $push_message
                            ]
                        ])
                        ->setDevicesToken($provider->token)->send();
                
                    
                    return $send;

                }elseif($provider->type == 'android'){
                    
                   $push = new PushNotification('fcm');
                   $send=  $push->setMessage(['message'=>$push_message])
                        ->setApiKey('AAAA2a_CYvc:APA91bHesVLkb7JJ4kXcE0DDrv3ocqAE3xSYHkjWke0ch5UdtaIGSMOGEq4t7dME0AH11N0iAkpemI5BeHRxF4YVmbflVEKdptecnCbeZZ1oKRVxAXT6uxQn8bNxobb9VrQ95K6c2W7H')
                        ->setDevicesToken($provider->token)->send();
                    
                    return $send;
                        

                }
            }


        } catch(Exception $e){           
            return $e;
        }


    }

}
