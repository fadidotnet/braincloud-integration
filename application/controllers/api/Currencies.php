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
	
	public function consume_tokens_post()
	{
		$member_id=$this->post('member_id');
		$amount=$this->post('amount');
		$app_id=$this->post('app_id');
		$purchase_type=$this->post('purchase_type');
		$additional_data=$this->post('data');
		
		
		if(!$member_id)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$amount)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'amount is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$app_id)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'app_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		if(!$purchase_type)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'purchase_type is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		$is_valid=TokenAuthentication::authenticate_token(null,null,null);
		if(!$is_valid)
		{
			$this->response([
                'status' => FALSE,
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
		if(!is_array($output_profile))
		$this->response([
                'status' => FALSE,
                'message' =>"This user does not exist. That's all we know. Contact Game Loot Network Customer Support for further details."
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
                'message' =>"Sorry you have not enough tokens please contact support."
            	], REST_Controller::HTTP_BAD_REQUEST);
		$remaining_tokens=(int)$total_tokens-(int)$used_tokens;
			if((int)$amount>$remaining_tokens)
			{
				$this->response([
                'status' => FALSE,
                'message' =>"Sorry you have not enough tokens.please contact support"
            	], REST_Controller::HTTP_BAD_REQUEST);
				
			}
		
			$used_tokens=(int)$used_tokens +(int)$amount;
			$remaining_tokens=(int)$total_tokens-(int)$used_tokens;
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
    		$tdata .="&ID=$member_id&DSX_NUM1=$total_tokens&DSX_NUM2=$used_tokens&DSX_NUM3=$remaining_tokens&UPDATE=Y";		
			$output_d=$this->imatrix_call($tdata);
			$remaining_tokens=$total_tokens-$output_d['DSX_NUM2'];

			
			$this->response([
                'status' => TRUE,
				'message'=>$amount." Tokens Consumed",
				'tokens'=>$remaining_tokens,
                'tickets' =>$output_d['DSX_NUM6']], REST_Controller::HTTP_CREATED);
			
		
	}
	
	public function consume_tickets_post()
	{
		$member_id=$this->post('member_id');
		$amount=$this->post('amount');
		$app_id=$this->post('app_id');
		$purchase_type=$this->post('purchase_type');
		$additional_data=$this->post('data');
		
		
		if(!$member_id)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$amount)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'amount is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$app_id)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'app_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		if(!$purchase_type)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'purchase_type is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		$is_valid=TokenAuthentication::authenticate_token(null,null,null);
		if(!$is_valid)
		{
			$this->response([
                'status' => FALSE,
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
		if(!is_array($output_profile))
		$this->response([
                'status' => FALSE,
                'message' =>"This user does not exist. That's all we know. Contact Game Loot Network Customer Support for further details."
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
                'message' =>"Sorry you have not enough tickets please contact support."
            	], REST_Controller::HTTP_BAD_REQUEST);
		$remaining_tickets=(int)$total_tickets-(int)$used_tickets;
			if((int)$amount>$remaining_tickets)
			{
				$this->response([
                'status' => FALSE,
                'message' =>"Sorry you have not enough tickets.please contact support"
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

			
			$this->response([
                'status' => TRUE,
				"message"=>$amount." Tickets Consumed",
				'tokens'=>$output_d['DSX_NUM3'],
                'tickets' =>$output_d['DSX_NUM6']], REST_Controller::HTTP_CREATED);
			
		
	}
	
	public function add_tokens_post()
	{
		$member_id=$this->post('member_id');
		$amount=$this->post('amount');
		$app_id=$this->post('app_id');
		$purchase_type=$this->post('purchase_type');
		$additional_data=$this->post('data');
		
		
		if(!$member_id)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$amount)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'amount is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$app_id)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'app_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		if(!$purchase_type)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'purchase_type is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		$is_valid=TokenAuthentication::authenticate_token(null,null,null);
		if(!$is_valid)
		{
			$this->response([
                'status' => FALSE,
                'message' => 'authentication failed'
            ], REST_Controller::HTTP_BAD_REQUEST);
		
		}
		else
		{
				$this->add_tokens($member_id,$amount,$app_id,$additional_data,$purchase_type);
			
		}
		
	}
	
	
	public function add_tokens($member_id,$amount,$app_id,$additional_data,$purchase_type)
	{
		$fdata = "APIACTION=PROFILE";
    	$fdata .= "&ID=$member_id&UPDATE=N";		
		$output_profile=$this->imatrix_call($fdata);
		
		//die();
		if(!is_array($output_profile))
		$this->response([
                'status' => FALSE,
                'message' =>"This user does not exist. That's all we know. Contact Game Loot Network Customer Support for further details."
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
			$this->response([
                'status' => TRUE,
				"message"=>$amount." Tokens Added",
				'tokens'=>$remaining_tokens,
                'tickets' =>$output_d['DSX_NUM6']], REST_Controller::HTTP_CREATED);
	}
	
	
	public function redeem_tickets_for_tokens_post()
	{
		$member_id=$this->post('member_id');
		$amount=$this->post('ticketsToRedeem');
		$app_id=$this->post('app_id');
		$additional_data=$this->post('data');
		if(!$member_id)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$amount)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'ticketsToRedeem is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$app_id)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'app_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		$is_valid=TokenAuthentication::authenticate_token(null,null,null);
		if(!$is_valid)
		{
			$this->response([
                'status' => FALSE,
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
		if(!is_array($output_profile))
		$this->response([
                'status' => FALSE,
                'message' =>"This user does not exist. That's all we know. Contact Game Loot Network Customer Support for further details."
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
                'message' =>"Sorry you have not enough tickets please contact support."
            	], REST_Controller::HTTP_BAD_REQUEST);
			$remaining_tickets=(int)$total_tickets-(int)$used_tickets;
			if((int)$amount>$remaining_tickets)
			{
				$this->response([
                'status' => FALSE,
                'message' =>"Sorry you have not enough tickets.please contact support"
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
		if($cl==0)
		{
			
			$this->response([
                'status' => FALSE,
                'message' =>"Tokens can not add.please try later."
            	], REST_Controller::HTTP_BAD_REQUEST);
		}
			
          // $more_tokens=(int)$total_tokens+(int)$params['tokens_add'];
		 	$total_tokens=	(int)$total_tokens+(int)$amount;
			$remaining_tokens=(int)$total_tokens-(int)$used_tokens;
			
		
			
			$tdata = "APIACTION=PROFILE";
    		$tdata .="&ID=$member_id&DSX_NUM1=$total_tokens&DSX_NUM2=$used_tokens&DSX_NUM3=$remaining_tokens&DSX_NUM4=$total_tickets&DSX_NUM5=$used_tickets&DSX_NUM6=$remaining_tickets&UPDATE=Y";		
			$output_d=$this->imatrix_call($tdata);
			$remaining_tickets=$total_tickets-$output_d['DSX_NUM5'];

			
			$this->response([
                'status' => TRUE,
				"message"=>$amount." Tickets Consumed",
				'tokens'=>$output_d['DSX_NUM3'],
                'tickets' =>$output_d['DSX_NUM6']], REST_Controller::HTTP_CREATED);
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
			}
			else
			{
				$this->response([
                'status' => FALSE,
                'message' =>"Sorry you have not enough Tickets please contact support."
            	], REST_Controller::HTTP_BAD_REQUEST);
				
				
			}
			
			
			
		}
		else
		{
				$this->response([
                'status' => FALSE,
                'message' =>"Value must be in multiples of 25."
            	], REST_Controller::HTTP_BAD_REQUEST);
			
		}
			
			
		if($output_profile['DSX_NUM3'] < 0)
		$this->response([
                'status' => FALSE,
                'message' =>"Sorry you have not enough tokens please contact support."
            	], REST_Controller::HTTP_BAD_REQUEST);
		$remaining_tokens=(int)$total_tokens-(int)$used_tokens;
			if((int)$amount>$remaining_tokens)
			{
				$this->response([
                'status' => FALSE,
                'message' =>"Sorry you have not enough tokens.please contact support"
            	], REST_Controller::HTTP_BAD_REQUEST);
				
			}
		
			$used_tokens=(int)$used_tokens +(int)$amount;
			$remaining_tokens=(int)$total_tokens-(int)$used_tokens;
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
    		$tdata .="&ID=$member_id&DSX_NUM1=$total_tokens&DSX_NUM2=$used_tokens&DSX_NUM3=$remaining_tokens&UPDATE=Y";		
			$output_d=$this->imatrix_call($tdata);
			$remaining_tokens=$total_tokens-$output_d['DSX_NUM2'];

			
			$this->response([
                'status' => TRUE,
				'tokens'=>$remaining_tokens,
                'tickets' =>0], REST_Controller::HTTP_CREATED);
			
		
	}
	
	public function add_tickets_post()
	{
		$member_id=$this->post('member_id');
		$amount=$this->post('amount');
		$app_id=$this->post('app_id');
		$purchase_type=$this->post('purchase_type');
		$additional_data=$this->post('data');
		
		
		if(!$member_id)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$amount)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'amount is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$app_id)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'app_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		if(!$purchase_type)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'purchase_type is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		$is_valid=TokenAuthentication::authenticate_token(null,null,null);
		if(!$is_valid)
		{
			$this->response([
                'status' => FALSE,
                'message' => 'authentication failed'
            ], REST_Controller::HTTP_BAD_REQUEST);
		
		}
		else
		{
				$this->add_tickets($member_id,$amount,$app_id,$additional_data,$purchase_type);
			
		}
		
	}
	
	public function add_tickets($member_id,$amount,$app_id,$additional_data,$purchase_type)
	{
		$fdata = "APIACTION=PROFILE";
    	$fdata .= "&ID=$member_id&UPDATE=N";		
		$output_profile=$this->imatrix_call($fdata);
		
		//die();
		if(!is_array($output_profile))
		$this->response([
                'status' => FALSE,
                'message' =>"This user does not exist. That's all we know. Contact Game Loot Network Customer Support for further details."
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
			$this->response([
                'status' => TRUE,
				"message"=>$amount." Tickets Added",
				'tickets'=>$output_d['DSX_NUM6'],
                'tokens' =>$output_d['DSX_NUM3']], REST_Controller::HTTP_CREATED);
	}
	
	public function get_token_cost_in_tickets_get()
	{
		
			$this->response([
                'status' => TRUE,
				"tokenCostInTickets"=>25], REST_Controller::HTTP_CREATED);
		
	}
	
	public function get_currency_balances_post()
	{
		
		$member_id=$this->post('member_id');		
		if(!$member_id)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		$fdata = "APIACTION=PROFILE";
    	$fdata .= "&ID=$member_id&UPDATE=N";		
		$output_profile=$this->imatrix_call($fdata);
		//die();
		if(!is_array($output_profile))
		$this->response([
                'status' => FALSE,
                'message' =>"This user does not exist. That's all we know. Contact Game Loot Network Customer Support for further details."
            	], REST_Controller::HTTP_BAD_REQUEST);
			$this->response([
                'status' => TRUE,
				'tickets'=>$output_profile['DSX_NUM6'],
                'tokens' =>$output_profile['DSX_NUM3'],
				'used_tickets'=>$output_profile['DSX_NUM5'],
				'used_tokens'=>$output_profile['DSX_NUM2'],
				'all_time_tickets'=>$output_profile['DSX_NUM4'],
				'all_time_tokens'=>$output_profile['DSX_NUM1'],
				
				], REST_Controller::HTTP_CREATED);
	}
	
	
	
	
}
