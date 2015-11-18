<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* Heredamos de la clase CI_Controller */
class Vendor extends CI_Controller {

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
		redirect('Vendor/administracion');
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
			$crud->set_table('vendor');

			/* Le asignamos un nombre */
			$crud->set_subject('vendedor');

			/* Asignamos el idioma español */
			$crud->set_language('spanish');


			/* Aqui le decimos a grocery que estos campos son obligatorios */
			$crud->required_fields(
					'VendorID',
					'Name',
					'Lastname',
					'IdentType',
					'Ident',
					'BirthDate',
					'RegisterDate',
					'Address',
					'City'
					); 

			/* Aqui le indicamos que campos deseamos mostrar */
			$crud->columns(
					'VendorID',
					'Name',
					'Lastname',
					'IdentType',
					'Ident',
					'BirthDate',
					'RegisterDate',
					'Address',
					'City',
					'Phone1',
					'Phone2',
					'Mail',
					'Facebook',
					'Twitter',
					'Facebook',
					'Skype'
			);

			$crud->display_as('VendorID','ID');
			$crud->display_as('Name','Nombre');
			$crud->display_as('Lastname','Apellido');
			$crud->display_as('IdentType','Tipo Identificaci&oacuten');
			$crud->display_as('Ident','Identificaci&oacuten');
			$crud->display_as('BirthDate','Fecha Nacimiento');
			$crud->display_as('RegisterDate','Fecha Ingreso');
			$crud->display_as('Address','Direcci&oacuten');
			$crud->display_as('City','Ciudad');
			$crud->display_as('Phone1','Tel&eacutefono 1');
			$crud->display_as('Phone2','Tel&eacutefono 2');
			$crud->display_as('Phone3','Tel&eacutefono 3');

			/* Generamos la tabla */
			$output = $crud->render();

			/* La cargamos en la vista situada en */
			$this->load->view('Vendor/administracion', $output);

		}catch(Exception $e){
			/* Si algo sale mal cachamos el error y lo mostramos */
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
}