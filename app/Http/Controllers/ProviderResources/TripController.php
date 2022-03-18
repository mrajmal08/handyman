<?php

namespace App\Http\Controllers\ProviderResources;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Http\Controllers\SendPushNotification;
use Auth;
use Setting;
use Storage;
use Carbon\Carbon;

use App\User;
use App\Chat;
use App\Helpers\Helper;
use App\RequestFilter;
use App\UserRequests;
use App\ProviderService;
use App\PromocodeUsage;
use App\Provider;
use App\Promocode;
use App\UserRequestRating;
use App\UserRequestPayment;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try{

            if($request->ajax()) {
                $Provider = Auth::user();
            } else {
                $Provider = Auth::guard('provider')->user();
            }

            $provider = $Provider->id;

            $AfterAssignProvider = RequestFilter::with(['request.user', 'request.payment', 'request' ,'request.service_type'])
                ->where('provider_id', $provider)
                ->whereHas('request', function($query) use ($provider) {
                        $query->where('status','<>', 'CANCELLED');
                        $query->where('status','<>', 'SCHEDULED');
                        $query->where('provider_id', $provider );
                        $query->where('current_provider_id', $provider);
                    });
            if(Setting::get('broadcast_request',0) == 1){
                $BeforeAssignProvider = RequestFilter::with(['request.user', 'request.payment', 'request','request.service_type'])
                ->where('provider_id', $provider)
                ->whereHas('request', function($query) use ($provider){
                    $query->where('status','<>', 'CANCELLED');
                    $query->where('status','<>', 'SCHEDULED');
                    $query->where('current_provider_id',0);
                });
            } else {
                $BeforeAssignProvider = RequestFilter::orderBy('created_at','desc')->with(['request.user', 'request.payment', 'request','request.service_type'])
                ->where('provider_id', $provider)
                ->whereHas('request', function($query) use ($provider){
                        $query->where('status','<>', 'CANCELLED');
                        $query->where('status','<>', 'SCHEDULED');
                        $query->where('current_provider_id',$provider);
                        $query->where('provider_id', 0 );
                    });   
            }
           
         $IncomingRequests = $BeforeAssignProvider->union($AfterAssignProvider)->get();
         
         \Log::info($IncomingRequests);
            if(!empty($request->latitude)) {
                $Provider->update([
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                ]);
            }

            $Timeout = Setting::get('provider_select_timeout', 180);
            if(!empty($IncomingRequests)){
                for ($i=0; $i < sizeof($IncomingRequests); $i++) {
                    $IncomingRequests[$i]->time_left_to_respond = $Timeout - (time() - strtotime($IncomingRequests[$i]->request->assigned_at));
                    if($IncomingRequests[$i]->request->status == 'SEARCHING' && $IncomingRequests[$i]->time_left_to_respond < 0) {
                        if(Setting::get('broadcast_request',0) == 1){
                            $this->assign_destroy($IncomingRequests[$i]->request->id);
                        }else{
                            $this->assign_next_provider($IncomingRequests[$i]->request->id);
                        }
                    }
                }
            }

            $Response = [
                'account_status' => $Provider->status,
                'service_status' => $Provider->service ? Auth::user()->service->status : 'offline',
                'requests' => $IncomingRequests,
            ];

            return $Response;

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }
    }

    /**
     * Cancel given request.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request)
    {
        try{

            $UserRequest = UserRequests::findOrFail($request->id);
            $Cancellable = ['SEARCHING', 'ACCEPTED', 'ARRIVED', 'STARTED', 'CREATED','SCHEDULED'];

            if(!in_array($UserRequest->status, $Cancellable)) {
                return back()->with(['flash_error' => 'Cannot cancel request at this stage!']);
            }

            $UserRequest->status = "CANCELLED";
            $UserRequest->cancelled_by = "PROVIDER";
            $UserRequest->save();

             RequestFilter::where('request_id', $UserRequest->id)->delete();

             ProviderService::where('provider_id',$UserRequest->provider_id)->update(['status' =>'active']);

             // Send Push Notification to User
            (new SendPushNotification)->ProviderCancellRide($UserRequest);

            return $UserRequest;

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function rate(Request $request, $id)
    {

        $this->validate($request, [
                'rating' => 'required|integer|in:1,2,3,4,5',
                'comment' => 'max:255',
            ]);
    
        try {

            $UserRequest = UserRequests::where('id', $id)
                ->where('status', 'COMPLETED')
                ->firstOrFail();

            if($UserRequest->rating == null) {
                UserRequestRating::create([
                        'provider_id' => $UserRequest->provider_id,
                        'user_id' => $UserRequest->user_id,
                        'request_id' => $UserRequest->id,
                        'provider_rating' => $request->rating,
                        'provider_comment' => $request->comment,
                    ]);
            } else {
                $UserRequest->rating->update([
                        'provider_rating' => $request->rating,
                        'provider_comment' => $request->comment,
                    ]);
            }

            $UserRequest->update(['provider_rated' => 1]);

            // Delete from filter so that it doesn't show up in status checks.
            RequestFilter::where('request_id', $id)->delete();

            // Send Push Notification to Provider 
            $base = UserRequestRating::where('user_id', $UserRequest->user_id);
            $average = $base->avg('user_rating');
            $average_count = $base->count();

            $UserRequest->user->update(['rating' => $average,'user_rating' => $average_count ]);

            ProviderService::where('provider_id',$UserRequest->provider_id)->update(['status' =>'active']);

            return response()->json(['message' => 'Request Completed!']);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Request not yet completed!'], 500);
        }
    }

    /**
     * Get the trip history of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function history(Request $request)
    {
        if($request->ajax()) {
            $Jobs = UserRequests::where('provider_id', Auth::user()->id)
                        ->where('status', 'COMPLETED')
                        ->orderBy('created_at','desc')
                        ->with('user', 'service_type', 'payment', 'rating')
                        ->get();
            if(!empty($Jobs)){
                $map_icon = asset('asset/marker.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=320x130&maptype=terrian&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$value->s_latitude.",".$value->s_longitude."&key=".Setting::get('map_key');
                }
            }
            return $Jobs;
        }
        $Jobs = UserRequests::where('provider_id', Auth::guard('provider')->user()->id)->with('user', 'service_type', 'payment', 'rating')->get();
        return view('provider.trip.index', compact('Jobs'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function accept(Request $request, $id)
    {
        try {

            $UserRequest = UserRequests::findOrFail($id);

            if($UserRequest->status != "SEARCHING") {
                return response()->json(['error' => 'Request already under progress!']);
            }
            
            $UserRequest->provider_id = Auth::user()->id;

            if(Setting::get('broadcast_request',0) == 1){
               $UserRequest->current_provider_id = Auth::user()->id; 
            }

            if($UserRequest->schedule_at != ""){

                $beforeschedule_time = strtotime($UserRequest->schedule_at."- 1 hour");
                $afterschedule_time = strtotime($UserRequest->schedule_at."+ 1 hour");

                $CheckScheduling = UserRequests::where('status','SCHEDULED')
                            ->where('provider_id', Auth::user()->id)
                            ->whereBetween('schedule_at',[$beforeschedule_time,$afterschedule_time])
                            ->count();

                if($CheckScheduling > 0 ){
                    if($request->ajax()) {
                        return response()->json(['error' => trans('api.ride.request_already_scheduled')]);
                    }else{
                        return redirect('dashboard')
                                ->with('flash_error', 'If the ride is already scheduled then we cannot schedule/request another ride for the after 1 hour or before 1 hour');
                    }
                }


                RequestFilter::where('request_id',$UserRequest->id)->where('provider_id',Auth::user()->id)->update(['status' => 2]);

                $UserRequest->status = "SCHEDULED";
                $UserRequest->save();

                // Send Push Notification to User
                (new SendPushNotification)->RideScheduled($UserRequest);

            }else{


                $UserRequest->status = "STARTED";
                $UserRequest->save();


                ProviderService::where('provider_id',$UserRequest->provider_id)->update(['status' =>'riding']);

                $provider_unsub = Provider::where('id',$UserRequest->provider_id)->where('subscription_status','INACTIVE')->first();

                if(count($provider_unsub) > 0)
                {
                    $Filters_prov = RequestFilter::where('request_id','!=', $UserRequest->id)->where('provider_id',$provider_unsub->id)->get();  

                    foreach ($Filters_prov as $Filters_provs) {
                    $Filters_provs->delete();
                    }              
                }

                $Filters = RequestFilter::where('request_id', $UserRequest->id)->where('provider_id', '!=', Auth::user()->id)->get();
                // dd($Filters->toArray());
                foreach ($Filters as $Filter) {
                    $Filter->delete();
                }
            }

           /* $UnwantedRequest = RequestFilter::where('request_id','!=' ,$UserRequest->id)
                                ->where('provider_id',Auth::user()->id )
                                ->whereHas('request', function($query){
                                    $query->where('status','<>','SCHEDULED');
                                });

            if($UnwantedRequest->count() > 0){
                $UnwantedRequest->delete();
            }*/ 

            // Send Push Notification to User
            (new SendPushNotification)->RideAccepted($UserRequest);

            return $UserRequest->with('user')->get();

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to accept, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Connection Error']);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
              'status' => 'required|in:ACCEPTED,STARTED,ARRIVED,PICKEDUP,DROPPED,PAYMENT,COMPLETED',
              'before_image' => 'mimes:jpeg,jpg,bmp,png',
              'after_image' => 'mimes:jpeg,jpg,bmp,png',
              'after_comment' => 'max:255',
              'before_comment' => 'max:255',
           ]);

        try{

            $UserRequest = UserRequests::with('user')->findOrFail($id);

            if($request->has('before_comment')){
                $UserRequest->before_comment = $request->before_comment;
            }

            if($request->has('after_comment')){
                $UserRequest->after_comment = $request->after_comment;
            }

            if ($request->hasFile('before_image')) {
                $UserRequest->before_image = $request->before_image->store('service');
            }

            if ($request->hasFile('after_image')) {
                $UserRequest->after_image = $request->after_image->store('service');
            }

            if($request->status == 'DROPPED' && $UserRequest->payment_mode != 'CASH') {
                $UserRequest->status = 'COMPLETED';
                //$UserRequest->paid = 1;
            } else if ($request->status == 'COMPLETED' && $UserRequest->payment_mode == 'CASH') {
                $UserRequest->status = $request->status;
                $UserRequest->paid = 1;
                ProviderService::where('provider_id',$UserRequest->provider_id)->update(['status' =>'active']);
            } else {
                $UserRequest->status = $request->status;
                if($request->status == 'ARRIVED'){
                    (new SendPushNotification)->Arrived($UserRequest);
                }
                if($request->status == 'PICKEDUP'){
                    $UserRequest->started_at = Carbon::now();
                    $UserRequest->save();
                }
            }

            $UserRequest->save();

            if($request->status == 'DROPPED') {
                $UserRequest->with('user')->findOrFail($id);
                $UserRequest->finished_at = Carbon::now();
                $UserRequest->save();
                $UserRequest->invoice = $this->invoice($id);
                return $UserRequest;
            }

            // Send Push Notification to User
       
            return $UserRequest;

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to update, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Connection Error']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $UserRequest = UserRequests::find($id);

        try {
            $this->assign_next_provider($UserRequest->id);
            return $UserRequest->with('user')->get();

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to reject, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Connection Error']);
        }
    }

    public function assign_destroy($id)
    {
        $UserRequest = UserRequests::find($id);
        try {
            UserRequests::where('id', $UserRequest->id)->update(['status' => 'CANCELLED']);
            // No longer need request specific rows from RequestMeta
            RequestFilter::where('request_id', $UserRequest->id)->delete();
            //  request push to user provider not available
            (new SendPushNotification)->ProviderNotAvailable($UserRequest->user_id);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to reject, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Connection Error']);
        }
    }

    public function assign_next_provider($request_id) {
        try {
            $UserRequest = UserRequests::findOrFail($request_id);
        } catch (ModelNotFoundException $e) {
            // Cancelled between update.
            return false;
        }

        // $RequestFilter = RequestFilter::where('provider_id', $UserRequest->current_provider_id)
        //     ->where('request_id', $UserRequest->id)
        //     ->delete();
        $RequestFilter = RequestFilter::where('provider_id', Auth::user()->id)
            ->where('request_id', $UserRequest->id)
            ->delete();

        try {

            $next_provider = RequestFilter::where('request_id', $UserRequest->id)
                            ->orderBy('id')
                            ->firstOrFail();


             if(Setting::get('broadcast_request',0) == 0){
                $UserRequest->current_provider_id = $next_provider->provider_id;
            $UserRequest->assigned_at = Carbon::now();
            $UserRequest->save();
             }else{

                $UserRequest->current_provider_id = 0;
            $UserRequest->assigned_at = Carbon::now();
            $UserRequest->save();
             }
            

            // incoming request push to provider
            //(new SendPushNotification)->IncomingRequest($next_provider->provider_id);
            
        } catch (ModelNotFoundException $e) {
            UserRequests::where('id', $UserRequest->id)->update(['status' => 'CANCELLED']);

            // No longer need request specific rows from RequestMeta
            RequestFilter::where('request_id', $UserRequest->id)->delete();

            //  request push to user provider not available
            (new SendPushNotification)->ProviderNotAvailable($UserRequest->user_id);
        }
    }

    public function invoice($request_id)
    {
        try {
            $UserRequest = UserRequests::findOrFail($request_id);
            
            $unit = $UserRequest->service_type->price;
            $unit_price = $unit/60;
            


            $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $UserRequest->finished_at);
            $from = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $UserRequest->started_at);
            
            $diff_in_minutes = $to->diffInMinutes($from);
            $TimePrice =  $diff_in_minutes * $unit_price ;

            $base_fixed = $UserRequest->service_type->fixed ? : 0;

            //$TimePrice = ceil($hourdiff) * $UserRequest->service_type->price;
            
            $Discount = 0; // Promo Code discounts should be added here.

            if($PromocodeUsage = PromocodeUsage::where('user_id',$UserRequest->user_id)->where('status','ADDED')->first()){
                if($Promocode = Promocode::find($PromocodeUsage->promocode_id)){
                    $Discount = $Promocode->discount;
                    $PromocodeUsage->status ='USED';
                    $PromocodeUsage->save();
                }
            }
            $Wallet = 0;


            $Commision = ($base_fixed + $TimePrice) * (Setting::get('commision_percentage', 0) / 100);
            $Fixed = $base_fixed + $Commision;
            $Tax = ($Fixed + $TimePrice) * (Setting::get('tax_percentage', 0) / 100);

            $Total = $Fixed + $TimePrice + $Tax - $Discount;
            
            // $Total += $Tax;

            if($Total < 0){
                $Total = 0.00; // prevent from negative value
            }
            
            $Payment = new UserRequestPayment;
            $Payment->request_id = $UserRequest->id;
            $Payment->fixed = $Fixed;
            $Payment->payment_mode = $UserRequest->payment_mode;
            $Payment->time_price = $TimePrice;
            $Payment->commision = $Commision;
            $Payment->tax = $Tax;
            if($Discount != 0 && $PromocodeUsage){
                $Payment->promocode_id = $PromocodeUsage->promocode_id;
            }
            $Payment->discount = $Discount;

            if($UserRequest->use_wallet == 1 && $Total > 0){

                $User = User::find($UserRequest->user_id);

                $Wallet = $User->wallet_balance;

                if($Wallet != 0){

                    if($Total > $Wallet){

                        $Payment->wallet = $Wallet;
                        $Payable = $Total - $Wallet;
                        User::where('id',$UserRequest->user_id)->update(['wallet_balance' => 0 ]);
                        $Payment->total = abs($Payable);

                        // charged wallet money push 
                        (new SendPushNotification)->ChargedWalletMoney($UserRequest->user_id,currency($Wallet));

                    }else{

                        $Payment->total = 0;
                        $WalletBalance = $Wallet - $Total;
                        User::where('id',$UserRequest->user_id)->update(['wallet_balance' => $WalletBalance]);
                        $Payment->wallet = $Total;

                        //update user request table
                        $UserRequest->paid = 1;
                        $UserRequest->status = 'COMPLETED';
                        $UserRequest->save();

                        // charged wallet money push 
                        (new SendPushNotification)->ChargedWalletMoney($UserRequest->user_id,currency($Total));
                    }

                }

            }else{
                $Payment->total = abs($Total);
            }

            $Payment->tax = $Tax;
            $Payment->save();
            
           // dd($Payment);   
            return $Payment;

        } catch (ModelNotFoundException $e) {
            return false;
        }
    }

    /**
     * Get the trip history details of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function history_details(Request $request)
    {
        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id',
            ]);

        if($request->ajax()) {
            
            $Jobs = UserRequests::where('id',$request->request_id)
                                ->where('provider_id', Auth::user()->id)
                                ->orderBy('created_at','desc')
                                ->with('payment','service_type','user','rating')
                                ->get();
            if(!empty($Jobs)){
                $map_icon = asset('asset/marker.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=320x130&maptype=terrian&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$value->s_latitude.",".$value->s_longitude."&key=".Setting::get('map_key');
                }
            }

            return $Jobs;
        }

    }

        /**
     * Get the trip history details of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function upcoming_details(Request $request)
    {
        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id',
            ]);

        if($request->ajax()) {
            
            $Jobs = UserRequests::where('id',$request->request_id)
                                ->where('provider_id', Auth::user()->id)
                                ->with('service_type','user')
                                ->get();
            if(!empty($Jobs)){
                $map_icon = asset('asset/marker.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=320x130&maptype=terrian&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$value->s_latitude.",".$value->s_longitude."&key=".Setting::get('map_key');
                }
            }

            return $Jobs;
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function upcoming_trips() {
    
        try{
            $UserRequests = UserRequests::ProviderUpcomingRequest(Auth::user()->id)->get();
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
     * Get the trip history details of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function summary(Request $request)
    {
        try{
            if($request->ajax()) {

                $rides = UserRequests::where('provider_id', Auth::user()->id)->count();
                $revenue = UserRequestPayment::whereHas('request', function($query){
                                $query->where('provider_id', Auth::user()->id);
                            })
                        ->sum('total');
                $cancel_rides = UserRequests::where('status','CANCELLED')->where('provider_id', Auth::user()->id)->count();
                $scheduled_rides = UserRequests::where('status','SCHEDULED')->where('provider_id', Auth::user()->id)->count();

                return response()->json([
                    'rides' => $rides, 
                    'revenue' => $revenue,
                    'cancel_rides' => $cancel_rides,
                    'scheduled_rides' => $scheduled_rides,
                ]);
            }

        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
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
     * Show the chat histroy.
     *
     * @return \Illuminate\Http\Response
     */

    public function chat_histroy(Request $request)
    {
        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id',
            ]);
        try{
            $Chat = Chat::where('request_id',$request->request_id)
                        ->where('provider_id', Auth::user()->id)
                        ->get();
            return response()->json(["status"=>true,"messages"=>$Chat]);
        }catch (Exception $e) {
            return response()->json(["status"=>false,'error' => trans('api.something_went_wrong')], 500);
        }
    }

}
