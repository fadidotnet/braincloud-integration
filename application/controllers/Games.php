<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . 'controllers/Currencies.php';

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
class Games extends Currencies {

    function __construct()
    {
		
        // Construct the parent class
        parent::__construct();
		
    }
	
	
	public function award_tournament_winnings_post()
	{
		$member_id=$this->post('member_id');
		$leaderboard_id=$this->post('leaderboard_id');
		$tournament_id=$this->post('tournament_id');
		$leaderboard_version=$this->post('leaderboard_version');
		$score=$this->post('score');
		$scoreData=$this->post('scoreData');
		$rank=$this->post('rank');
		$tokensWon=$this->post('tokensWon');
		$ticketsWon=$this->post('ticketsWon');
		$purchase_type="tournament";
		$app_id=$this->post('app_id');
		
		
		if(!$tokensWon && !$ticketsWon)
		{
			$this->response([
				'error_code'=>4023,
                'status' => FALSE,
                'message' => 'tokensWon or  ticketsWon value must pass'
            ], REST_Controller::HTTP_BAD_REQUEST);
			
		}
		
		if(!$member_id)
        {
             $this->response([
                'status' => FALSE,
				'error_code'=>4016,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		if(!$leaderboard_id)
        {
             $this->response([
			 	'error_code'=>4026,
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		if(!$tournament_id)
        {
             $this->response([
			 	'error_code'=>4025,
                'status' => FALSE,
                'message' => 'member_id is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		if(!$leaderboard_version)
        {
             $this->response([
			 	'error_code'=>4027,
                'status' => FALSE,
                'message' => 'leaderboard_version is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$score)
        {
             $this->response([
			 	'error_code'=>4028,
                'status' => FALSE,
                'message' => 'score is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if(!$rank)
        {
             $this->response([
			 	'error_code'=>4029,
                'status' => FALSE,
                'message' => 'rank is missing'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
		
		if( ! filter_var($member_id, FILTER_VALIDATE_INT) ){
		  $this->response([
		  	'error_code'=>4016,
                'status' => FALSE,
                'message' => 'Invalid member_id.'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		if( ! filter_var($app_id, FILTER_VALIDATE_INT) ){
		  $this->response([
		  	'error_code'=>4022,
                'status' => FALSE,
                'message' => 'Invalid app_id.'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		
		if( ! filter_var($leaderboard_id, FILTER_VALIDATE_INT) ){
		  $this->response([
                'status' => FALSE,
					'error_code'=>4026,
                'message' => 'Invalid leaderboard_id.'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
		
		if(!filter_var($tournament_id, FILTER_VALIDATE_INT) ){
		  $this->response([
                'status' => FALSE,
					'error_code'=>4025,
                'message' => 'Invalid tournament_id.'
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
		
		
		$tokens_added=0;
		$tickets_added=0;
		if($tokensWon)
		{
			$this->add_tokens($member_id,$tokensWon,$app_id,$scoreData,$purchase_type,false);
			$tokens_added=1;
		}
		
		if($ticketsWon)
		{
			$this->add_tickets($member_id,$ticketsWon,$app_id,$scoreData,$purchase_type,false);
			$tickets_added=1;
		}
		
		$member_id=$this->post('member_id');
		$leaderboard_id=$this->post('leaderboard_id');
		$tournament_id=$this->post('tournament_id');
		$leaderboard_version=$this->post('leaderboard_version');
		$score=$this->post('score');
		$scoreData=$this->post('scoreData');
		$rank=$this->post('rank');
		$tokensWon=$this->post('tokensWon');
		$ticketsWon=$this->post('ticketsWon');
		$purchase_type="tournament";
		$app_id=$this->post('app_id');
		
		
        $data['date_created'] = function_exists('now') ? now() : time();
		$data['member_id'] = $member_id;
		$data['leaderboard_id'] = $leaderboard_id;
		$data['tournament_id'] = $tournament_id;
		$data['leaderboard_version'] = $leaderboard_version;
		$data['score'] = $leaderboard_id;
		$data['scoreData'] = $leaderboard_id;
		$data['tokensWon'] = $tokensWon;
		$data['purchase_type'] = $purchase_type;
		$data['app_id'] = $app_id;
		$data['is_tokens_added']=$tokens_added;
		$data['is_tickets_added']=$tickets_added;	
		try
		{
        	$this->rest->db
            ->set($data)
            ->insert("gln_award_winning_log");
			
			$this->response([
                'status' => TRUE,
				'message'=>"Log added successfully",
            ], REST_Controller::HTTP_CREATED);
			
		}
		catch (Exception $e)
		{
			
		  	$this->response([
				'error_code'=>4011,
                'status' => FALSE,
                'message' => 'Failure due to exception.'
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		
			
		}
		
					
	}
	
}
