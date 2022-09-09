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
class User extends REST_Controller {

    function __construct()
    {
		
        // Construct the parent class
        parent::__construct();

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
		//$this->load->file('TokenAuthentication.php', false);
		require('TokenAuthentication.php');	
		
    }
	
	public function authenticate_post()
	{
		$username=$this->post('userid');
		$password=$this->post('password');
		
		if(!$username)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'userid is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		if(!$password)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'password is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		$data="";
   		$data .= "APIACTION=LOGIN";
    	$data .= "&USER=$username&PASS=$password";
		$imatrix_response=$this->imatrix_call($data);
		
		if(!isset($imatrix_response['LOGIN']))
		{
			$this->response([
                'status' => FALSE,
                'message' => 'Invalid username/password'
            ], REST_Controller::HTTP_BAD_REQUEST);
			
		}
		else
		{
			
			$member_id=$imatrix_response['DSM_DST_RCN'];
			$session_id=$imatrix_response['SESSION'];
			
			$this->response([
                'status' => TRUE,
				'message'=>"Login Successfully",
                'imatrix_id' => $member_id,
				'session_id'=>$session_id,
            ], REST_Controller::HTTP_CREATED);
					
		}
		
		
		
				
		
	}
	
	public function get_profile_post()
	{
		
		$member_id=$this->post('member_id');
		$session_id=$this->post('session_id');
		if(!$member_id)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		if(!$session_id)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'session_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		$is_valid=TokenAuthentication::authenticate_token(null,$session_id,null);
		
		if(!$is_valid)
		{
			if(!isset($imatrix_response['LOGIN']))
			{
				$this->response([
                'status' => FALSE,
                'message' => 'Sorry token is invalid'
            	], REST_Controller::HTTP_BAD_REQUEST);
			
			}	
			
		}
		
    	$fdata= "APIACTION=PROFILE";
   
    	$fdata .= "&ID=$member_id&0UPDATE=N";		
		$output_profile=$this->imatrix_call($fdata);
		
		/*"session"=>$output_profile["SESSION"],*/
		$data=array(
		"member_id"=>$output_profile["USER"],
		"member_firstname"=>$output_profile["ALL_FNAME1"],
		"member_lastname"=>$output_profile["ALL_LNAME1"],
		"member_full_name"=>$output_profile["ALL_NM"],
		"member_address1"=>$output_profile["ALL_ADR1"],
		"member_country"=>$output_profile["ALL_COU"],
		"member_city"=>$output_profile["ALL_CITY"],
		"member_state"=>$output_profile["ALL_STATE"],
		"member_zip"=>$output_profile["ALL_ZIP"],
		"member_phone"=>$output_profile["ALL_RNK"],
		"member_email"=>$output_profile["ALL_EML"],
		"member_username"=>$output_profile["DSM_UID2"],
		"member_rank"=>$output_profile["RNK_NM"],
		"member_status"=>$output_profile["STS_NM"],
		"member_status_rank"=>$output_profile["ALL_RNK"],
		"member_status_flag"=>$output_profile["ALL_STS"],
		"member_all_tokens"=>$output_profile["DSX_NUM1"],
		"member_used_tokens"=>$output_profile["DSX_NUM2"],
		"member_remaining_tokens"=>$output_profile["DSX_NUM3"],
		"sponsor_firstname"=>$output_profile["RCR_FNM1"],
		"sponsor_lastname"=>$output_profile["RCR_LNM1"],
		"sponsor_full_name"=>$output_profile["SPN_NM"],
		"sponsor_email"=>$output_profile["SPN_EML_ADR"],
		"sponsor_username"=>$output_profile["SPN_UID2"]);
		
		if(!isset($output_profile) && !is_array($output_profile))
		$this->set_response(array("status"=>FALSE,"message"=>"Bad Request"), REST_Controller::HTTP_BAD_REQUEST);
		else
		$this->set_response(array("status"=>true,"message"=>"data loaded","data"=>$data), REST_Controller::HTTP_OK);
	}
	
