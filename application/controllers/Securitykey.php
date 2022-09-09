<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

/**
 * Keys Controller
 * This is a basic Key Management REST controller to make and delete keys
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Securitykey extends REST_Controller {

    protected $methods = [
            'index_put' => ['level' => 10, 'limit' => 10],
            'index_delete' => ['level' => 10],
            'level_post' => ['level' => 10],
            'regenerate_post' => ['level' => 10],
        ];

    /**
     * Insert a key into the database
     *
     * @access public
     * @return void
     */
	 
	  function __construct()
    {
        // Construct the parent class
        parent::__construct();
		
    }
	
	/**
	* Verify a token is valid or not
	* @ccess Public
	* @access public
	*@return void
	
	*/ 
	
	
	public function verify_user_security_key_post()
	{
		 $ci = get_instance();
		 
		 $member_id=$this->post('member_id');
		$security_key=$this->post('security_key');	
		if(!$member_id)
        {
             $this->response([
                'status' => FALSE,
				'error_code'=>4016,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if( ! filter_var($member_id, FILTER_VALIDATE_INT) ){
		  $this->response([
                'status' => FALSE,
				'error_code'=>4016,
                'message' => 'Invalid member_id.'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		 	
		$is_valid=$ci->rest->db
            ->where(array(config_item('rest_key_column')=>$security_key,"user_id"=>$member_id))->count_all_results(config_item('rest_keys_table')) > 0;
			
			if($is_valid)
			{
				 $this->response([
                'status' => true,
                'message' => 'Valid security key'
            ], REST_Controller::HTTP_ACCEPTED);
				
				
			}
			else
			{
				 $this->response([
                'status' => false,
				'error_code'=>4007,
                'message' => 'Invalid security key'
            ], REST_Controller::HTTP_BAD_REQUEST);
				
			}
			
		
	}
	 
	 
    public function get_user_security_key_post()
    {
		 // If no key level provided, provide a generic key
		  $ci = get_instance();
        $level = $this->post('level') ? $this->post('level') : 1;
        $ignore_limits = ctype_digit($this->put('ignore_limits')) ? (int) $this->put('ignore_limits') : 1;
		$ip_address=$_SERVER['REMOTE_ADDR'];
		$user_id=$this->post('member_id');
		$session_id=$this->post('session_id');	
		if(!$user_id)
        {
             $this->response([
			 	'error_code'=>4016,
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		if(!$session_id)
        {
             $this->response([
			 	'error_code'=>4017,
                'status' => FALSE,
                'message' => 'session_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		$is_key_exists=$ci->rest->db
            ->where(array("session_id"=>$session_id,"user_id"=>$user_id))->count_all_results(config_item('rest_keys_table')) > 0;
		
		
		
		
		if($is_key_exists)
		{
			 $existing_key=$this->rest->db
            ->where(array("session_id"=>$session_id,"user_id"=>$user_id))
            ->get(config_item('rest_keys_table'))
            ->row();
			$this->response([
                'status' => true,
                'key' =>$existing_key->key], REST_Controller::HTTP_OK);
		}
		
		
		// if new session come then we need update the session and key as well need apply this logic
		
		
			//$data="";
			$data= "APIACTION=CHECK_SESSION";
    		$data .= "&SESSION_ID=$session_id";
		
		$imatrix_response=$this->imatrix_call($data);
		
		//print_r($imatrix_response);
		if(isset($imatrix_response['SESS_VLD']) &&  $imatrix_response['SESS_VLD']=="Y")
		{
			$member_id=$imatrix_response['SESS_ID'];
			
			if($member_id!=$user_id)
			{
				$this->response([
				'error_code'=>4008,
                'status' => FALSE,
                'message' => 'Bad request',
            ], REST_Controller::HTTP_BAD_REQUEST);
				
		}
			
			
		$key = $this->_generate_key();
        if ($this->_insert_key($key, ['level' => $level, 'ignore_limits' => $ignore_limits,"ip_addresses"=>$ip_address,"user_id"=>$user_id,"session_id"=>$session_id]))
        {
            $this->response([
                'status' => TRUE,
                'key' => $key
            ], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
        }
        else
        {
            $this->response([
				'error_code'=>4011,
                'status' => FALSE,
                'message' => 'Could not generate the key.Please contact support'
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR); // INTERNAL_SERVER_ERROR (500) being the HTTP response code
        }
			
			
			
			
		}
		else
		{
			   $this->response([
			   	'error_code'=>4009,
                'status' => FALSE,
                'message' => 'Invalid Session ID'
            ], REST_Controller::HTTP_BAD_REQUEST);
			
		}
       
    }
	
	

    /**
     * Remove a key from the database to stop it working
     *
     * @access public
     * @return void
     */
    public function index_delete()
    {
        $key = $this->delete('key');

        // Does this key exist?
        if (!$this->_key_exists($key))
        {
            // It doesn't appear the key exists
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid API key'
            ], REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // Destroy it
        $this->_delete_key($key);

        // Respond that the key was destroyed
        $this->response([
            'status' => TRUE,
            'message' => 'API key was deleted'
            ], REST_Controller::HTTP_NO_CONTENT); // NO_CONTENT (204) being the HTTP response code
    }

    /**
     * Change the level
     *
     * @access public
     * @return void
     */
    public function level_post()
    {
        $key = $this->post('key');
        $new_level = $this->post('level');

        // Does this key exist?
        if (!$this->_key_exists($key))
        {
            // It doesn't appear the key exists
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid API key'
            ], REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // Update the key level
        if ($this->_update_key($key, ['level' => $new_level]))
        {
            $this->response([
                'status' => TRUE,
                'message' => 'API key was updated'
            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->response([
                'status' => FALSE,
                'message' => 'Could not update the key level'
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR); // INTERNAL_SERVER_ERROR (500) being the HTTP response code
        }
    }

    /**
     * Suspend a key
     *
     * @access public
     * @return void
     */
    public function suspend_post()
    {
        $key = $this->post('key');
        // Does this key exist?
        if (!$this->_key_exists($key))
        {
            // It doesn't appear the key exists
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid API key'
            ], REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // Update the key level
        if ($this->_update_key($key, ['level' => 0]))
        {
            $this->response([
                'status' => TRUE,
                'message' => 'Key was suspended'
            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->response([
                'status' => FALSE,
                'message' => 'Could not suspend the user'
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR); // INTERNAL_SERVER_ERROR (500) being the HTTP response code
        }
    }

    /**
     * Regenerate a key
     *
     * @access public
     * @return void
     */
    public function refresh_user_security_key_post()
    {
       // $old_key = $this->post('key');
	   
	   $ci = get_instance();
	   $member_id=$this->post('member_id');		
		if(!$member_id)
        {
             $this->response([
			 	'error_code'=>4016,
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
	   
	   
	   $api_key_variable = config_item('rest_key_name');

        // Work out the name of the SERVER entry based on config
        $key_name = 'HTTP_' . strtoupper(str_replace('-', '_', $api_key_variable));
		$old_key=$_SERVER[$key_name];
	   
        $key_details = $this->_get_key($old_key);

        // Does this key exist?
        if (!$key_details)
        {
            // It doesn't appear the key exists
            $this->response([
				'error_code'=>4010,
                'status' => FALSE,
                'message' => 'Invalid API key'
            ], REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // Build a new key
        $new_key = $this->_generate_key();
        // Insert the new key
       // if ($this->_insert_key($new_key, ['level' => $key_details->level, 'ignore_limits' => $key_details->ignore_limits]))
        {
            // Suspend old key
          //  $this->_update_key($member_id,$old_key, ['level' => 0]);

			
			if($ci->rest->db
            ->where(array(config_item('rest_key_column')=>$old_key,"user_id"=>$member_id))->update(config_item('rest_keys_table'),array("key"=>$new_key)))
			{

            $this->response([
                'status' => TRUE,
                'key' => $new_key
            ], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
			
			}
			else
			{
				 $this->response([
				'error_code'=>4011,
                'status' => FALSE,
                'message' => 'Could not generate the key.Please contact support'
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				
			}
			
        }
      //  else
        //{
          //  $this->response([
            //    'status' => FALSE,
              //  'message' => 'Could not save the key'
            //], REST_Controller::HTTP_INTERNAL_SERVER_ERROR); 
			
			
			// INTERNAL_SERVER_ERROR (500) being the HTTP response code
        //}
    }

    /* Helper Methods */

    private function _generate_key()
    {
        do
        {
            // Generate a random salt
            $salt = base_convert(bin2hex($this->security->get_random_bytes(64)), 16, 36);

            // If an error occurred, then fall back to the previous method
            if ($salt === FALSE)
            {
                $salt = hash('sha256', time() . mt_rand());
            }

            $new_key = substr($salt, 0, config_item('rest_key_length'));
        }
        while ($this->_key_exists($new_key));

        return $new_key;
    }

    /* Private Data Methods */

    private function _get_key($key)
    {
        return $this->rest->db
            ->where(config_item('rest_key_column'), $key)
            ->get(config_item('rest_keys_table'))
            ->row();
    }

    private function _key_exists($key)
    {
        return $this->rest->db
            ->where(config_item('rest_key_column'), $key)
            ->count_all_results(config_item('rest_keys_table')) > 0;
    }

    private function _insert_key($key, $data)
    {
        $data[config_item('rest_key_column')] = $key;
        $data['date_created'] = function_exists('now') ? now() : time();

        return $this->rest->db
            ->set($data)
            ->insert(config_item('rest_keys_table'));
    }

    private function _update_key($key, $data)
    {
        return $this->rest->db
            ->where(config_item('rest_key_column'), $key)
            ->update(config_item('rest_keys_table'), $data);
    }

    private function _delete_key($key)
    {
        return $this->rest->db
            ->where(config_item('rest_key_column'), $key)
            ->delete(config_item('rest_keys_table'));
    }

}
