<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Exception;
use Storage;
use Setting;

use Carbon\Carbon;
use App\UserRequests;
use App\User;
use App\ServiceType;
use App\ProviderService;

class ProviderApiController extends Controller
{

    /**
     * Show the services.
     *
     * @return \Illuminate\Http\Response
     */

    public function services() {

        if($Services = ServiceType::all()) {
            foreach ($Services as $key => $value) {

                $price = ProviderService::where('provider_id',Auth::user()->id)
                            ->where('service_type_id',$value->id)
                            ->first();

                if($price){
                    $Services[$key]->available = true;
                }else{
                    $Services[$key]->available = false;
                }
            }
            return $Services;
        } else {
            return response()->json(['error' => 'No Services!'], 500);
        }

    }

    /**
     * Update the services.
     *
     * @return \Illuminate\Http\Response
     */

    public function update_services(Request $request) {

        $this->validate($request, [
                'services' => 'required',
            ]);

        try{

            $checked_services = $request->services;

            ProviderService::where('provider_id',Auth::user()->id)->delete();

               foreach($checked_services as $value){
                    $add_service = new ProviderService;
                    $add_service->provider_id = Auth::user()->id;
                    $add_service->service_type_id = $value;
                    $add_service->save();
                }

            return response()->json(['message' => "Services Updated"]); 


    } catch (Exception $e) {

            if($request->ajax()){
                return response()->json(['error' => "try again later"], 500);
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


    public function upcoming_request() {

        try{

            $Jobs = UserRequests::where('provider_id',Auth::user()->id)
                    ->where('status','SCHEDULED')
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

        catch(Exception $e) {
            return response()->json(['error' => "Something Went Wrong"]);
        }

    }


    public function target(){

        try{

            $rides = UserRequests::where('provider_id',Auth::user()->id)
                        ->where('status','COMPLETED')
                        ->where('created_at', '>=', Carbon::today())
                        ->with('payment','service_type')
                        ->orderBy('created_at','desc')
                        ->get();

            return response()->json([
                    'rides' => $rides, 
                    'rides_count' => $rides->count(), 
                    'target' => Setting::get('daily_target','0')]);
        }   
        catch(Exception $e) {
            return response()->json(['error' => "Something Went Wrong"]);
        }
    }


    /**
     * Show the user.
     *
     * @return \Illuminate\Http\Response
     */

    public function user(Request $request) {

        $this->validate($request, [
                'user_id' => 'required|numeric|exists:users,id'
            ]);

        if($User = User::find($request->user_id)) {
            return $User;
        } else {
            return response()->json(['error' => 'No User Found!'], 500);
        }

    }


}
