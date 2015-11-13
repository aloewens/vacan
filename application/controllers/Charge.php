<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* Heredamos de la clase CI_Controller */
class Charge extends CI_Controller {

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
		redirect('Charge/administracion');
	}

	/*
	 *
	 **/
	function administracion($ID = NULL)
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
			$crud->set_table('charge');

			/* Le asignamos un nombre */
			$crud->set_subject('Movimientos');

			/* Asignamos el idioma espa�ol */
			$crud->set_language('spanish');

			$state = $crud->getState();

			switch ($state) {
			    case 'success':
			    	if ($ID != NULL && $ID != 'success') {
						//echo "success ID = " . $ID . ", state = " . $state;
						$crud->where('ProductID',$ID);	
			    	}
			    break;
			    case 'list':
			    	if ($ID != NULL) {
						//echo "list ID = " . $ID . ", state = " . $state;
						$crud->where('ProductID',$ID);	
			    	}
			    break;
			    case 'add':
			    	if ($ID != "add") {
			        	$crud->field_type('ProductID', 'hidden', $ID);
			    	}
			    	else
			    		$crud->set_relation('ProductID','product','ProductID');
			    	//echo "add ID = " . $ID . ", state = " . $state;
				break;
			    default:
					//echo "default ID = " . $ID . ", state = " . $state;
					$crud->set_relation('ProductID','product','ProductID');
			}

			/* Aqui le decimos a grocery que estos campos son obligatorios */
			$crud->required_fields(
					'ChargeID',
					'ProductID',
					'Reference',
					'ChargeDate',
					'ChargeType',
					'Value',
					'Method',
					'Entity'	
			); 

			/* Aqui le indicamos que campos deseamos mostrar */
			$crud->columns(
					'ChargeID',
					'ProductID',
					'Reference',
					'ChargeDate',
					'ChargeType',
					'Value',
					'Method',
					'Entity',
					'Comment'	
			);

			$crud->display_as('ChargeID','ID');
			$crud->display_as('ProductID','Producto');
			$crud->display_as('Reference','Referencia');
			$crud->display_as('ChargeDate','Fecha Movimiento');
			$crud->display_as('ChargeType','Tipo Movimiento');
			$crud->display_as('Value','Valor');
			$crud->display_as('Method','Metodo');
			$crud->display_as('Entity','Entidad');
			$crud->display_as('Comment','Comentario');

			$crud->set_rules('Value','Valor','numeric');
			$crud->change_field_type('user_id','invisible');

			// No permite borrado, actualizacion de movimientos
			$crud->unset_delete();
			$crud->unset_edit();

			// Log User_ID
			$crud->callback_before_insert(array($this,'Set_User_ID'));

			/* Generamos la tabla */
			$output = $crud->render();

			/* La cargamos en la vista situada en */
			$this->load->view('Charge/administracion', $output);

		}catch(Exception $e){
			/* Si algo sale mal cachamos el error y lo mostramos */
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}

	// ALA : 13/11/2015 : Gesti�n Identificador Usuario del aplicativo para log en el movimiento
	function Set_User_ID($post_array) {
  		$post_array['user_id'] = $this->session->userdata('user_id');
 
  		return $post_array;
	}
}