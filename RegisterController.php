<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Generalsetting;
use App\Models\User;
use App\Classes\GeniusMailer;
use App\Models\Notification;
use App\Models\Invites;
use Auth;

use Validator;

class RegisterController extends Controller
{

	

    public function register(Request $request)
    {

    	$gs = Generalsetting::findOrFail(1);

    	if($gs->is_capcha == 1)
    	{
	        $value = session('captcha_string');
	        if ($request->codes != $value){
	            return response()->json(array('errors' => [ 0 => 'Please enter Correct Capcha Code.' ]));    
	        }    		
    	}


     //    //--- Validation Section

        $rules = [
		        'email'   => 'required|email|unique:users',
		        'password' => 'required|confirmed'
                ];
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        // referral code
		if($request->referral_code && $request->referral_code != ''){
			$referral_code = $request->referral_code;
			// echo $referral_code; die;
			$is_referal =$this->verifyReferralCode($referral_code);
			//echo $is_referal['status']; die;
			
			if($is_referal['status'] == '0'){
				return response()->json(array('errors' => [ 0 => 'Wrong Referal Code' ]));    
			}
			//echo $is_referal['status']; die;
		}




        //--- Validation Section Ends

	        $user = new User;

	        $input = $request->all();        
	        $input['password'] = bcrypt($request['password']);
	        $token = md5(time().$request->name.$request->email);
	        $input['verification_link'] = $token;
	        $input['affilate_code'] = md5($request->name.$request->email);
			$input['refer_code'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 6);
			$input['sponsor_code'] = $request->referral_code;
	          if(!empty($request->vendor))
	          {
					//--- Validation Section
					$rules = [
						'shop_name' => 'unique:users',
						'shop_number'  => 'max:10'
							];
					$customs = [
						'shop_name.unique' => 'This Shop Name has already been taken.',
						'shop_number.max'  => 'Shop Number Must Be Less Then 10 Digit.'
					];

					$validator = Validator::make($request->all(), $rules, $customs);
					if ($validator->fails()) {
					return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
					}
					$input['is_vendor'] = 1;

			  }
			  
			$user->fill($input)->save();

			// $invite = new Invites();

			// $avail_user_id = explode(',', $is_referal['avail_user_id']);

			// $total_count = count($avail_user_id);
			
			// $avail_user_id[$total_count] = $user['id'];
			// $avail_user_id = implode(',', $avail_user_id);

		
			// $invite->where('id' ,$is_referal['is_applicable_id'])->update(array('avail_used_id' => $avail_user_id));

	        if($gs->is_verification_email == 1)
	        {
	        $to = $request->email;
	        $subject = 'Verify your email address.';
	        $msg = "Dear Customer,<br> We noticed that you need to verify your email address. <a href=".url('user/register/verify/'.$token).">Simply click here to verify. </a>";
	        //Sending Email To Customer
	        if($gs->is_smtp == 1)
	        {
	        $data = [
	            'to' => $to,
	            'subject' => $subject,
	            'body' => $msg,
	        ];

	        $mailer = new GeniusMailer();
	        $mailer->sendCustomMail($data);
	        }
	        else
	        {
	        $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
	        mail($to,$subject,$msg,$headers);
	        }
          	return response()->json('We need to verify your email address. We have sent an email to '.$to.' to verify your email address. Please click link in that email to continue.');
	        }
	        else {

            $user->email_verified = 'Yes';
            $user->update();
	        $notification = new Notification;
	        $notification->user_id = $user->id;
	        $notification->save();
            Auth::guard('web')->login($user); 
          	return response()->json(1);
	        }

    }

    public function verifyReferralCode($refer_code){
		// $invite = new Invites();
		$user = new User;
		$data_array = array('refer_code'=> $refer_code);
		$is_applicable = User::where($data_array)->get()->toArray();
		if(isset($is_applicable) && !empty($is_applicable) && $is_applicable != '' ){
			//echo "<pre>";print_r($is_applicable); die;
			return array(
				'status' => '1'
				);
		}else{
			return array(
				'status' => '0');
		}
	}

    public function token($token)
    {
        $gs = Generalsetting::findOrFail(1);

        if($gs->is_verification_email == 1)
	        {    	
        $user = User::where('verification_link','=',$token)->first();
        if(isset($user))
        {
            $user->email_verified = 'Yes';
            $user->update();
	        $notification = new Notification;
	        $notification->user_id = $user->id;
	        $notification->save();
            Auth::guard('web')->login($user); 
            return redirect()->route('user-dashboard')->with('success','Email Verified Successfully');
        }
    		}
    		else {
    		return redirect()->back();	
    		}
    }
}