<?php

namespace App\Http\Controllers\ProviderAuth;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Controller;

use Tymon\JWTAuth\Exceptions\JWTException;
use App\Notifications\ResetPasswordOTP;
use App\Http\Controllers\TwilioController;
use Illuminate\Support\Facades\Validator;

use Auth;
use Config;
use Setting;
use JWTAuth;
use Exception;
use Notification;
use Socialite;
use File; 

use App\Provider;
use App\ProviderDevice;
use App\Document;
use App\ProviderDocument;

class TokenController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function register(Request $request)
    {
        $this->validate($request, [
                'device_id' => 'required',
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:providers',
                'mobile' => 'required',
                'password' => 'required|min:6|confirmed',
            ]);

        try{

            $Provider = $request->all();
            $Provider['password'] = bcrypt($request->password);
            // $Provider['status'] = 'approved';

            $Provider = Provider::create($Provider);
		if(setting::get('demo_mode')==1){
		 \App\ProviderService::create([
                    'provider_id' => $Provider->id,
                    'service_type_id' => 1,
                    'status' => 'active'
                ]);

		}
            ProviderDevice::create([
                    'provider_id' => $Provider->id,
                    'udid' => $request->device_id,
                    'token' => $request->device_token,
                    'type' => $request->device_type,
                ]);

            return $Provider;


        } catch (QueryException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Something went wrong, Please try again later!'], 500);
            }
            return abort(500);
        }
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function authenticate(Request $request)
    {
        $this->validate($request, [
                'device_id' => 'required',
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

        Config::set('auth.providers.users.model', 'App\Provider');

        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'The email address or password you entered is incorrect.'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Something went wrong, Please try again later!'], 500);
        }

        $User = Provider::with('service', 'device')->find(Auth::user()->id);

        $User->access_token = $token;
        $User->currency = Setting::get('currency', '$');

        if($User->device) {
            if($User->device->token != $request->device_token) {
                $User->device->update([
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
            }
        } else {
            ProviderDevice::create([
                    'provider_id' => $User->id,
                    'udid' => $request->device_id,
                    'token' => $request->device_token,
                    'type' => $request->device_type,
                ]);
        }

        return response()->json($User);
    }



 /**
     * Forgot Password.
     *
     * @return \Illuminate\Http\Response
     */


    public function forgot_password(Request $request){

        $this->validate($request, [
                'email' => 'required|email|exists:providers,email',
            ]);

        try{  
            
            $provider = Provider::where('email' , $request->email)->first();

            $otp = mt_rand(100000, 999999);

            $provider->otp = $otp;
            $provider->save();

            Notification::send($provider, new ResetPasswordOTP($otp));

            return response()->json([
                'message' => 'OTP sent to your email!',
                'provider' => $provider
            ]);

        }catch(Exception $e){
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }


    /**
     * Reset Password.
     *
     * @return \Illuminate\Http\Response
     */

    public function reset_password(Request $request){

        $this->validate($request, [
                'password' => 'required|confirmed|min:6',
                'id' => 'required|numeric|exists:providers,id'
            ]);

        try{

            $Provider = Provider::findOrFail($request->id);
            $Provider->password = bcrypt($request->password);
            $Provider->save();

            if($request->ajax()) {
                return response()->json(['message' => 'Password Updated']);
            }

        }catch (Exception $e) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')]);
            }
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function logout(Request $request)
    {
        try {
            ProviderDevice::where('provider_id', $request->id)->update(['udid'=> '', 'token' => '']);
            return response()->json(['message' => trans('api.logout_success')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * Show the email availability.
     *
     * @return \Illuminate\Http\Response
     */

    public function verify(Request $request)
    {
        $this->validate($request, [
                'email' => 'required|email|max:255|unique:providers',
            ]);

        try{
            
            return response()->json(['message' => trans('api.email_available')]);

        } catch (Exception $e) {
             return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

     public function facebookViaAPI(Request $request) { 

        $validator = Validator::make(
            $request->all(),
            [
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'accessToken'=>'required',
                //'mobile' => 'required',
                'device_id' => 'required',
                'login_by' => 'required|in:manual,facebook,google'
            ]
        );
        
        if($validator->fails()) {
            return response()->json(['status'=>false,'message' => $validator->messages()->all()]);
        }
        $user = Socialite::driver('facebook')->stateless();
        $FacebookDrive = $user->userFromToken( $request->accessToken);
       
        try{
            $FacebookSql = Provider::where('social_unique_id',$FacebookDrive->id);
            if($FacebookDrive->email !=""){
                $FacebookSql->orWhere('email',$FacebookDrive->email);
            }
            $AuthUser = $FacebookSql->first();
            if($AuthUser){ 
                $AuthUser->social_unique_id=$FacebookDrive->id;
                $AuthUser->login_by="facebook";
                if($request->mobile != "") {
                    $AuthUser->mobile=$request->mobile;
                }
                $AuthUser->save();  
            }else{   
                $AuthUser["email"]=$FacebookDrive->email;
                $name = explode(' ', $FacebookDrive->name, 2);
                $AuthUser["first_name"]=$name[0];
                $AuthUser["last_name"]=isset($name[1]) ? $name[1] : '';
                $AuthUser["password"]=bcrypt($FacebookDrive->id);
                $AuthUser["social_unique_id"]=$FacebookDrive->id;
               // $AuthUser["avatar"]=$FacebookDrive->avatar;
                $fileContents = file_get_contents($FacebookDrive->getAvatar());
                        File::put(public_path() . '/storage/provider/profile/' . $FacebookDrive->getId() . ".jpg", $fileContents);

                        //To show picture 
                        $picture = 'provider/profile/' . $FacebookDrive->getId() . ".jpg";
                $AuthUser["avatar"]=$picture;        
                if($request->mobile != "") {
                    $AuthUser["mobile"]=$request->mobile;
                }
                $AuthUser["login_by"]="facebook";
                $AuthUser = Provider::create($AuthUser);

                   
            }    
            if($AuthUser){ 
                $userToken = JWTAuth::fromUser($AuthUser);
                $User = Provider::with('service', 'device')->find($AuthUser->id);
                if($User->device) {
                    ProviderDevice::where('id',$User->device->id)->update([
                        
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                    
                } else {
                    ProviderDevice::create([
                        'provider_id' => $User->id,
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                }
                return response()->json([
                            "status" => true,
                            "token_type" => "Bearer",
                            "access_token" => $userToken,
                            'currency' => Setting::get('currency', '$'),
                            'sos' => Setting::get('sos_number', '911')
                        ]);
            }else{
                return response()->json(['status'=>false,'message' => trans('api.invalid')]);
            }  
        } catch (Exception $e) {
            return response()->json(['status'=>false,'message' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function googleViaAPI(Request $request) { 

        $validator = Validator::make(
            $request->all(),
            [
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'accessToken'=>'required',
                //'mobile' => 'required',
                'device_id' => 'required',
                'login_by' => 'required|in:manual,facebook,google'
            ]
        );
        
        if($validator->fails()) {
            return response()->json(['status'=>false,'message' => $validator->messages()->all()]);
        }
        $user = Socialite::driver('google')->stateless();        

        $GoogleDrive = $user->userFromToken($request->accessToken);        
       
        try{
            $GoogleSql = Provider::where('social_unique_id',$GoogleDrive->id);
            if($GoogleDrive->email !=""){
                $GoogleSql->orWhere('email',$GoogleDrive->email);
            }
            $AuthUser = $GoogleSql->first();
            if($AuthUser){
                $AuthUser->social_unique_id=$GoogleDrive->id;
                if($request->mobile != "") {
                    $AuthUser->mobile=$request->mobile;
                }  
                $AuthUser->login_by="google";
                $AuthUser->save();
            }else{   
                $AuthUser["email"]=$GoogleDrive->email;
                $name = explode(' ', $GoogleDrive->name, 2);
                $AuthUser["first_name"]=$name[0];
                $AuthUser["last_name"]=isset($name[1]) ? $name[1] : '';
                $AuthUser["password"]=($GoogleDrive->id);
                $AuthUser["social_unique_id"]=$GoogleDrive->id;
                //$AuthUser["avatar"]=$GoogleDrive->avatar;
                $fileContents = file_get_contents($GoogleDrive->getAvatar());
                        File::put(public_path() . '/storage/provider/profile/' . $GoogleDrive->getId() . ".jpg", $fileContents);

                        //To show picture 
                        $picture = 'provider/profile/' . $GoogleDrive->getId() . ".jpg";
                $AuthUser["avatar"]=$picture;   
                if($request->mobile != "") {
                    $AuthUser["mobile"]=$request->mobile;
                }
                $AuthUser["login_by"]="google";
                $AuthUser = Provider::create($AuthUser);

    
            }    
            if($AuthUser){
                $userToken = JWTAuth::fromUser($AuthUser);
                $User = Provider::with('service', 'device')->find($AuthUser->id);
                if($User->device) {
                    ProviderDevice::where('id',$User->device->id)->update([
                        
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                    
                } else {
                    ProviderDevice::create([
                        'provider_id' => $User->id,
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                }
                return response()->json([
                            "status" => true,
                            "token_type" => "Bearer",
                            "access_token" => $userToken,
                            'currency' => Setting::get('currency', '$'),
                            'sos' => Setting::get('sos_number', '911')
                        ]);
            }else{
                return response()->json(['status'=>false,'message' => trans('api.invalid')]);
            }  
        } catch (Exception $e) {
            return response()->json(['status'=>false,'message' => trans('api.something_went_wrong')]);
        }
    }

     public function OTP(Request $request)
    
    {   
        try{

            if(Provider::where('mobile',$request->mobile)->first()){

                return response()->json([
                    'error' => "Mobile Number Already Exist",
                ], 422); 
            }else{

            $newotp = rand(1000,9999);
            $data['otp'] = $newotp;
            if($request->has('country_code')){
                $data['mobile'] = $request->country_code.$request->mobile;   
            }else{
              $data['mobile'] = $request->mobile;       
            }
            
            $data['message'] = 'Your Otp is '.$newotp;         
         
                
                (new TwilioController)->sendSms($data);
                return response()->json([
                    'message' => 'OTP Sent',
                    'otp' => $newotp
                ]);

           }
        }catch (Exception $e) {
            dd($e);
            return response()->json(['error' => trans('form.whoops')], 500);
        }
    }

      public function verifymobile(Request $request)
    {
        $check =Provider::wheremobile($request->username)->first(); 

        if($check)   
            return response()->json(['status' => true ]); 
        else
            return response()->json(['status' => false ]);  
    }

     public function verifyemail(Request $request)
    {
        $check =Provider::where('email',$request->email)->first();

        if($check)   
            return response()->json(['status' => true ]); 
        else
            return response()->json(['status' => false ]);  
    }



     public function appleViaAPI(Request $request) { 

        $validator = Validator::make(
            $request->all(),
            [
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'social_unique_id'=>'required',
                'mobile' => 'required',
                'device_id' => 'required',
                'login_by' => 'required|in:manual,facebook,google,apple'
            ]
        );
        
        if($validator->fails()) {
            return response()->json(['status'=>false,'message' => $validator->messages()->all()]);
        }
       
       
        try{
            $applesignSql = Provider::where('social_unique_id',$request->social_unique_id);
           
            $AuthUser = $applesignSql->first();
            if($AuthUser){ 
                $AuthUser->social_unique_id=$request->social_unique_id;
                $AuthUser->login_by="apple";
                if($request->mobile != "") {
                    $AuthUser->mobile=$request->mobile;
                } 
                $AuthUser->save();  
            }else{   
                $NewRefferalCode = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
                        $NewRefferalCode = "aaltonen".$NewRefferalCode;
                $AuthUser["email"]=$request->email;
                $name = $request->name;
                $AuthUser["first_name"]=$name;
                $AuthUser["last_name"]=$name;
                $AuthUser["password"]=bcrypt($request->social_unique_id);
                $AuthUser["referral_code"]=$NewRefferalCode;
                $AuthUser["social_unique_id"]=$request->social_unique_id;
              //  $AuthUser["avatar"]=$FacebookDrive->avatar;
                $AuthUser["mobile"]=$request->mobile?:'';
                $AuthUser["login_by"]="apple";
                $AuthUser = Provider::create($AuthUser);

                if(Setting::get('demo_mode', 0) == 1) {
                    // $AuthUser->update(['status' => 'approved']);
                    ProviderService::create([
                        'provider_id' => $AuthUser->id,
                        'service_type_id' => '1',
                        'status' => 'active',
                        'service_number' => '4pp03ets',
                        'service_model' => 'Audi R8',
                    ]);
                }
            }    
             if($AuthUser){ 
                $userToken = JWTAuth::fromUser($AuthUser);
                $User = Provider::with('service', 'device')->find($AuthUser->id);
                if($User->device) {
                    ProviderDevice::where('id',$User->device->id)->update([
                        
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                    
                } else {
                    ProviderDevice::create([
                        'provider_id' => $User->id,
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                }
                return response()->json([
                            "status" => true,
                            "token_type" => "Bearer",
                            "access_token" => $userToken,
                            'currency' => Setting::get('currency', '$'),
                            'measurement' => Setting::get('distance', 'Kms'),
                            'sos' => Setting::get('sos_number', '911')
                        ]);
            }else{
                return response()->json(['status'=>false,'message' => trans('api.invalid')]);
                  // $response=Helper::transresponse(trans('api.invalid'));
                // return response()->json(['status'=>false,'message' =>$response ]);

            }    
        } catch (Exception $e) {
            return response()->json(['status'=>false,'message' => trans('api.something_went_wrong')]);
             // $response=Helper::transresponse(trans('api.something_went_wrong'));
            // return response()->json(['status'=>false,'message' => $response]);

        }
    }

    
}
