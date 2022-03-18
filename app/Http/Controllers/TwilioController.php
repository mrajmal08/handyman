<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use Twilio\Rest\Client;
use Auth;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;
use Twilio\TwiML\VoiceResponse;

use Twilio\Jwt\Grants\VoiceGrant;
use App\Http\Controllers\SendPushNotification;
use App\User;
use Setting;

class TwilioController extends Controller
{
	protected $sid;
	protected $token;
	protected $key;
	protected $secret;
	protected $phone_no;
	protected $app_sid;

	public function __construct()
	{
	   $this->sid = Setting::get('twilio_sid');
	   $this->token = Setting::get('twilio_token');
	   $this->key = Setting::get('twilio_key');
	   $this->secret = Setting::get('twilio_secret');
	   $this->phone_no = Setting::get('twilio_phone_no');
	   $this->app_sid = Setting::get('twilio_app_sid');
	}

	public function sendSms($data)
	{
		$twilio = new Client($this->sid, $this->token);

		$message = $twilio->messages
		  ->create($data['mobile'], // to
		           array(
		               "body" =>$data['message'],
		               "from" => $this->phone_no
		           )
		  );




	}

	public function index(Request $request)
	{
	   $rooms = [];
	   try {
	       $client = new Client($this->sid, $this->token);
	       $allRooms = $client->video->rooms->read([]);

	        $rooms = array_map(function($room) {
	           return $room->uniqueName;
	        }, $allRooms);

	   } catch (Exception $e) {
	       echo "Error: " . $e->getMessage();
	   }

	   if($request->ajax()){
	   	  return response()->json(['rooms' => $rooms]);

	   }else{

	   	 return view('video', ['rooms' => $rooms]);

	   }

	   
	}

	public function createRoom(Request $request)
	{
	   $client = new Client($this->sid, $this->token);

	   $exists = $client->video->rooms->read([ 'uniqueName' => $request->roomName]);

	   if (empty($exists)) {
	       $client->video->rooms->create([
	           'uniqueName' => $request->roomName,
	           'type' => 'group',
	           'recordParticipantsOnConnect' => false
	       ]);

	       \Log::debug("created new room: ".$request->roomName);
	   }

	   if($request->ajax()){
	   	  return response()->json(['roomName' => $request->roomName]);

	   }else{

	   	 return redirect()->action('VideoRoomsController@joinRoom', [
	       'roomName' => $request->roomName
	     ]);

	   }

	   
	}

	public function joinRoom(Request $request,$roomName)
	{
	   // A unique identifier for this user
	   $identity = "user ".Auth::user()->first_name;



	   \Log::debug("joined with identity: $identity");
	   $token = new AccessToken($this->sid, $this->key, $this->secret, 3600, $identity);

	   $videoGrant = new VideoGrant();
	   $videoGrant->setRoom($roomName);

	   $token->addGrant($videoGrant);

	   if($request->ajax()){

	   	  return response()->json(['accessToken' => $token->toJWT(), 'roomName' => $roomName]);

	   }else{
	   	 return view('room', [ 'accessToken' => $token->toJWT(), 'roomName' => $roomName ]);
	   }

	  
	}

	public function accesstoken(Request $request)
	{
	   // A unique identifier for this user
	   $identity = "user_".Auth::user()->first_name;

	   $user_name = Auth::user()->first_name;

	   $roomName = $request->room_id;

       \Log::debug("joined with identity: $identity");
	   
	   $token = new AccessToken($this->sid, $this->key, $this->secret, 3600, $identity);

	   $videoGrant = new VideoGrant();
	   $videoGrant->setRoom($roomName);

	   $token->addGrant($videoGrant);

	   $message = "video_call";

	   (new SendPushNotification)->sendPushToProviderVideo($request->id,$message,$user_name,$roomName); 

	  

	   if($request->ajax()){

	   	  return response()->json(['accessToken' => $token->toJWT()]);

	   }else{
	   	 return view('room', [ 'accessToken' => $token->toJWT()]);
	   }

	  
	}

	public function voiceaccesstoken(Request $request)
	{
	   // A unique identifier for this user
	   $identity = "user_".mt_rand(1111,9999);

	  // $user_name = Auth::user()->first_name;

	   $outgoingApplicationSid=$this->app_sid;

       \Log::debug("joined with identity: $identity");
	   $token = new AccessToken($this->sid, $this->key, $this->secret, 3600, $identity);

	    $voiceGrant = new VoiceGrant();
		$voiceGrant->setOutgoingApplicationSid($outgoingApplicationSid);

		// Optional: add to allow incoming calls
		$voiceGrant->setIncomingAllow(true);

		// Add grant to token
		$token->addGrant($voiceGrant);

        return response()->json(['accessToken' => $token->toJWT()]);


 		  
	}

	 public function dial_number(Request $request)
    {
	       $twilio_number = $this->phone_no;

			
			$to_number = $request->phone;

			$response = new VoiceResponse();
			$dial = $response->dial('', ['callerId' => $twilio_number]);
			$dial->number($to_number);

			return $response;
	}

}
