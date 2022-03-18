<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Provider;
use App\Settings;
use App\Admin;
use App\UserRequestRating;
use App\UserPayment;
use App\ProviderService;
use App\UserRequests;
use App\ServiceType;
use App\UserRequestPayment;
use App\Helpers\Helper;
use Auth;
use Exception;
use Carbon\Carbon;
use Storage;
use Setting;


class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin');  
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        try{
            
            $rides = UserRequests::has('user')->orderBy('id','desc')->get();
            $cancel_rides = UserRequests::where('status','CANCELLED')->count();
            $service = ServiceType::count();
            $revenue = UserRequestPayment::sum('total');
            $providers = Provider::take(10)->orderBy('rating','desc')->get();
            return view('admin.dashboard',compact('providers','service','rides','cancel_rides','revenue'));
        }
        catch(Exception $e){
            return redirect()->route('admin.user.index')->with('flash_error','Something Went Wrong with Dashboard!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function user_map()
    {
        try{

            $Users = User::where('latitude', '!=', 0)->where('longitude', '!=', 0)->get();
            return view('admin.map.user_map', compact('Users'));
        }
        catch(Exception $e){
            return redirect()->route('admin.setting')->with('flash_error','Something Went Wrong!');
        }
    }

   	/**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function provider_map()
    {
        try{
            $Providers = Provider::where('latitude', '!=', 0)->where('longitude', '!=', 0)->has('service')->get();
            return view('admin.map.provider_map', compact('Providers'));
        }
        catch(Exception $e){
            return redirect()->route('admin.setting')->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function setting()
    {
        return view('admin.setting.site-setting');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function setting_store(Request $request)
    {
        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        $this->validate($request,[
            'site_icon' => 'mimes:jpeg,jpg,bmp,png||max:5242880',
            'site_logo' => 'mimes:jpeg,jpg,bmp,png||max:5242880',
        ]);

        $settings = Settings::all();

        foreach ($settings as $setting) {

            $key = $setting->key;
           
            $temp_setting = Settings::find($setting->id);

            if($temp_setting->key == 'site_icon'){
                
                if($request->file('site_icon') == null){
                    $icon = $temp_setting->value;
                } else {
                    if($temp_setting->value) {
                        Helper::delete_picture($temp_setting->value);
                    }
                    $icon = Helper::upload_picture($request->file('site_icon'));
                }

                $temp_setting->value = $icon;

            }else if($temp_setting->key == 'site_logo'){

                if($request->file('site_logo') == null){
                    $logo = $temp_setting->value;
                } else {
                    if($temp_setting->value) {
                        Helper::delete_picture($temp_setting->value);
                    }
                    $logo = Helper::upload_picture($request->file('site_logo'));
                }

                $temp_setting->value = $logo;

            }else if($temp_setting->key == 'email_logo'){

                if($request->file('email_logo') == null){
                    $logo = $temp_setting->value;
                } else {
                    if($temp_setting->value) {
                        Helper::delete_picture($temp_setting->value);
                    }
                    $logo = Helper::upload_picture($request->file('email_logo'));
                }

                $temp_setting->value = $logo;

            }else if($temp_setting->key == 'manual_request'){

                if($request->$key==1)
                {
                    $temp_setting->value   = 1;
                }

            }else if($temp_setting->key == 'broadcast_request'){
                if($request->$key == 'on')
                {
                    $temp_setting->value = 1;
                }
                else
                {
                    $temp_setting->value = 0;
                }
            }
            else if($temp_setting->key == 'social_login'){
                if($request->$key == 'on')
                {
                    $temp_setting->value = 1;
                }
                else
                {
                    $temp_setting->value = 0;
                }
            }else if($request->$key != ''){

                $temp_setting->value = $request->$key;
            
            }
            
            $temp_setting->save();
              
        }
        
        return back()->with('flash_success','Settings Updated Successfully');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        return view('admin.account.profile');
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function profile_update(Request $request)
    {
        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }
        
        $this->validate($request,[
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'required',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
                       
        ]);

        try{

            $admin = Admin::find(Auth::guard('admin')->user()->id);
            $admin->name = $request->name;
            $admin->email = $request->email;
            $admin->mobile = $request->mobile;

            if ($request->hasFile('picture')) {
                $admin->picture = Storage::url(Storage::putFile('admin/profile', $request->picture, 'public'));
            }

            $admin->gender = $request->gender;
            $admin->save();

            return redirect()->back()->with('flash_success','Profile Updated');
        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function password()
    {
        return view('admin.account.change-password');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function password_update(Request $request)
    {
        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        $this->validate($request,[
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        try{

           $Admin = Admin::find(Auth::guard('admin')->user()->id);

            if(password_verify($request->old_password, $Admin->password))
            {
                $Admin->password = bcrypt($request->password);
                $Admin->save();

                return redirect()->back()->with('flash_success','Password Updated');
            }else{
                return redirect()->back()->with('flash_error','Incorrect Password');
            }
        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function payment()
    {
        try{
             $payments = UserRequests::where('paid',1)
                    ->has('user')
                    ->has('provider')
                    ->has('payment')
                    ->orderBy('user_requests.created_at','desc')
                    ->get();
            
            return view('admin.payment.payment-history', compact('payments'));
        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function payment_setting()
    {
        return view('admin.payment.payment-setting');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function help(){

        try{
            $str = curl('https://www.appoets.com/help.json');
            $Data = json_decode($str, true);
            return view('admin.help', compact('Data'));
        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function request_history(){

        try{

            $requests = UserRequests::RequestHistory()->get();

            return view('admin.request.request-history', compact('requests'));

        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function request_details($id) {
        try{
            $request = UserRequests::where('id',$id)->with('service_type','user','provider')->first();
            
            return view('admin.request.request-details', compact('request'));
        } catch (Exception $e) {
            return back()->with('flash_error','Something Went Wrong!');
        }
    }


    /**
     * User Rating.
     *
     * @return \Illuminate\Http\Response
     */
    public function user_review()
    {
        try{
            $Reviews = UserRequestRating::where('user_id','!=',0)->with('user','provider')->get();
            return view('admin.review.user_review',compact('Reviews'));
        }
        catch(Exception $e){
            return redirect()->route('admin.setting')->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * Provider Rating.
     *
     * @return \Illuminate\Http\Response
     */
    public function provider_review()
    {
        try{
            $Reviews = UserRequestRating::where('provider_id','!=',0)->with('user','provider')->get();
            return view('admin.review.provider_review',compact('Reviews'));
        }
        catch(Exception $e){
            return redirect()->route('admin.setting')->with('flash_error','Something Went Wrong!');
        }
    }

    public function destory_allocation(Request $request)
    {
         $this->validate($request, [
                'id' => 'required|exists:providers,id',
                'service' => 'required|exists:service_types,id',
            ]);

        try{

            if(Setting::get('demo_mode', 0) == 1) {
                return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
            }

            ProviderService::where('provider_id',$request->id) 
                    ->where('service_type_id', $request->service)
                    ->delete();

            return back()->with('flash_success','Service Deleted');
        }

         catch(Exception $e){
            return redirect()->route('admin.setting')->with('flash_error','Something Went Wrong!');
        }
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ProviderService
     * @return \Illuminate\Http\Response
     */
    public function destory_provider_service($id){

        try{
            if(Setting::get('demo_mode', 0) == 1) {
                return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
            }
            
            ProviderService::find($id)->delete();
            return back()->with('message', 'Service deleted successfully');
        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }

    }

        /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function scheduled_request(){
        try{
            $requests = UserRequests::where('status' , 'SCHEDULED')
                        ->RequestHistory()
                        ->get();

            return view('admin.request.scheduled-request', compact('requests'));
        } catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }


        /**
     * privacy.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */

    public function privacy(){
        return view('admin.pages.static')
            ->with('title',"Privacy Page")
            ->with('page', "privacy");
    }

    /**
     * pages.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function pages(Request $request){
        $this->validate($request, [
                'page' => 'required|in:page_privacy',
                'content' => 'required',
            ]);

        Setting::set($request->page, $request->content);
        Setting::save();

        return back()->with('flash_success', 'Content Updated!');
    }

    /**
     * account statements.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function statement($type = 'individual'){

        try{

            $page = 'Service Statement';

            if($type == 'individual'){
                $page = 'Service Statement';
            }elseif($type == 'today'){
                $page = 'Today Statement - '. date('d M Y');
            }elseif($type == 'monthly'){
                $page = 'This Month Statement - '. date('F');
            }elseif($type == 'yearly'){
                $page = 'This Year Statement - '. date('Y');
            }

            $rides = UserRequests::with('payment')->orderBy('id','desc');
            $cancel_rides = UserRequests::where('status','CANCELLED');
            $revenue = UserRequestPayment::select(\DB::raw(
                           'SUM(ROUND(fixed) + ROUND(distance)) as overall, SUM(ROUND(commision)) as commission' 
                       ));

            if($type == 'today'){

                $rides->where('created_at', '>=', Carbon::today());
                $cancel_rides->where('created_at', '>=', Carbon::today());
                $revenue->where('created_at', '>=', Carbon::today());

            }elseif($type == 'monthly'){

                $rides->where('created_at', '>=', Carbon::now()->month);
                $cancel_rides->where('created_at', '>=', Carbon::now()->month);
                $revenue->where('created_at', '>=', Carbon::now()->month);

            }elseif($type == 'yearly'){

                $rides->where('created_at', '>=', Carbon::now()->year);
                $cancel_rides->where('created_at', '>=', Carbon::now()->year);
                $revenue->where('created_at', '>=', Carbon::now()->year);

            }

            $rides = $rides->get();
            $cancel_rides = $cancel_rides->count();
            $revenue = $revenue->get();

            return view('admin.providers.statement', compact('rides','cancel_rides','revenue'))
                    ->with('page',$page);

        } catch (Exception $e) {
            return back()->with('flash_error','Something Went Wrong!');
        }
    }


    /**
     * account statements today.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function statement_today(){
        return $this->statement('today');
    }

    /**
     * account statements monthly.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function statement_monthly(){
        return $this->statement('monthly');
    }

     /**
     * account statements monthly.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function statement_yearly(){
        return $this->statement('yearly');
    }

    /**
     * account statements.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function statement_provider(){

        try{

            $Providers = Provider::all();

            foreach($Providers as $index => $Provider){

                $Rides = UserRequests::where('provider_id',$Provider->id)
                            ->where('status','<>','CANCELLED')
                            ->get()->pluck('id');

                $Providers[$index]->rides_count = $Rides->count();

                $Providers[$index]->payment = UserRequestPayment::whereIn('request_id', $Rides)
                                ->select(\DB::raw(
                                   'SUM(ROUND(fixed) + ROUND(distance)) as overall, SUM(ROUND(commision)) as commission' 
                                ))->get();
            }

            return view('admin.providers.provider-statement', compact('Providers'))->with('page','Providers Statement');

        } catch (Exception $e) {
            return back()->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function translation(){
        try {
            return view('admin.translation');
        } catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }

     public function settings_payment_store(Request $request)
    {
if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        $settings = Settings::all();

        foreach ($settings as $setting) {

            $key = $setting->key;
           
            $temp_setting = Settings::find($setting->id);

           if($temp_setting->key == 'CARD'){
                if($request->$key == 'on')
                {
                    $temp_setting->value = 1;
                }
                else
                {
                    $temp_setting->value = 0;
                }

            }else if($request->$key != ''){

                $temp_setting->value = $request->$key;
            
            }
            
            $temp_setting->save();
              
        }
        
        return back()->with('flash_success','Settings Updated Successfully');
    }

     /* DataBase BackUp*/
    public function DBbackUp()
    {   
        if(Setting::get('demo_mode') == 0){
            $host           = env('DB_HOST','');
            $username       = env('DB_USERNAME','');
            $password       = env('DB_PASSWORD','');
            $database       = env('DB_DATABASE','');
            $dateFormat     = $database."_".(new \DateTime())->format('Y-m-d');
            $fileName       = public_path('/').$dateFormat.".sql";
        if(!empty($password))
             $command        = sprintf('mysqldump -h %s -u %s -p\'%s\' %s > %s', $host, $username,$password, $database, $fileName);
             else
             $command        = sprintf('mysqldump -h %s -u %s %s > %s', $host, $username, $database, $fileName);
             exec($command);

         return response()->download($fileName)->deleteFileAfterSend(true);
        }else{
            return back()->with('flash_error','Permission Denied.');    
        }
    }

}
