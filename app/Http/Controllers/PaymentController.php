<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserRequestPayment;
use App\UserRequests;
use App\Card;
use App\User;
use App\Http\Controllers\SendPushNotification;

use Setting;
use Exception;
use Auth;
use App\WalletPassbook;
use Carbon\Carbon;

class PaymentController extends Controller
{
	/**
     * payment for user.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment(Request $request){

    	$this->validate($request, [
    			'request_id' => 'required|exists:user_request_payments,request_id|exists:user_requests,id,paid,0,user_id,'.Auth::user()->id
    		]);


    	$UserRequest = UserRequests::find($request->request_id);

    	if($UserRequest->payment_mode == 'CARD'){

    		$RequestPayment = UserRequestPayment::where('request_id',$request->request_id)->first(); 

    		$StripeCharge = $RequestPayment->total * 100;

            if($RequestPayment->total > 0){


          		try{

          			$Card = Card::where('user_id',Auth::user()->id)->where('is_default',1)->first();

      	    		\Stripe\Stripe::setApiKey(Setting::get('stripe_secret_key'));

      	    		$Charge = \Stripe\Charge::create(array(
      					  "amount" => $StripeCharge,
      					  "currency" => "usd",
      					  "customer" => Auth::user()->stripe_cust_id,
      					  "card" => $Card->card_id,
      					  "description" => "Payment Charge for ".Auth::user()->email,
      					  "receipt_email" => Auth::user()->email
      					));

      	    		$RequestPayment->payment_id = $Charge["id"];
      	    		$RequestPayment->payment_mode = 'CARD';
      	    		$RequestPayment->save();

      	    		$UserRequest->paid = 1;
      	    		$UserRequest->status = 'COMPLETED';
      	    		$UserRequest->save();

                    if($request->ajax()){
                  	   return response()->json(['message' => trans('api.paid')]); 
                    }else{
                        return redirect('dashboard')->with('flash_success','Paid');
                    }

          		} catch(\Stripe\StripeInvalidRequestError $e){
                    if($request->ajax()){
          			     return response()->json(['error' => $e->getMessage()], 500);
                    }else{
                          return back()->with('flash_error',$e->getMessage());
                    }
          		} 

            }if($RequestPayment->total == 0){

                $RequestPayment->payment_mode = 'CARD';
                $RequestPayment->save();

                $UserRequest->paid = 1;
                $UserRequest->status = 'COMPLETED';
                $UserRequest->save();


                if($request->ajax()){
                   return response()->json(['message' => trans('api.paid')]); 
                }else{
                    return redirect('dashboard')->with('flash_success','Paid');
                }

            }else{
                return back()->with('flash_error','Try again later');
            }

    	}else{
                return back()->with('flash_error','Try again later');
        }
    }


    /**
     * add wallet money for user.
     *
     * @return \Illuminate\Http\Response
     */
    public function add_money(Request $request){


// dd($request->all());


        $this->validate($request, [
                'amount' => 'required|integer',
                'card_id' => 'required|exists:cards,card_id,user_id,'.Auth::user()->id
            ]);

        try{
            
            $StripeWalletCharge = $request->amount * 100;

            \Stripe\Stripe::setApiKey(Setting::get('stripe_secret_key'));

            $Charge = \Stripe\Charge::create(array(
                  "amount" => $StripeWalletCharge,
                  "currency" => "usd",
                  "customer" => Auth::user()->stripe_cust_id,
                  "card" => $request->card_id,
                  "description" => "Adding Money for ".Auth::user()->email,
                  "receipt_email" => Auth::user()->email
                ));

            $update_user = User::find(Auth::user()->id);
            $update_user->wallet_balance += $request->amount;
            $update_user->save();

            Card::where('user_id',Auth::user()->id)->update(['is_default' => 0]);
            Card::where('card_id',$request->card_id)->update(['is_default' => 1]);

            //sending push on adding wallet money
            (new SendPushNotification)->WalletMoney(Auth::user()->id,currency($request->amount));

            if($request->ajax()){
               return response()->json(['message' => currency($request->amount).trans('api.added_to_your_wallet'), 'user' => $update_user]); 
            }else{
                return redirect('wallet')->with('flash_success',currency($request->amount).' added to your wallet');
            }

        } catch(\Stripe\StripeInvalidRequestError $e){
            if($request->ajax()){
                 return response()->json(['error' => $e->getMessage()], 500);
            }else{
                return back()->with('flash_error',$e->getMessage());
            }
        } 

    }



    public function wayWalletSuccess(Request $request){
// dd($request->all());
 try{

      $payment_logs = $request->all();

      if($request->transactionStatus == 'Approved'){

         $Wallet=WalletPassbook::where('user_id',Auth::user()->id)
                      ->where('status',"UNPAID")
                      ->orderBy('created_at', 'desc')
                      ->first();
          
        
        // dd($Wallet);
        $Wallet->status="PAID";
        $Wallet->payment_id=$request->orderReference;
        $Wallet->payment_log = json_encode($payment_logs);
        $Wallet->save();         

        $update_user = User::find(Auth::user()->id);
        $update_user->wallet_balance += $request->amount;
        $update_user->save();
        // dd($update_user);

        (new SendPushNotification)->WalletMoney(Auth::user()->id,currency($request->amount));

        $total=$update_user->wallet_balance;
        if($Wallet->via == 'wayforpayapi'){
           return response()->json(['status'=>true,'total'=>$total,'message' => "Payment Added to Wallet Successfully!"]);
        }else{
        return redirect('wallet')->with('flash_success', 'Payment Added to Wallet Successfully!');

        } 
        }else{
          return redirect('wallet')->with('flash_error', 'Payment Not Added to Wallet Failed');
        }
       }catch (Exception $ex) {
        // dd($ex->getMessage());
         return redirect('wallet')->with('flash_error', $ex->getMessage());
      } 

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
              $Wallet->payment_id=$random_store_id;
              $Wallet->user_id=$user->id;
              $Wallet->request_id=$request->request_id;
              $Wallet->status='UNPAID';
              $Wallet->via= 'wayforpay';
              $Wallet->save();

               $curdate = Carbon::now()->format('Y/m/d');
              $current_date = str_replace('/','',$curdate);
           

         return view('user.ride.flow_form' , compact('amount','current_date','random_store_id','user'));
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
                        return response()->json(['message' => trans('api.paid')]); 
                    } else {
                        return redirect('dashboard')->with('flash_success', trans('api.paid'));
                    }

                 
              }else{
                return redirect('dashboard')->with('flash_error', 'Wayforpay Failed to pay');
              }
            }catch (Exception $ex) {
             dd($ex->getMessage());
             return redirect('dashboard')->with('flash_error', $ex->getMessage());
            } 

        }

}
