<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* Heredamos de la clase CI_Controller */
class Beneficiary extends CI_Controller {

	function __construct()
	{

		parent::__construct();

		/* Cargamos la base de datos */
		$this->load->database();

		/* Cargamos la libreria*/
		$this->load->library('grocery_crud');

		/* Añadimos el helper al controlador */
		$this->load->helper('url');
	}

	function index()
	{
		/*
		 * Mandamos todo lo que llegue a la funcion
		 * administracion().
		 **/
		redirect('Beneficiary/administracion');
	}

	/*
	 *
	 **/
	function administracion()
	{
		if (!$this->tank_auth->is_logged_in()) {
			redirect('/auth/login/');
		} 

		try{

			/* Creamos el objeto */
			$crud = new grocery_CRUD();

			/* Seleccionamos el tema */
			$crud->set_theme('flexigrid');

			/* Seleccionmos el nombre de la tabla de nuestra base de datos*/
			$crud->set_table('beneficiary');

			/* Le asignamos un nombre */
			$crud->set_subject('Beneficiario');

			/* Asignamos el idioma español */
			$crud->set_language('spanish');

    		$crud->set_relation('ProductID','product','ProductID');

			/* Aqui le decimos a grocery que estos campos son obligatorios */
			$crud->required_fields(
					'BeneficiaryID',
					'ProductID',
					'Name',
					'Lastname',
					'IdentType',
					'Ident',
					'BirthDate',
					'Address',
					'City'
					); 

			/* Aqui le indicamos que campos deseamos mostrar */
			$crud->columns(
					'BeneficiaryID',
					'ProductID',
					'Name',
					'Lastname',
					'IdentType',
					'Ident',	
					'BirthDate',
					'Address',
					'City',
					'Phone1',
					'Phone2',
					'Phone3',
					'Mail',
					'Facebook',
					'Twitter',
					'Facebook',
					'Skype',
					'Company'
			);

			$crud->display_as('BeneficiaryID','ID');
			$crud->display_as('ProductID','ID Producto');
			$crud->display_as('Name','Nombre');
			$crud->display_as('Lastname','Apellido');
			$crud->display_as('IdentType','Tipo Identificaci&oacuten');
			$crud->display_as('Ident','Identificaci&oacuten');
			$crud->display_as('BirthDate','Fecha Nacimiento');
			$crud->display_as('Address','Direcci&oacuten');
			$crud->display_as('City','Ciudad');
			$crud->display_as('Phone1','Tel&eacutefono 1');
			$crud->display_as('Phone2','Tel&eacutefono 2');
			$crud->display_as('Phone3','Tel&eacutefono 3');
	
			/* Generamos la tabla */
			$output = $crud->render();

			/* La cargamos en la vista situada en */
			$this->load->view('Beneficiary/administracion', $output);

		}catch(Exception $e){
			/* Si algo sale mal cachamos el error y lo mostramos */
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
}