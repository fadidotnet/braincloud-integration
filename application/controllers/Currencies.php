<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Currencies extends REST_Controller {

    function __construct()
    {
		
        // Construct the parent class
        parent::__construct();
		require('TokenAuthentication.php');
		TokenAuthentication::authenticate_token(null,null,null);
    }
	
	public function add_tokens_history_log($type,$output_profile,$tokens)
	{
		$add_history_data=array();
		$fname="gameloot";
		$lname="user";
		if($output_profile && isset($output_profile['ALL_FNAME1']))
		$fname=$output_profile['ALL_FNAME1'];
		else
		$fname="gameloot";
		if($output_profile && isset($output_profile['ALL_LNAME1']))
		$lname=$output_profile['ALL_LNAME1'];
		else
		$lname="user";
		if(isset($output_profile['SHP_NM1_1'])):
		$name_parts=explode(",",$output_profile['SHP_NM1_1']);
		if(is_array($name_parts))
		{
	  		$fname=$name_parts[0];
	  		if(isset($name_parts[1]))
	  		$lname=$name_parts[1];
			else
			$lname="user";
	  	}
		else
		{
			$lname="user";
		}
		endif;
		if(is_array($lname))
		$lname="user";		
		$add_history_data['reason']="";
		$add_history_data['src']="";
		$add_history_data['member_id']=$output_profile['ALL_ID'];
		$add_history_data['user_name']=$output_profile['DSM_UID2'];
		$add_history_data['user_type']=$output_profile['STS_NM'];
		$add_history_data['email']=$output_profile['ALL_EML'];
		$add_history_data['fname']=$fname;
		$add_history_data['lname']=$lname;
		$add_history_data['datetime']=date('Y-m-d H:i:s');
		$add_history_data['tokens']=$tokens;
		$add_history_data['type']=$type;
		$add_history_data['total']=$output_profile['DSX_NUM1'];
		$add_history_data['remaining']=$output_profile['DSX_NUM3'];
		$add_history_data['used']=$output_profile['DSX_NUM2'];
		
		try
		{
        	$this->rest->db
            ->set($add_history_data)
            ->insert("gln_tokens_use_history");
			
			/*$this->response([
                'status' => TRUE,
				'message'=>"Log Added",
            ], REST_Controller::HTTP_CREATED);*/
			
		}
		catch (Exception $e)
		{
			
		  	$this->response([
                'status' => FALSE,
				'error_code'=>4011,
                'message' => 'Could not save the Log.Please contact support.'
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		
			
		}
		
	}
	
	public function add_tickets_history_log($type,$output_profile,$tickets)
	{
		$add_history_data=array();
		$fname="gameloot";
		$lname="user";
		if($output_profile && isset($output_profile['ALL_FNAME1']))
		$fname=$output_profile['ALL_FNAME1'];
		else
		$fname="gameloot";
		if($output_profile && isset($output_profile['ALL_LNAME1']))
		$lname=$output_profile['ALL_LNAME1'];
		else
		$lname="user";
		if(isset($output_profile['SHP_NM1_1'])):
		$name_parts=explode(",",$output_profile['SHP_NM1_1']);
		if(is_array($name_parts))
		{
	  		$fname=$name_parts[0];
	  		if(isset($name_parts[1]))
	  		$lname=$name_parts[1];
			else
			$lname="user";
	  	}
		else
		{
			$lname="user";
		}
		endif;
		if(is_array($lname))
		$lname="user";		
		$add_history_data['reason']="";
		$add_history_data['src']="";
		$add_history_data['member_id']=$output_profile['ALL_ID'];
		$add_history_data['user_name']=$output_profile['DSM_UID2'];
		$add_history_data['user_type']=$output_profile['STS_NM'];
		$add_history_data['email']=$output_profile['ALL_EML'];
		$add_history_data['fname']=$fname;
		$add_history_data['lname']=$lname;
		$add_history_data['datetime']=date('Y-m-d H:i:s');
		$add_history_data['tickets']=$tickets;
		$add_history_data['type']=$type;
		$add_history_data['total']=$output_profile['DSX_NUM4'];
		$add_history_data['remaining']=$output_profile['DSX_NUM6'];
		$add_history_data['used']=$output_profile['DSX_NUM5'];
		
		try
		{
        	$this->rest->db
            ->set($add_history_data)
            ->insert("gln_tickets_use_history");
			
		/*	$this->response([
                'status' => TRUE,
				'message'=>"Log Added",
            ], REST_Controller::HTTP_CREATED);*/
			
		}
		catch (Exception $e)
		{
			
		  	$this->response([
                'status' => FALSE,
				'error_code'=>4011,
                'message' => 'Could not save the tickets Log.Please contact support.'
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		
			
		}
		
	}
	
	
	public function consume_tokens_post()
	{
		$member_id=$this->post('member_id');
		$amount=$this->post('amount');
		$app_id=$this->post('app_id');
		$purchase_type=$this->post('purchase_type');
		$additional_data=$this->post('data');
		
		if( ! filter_var($amount, FILTER_VALIDATE_INT) ){
		  $this->response([
		  		'error_code'=>4020,
                'status' => FALSE,
                'message' => 'Invalid amount.'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		
		if ($amount< 0)
		{
   			$this->response([
				'error_code'=>4020,
                'status' => FALSE,
                'message' => "Invalid amount."
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		
		if($purchase_type=="iap" || $purchase_type=="tournament" || $purchase_type=="transfer")
		$pass=1;//////// 
		else
		{
			 $this->response([
			 	'error_code'=>4021,
                'status' => FALSE,
                'message' => 'Invalid purchase type.'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}	
			

		
		if(!$member_id)
        {
             $this->response([
			 	'error_code'=>4016,
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$amount)
        {
             $this->response([
			 	'error_code'=>4021,
                'status' => FALSE,
                'message' => 'amount is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$app_id)
        {
             $this->response([
			 'error_code'=>4022,
                'status' => FALSE,
                'message' => 'app_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		if(!$purchase_type)
        {
             $this->response([
			 	'error_code'=>4021,
                'status' => FALSE,
                'message' => 'purchase_type is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		$is_valid=TokenAuthentication::authenticate_token(null,null,null);
		if(!$is_valid)
		{
			$this->response([
                'status' => FALSE,
				'error_code'=>4012,
                'message' => 'authentication failed'
            ], REST_Controller::HTTP_BAD_REQUEST);
		
		}
		else
		{
				$this->consume_tokens($member_id,$amount,$app_id,$additional_data,$purchase_type);
			
		}
		
	}
	
	public function consume_tokens($member_id,$amount,$app_id,$additional_data,$purchase_type)
	{
		$fdata = "APIACTION=PROFILE";
    	$fdata .= "&ID=$member_id&UPDATE=N";		
		$output_profile=$this->imatrix_call($fdata);
		//die();
		if(isset($output_profile['ERROR1']))
		$this->response([
                'status' => FALSE,
				'error_code'=>4006,
                'message' =>$output_profile['ERROR1']
            	], REST_Controller::HTTP_BAD_REQUEST);
		//print_r($output_profile);
		$remaining_tokens=0;
		if(isset($output_profile['DSX_NUM1']) && $output_profile['DSX_NUM1'])
		$total_tokens=$output_profile['DSX_NUM1'];
		else
		$total_tokens=0;
		if(isset($output_profile['DSX_NUM2'])&& $output_profile['DSX_NUM2'])
		$used_tokens=$output_profile['DSX_NUM2'];
		else
		$used_tokens="0";
			
		if($output_profile['DSX_NUM3'] < 0)
		$this->response([
                'status' => FALSE,
				'error_code'=>4002,
                'message' =>"Insufficient Tokens."
            	], REST_Controller::HTTP_BAD_REQUEST);
		$remaining_tokens=(int)$total_tokens-(int)$used_tokens;
			if((int)$amount>$remaining_tokens)
			{
				$this->response([
                'status' => FALSE,
				'error_code'=>4002,
                'message' =>"Insufficient Tokens."
            	], REST_Controller::HTTP_BAD_REQUEST);
				
			}
		
			$used_tokens=(int)$used_tokens +(int)$amount;
			$remaining_tokens=(int)$total_tokens-(int)$used_tokens;
			
			$tdata = "APIACTION=PROFILE";
    		$tdata .="&ID=$member_id&DSX_NUM1=$total_tokens&DSX_NUM2=$used_tokens&DSX_NUM3=$remaining_tokens&UPDATE=Y";		
			$output_d=$this->imatrix_call($tdata);
			$remaining_tokens=$total_tokens-$output_d['DSX_NUM2'];

			$this->add_tokens_history_log("consume",$output_d,$amount);
			//------------------- SAVE TOKENS HISTORY --------------------

			$action = 'save_tokens_history';
			$user_name = $output_profile['DSM_UID2'];
			$email = $output_profile['ALL_EML'];
			$src =  'GLN BC API';
			$reason = $purchase_type . ' :: ' . $app_id;
			$no_of_tokens = (int)$amount;
			$remaining_tokens = $remaining_tokens;
			$action_type = 'SUBTRACT';

			if($app_id == 10278)
				$reason = "Tokens converted into BIDS from Loot Cove";
			
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://gamelootnetwork.com/manage-gln/requests/requests.php",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS =>  "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"action\"\r\n\r\nsave_tokens_history\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"member_id\"\r\n\r\n$member_id\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"user_name\"\r\n\r\n$user_name\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"email\"\r\n\r\n$email\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"src\"\r\n\r\n$src\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"reason\"\r\n\r\n$reason\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"no_of_tokens\"\r\n\r\n$no_of_tokens\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"remaining_tokens\"\r\n\r\n$remaining_tokens\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"action_type\"\r\n\r\n$action_type\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
			  CURLOPT_HTTPHEADER => array(
			    "cache-control: no-cache",
			    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);
			//------------------- END SAVE TOKENS HISTORY --------------------

			$this->response([
                'status' => TRUE,
				'message'=>$amount." Tokens Consumed",
				'tokens'=>(string)$remaining_tokens,
                'tickets' =>(string)$output_d['DSX_NUM6']], REST_Controller::HTTP_ACCEPTED);
			
		
	}
	
	public function consume_tickets_post()
	{
		$member_id=$this->post('member_id');
		$amount=$this->post('amount');
		$app_id=$this->post('app_id');
		$purchase_type=$this->post('purchase_type');
		$additional_data=$this->post('data');
		
		if( ! filter_var($amount, FILTER_VALIDATE_INT) ){
		  $this->response([
		  		'error_code'=>4020,
                'status' => FALSE,
                'message' => 'Invalid amount.'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		
		if ($amount< 0)
		{
   			$this->response([
				'error_code'=>4020,
                'status' => FALSE,
                'message' => "Invalid amount."
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		
		if($purchase_type=="iap" || $purchase_type=="tournament" || $purchase_type=="transfer")
		$pass=1;
			//////// 
		else
		{
			 $this->response([
			 	'error_code'=>4021,
                'status' => FALSE,
                'message' => 'Invalid purchase type.'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		
		
		
		if(!$member_id)
        {
             $this->response([
			 	'error_code'=>4016,
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$amount)
        {
             $this->response([
			 	'error_code'=>4020,
                'status' => FALSE,
                'message' => 'amount is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$app_id)
        {
             $this->response([
			 	'error_code'=>4022,
                'status' => FALSE,
                'message' => 'app_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		if(!$purchase_type)
        {
             $this->response([
			 	'error_code'=>4021,
                'status' => FALSE,
                'message' => 'purchase_type is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		$is_valid=TokenAuthentication::authenticate_token(null,null,null);
		if(!$is_valid)
		{
			$this->response([
                'status' => FALSE,
				'error_code'=>4012,
                'message' => 'authentication failed'
            ], REST_Controller::HTTP_BAD_REQUEST);
		
		}
		else
		{
				$this->consume_tickets($member_id,$amount,$app_id,$additional_data,$purchase_type);
			
		}
		
	}
	
	
	public function consume_tickets($member_id,$amount,$app_id,$additional_data,$purchase_type)
	{
		$fdata = "APIACTION=PROFILE";
    	$fdata .= "&ID=$member_id&UPDATE=N";		
		$output_profile=$this->imatrix_call($fdata);
		
		//die();
		if(isset($output_profile['ERROR1']))
		$this->response([
                'status' => FALSE,
				'error_code'=>4006,
                'message' =>$output_profile['ERROR1']
            	], REST_Controller::HTTP_BAD_REQUEST);
		//print_r($output_profile);
		$remaining_tickets=0;
		if(isset($output_profile['DSX_NUM4']) && $output_profile['DSX_NUM4'])
		$total_tickets=$output_profile['DSX_NUM4'];
		else
		$total_tickets=0;
		if(isset($output_profile['DSX_NUM5'])&& $output_profile['DSX_NUM5'])
		$used_tickets=$output_profile['DSX_NUM5'];
		else
		$used_tickets="0";
			
		if($output_profile['DSX_NUM6'] < 0)
		$this->response([
                'status' => FALSE,
				'error_code'=>4003,
                'message' =>"Insufficient tickets."
            	], REST_Controller::HTTP_BAD_REQUEST);
		$remaining_tickets=(int)$total_tickets-(int)$used_tickets;
			if((int)$amount>$remaining_tickets)
			{
				$this->response([
                'status' => FALSE,
				'error_code'=>4003,
                'message' =>"Insufficient tickets."
            	], REST_Controller::HTTP_BAD_REQUEST);
				
			}
		
			$used_tickets=(int)$used_tickets +(int)$amount;
			$remaining_tickets=(int)$total_tickets-(int)$used_tickets;
			/*$add_history_data=array();
			$add_history_data['tokens']=$amount;
			$add_history_data['type']="detect";
			$add_history_data['total']=$total_tokens;
			$add_history_data['remaining']=$remaining_tokens;
			$add_history_data['used']=$used_tokens;
			$add_history_data['reason']=$reason;
			$add_history_data['src']=$src_call;
			$add_history_data['member_id']=$output_profile['ALL_ID'];
			$this->add_tokens_history_data($add_history_data);*/
			
		
			
			$tdata = "APIACTION=PROFILE";
    		$tdata .="&ID=$member_id&DSX_NUM4=$total_tickets&DSX_NUM5=$used_tickets&DSX_NUM6=$remaining_tickets&UPDATE=Y";		
			$output_d=$this->imatrix_call($tdata);
			$remaining_tickets=$total_tickets-$output_d['DSX_NUM5'];
			
			$this->add_tickets_history_log("consume",$output_d,$amount);
			
			$this->response([
                'status' => TRUE,
				"message"=>$amount." Tickets Consumed",
				'tokens'=>(string)$output_d['DSX_NUM3'],
                'tickets' =>(string)$output_d['DSX_NUM6']], REST_Controller::HTTP_ACCEPTED);
			
		
	}
	
	public function add_tokens_post()
	{
		$member_id=$this->post('member_id');
		$amount=$this->post('amount');
		$app_id=$this->post('app_id');
		$purchase_type=$this->post('purchase_type');
		$additional_data=$this->post('data');
		
		if( ! filter_var($amount, FILTER_VALIDATE_INT) ){
		  $this->response([
		  		'error_code'=>4020,
                'status' => FALSE,
                'message' => 'Invalid amount.'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		
		if ($amount< 0)
		{
   			$this->response([
				'error_code'=>4020,
                'status' => FALSE,
                'message' => "Invalid amount."
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		
		if($purchase_type=="iap" || $purchase_type=="tournament" || $purchase_type=="transfer")
		$pass=1;
			//////// 
		else
		{
			 $this->response([
			 	'error_code'=>4021,
                'status' => FALSE,
                'message' => 'Invalid purchase type.'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		
		
		
		
		
		
		if(!$member_id)
        {
             $this->response([
			 	'error_code'=>4016,
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$amount)
        {
             $this->response([
			 	'error_code'=>4020,
                'status' => FALSE,
                'message' => 'amount is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$app_id)
        {
             $this->response([
			 	'error_code'=>4022,
                'status' => FALSE,
                'message' => 'app_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		if(!$purchase_type)
        {
             $this->response([
			 	'error_code'=>4021,
                'status' => FALSE,
                'message' => 'purchase_type is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		$is_valid=TokenAuthentication::authenticate_token(null,null,null);
		if(!$is_valid)
		{
			$this->response([
                'status' => FALSE,
				'error_code'=>4012,
                'message' => 'authentication failed'
            ], REST_Controller::HTTP_BAD_REQUEST);
		
		}
		else
		{
				$this->add_tokens($member_id,$amount,$app_id,$additional_data,$purchase_type,true);
			
		}
		
	}
	
	
	public function add_tokens($member_id,$amount,$app_id,$additional_data,$purchase_type,$show_response=true)
	{
		$fdata = "APIACTION=PROFILE";
    	$fdata .= "&ID=$member_id&UPDATE=N";		
		$output_profile=$this->imatrix_call($fdata);
		
		//die();
		if(isset($output_profile['ERROR1']))
		$this->response([
                'status' => FALSE,
				'error_code'=>4006,
                'message' =>$output_profile['ERROR1']
            	], REST_Controller::HTTP_BAD_REQUEST);
		//print_r($output_profile);
		$remaining_tokens=0;
		if(isset($output_profile['DSX_NUM1']) && $output_profile['DSX_NUM1'])
		$total_tokens=$output_profile['DSX_NUM1'];
		else
		$total_tokens=0;
		if(isset($output_profile['DSX_NUM2'])&& $output_profile['DSX_NUM2'])
		$used_tokens=$output_profile['DSX_NUM2'];
		else
		$used_tokens="0";
		if($amount==0)
		{
			
			$this->response([
                'status' => FALSE,
				'error_code'=>4002,
                'message' =>"Tokens can not add.please try later."
            	], REST_Controller::HTTP_BAD_REQUEST);
		}
			
          // $more_tokens=(int)$total_tokens+(int)$params['tokens_add'];
		 	$total_tokens=	(int)$total_tokens+(int)$amount;
			$remaining_tokens=(int)$total_tokens-(int)$used_tokens;
		//	$add_history_data['tokens']=$params['tokens_add'];
		//	$add_history_data['type']="add";
		//	$add_history_data['total']=$total_tokens;
		//	$add_history_data['remaining']=$remaining_tokens;
		//	$add_history_data['used']=$used_tokens;
		//	$this->add_tokens_history_data($add_history_data);
					
			$tdata = "APIACTION=PROFILE";
    		$tdata .="&ID=$member_id&DSX_NUM1=$total_tokens&DSX_NUM2=$used_tokens&DSX_NUM3=$remaining_tokens&UPDATE=Y";		
			$output_d=$this->imatrix_call($tdata);
			$remaining_tokens=$total_tokens-$output_d['DSX_NUM2'];
			
			$this->add_tokens_history_log("added",$output_d,$amount);

			//------------------- SAVE TOKENS HISTORY --------------------

			$action = 'save_tokens_history';
			$user_name = $output_profile['DSM_UID2'];
			$email = $output_profile['ALL_EML'];
			$src =  'GLN BC API';
			$reason = $purchase_type . ' :: ' . $app_id;
			$no_of_tokens = (int)$amount;
			$remaining_tokens = $remaining_tokens;
			$action_type = 'ADD';

			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://gamelootnetwork.com/manage-gln/requests/requests.php",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS =>  "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"action\"\r\n\r\nsave_tokens_history\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"member_id\"\r\n\r\n$member_id\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"user_name\"\r\n\r\n$user_name\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"email\"\r\n\r\n$email\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"src\"\r\n\r\n$src\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"reason\"\r\n\r\n$reason\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"no_of_tokens\"\r\n\r\n$no_of_tokens\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"remaining_tokens\"\r\n\r\n$remaining_tokens\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"action_type\"\r\n\r\n$action_type\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
			  CURLOPT_HTTPHEADER => array(
			    "cache-control: no-cache",
			    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);
			//------------------- END SAVE TOKENS HISTORY --------------------

			if($show_response)
			{
				$this->response([
                'status' => TRUE,
				"message"=>$amount." Tokens Added",
				'tokens'=>(string)$remaining_tokens,
                'tickets' =>(string)$output_d['DSX_NUM6']], REST_Controller::HTTP_CREATED);
			}
			else
			{
				return true;	
				
			}
	}
	
	public function add_tickets_post()
	{
		$member_id=$this->post('member_id');
		$amount=$this->post('amount');
		$app_id=$this->post('app_id');
		$purchase_type=$this->post('purchase_type');
		$additional_data=$this->post('data');
		
		if( ! filter_var($amount, FILTER_VALIDATE_INT) ){
		  $this->response([
		  		'error_code'=>4020,
                'status' => FALSE,
                'message' => 'Invalid amount.'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		
		if ($amount< 0)
		{
   			$this->response([
				'error_code'=>4020,
                'status' => FALSE,
                'message' => "Invalid amount."
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		
		if($purchase_type=="iap" || $purchase_type=="tournament" || $purchase_type=="transfer")
		$pass=1;
			//////// 
		else
		{
			 $this->response([
			 	'error_code'=>4021,
                'status' => FALSE,
                'message' => 'Invalid purchase type.'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		
		
		
		
		if(!$member_id)
        {
             $this->response([
			 	'error_code'=>4016,
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$amount)
        {
             $this->response([
			 	'error_code'=>4020,
                'status' => FALSE,
                'message' => 'amount is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$app_id)
        {
             $this->response([
			 	'error_code'=>4022,
                'status' => FALSE,
                'message' => 'app_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		if(!$purchase_type)
        {
             $this->response([
			 	'error_code'=>4021,
                'status' => FALSE,
                'message' => 'purchase_type is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		$is_valid=TokenAuthentication::authenticate_token(null,null,null);
		if(!$is_valid)
		{
			$this->response([
                'status' => FALSE,
				'error_code'=>4012,
                'message' => 'authentication failed'
            ], REST_Controller::HTTP_BAD_REQUEST);
		
		}
		else
		{
				$this->add_tickets($member_id,$amount,$app_id,$additional_data,$purchase_type,true);
			
		}
		
	}
	
	public function add_tickets($member_id,$amount,$app_id,$additional_data,$purchase_type,$show_response=true)
	{
		$fdata = "APIACTION=PROFILE";
    	$fdata .= "&ID=$member_id&UPDATE=N";		
		$output_profile=$this->imatrix_call($fdata);
		
		//die();
		if(isset($output_profile['ERROR1']))
		$this->response([
                'status' => FALSE,
				'error_code'=>4006,
                'message' =>$output_profile['ERROR1']
            	], REST_Controller::HTTP_BAD_REQUEST);
		//print_r($output_profile);
		$remaining_tickets=0;
		if(isset($output_profile['DSX_NUM4']) && $output_profile['DSX_NUM4'])
		$total_tickets=$output_profile['DSX_NUM4'];
		else
		$total_tickets=0;
		if(isset($output_profile['DSX_NUM5'])&& $output_profile['DSX_NUM5'])
		$used_tickets=$output_profile['DSX_NUM5'];
		else
		$used_tickets="0";
		if($amount==0)
		{
			
			$this->response([
                'status' => FALSE,
				'error_code'=>4014,
                'message' =>"Tickets can not add.please try later."
            	], REST_Controller::HTTP_BAD_REQUEST);
		}
			
          // $more_tokens=(int)$total_tokens+(int)$params['tokens_add'];
		 	$total_tickets=	(int)$total_tickets+(int)$amount;
			$remaining_tickets=(int)$total_tickets-(int)$used_tickets;
		//	$add_history_data['tokens']=$params['tokens_add'];
		//	$add_history_data['type']="add";
		//	$add_history_data['total']=$total_tokens;
		//	$add_history_data['remaining']=$remaining_tokens;
		//	$add_history_data['used']=$used_tokens;
		//	$this->add_tokens_history_data($add_history_data);
					
			$tdata = "APIACTION=PROFILE";
    		$tdata .="&ID=$member_id&DSX_NUM4=$total_tickets&DSX_NUM5=$used_tickets&DSX_NUM6=$remaining_tickets&UPDATE=Y";		
			$output_d=$this->imatrix_call($tdata);
			$remaining_tickets=$total_tickets-$output_d['DSX_NUM4'];
			
			$this->add_tickets_history_log("added",$output_d,$amount);
			
			if($show_response)
			{
				$this->response([
                'status' => TRUE,
				"message"=>$amount." Tickets Added",
				'tickets'=>(string)$output_d['DSX_NUM6'],
                'tokens' =>(string)$output_d['DSX_NUM3']], REST_Controller::HTTP_CREATED);
			}
			else
			{
				return true;	
			}
	}
	
	
	public function redeem_tickets_for_tokens_post()
	{
		$member_id=$this->post('member_id');
		$amount=$this->post('ticketsToRedeem');
		$app_id=$this->post('app_id');
		$additional_data=$this->post('data');
		
		if( ! filter_var($amount, FILTER_VALIDATE_INT) ){
		  $this->response([
		  		'error_code'=>4020,
                'status' => FALSE,
                'message' => 'Invalid amount.'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		
		if ($amount< 0)
		{
   			$this->response([
				'error_code'=>4020,
                'status' => FALSE,
                'message' => "Invalid amount."
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		
		if(!$member_id)
        {
             $this->response([
			 	'error_code'=>4016,
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$amount)
        {
             $this->response([
			 	'error_code'=>4022,
                'status' => FALSE,
                'message' => 'ticketsToRedeem is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$app_id)
        {
             $this->response([
			 	'error_code'=>4022,
                'status' => FALSE,
                'message' => 'app_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		$is_valid=TokenAuthentication::authenticate_token(null,null,null);
		if(!$is_valid)
		{
			$this->response([
                'status' => FALSE,
				'error_cdoe'=>4012,
                'message' => 'authentication failed'
            ], REST_Controller::HTTP_BAD_REQUEST);
		
		}
		else
		{
				$this->convert_tickets_to_tokens($member_id,$amount,$app_id,$additional_data);
			
		}
		
	}
	
	public function convert_tickets_to_tokens($member_id,$amount,$app_id,$additional_data)
	{
		
	
		$fdata = "APIACTION=PROFILE";
    	$fdata .= "&ID=$member_id&UPDATE=N";		
		$output_profile=$this->imatrix_call($fdata);
		
		//die();
		if(isset($output_profile['ERROR1']))
		$this->response([
                'status' => FALSE,
				'error_code'=>4006,
                'message' =>$output_profile['ERROR1']
            	], REST_Controller::HTTP_BAD_REQUEST);
		//print_r($output_profile);
		if(isset($output_profile['DSX_NUM4']) && $output_profile['DSX_NUM4'])
		$total_tickets=$output_profile['DSX_NUM4'];
		else
		$total_tickets=0;
		if(isset($output_profile['DSX_NUM5'])&& $output_profile['DSX_NUM5'])
		$used_tickets=$output_profile['DSX_NUM5'];
		else
		$used_tickets="0";
		
		if(isset($output_profile['DSX_NUM6'])&& $output_profile['DSX_NUM6'])
		$remaining_tickets=$output_profile['DSX_NUM6'];
		else
		$used_tokens="0";
		
		
			
			
		if($amount % 25 == 0)
		{
			
			 $cl=$amount/ 25;
      		if($remaining_tickets > 25 && $remaining_tickets>=$amount)
      		{
				
				
				
				if($output_profile['DSX_NUM6'] < 0)
		$this->response([
                'status' => FALSE,
				'error_code'=>4003,
                'message' =>"Sorry you do not have not enough Tickets. Please contact support."
            	], REST_Controller::HTTP_BAD_REQUEST);
			$remaining_tickets=(int)$total_tickets-(int)$used_tickets;
			if((int)$amount>$remaining_tickets)
			{
				$this->response([
                'status' => FALSE,
				'error_code'=>4003,
                'message' =>"Sorry you do not have not enough Tickets. Please contact support."
            	], REST_Controller::HTTP_BAD_REQUEST);
				
			}
		
			$used_tickets=(int)$used_tickets +(int)$amount;
			$remaining_tickets=(int)$total_tickets-(int)$used_tickets;
			/*$add_history_data=array();
			$add_history_data['tokens']=$amount;
			$add_history_data['type']="detect";
			$add_history_data['total']=$total_tokens;
			$add_history_data['remaining']=$remaining_tokens;
			$add_history_data['used']=$used_tokens;
			$add_history_data['reason']=$reason;
			$add_history_data['src']=$src_call;
			$add_history_data['member_id']=$output_profile['ALL_ID'];
			$this->add_tokens_history_data($add_history_data);*/
			
			
			
			// we need add tokens in user database so we deduct tickets and add tokens in user account
			
			$remaining_tokens=0;
		if(isset($output_profile['DSX_NUM1']) && $output_profile['DSX_NUM1'])
		$total_tokens=$output_profile['DSX_NUM1'];
		else
		$total_tokens=0;
		if(isset($output_profile['DSX_NUM2'])&& $output_profile['DSX_NUM2'])
		$used_tokens=$output_profile['DSX_NUM2'];
		else
		$used_tokens="0";
		/*if($cl==0 || $output_profile['DSX_NUM6']< 0 || $remaining_tickets < 25)
		{
			$this->response([
                'status' => FALSE,
				'error_code'=>4013,
                'message' =>"Tokens can not be added. Please try later."
            	], REST_Controller::HTTP_BAD_REQUEST);
		}*/
			
          // $more_tokens=(int)$total_tokens+(int)$params['tokens_add'];
		 	$total_tokens=	(int)$total_tokens+(int)$cl;
			$remaining_tokens=(int)$total_tokens-(int)$used_tokens;
			
		
			
			$tdata = "APIACTION=PROFILE";
    		$tdata .="&ID=$member_id&DSX_NUM1=$total_tokens&DSX_NUM2=$used_tokens&DSX_NUM3=$remaining_tokens&DSX_NUM4=$total_tickets&DSX_NUM5=$used_tickets&DSX_NUM6=$remaining_tickets&UPDATE=Y";		
			$output_d=$this->imatrix_call($tdata);
			$remaining_tickets=$total_tickets-$output_d['DSX_NUM5'];

			
			$this->response([
                'status' => TRUE,
				"message"=>$amount." Tickets Redeemed.",
				'tokens'=>(string)$output_d['DSX_NUM3'],
                'tickets' =>(string)$output_d['DSX_NUM6']], REST_Controller::HTTP_CREATED);
				
				
			}
			else
			{
				$this->response([
                'status' => FALSE,
				'error_code'=>4003,
                'message' =>"Insufficient tickets."
            	], REST_Controller::HTTP_BAD_REQUEST);
				
				
			}
			
			
			
		}
		else
		{
				$this->response([
                'status' => FALSE,
				'error_code'=>4005,
                'message' =>"Value must be in multiples of 25."
            	], REST_Controller::HTTP_BAD_REQUEST);
			
		}
			
			
		
	}
	
	public function get_token_cost_in_tickets_post()
	{
		
			$this->response([
                'status' => TRUE,
				"tokenCostInTickets"=>(string)25], REST_Controller::HTTP_OK);
		
	}
	
	public function get_currency_balances_post()
	{
	
		$member_id=$this->post('member_id');	
		if( ! filter_var($member_id, FILTER_VALIDATE_INT) ){
		  $this->response([
		  		'error_code'=>4016,
                'status' => FALSE,
                'message' => 'Invalid member id'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
			
		if(!$member_id)
        {
             $this->response([
			 	'error_code'=>4016,
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		$fdata = "APIACTION=PROFILE";
    	$fdata .= "&ID=$member_id&UPDATE=N";		
		$output_profile=$this->imatrix_call($fdata);
		
		//print_r($output_profile);
		//die();
		if(isset($output_profile['ERROR1']))
		$this->response([
                'status' => FALSE,
				'error_code'=>4006,
                'message' =>$output_profile['ERROR1']
            	], REST_Controller::HTTP_BAD_REQUEST);
			$this->response([
                'status' => TRUE,
				'tickets'=>(string)$output_profile['DSX_NUM6'],
                'tokens' =>(string)$output_profile['DSX_NUM3'],
				'used_tickets'=>(string)$output_profile['DSX_NUM5'],
				'used_tokens'=>(string)$output_profile['DSX_NUM2'],
				'all_time_tickets'=>(string)$output_profile['DSX_NUM4'],
				'all_time_tokens'=>(string)$output_profile['DSX_NUM1'],
				
				], REST_Controller::HTTP_OK);
	}
		
}