	public function refresh_user_profile_post()
	{
		$member_id=$this->post('member_id');
		if(!$member_id)
        {
             $this->response([
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		$is_valid=TokenAuthentication::authenticate_token(null,null,null);
		
		if(!$is_valid)
		{
			if(!isset($imatrix_response['LOGIN']))
			{
				$this->response([
                'status' => FALSE,
                'message' => 'Sorry token is invalid'
            	], REST_Controller::HTTP_BAD_REQUEST);
			
			}	
			
		}
		
    	$fdata= "APIACTION=PROFILE";
   
    	$fdata .= "&ID=$member_id&0UPDATE=N";		
		$output_profile=$this->imatrix_call($fdata);
		
		/*"session"=>$output_profile["SESSION"],*/
		$data=array(
		"member_id"=>$output_profile["USER"],
		"member_firstname"=>$output_profile["ALL_FNAME1"],
		"member_lastname"=>$output_profile["ALL_LNAME1"],
		"member_full_name"=>$output_profile["ALL_NM"],
		"member_address1"=>$output_profile["ALL_ADR1"],
		"member_country"=>$output_profile["ALL_COU"],
		"member_city"=>$output_profile["ALL_CITY"],
		"member_state"=>$output_profile["ALL_STATE"],
		"member_zip"=>$output_profile["ALL_ZIP"],
		"member_phone"=>$output_profile["ALL_RNK"],
		"member_email"=>$output_profile["ALL_EML"],
		"member_username"=>$output_profile["DSM_UID2"],
		"member_rank"=>$output_profile["RNK_NM"],
		"member_status"=>$output_profile["STS_NM"],
		"member_status_rank"=>$output_profile["ALL_RNK"],
		"member_status_flag"=>$output_profile["ALL_STS"],
		"member_all_tokens"=>$output_profile["DSX_NUM1"],
		"member_used_tokens"=>$output_profile["DSX_NUM2"],
		"member_remaining_tokens"=>$output_profile["DSX_NUM3"],
		"sponsor_firstname"=>$output_profile["RCR_FNM1"],
		"sponsor_lastname"=>$output_profile["RCR_LNM1"],
		"sponsor_full_name"=>$output_profile["SPN_NM"],
		"sponsor_email"=>$output_profile["SPN_EML_ADR"],
		"sponsor_username"=>$output_profile["SPN_UID2"]);
		
		if(!isset($output_profile) && !is_array($output_profile))
		$this->set_response(array("status"=>FALSE,"message"=>"Bad Request"), REST_Controller::HTTP_BAD_REQUEST);
		else
		$this->set_response(array("status"=>true,"message"=>"data loaded","data"=>$data), REST_Controller::HTTP_OK);
		
	}
	
	

    public function users_get()
    {
        // Users from a data store e.g. database
        $users = [
            ['id' => 1, 'name' => 'John', 'email' => 'john@example.com', 'fact' => 'Loves coding'],
            ['id' => 2, 'name' => 'Jim', 'email' => 'jim@example.com', 'fact' => 'Developed on CodeIgniter'],
            ['id' => 3, 'name' => 'Jane', 'email' => 'jane@example.com', 'fact' => 'Lives in the USA', ['hobbies' => ['guitar', 'cycling']]],
        ];

        $id = $this->get('id');

        // If the id parameter doesn't exist return all the users

        if ($id === NULL)
        {
            // Check if the users data store contains users (in case the database result returns NULL)
            if ($users)
            {
                // Set the response and exit
                $this->response($users, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
            else
            {
                // Set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'No users were found'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
        }

        // Find and return a single record for a particular user.

        $id = (int) $id;

        // Validate the id.
        if ($id <= 0)
        {
            // Invalid id, set the response and exit.
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // Get the user from the array, using the id as key for retrieval.
        // Usually a model is to be used for this.

        $user = NULL;

        if (!empty($users))
        {
            foreach ($users as $key => $value)
            {
                if (isset($value['id']) && $value['id'] === $id)
                {
                    $user = $value;
                }
            }
        }

        if (!empty($user))
        {
            $this->set_response($user, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->set_response([
                'status' => FALSE,
                'message' => 'User could not be found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function users_post()
    {
        // $this->some_model->update_user( ... );
        $message = [
            'id' => 100, // Automatically generated by the model
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'message' => 'Added a resource'
        ];

        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    public function users_delete()
    {
        $id = (int) $this->get('id');

        // Validate the id.
        if ($id <= 0)
        {
            // Set the response and exit
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // $this->some_model->delete_something($id);
        $message = [
            'id' => $id,
            'message' => 'Deleted the resource'
        ];

        $this->set_response($message, REST_Controller::HTTP_NO_CONTENT); // NO_CONTENT (204) being the HTTP response code
    }

}
