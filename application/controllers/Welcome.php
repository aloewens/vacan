<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller
{
	function __construct()
	{
		parent::__construct();

        $this->load->helper('form');
        $this->load->helper('url');    
        $this->load->helper('navigation');       
   	}

	function index()
	{
		$this->load->library('tank_auth');
		
		if (!$this->tank_auth->is_logged_in()) {
			redirect('/auth/login/');
		} else {
			$data['user_id']	= $this->tank_auth->get_user_id();
			$data['username']	= $this->tank_auth->get_username();
			$this->load->view('Init', $data);
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */