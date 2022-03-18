<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Setting;
use Carbon\Carbon;
use App\WalletPassbook;
use Auth;

class HomeController extends Controller
{
    protected $UserAPI;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserApiController $UserAPI)
    {
        $this->middleware('auth');
        $this->UserAPI = $UserAPI;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $Response = $this->UserAPI->request_status_check()->getData();

        if(empty($Response->data))
        {
            if($request->has('service')){
                $cards = (new Resource\CardResource)->index();
                $service = (new Resource\ServiceResource)->show($request->service);
                return view('user.request',compact('cards','service'));
            }else{
                $services = $this->UserAPI->services();
                return view('user.dashboard',compact('services'));
            }
        }else{
            return view('user.ride.waiting')->with('request',$Response->data[0]);
        }
    }

    /**
     * Show the application profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        return view('user.account.profile');
    }


     public function paisa_wallet_form(Request $request)
    {
      

        if($request->mode == 'api'){
         $user = User::findOrFail($request->user_id);
        }else{
         $user = Auth::user();
        }

        $random_store_id = 'WAL'.rand ( 10000 , 99999 );

        $Wallet= new WalletPassbook();
        $Wallet->amount=$request->amount;
        $Wallet->user_id=$user->id;
        $Wallet->payment_id = $random_store_id;
        $Wallet->status='UNPAID';
        $Wallet->via= 'wayforpay';
        $Wallet->save();
        $curdate = Carbon::now()->format('Y/m/d');
        $current_date = str_replace('/','',$curdate);
        // dd($current_date);
         return view('user.ride.wallet_form' , compact('request','random_store_id','current_date','user'));

    }

    /**
     * Show the application profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit_profile()
    {
        return view('user.account.edit_profile');
    }

    /**
     * Update profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function update_profile(Request $request)
    {

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        return $this->UserAPI->update_profile($request);
    }

    /**
     * Show the application change password.
     *
     * @return \Illuminate\Http\Response
     */
    public function change_password()
    {
        return view('user.account.change_password');
    }

    /**
     * Change Password.
     *
     * @return \Illuminate\Http\Response
     */
    public function update_password(Request $request)
    {
        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }
        
        return $this->UserAPI->change_password($request);
    }

    /**
     * Trips.
     *
     * @return \Illuminate\Http\Response
     */
    public function trips()
    {
        $trips = $this->UserAPI->trips();
        return view('user.ride.trips',compact('trips'));
    }

     /**
     * Payment.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment()
    {
        $cards = (new Resource\CardResource)->index();
        return view('user.account.payment',compact('cards'));
    }


    /**
     * Wallet.
     *
     * @return \Illuminate\Http\Response
     */
    public function wallet(Request $request)
    {
        $cards = (new Resource\CardResource)->index();
        return view('user.account.wallet',compact('cards'));
    }

    /**
     * Promotion.
     *
     * @return \Illuminate\Http\Response
     */
    public function promotion(Request $request)
    {
        $promocodes = $this->UserAPI->promocodes()->getData();
        return view('user.account.promotion',compact('promocodes'));
    }

    /**
     * Add promocode.
     *
     * @return \Illuminate\Http\Response
     */
    public function add_promocode(Request $request)
    {
        return $this->UserAPI->add_promocode($request);
    }

    /**
     * Upcoming Trips.
     *
     * @return \Illuminate\Http\Response
     */
    public function upcoming_trips()
    {
        $trips = $this->UserAPI->upcoming_trips();
        return view('user.ride.upcoming',compact('trips'));
    }

    public function account_kit(Request $request){

        // Initialize variables
        $app_id = Setting::get('fb_app_id');
        $secret = env('FB_APP_SECRET');
        $version = Setting::get('fb_app_version'); // 'v1.1' for example

        // Method to send Get request to url
        function doCurl($url) {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $data = json_decode(curl_exec($ch), true);
          curl_close($ch);
          return $data;
        }

        // Exchange authorization code for access token
        $token_exchange_url = 'https://graph.accountkit.com/'.$version.'/access_token?'.
          'grant_type=authorization_code'.
          '&code='.$request->code.
          "&access_token=AA|$app_id|$secret";

        $data = doCurl($token_exchange_url);
        $user_id = $data['id'];
        $user_access_token = $data['access_token'];
        $refresh_interval = $data['token_refresh_interval_sec'];

        // Get Account Kit information
        $me_endpoint_url = 'https://graph.accountkit.com/'.$version.'/me?'.
          'access_token='.$user_access_token;
        $data = doCurl($me_endpoint_url);

        return $data;

    }

}
