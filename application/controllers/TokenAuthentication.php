<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require_once APPPATH . '/libraries/REST_Controller.php';

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
class TokenAuthentication extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		
    }
	
	public static function authenticate_token($member_id=null,$session_id=null,$key=null)
	{
		//die("Umer");
		$ci = get_instance();
		if(!isset($_SERVER["HTTP_X_GLN_SECURE_SECURITY_TOKEN"]))
		{
			
				$ci->response([
				'error_code'=>4001,
                'status' => FALSE,
                'message' => 'GLN headers are missing'
            ], REST_Controller::HTTP_NOT_ACCEPTABLE);	
			
			
		}
		//$ci = get_instance();
		 $api_key_variable = config_item('rest_key_name');

        // Work out the name of the SERVER entry based on config
        $key_name = 'HTTP_' . strtoupper(str_replace('-', '_', $api_key_variable));
		$key=$_SERVER[$key_name];
		
		if(!$key)
        {
             $ci->response([
                'status' => FALSE,
				'error_code'=>4001,
                'message' => 'x-gln-secure-security-token is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		if($session_id!=null)
		{
			if(!$ci->post('session_id'))
        	{
            	 $this->response([
				 'error_code'=>2017,
                'status' => FALSE,
                'message' => 'session_id is missing'
            	], REST_Controller::HTTP_BAD_REQUEST);
        	}
			return $ci->rest->db
            ->where(array(config_item('rest_key_column')=>$key,"session_id"=>$session_id))
            ->count_all_results(config_item('rest_keys_table')) > 0;
		}
		else
		{
		
			return $ci->rest->db
            ->where(array(config_item('rest_key_column')=>$key))
            ->count_all_results(config_item('rest_keys_table')) > 0;
		
		
		}
		
	}

}
