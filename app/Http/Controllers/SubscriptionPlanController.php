<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SubscriptionPlan;

class SubscriptionPlanController extends Controller
{
    public function index(){
    	$subscription_plans = SubscriptionPlan::where('is_deleted',0)->where('is_active',1)->get();
    	return view('admin.subscription_plan.index',compact('subscription_plans'));
    }
    public function show(){
    	return view('admin.subscription_plan.create');
    }
    public function store(Request $request){
    	$this->validate($request,[
    		'name' => 'required',
    		'amount' => 'required|numeric',
    		'validity' => 'required|numeric',
    	]);
    	$request->merge([
    		'is_active' => 1,
    		'is_deleted' => 0
    	]);
    	$subscription_plan = SubscriptionPlan::create($request->all());
    	if($subscription_plan){
    		return back()->with('flash_success','SubscriptionPlan Saved Successfully');
    	}else{
    		return back()->with('flash_error','Sorry!Somethingwent wrong');
    	}
    	return view('subscription_plan.show');
    }
    public function edit($id){
    	$subscription_plan = SubscriptionPlan::where('id',$id)->first();
    	return view('admin.subscription_plan.edit',compact('subscription_plan'));
    }
    public function update($id,Request $request){
    	$this->validate($request,[
    		'name' => 'required',
    		'amount' => 'required|numeric',
    		'validity' => 'required|numeric',
    	]);
    	$subscription_plan = SubscriptionPlan::where('id',$id)->first();
    	if($subscription_plan){
    		$subscription_plan->name = $request->name;
    		$subscription_plan->amount = $request->amount;
    		$subscription_plan->validity = $request->validity;
    		$subscription_plan->description = $request->description;
    		$subscription_plan->is_active = 1;
    		$subscription_plan->is_deleted = 0;
    		if($subscription_plan->save()){
    			return back()->with('flash_success','SubscriptionPlan Updated Successfully');
    		}else{
    			return back()->with('flash_error','Sorry!Somethingwent wrong');
    		}
    	}else{
    		return back()->with('flash_error','Access denied!');
    	}
    }
    public function destroy($id){
    	$subscription_plan = SubscriptionPlan::where('id',$id)->first();
    	if($subscription_plan){
    		$subscription_plan->is_deleted = 1;
    		if($subscription_plan->save()){
    			return back()->with('flash_success','SubscriptionPlan Deleted Successfully');
    		}else{
    			return back()->with('flash_error','Sorry!Somethingwent wrong');
    		}
    	}else{
    		return back()->with('flash_error','Access denied!');
    	}
    }
}
