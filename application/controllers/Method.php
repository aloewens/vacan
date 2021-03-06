<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* Heredamos de la clase CI_Controller */
class Method extends CI_Controller {

	function __construct()
	{

		parent::__construct();

		/* Cargamos la base de datos */
		$this->load->database();

		/* Cargamos la libreria*/
		$this->load->library('grocery_crud');

		/* A�adimos el helper al controlador */
		$this->load->helper('url');
	}

	function index()
	{
		/*
		 * Mandamos todo lo que llegue a la funcion
		 * administracion().
		 **/
		redirect('Method/administracion');
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
			$crud->set_table('method');

			/* Le asignamos un nombre */
			$crud->set_subject('Metodo');

			/* Asignamos el idioma espa�ol */
			$crud->set_language('spanish');

			/* Aqui le decimos a grocery que estos campos son obligatorios */
			$crud->required_fields(
					'MethodID',
					'Description'
					); 

			/* Aqui le indicamos que campos deseamos mostrar */
			$crud->columns(
					'MethodID',
					'Description'
			);

			$crud->display_as('MethodID','ID');
			$crud->display_as('Description','Descripci&oacuten');

			/* Generamos la tabla */
			$output = $crud->render();

			/* La cargamos en la vista situada en */
			$this->load->view('Method/administracion', $output);

		}catch(Exception $e){
			/* Si algo sale mal cachamos el error y lo mostramos */
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
}