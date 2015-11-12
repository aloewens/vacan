<?php
if (!defined('BASEPATH'))
   exit('No direct script access allowed');
class Init extends CI_Controller { 
   public function index(){
   		$this->load->view('Init');
   }
}
