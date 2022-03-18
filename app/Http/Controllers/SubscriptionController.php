<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SubscriptionPlan;
use App\Subscription;
use App\Provider;
use App\ProviderCard;
use Auth;
use Setting;
use App\WalletPassbook;
use Carbon\Carbon;


class SubscriptionController extends Controller
{
    public function index(Request $request){
        
    	$provider = Provider::with('subscription')->where('id',\Auth::user()->id)->first();
    	$subscription_plans = SubscriptionPlan::where('is_deleted',0)->where('is_active',1)->get();
    	return view('provider.subscription.index',compact('subscription_plans','provider'));
    }
    public function show(){
    	return view('admin.subscription_plan.create');
    }
    public function subscription_plans(){
        $subscription_plans = SubscriptionPlan::where('is_deleted',0)->where('is_active',1)->get();
        $provider = Provider::where('id',\Auth::user()->id)->first();
        if(count($provider) > 0 && count($subscription_plans) > 0){
            if($provider->subscription_id > 0 && $provider->subscription_status == 'ACTIVE'){
                $subscription = Subscription::where('id',$provider->subscription_id)->first();
                if($subscription){
                    foreach($subscription_plans as $key => $subscription_plan){
                        if($subscription_plan->id == $subscription->subscription_plan_id){
                            $subscription_plans[$key]['subscription_status'] = 1;
                            $subscription_plans[$key]['start_date'] = $subscription->start_date;
                            $subscription_plans[$key]['end_date'] = $subscription->end_date;
                        }else{
                            $subscription_plans[$key]['subscription_status'] = 0;
                            $subscription_plans[$key]['start_date'] = NULL;
                            $subscription_plans[$key]['end_date'] = NULL;
                        }
                    }
                }else{
                    foreach($subscription_plans as $key => $subscription_plan){
                        $subscription_plans[$key]['subscription_status'] = 0;
                        $subscription_plans[$key]['start_date'] = NULL;
                        $subscription_plans[$key]['end_date'] = NULL;
                    }
                }
            }else{
                foreach($subscription_plans as $key => $subscription_plan){
                    $subscription_plans[$key]['subscription_status'] = 0;
                    $subscription_plans[$key]['start_date'] = NULL;
                    $subscription_plans[$key]['end_date'] = NULL;
                }
            }
        }
        return response()->json(['subscription_plans' => $subscription_plans], 200);
    }

    public function sub_wayforpau(Request $request){

         if($request->mode == 'api'){
            $user = Provider::findOrFail($request->provider_id);
        }else{
            $user = Auth::user();
        }
   
        $random_store_id = 'HANDYSUB'.rand ( 10000 , 99999 ).'_'.$request->subscription;

         $subscription_plan = SubscriptionPlan::where('id',$request->subscription)->first();
             $amount = $subscription_plan->amount;

              $Wallet= new WalletPassbook();
              $Wallet->amount=$amount;
              $Wallet->payment_id=$random_store_id;
              $Wallet->provider_id=$user->id;
              $Wallet->status='UNPAID';
              $Wallet->via= 'wayforpayprovidersub';
              $Wallet->save();

               $curdate = Carbon::now()->format('Y/m/d');
              $current_date = str_replace('/','',$curdate);

        if($request->mode == 'api'){
            return view('provider.subscription.subapi' , compact('amount','current_date','random_store_id','user'));
        }else{
            return view('provider.subscription.sub' , compact('amount','current_date','random_store_id','user'));
        }
            

        
       
    }



    public function subscription(Request $request){
\Log::info($request->all());
        $payment_logs = $request->all();
        if($request->transactionStatus == 'Approved'){

         $Wallet=WalletPassbook::where('payment_id',$request->orderReference)
                      ->where('status',"UNPAID")
                      ->orderBy('created_at', 'desc')
                      ->first();
          
                $Wallet->status="PAID";
                $Wallet->payment_id=$request->orderReference;
                $Wallet->payment_log = json_encode($payment_logs);
                $Wallet->save();         

                }else{
                    
                   return redirect('provider/subscription')->with('flash_error','Wayforpay failed to subscribe'); 
                }



    	   $subscription = substr($request->orderReference, 14);
         
           $provider = Provider::where('id',\Auth::user()->id)->first();
          
          $subscription_plan = SubscriptionPlan::where('id',$subscription)->first();


        	if(count($provider) > 0 && count($subscription_plan) > 0){
        		//log
        		$subscription = new Subscription();
        		$subscription->provider_id = \Auth::user()->id;
        		$subscription->subscription_plan_id = $subscription_plan->id;
        		$subscription->start_date = date('Y-m-d');
        		$subscription->end_date = date('Y-m-d',strtotime("+".$subscription_plan->validity." days"));
        		$subscription->is_active = 1;
        		$subscription->save();
        		//provider
        		$provider->subscription_id = $subscription_plan->id;
        		$provider->subscription_status = 'ACTIVE';
        		$provider->save();
               
                
                return redirect('provider/subscription')->with('flash_success','Subscription added successfully.');
        	}else{
               
                return redirect('provider/subscription')->with('flash_error','Subscription failed.');
            }
      
    }



    public function subscription_api(Request $request){
\Log::info($request->all());
        $payment_logs = $request->all();
        if($request->transactionStatus == 'Approved'){

         $Wallet=WalletPassbook::where('payment_id',$request->orderReference)
                      ->where('status',"UNPAID")
                      ->orderBy('created_at', 'desc')
                      ->first();
          
                $Wallet->status="PAID";
                $Wallet->payment_id=$request->orderReference;
                $Wallet->payment_log = json_encode($payment_logs);
                $Wallet->save();         

                }else{
                    
                   return response()->json(['error' => 'Wayforpay failed to subscribe']); 
                }



           $subscription = substr($request->orderReference, 14);
           $provider = Provider::where('id',$Wallet->provider_id)->first();
          
          $subscription_plan = SubscriptionPlan::where('id',$subscription)->first();


            if(count($provider) > 0 && count($subscription_plan) > 0){
                //log
                $subscription = new Subscription();
                $subscription->provider_id = $provider->id;
                $subscription->subscription_plan_id = $subscription_plan->id;
                $subscription->start_date = date('Y-m-d');
                $subscription->end_date = date('Y-m-d',strtotime("+".$subscription_plan->validity." days"));
                $subscription->is_active = 1;
                $subscription->save();
                //provider
                $provider->subscription_id = $subscription_plan->id;
                $provider->subscription_status = 'ACTIVE';
                $provider->save();
               
                
                return response()->json(['message' => 'Subscription added successfully.']); 
            }else{
               
                 return response()->json(['error' => 'Wayforpay failed to subscribe']); 
            }
      
    }
   
}
