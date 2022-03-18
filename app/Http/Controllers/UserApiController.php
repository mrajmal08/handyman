<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Log;
use Auth;
use Hash;
use Setting;
use Exception;
use Notification;
use Storage;
use Carbon\Carbon;
use App\Http\Controllers\SendPushNotification;
use App\Notifications\ResetPasswordOTP;
use App\Http\Controllers\ProviderResources\TripController;
use App\Http\Controllers\TwilioController;
use DB;
use App\User;
use App\Chat;
use App\ProviderService;
use App\UserRequests;
use App\Promocode;
use App\RequestFilter;
use App\ServiceType;
use App\Provider;
use App\Settings;
use App\UserRequestRating;
use App\Card;
use App\PromocodeUsage;
use App\Helpers\Helper;
use App\WalletPassbook;
use App\UserRequestPayment;

class UserApiController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function signup(Request $request)
    {
        $this->validate($request, [
                'social_unique_id' => ['required_if:login_by,facebook,google','unique:users'],
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'device_id' => 'required',
                'login_by' => 'required|in:manual,facebook,google',
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
                'mobile' => 'required',
                'password' => 'required|min:6',
            ]);

        try{
            
            $User = $request->all();

            $User['payment_mode'] = 'CASH';
            $User['password'] = bcrypt($request->password);
            $User = User::create($User);

            return $User;
        } catch (Exception $e) {
             return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }


             /**
     * Forgot Password.
     *
     * @return \Illuminate\Http\Response
     */


    public function forgot_password(Request $request){

        $this->validate($request, [
                'email' => 'required|email|exists:users,email',
            ]);

        try{  
            
            $user = User::where('email' , $request->email)->first();

            $otp = mt_rand(100000, 999999);

            $user->otp = $otp;
            $user->save();

            Notification::send($user, new ResetPasswordOTP($otp));

            return response()->json([
                'message' => 'OTP sent to your email!',
                'user' => $user
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
                'id' => 'required|numeric|exists:users,id'
            ]);

        try{

            $User = User::findOrFail($request->id);
            $User->password = bcrypt($request->password);
            $User->save();

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

    public function change_password(Request $request){

        $this->validate($request, [
                'password' => 'required|confirmed|min:6',
                'old_password' => 'required',
            ]);

        $User = Auth::user();

        if(Hash::check($request->old_password, $User->password))
        {
            $User->password = bcrypt($request->password);
            $User->save();

            if($request->ajax()) {
                return response()->json(['message' => trans('api.user.password_updated')]);
            }else{
                return back()->with('flash_success', 'Password Updated');
            }

        } else {
             if($request->ajax()) {
                return response()->json(['message' => trans('api.user.incorrect_password')]);
            }else{
                return back()->with('flash_error', 'InCorrect Password');
            }
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function update_location(Request $request){

        $this->validate($request, [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

        if($user = User::find(Auth::user()->id)){

            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
            $user->save();

            return response()->json(['message' => trans('api.user.location_updated')]);

        }else{

            return response()->json(['error' => trans('api.user.user_not_found')], 500);

        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function details(Request $request){

        $this->validate($request, [
            'device_type' => 'in:android,ios',
        ]);

        try{

            if($user = User::find(Auth::user()->id)){

                if($request->has('device_token')){
                    $user->device_token = $request->device_token;
                }

                if($request->has('device_type')){
                    $user->device_type = $request->device_type;
                }

                if($request->has('device_id')){
                    $user->device_id = $request->device_id;
                }

                $user->save();

                $user->currency = Setting::get('currency');
                return $user;

            }else{
                return response()->json(['error' => trans('api.user.user_not_found')], 500);
            }
        }
        catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function update_profile(Request $request)
    {

        $this->validate($request, [
                'first_name' => 'required|max:255',
                'last_name' => 'max:255',
                'email' => 'email|unique:users,email,'.Auth::user()->id,
                'mobile' => 'required',
                'picture' => 'mimes:jpeg,bmp,png',
            ]);

         try {

            $user = User::findOrFail(Auth::user()->id);

            if($request->has('first_name')){ 
                $user->first_name = $request->first_name;
            }
            
            if($request->has('last_name')){
                $user->last_name = $request->last_name;
            }

            if($request->has('mobile')){
                $user->mobile = $request->mobile;
            }
            
            if($request->has('email')){
                $user->email = $request->email;
            }

             if($request->has('language')){
                $user->language = $request->language;
            }

            if ($request->picture != "") {
                $user->picture = $request->picture->store('user/profile');
            }

            $user->save();

            if($request->ajax()) {
                return response()->json($user);
            }else{
                return back()->with('flash_success', trans('api.user.profile_updated'));
            }
        }

        catch (ModelNotFoundException $e) {
             return response()->json(['error' => trans('api.user.user_not_found')], 500);
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function services() {

        if($serviceList = ServiceType::all()) {
            return $serviceList;
        } else {
            return response()->json(['error' => trans('api.services_not_found')], 500);
        }

    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function send_request(Request $request) {


        $this->validate($request, [
                's_latitude' => 'required|numeric',
                's_longitude' => 'required|numeric',
                'service_type' => 'required|numeric|exists:service_types,id',
                'promo_code' => 'exists:promocodes,promo_code',
                'use_wallet' => 'numeric',
                'payment_mode' => 'required|in:CASH,CARD,PAYPAL,WAYFORPAY',
                'card_id' => ['required_if:payment_mode,CARD','exists:cards,card_id,user_id,'.Auth::user()->id],
            ]);

        Log::info('New Request from user id :'. Auth::user()->id .' params are :');
        Log::info($request->all());

        $ActiveRequests = UserRequests::PendingRequest(Auth::user()->id)->count();


        if($ActiveRequests > 0) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.ride.request_inprogress')], 500);
            }else{
                return redirect('dashboard')->with('flash_error', 'Already request is in progress. Try again later');
            }
        }


        if($request->has('schedule_date') && $request->has('schedule_time')){

            if(time() > strtotime($request->schedule_date.$request->schedule_time)){
                if($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.request_inprogress')], 500);
                }else{
                    return redirect('dashboard')->with('flash_error', 'Unable to Create Request! Try again later');
                }
            }

            $beforeschedule_time = (new Carbon("$request->schedule_date $request->schedule_time"))->subHour(1);
            $afterschedule_time = (new Carbon("$request->schedule_date $request->schedule_time"))->addHour(1);
            
            $CheckScheduling = UserRequests::where('status','SCHEDULED')
                            ->where('user_id', Auth::user()->id)
                            ->whereBetween('schedule_at',[$beforeschedule_time,$afterschedule_time])
                            ->get();

            if($CheckScheduling->count() > 0){
                if($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.no_providers_found')], 500);
                }else{
                    return redirect('dashboard')->with('flash_error', 'Already request is Scheduled on this time.');
                }
            }

        }

        $ActiveProviders1 = ProviderService::AvailableServiceProvider($request->service_type)->get()->pluck('provider_id')->toArray();
       // dd($ActiveProviders);
        $service_type = $request->service_type;
        $subscribeproviders = Provider::where('subscription_status','ACTIVE')
         ->whereHas('service', function($query) use ($service_type){
                    $query->where('service_type_id',$service_type); 
        })
        ->get()->pluck('id')->toArray();
       // dd($subscribeproviders);
        $ActiveProviders = array_unique(array_merge($ActiveProviders1,$subscribeproviders), SORT_REGULAR);
       // dd($ActiveProviders);
        $distance = Setting::get('search_radius', '10');
        $latitude = $request->s_latitude;
        $longitude = $request->s_longitude;

        $Providers = Provider::whereIn('id', $ActiveProviders)
            ->where('status', 'approved')
            ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
            ->get();

        // List Providers who are currently busy and add them to the filter list.

        if(count($Providers) == 0) {

            if($request->ajax()) {
                // Push Notification to User
                return response()->json(['message' => trans('api.ride.no_providers_found')]); 
            }else{
                return back()->with('flash_success', 'No Providers Found! Please try again.');
            }
        }

        try{


            $UserRequest = new UserRequests;
            $UserRequest->booking_id = Helper::generate_booking_id();

            /*$otp2 = substr($UserRequest->booking_id, -3);
            $otp1 = substr($request->s_address, 0, 1);
            
            $otp = $otp2.$otp1;*/
	    $otp = rand(1000,9999);
            $UserRequest->user_id = Auth::user()->id;

            if(Setting::get('broadcast_request',0) == 0){
                $UserRequest->current_provider_id = $Providers[0]->id;
            }else{
                $UserRequest->current_provider_id = 0;
            }

            $UserRequest->service_type_id = $request->service_type;
            $UserRequest->payment_mode = $request->payment_mode;
            
            $UserRequest->status = 'SEARCHING';

            $UserRequest->s_address = $request->s_address ? : "";

            $UserRequest->s_latitude = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;
            $UserRequest->description = $request->has('description') ? $request->description : '';
            $UserRequest->otp = $otp;
            $UserRequest->use_wallet = $request->use_wallet ? : 0;
            
            $UserRequest->assigned_at = Carbon::now();

            if($request->has('schedule_date') && $request->has('schedule_time')){
                $UserRequest->schedule_at = date("Y-m-d H:i:s",strtotime("$request->schedule_date $request->schedule_time"));
            }


            $UserRequest->save();

            if(Setting::get('broadcast_request',0) == 0) {
                
                Log::info('New Request id : '. $UserRequest->id .' Assigned to provider : '. $UserRequest->current_provider_id);

                // incoming request push to provider
                (new SendPushNotification)->IncomingRequest($UserRequest->current_provider_id);
            }

            // update payment mode 
 
            User::where('id',Auth::user()->id)->update(['payment_mode' => $request->payment_mode]);

            if($request->has('card_id')){

                Card::where('user_id',Auth::user()->id)->update(['is_default' => 0]);
                Card::where('card_id',$request->card_id)->update(['is_default' => 1]);
                
            }

            foreach ($Providers as $key => $Provider) {

                $Filter = new RequestFilter;
                $Filter->request_id = $UserRequest->id;
                $Filter->provider_id = $Provider->id;

                // Send push notifications to all providers
                if(Setting::get('broadcast_request',0) == 1){
                    (new SendPushNotification)->IncomingRequest($Provider->id);
                }
                $Filter->save();
            }

            if($request->ajax()) {
                return response()->json([
                        'message' => 'New request Created!',
                        'request_id' => $UserRequest->id,
                        'current_provider' => $UserRequest->current_provider_id,
                    ]);
            }else{
                return redirect('dashboard');
            }

        } catch (Exception $e) {
            dd($e->getMessage());
           
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            }else{
                return back()->with('flash_error', 'Something went wrong while sending request. Please try again.');
            }
        }
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function cancel_request(Request $request) {

        $this->validate($request, [
                'request_id' => 'required|numeric|exists:user_requests,id,user_id,'.Auth::user()->id,
            ]);

        try{

            $UserRequest = UserRequests::findOrFail($request->request_id);

            if($UserRequest->status == 'CANCELLED')
            {
                if($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.already_cancelled')], 500); 
                }else{
                    return back()->with('flash_error', 'Request is Already Cancelled!');
                }
            }

            if(in_array($UserRequest->status, ['SEARCHING','STARTED','ARRIVED','SCHEDULED'])) {

                $UserRequest->status = 'CANCELLED';
                $UserRequest->cancelled_by = 'USER';
                $UserRequest->save();

                RequestFilter::where('request_id', $UserRequest->id)->delete();

                if($UserRequest->status != 'SCHEDULED'){

                    if($UserRequest->provider_id != 0){

                        ProviderService::where('provider_id',$UserRequest->provider_id)->update(['status' => 'active']);

                    }
                }

                 // Send Push Notification to User
                (new SendPushNotification)->UserCancellRide($UserRequest);

                if($request->ajax()) {
                    return response()->json(['message' => trans('api.ride.ride_cancelled')]); 
                }else{
                    return redirect('dashboard')->with('flash_success','Request Cancelled Successfully');
                }

            } else {
                if($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.already_onride')], 500); 
                }else{
                    return back()->with('flash_error', 'Service Already Started!');
                }
            }
        }

        catch (ModelNotFoundException $e) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')]);
            }else{
                return back()->with('flash_error', 'No Request Found!');
            }
        }

    }

    /**
     * Show the request status check.
     *
     * @return \Illuminate\Http\Response
     */

    public function request_status_check() {

        try{

            $check_status = ['CANCELLED','SCHEDULED'];

            $UserRequests = UserRequests::UserRequestStatusCheck(Auth::user()->id,$check_status)
                                        ->get()
                                        ->toArray();

            $search_status = ['SEARCHING','SCHEDULED'];
            $UserRequestsFilter = UserRequests::UserRequestAssignProvider(Auth::user()->id,$search_status)->get(); 

            $Timeout = Setting::get('provider_select_timeout', 180);

            if(!empty($UserRequestsFilter)){
                for ($i=0; $i < sizeof($UserRequestsFilter); $i++) {
                    $ExpiredTime = $Timeout - (time() - strtotime($UserRequestsFilter[$i]->assigned_at));
                    if($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime < 0) {
                        $Providertrip = new TripController();
                        $Providertrip->assign_next_provider($UserRequestsFilter[$i]->id);
                    }else if($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime > 0){
                        break;
                    }
                }
            }

            return response()->json(['data' => $UserRequests]);

        }

        catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }

    } 

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    public function rate_provider(Request $request) {

        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id,user_id,'.Auth::user()->id,
                'rating' => 'required|integer|in:1,2,3,4,5',
                'comment' => 'max:255',
            ]);
    
        $UserRequests = UserRequests::where('id' ,$request->request_id)
                ->where('status' ,'COMPLETED')
                ->where('paid', 0)
                ->first();

        if ($UserRequests) {
            if($request->ajax()){
                return response()->json(['error' => trans('api.user.not_paid')], 500);
            } else {
                return back()->with('flash_error', 'Service Already Started!');
            }
        }

        try{

            $UserRequest = UserRequests::findOrFail($request->request_id);
            
            if($UserRequest->rating == null) {
                UserRequestRating::create([
                        'provider_id' => $UserRequest->provider_id,
                        'user_id' => $UserRequest->user_id,
                        'request_id' => $UserRequest->id,
                        'user_rating' => $request->rating,
                        'user_comment' => $request->comment,
                    ]);
            } else {
                $UserRequest->rating->update([
                        'user_rating' => $request->rating,
                        'user_comment' => $request->comment,
                    ]);
            }

            $UserRequest->user_rated = 1;
            $UserRequest->save();

            $base = UserRequestRating::where('provider_id', $UserRequest->provider_id);
            $average = $base->avg('user_rating');
            $average_count = $base->count();

            $UserRequest->provider->update(['rating' => $average, 'rating_count' => $average_count]);

            // Send Push Notification to Provider 
            if($request->ajax()){
                return response()->json(['message' => trans('api.ride.provider_rated')]); 
            }else{
                return redirect('dashboard')->with('flash_success', 'Provider Rated Successfully!');
            }
        } catch (Exception $e) {
            if($request->ajax()){
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            }else{
                return back()->with('flash_error', 'Something went wrong');
            }
        }

    } 


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function trips() {
    
        try{
            $UserRequests = UserRequests::UserTrips(Auth::user()->id)->get();
            if(!empty($UserRequests)){
                $map_icon = asset('asset/marker.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=320x130&maptype=terrian&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$value->s_latitude.",".$value->s_longitude."&key=".Setting::get('map_key');
                }
            }
            return $UserRequests;
        }

        catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }



    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function trip_details(Request $request) {

         $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id',
            ]);
    
        try{
            $UserRequests = UserRequests::UserTripDetails(Auth::user()->id,$request->request_id)->get();
            if(!empty($UserRequests)){
                $map_icon = asset('asset/marker.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=320x130&maptype=terrian&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$value->s_latitude.",".$value->s_longitude."&key=".Setting::get('map_key');
                }
            }
            return $UserRequests;
        }

        catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * get all promo code.
     *
     * @return \Illuminate\Http\Response
     */

    public function promocodes() {

        try{

            $this->check_expiry();

            $Promocode = PromocodeUsage::Active()->where('user_id',Auth::user()->id)
                                ->with('promocode')
                                ->get()
                                ->toArray();

            return response()->json($Promocode);

        }

        catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }

    } 


    public function check_expiry(){

        try{

            $Promocode = Promocode::all();

            foreach ($Promocode as $index => $promo) {

                if(date("Y-m-d") > $promo->expiration){
                    $promo->status = 'EXPIRED';
                    $promo->save();
                    PromocodeUsage::where('promocode_id',$promo->id)->update(['status' => 'EXPIRED']);
                }

            }

        }    
        catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }  
    }


    /**
     * add promo code.
     *
     * @return \Illuminate\Http\Response
     */

    public function add_promocode(Request $request) {

         $this->validate($request, [
                'promocode' => 'required|exists:promocodes,promo_code',
            ]);

        try{

            $find_promo = Promocode::where('promo_code',$request->promocode)->first();

            if($find_promo->status == 'EXPIRED' || (date("Y-m-d") > $find_promo->expiration)){

                if($request->ajax()){

                    return response()->json([
                        'message' => trans('api.promocode_expired'), 
                        'code' => 'promocode_expired'
                    ]);

                }else{
                    return back()->with('flash_error', trans('api.promocode_expired'));
                }

            }elseif(PromocodeUsage::where('promocode_id',$find_promo->id)->where('user_id', Auth::user()->id)->where('status','ADDED')->count() > 0){

                if($request->ajax()){

                    return response()->json([
                        'message' => trans('api.promocode_already_in_use'), 
                        'code' => 'promocode_already_in_use'
                        ]);

                }else{
                    return back()->with('flash_error', 'Promocode Already in use');
                }

            }else{

                $promo = new PromocodeUsage;
                $promo->promocode_id = $find_promo->id;
                $promo->user_id = Auth::user()->id;
                $promo->status = 'ADDED';
                $promo->save();

                if($request->ajax()){

                    return response()->json([
                            'message' => trans('api.promocode_applied') ,
                            'code' => 'promocode_applied'
                         ]); 

                }else{
                    return back()->with('flash_success', trans('api.promocode_applied'));
                }
            }

        }

        catch (Exception $e) {
            if($request->ajax()){
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            }else{
                return back()->with('flash_error', 'Something Went Wrong');
            }
        }

    } 

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function upcoming_trips() {
    
        try{
            $UserRequests = UserRequests::UserUpcomingTrips(Auth::user()->id)->get();
            if(!empty($UserRequests)){
                $map_icon = asset('asset/marker.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=320x130&maptype=terrian&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$value->s_latitude.",".$value->s_longitude."&key=".Setting::get('map_key');
                }
            }
            return $UserRequests;
        }

        catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function upcoming_trip_details(Request $request) {

         $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id',
            ]);
    
        try{
            $UserRequests = UserRequests::UserUpcomingTripDetails(Auth::user()->id,$request->request_id)->get();
            if(!empty($UserRequests)){
                $map_icon = asset('asset/marker.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=320x130&maptype=terrian&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$value->s_latitude.",".$value->s_longitude."&key=".Setting::get('map_key');
                }
            }
            return $UserRequests;
        }

        catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }


        /**
     * Show the nearby providers.
     *
     * @return \Illuminate\Http\Response
     */

    public function show_providers(Request $request) {

        $this->validate($request, [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'service' => 'required|numeric|exists:service_types,id',
            ]);

        try{

            $ActiveProviders = ProviderService::AvailableServiceProvider($request->service)->get()->pluck('provider_id');

            $distance = Setting::get('search_radius', '10');
            $latitude = $request->latitude;
            $longitude = $request->longitude;

            $Providers = Provider::whereIn('id', $ActiveProviders)
                ->where('status', 'approved')
                ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                ->get();

            if(count($Providers) == 0) {
                if($request->ajax()) {
                    return response()->json(['message' => "No Providers Found"]); 
                }else{
                    return back()->with('flash_success', 'No Providers Found! Please try again.');
                }
            }
        
            return $Providers;

        } catch (Exception $e) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            }else{
                return back()->with('flash_error', 'Something went wrong while sending request. Please try again.');
            }
        }
    }



    /**
     * Show the provider.
     *
     * @return \Illuminate\Http\Response
     */

    public function provider(Request $request) {

        $this->validate($request, [
                'provider_id' => 'required|numeric|exists:providers,id',
            ]);

        if($Provider = Provider::find($request->provider_id)) {

            if($Services = ServiceType::all()) {
                foreach ($Services as $key => $value) {
                    $price = ProviderService::where('provider_id',$request->provider_id)
                            ->where('service_type_id',$value->id)
                            ->first();
                    if($price){
                        $Services[$key]->available = true;
                    }else{
                        $Services[$key]->available = false;
                    }
                }
            } 


            return response()->json([
                    'provider' => $Provider, 
                    'services' => $Services,
                ]);

        } else {
            return response()->json(['error' => 'No Provider Found!'], 500);
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
            User::where('id', $request->id)->update(['device_id'=> '', 'device_token' => '']);
            return response()->json(['message' => trans('api.logout_success')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    
    /**
     * help Details.
     *
     * @return \Illuminate\Http\Response
     */

    public function help_details(Request $request){

        try{

            if($request->ajax()) {
                return response()->json([
                        'contact_number' => Setting::get('contact_number',''), 
                        'contact_email' => Setting::get('contact_email',''),
                        'contact_text' => Setting::get('contact_text',''),
                        'contact_title' => Setting::get('site_title',''),
                     ]);
            }

        }catch (Exception $e) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')]);
            }
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
                'email' => 'required|email|max:255|unique:users',
            ]);

        try{
            
            return response()->json(['message' => trans('api.email_available')]);

        } catch (Exception $e) {
             return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function chat_histroy(Request $request)
    {
        $this->validate($request, [
                'request_id' => 'required|integer'
            ]);
        try{
            $Chat = Chat::where('request_id',$request->request_id)
                        ->where('user_id', Auth::user()->id)
                        ->get();
            return response()->json(["status"=>true,"messages"=>$Chat]);
        }catch (Exception $e) {
            return response()->json(["status"=>false,'error' => trans('api.something_went_wrong')], 500);
        }
    }


      public function OTP(Request $request)
    {   
        

        try {

            if(User::where('mobile',$request->mobile)->first()){

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
        } catch (Exception $e) {
            dd($e);
            return response()->json(['error' => trans('form.whoops')], 500);
        }
    }

      public function user_check_mobile(Request $request)
    {
        $check =User::wheremobile($request->username)->first(); 

        if($check)   
            return response()->json(['status' => true ]); 
        else
            return response()->json(['status' => false ]);  
    }

     public function user_check_email(Request $request)
    {
        $check =User::where('email',$request->email)->first();

        if($check)   
            return response()->json(['status' => true ]); 
        else
            return response()->json(['status' => false ]);  
    }


    public function walletApiView(Request $request){
         $random_store_id = 'WAL'.rand ( 10000 , 99999 );


        if($request->mode == 'api'){
            $user = User::findOrFail($request->user_id);
        }else{
            $user = Auth::user();
        }

        $Wallet= new WalletPassbook();
        $Wallet->amount=$request->amount;
        $Wallet->user_id=$user->id;
        $Wallet->payment_id = $random_store_id;
        $Wallet->status='UNPAID';
        $Wallet->via= 'wayforpayapi';
        $Wallet->save();
        $curdate = Carbon::now()->format('Y/m/d');
        $current_date = str_replace('/','',$curdate);
        // dd($current_date);

         return view('user.ride.walletapi_form' , compact('request','random_store_id','current_date','user'));

    }



    public function wayforpay_payment_form(Request $request){

        if($request->mode == 'api'){
            $user = User::findOrFail($request->user_id);
        }else{
            $user = Auth::user();
        }

   
        $UserRequest = UserRequests::find($request->request_id);
        
        $random_store_id = 'HANDY'.rand ( 10000 , 99999 );

        if($UserRequest->payment_mode == 'WAYFORPAY') {

            $RequestPayment = UserRequestPayment::where('request_id',$request->request_id)->first(); 
          
            $amount = $RequestPayment->total;

              $Wallet= new WalletPassbook();
              $Wallet->amount=$amount;
              $Wallet->request_id=$request->request_id;
              $Wallet->payment_id=$random_store_id;
              $Wallet->user_id=$user->id;
              $Wallet->status='UNPAID';
              $Wallet->via= 'wayforpayapi';
              $Wallet->save();

               $curdate = Carbon::now()->format('Y/m/d');
              $current_date = str_replace('/','',$curdate);
            

         return view('user.ride.flowapi_form' , compact('amount','current_date','random_store_id','user'));
           

        }

    }



 public function wayWalletSuccess(Request $request){
// dd($request->all());
 try{

      $payment_logs = $request->all();

      if($request->transactionStatus == 'Approved'){

         $Wallet=WalletPassbook::where('payment_id',$request->orderReference)
                      ->where('status',"UNPAID")
                      ->orderBy('created_at', 'desc')
                      ->first();
          
        
        // dd($Wallet);
        $Wallet->status="PAID";
        $Wallet->payment_id=$request->orderReference;
        $Wallet->payment_log = json_encode($payment_logs);
        $Wallet->save();         

        $update_user = User::find($Wallet->user_id);
        $update_user->wallet_balance += $request->amount;
        $update_user->save();
        // dd($update_user);

        (new SendPushNotification)->WalletMoney($update_user->id,currency($request->amount));

        $total=$update_user->wallet_balance;
        if($Wallet->via == 'wayforpayapi'){
           return response()->json(['status'=>true,'total'=>$total,'message' => "Payment Added to Wallet Successfully!"]);
        }else{
        return redirect('wallet')->with('flash_success', 'Payment Added to Wallet Successfully!');

        } 
        }else{
          return response()->json(['error' => "Payment Not Added to Wallet Failed"]);
        }
       }catch (Exception $ex) {
        // dd($ex->getMessage());
         return response()->json(['error' => "Payment Not Added to Wallet Failed"]);
      } 

    }


     public function wayPaymentSuccess(Request $request)
        {

            try{

                if($request->transactionStatus == 'Approved'){
                    
                   
                     $Wallet=WalletPassbook::where('status',"UNPAID")
                                  ->where('payment_id',$request->orderReference)
                                  ->first();
                      
                    $Wallet->status="PAID";
                    $Wallet->payment_id=$request->orderReference;
                    $Wallet->save();     

                    $UserRequest = UserRequests::where('id',$Wallet->request_id)->first();
                    $UserRequest->paid = 1;
                    $UserRequest->status = 'COMPLETED';
                    $UserRequest->save();    

                    if($Wallet->via == 'wayforpayapi'){
                        return response()->json(['message' => 'Paid Successfully']); 
                    } else {
                        return redirect('dashboard')->with('flash_success', trans('api.paid'));
                    }

                 
              }else{
                 return response()->json(['error' => 'Wayforpay Failed to pay']); 
              }
            }catch (Exception $ex) {
              return response()->json(['error' => 'Wayforpay Failed to pay']); 
            } 

        }



}
