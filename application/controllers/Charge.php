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

		/* Añadimos el helper al controlador */
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
			//$crud->set_theme('datatables');

			/* Seleccionmos el nombre de la tabla de nuestra base de datos*/
			$crud->set_table('charge');

			/* Le asignamos un nombre */
			$crud->set_subject('Movimientos');

			/* Asignamos el idioma español */
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

			$crud->set_relation('MethodID','method','Description');

			/* Aqui le decimos a grocery que estos campos son obligatorios */
			$crud->required_fields(
					'ChargeID',
					'ProductID',
					'Reference',
					'ChargeDate',
					'ChargeType',
					'Value',
					'MethodID',
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
					'MethodID',
					'Entity',
					'Comment'	
			);

			$crud->display_as('ChargeID','ID');
			$crud->display_as('ProductID','Producto');
			$crud->display_as('Reference','Referencia');
			$crud->display_as('ChargeDate','Fecha Movimiento');
			$crud->display_as('ChargeType','Tipo Movimiento');
			$crud->display_as('Value','Valor');
			$crud->display_as('MethodID','Metodo');
			$crud->display_as('Entity','Entidad');
			$crud->display_as('Comment','Comentario');

			$crud->set_rules('Value','Valor','numeric|greater_than[0]|callback_ValidBalance'); //http://www.grocerycrud.com/documentation/options_functions/set_rules
			$crud->set_rules('ChargeType','Tipo Movimiento','in_list[Credito]'); 
			
			// Log User_ID
			$crud->callback_before_insert(array($this,'Set_User_ID'));

			// Procesos posteriores al registro del movimiento
			$crud->callback_after_insert(array($this,'ProcessCharge'));

			// No permite borrado, actualizacion de movimientos
			$crud->change_field_type('user_id','invisible');
			$crud->unset_delete();
			$crud->unset_edit();

			//$crud->set_rules('Value', 'Valor', 'callback_ValidBalance'); 

			/* Generamos la tabla */
			$output = $crud->render();

			/* La cargamos en la vista situada en */
			$this->load->view('Charge/administracion', $output);

		}catch(Exception $e){
			/* Si algo sale mal cachamos el error y lo mostramos */
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}

	}

	// ALA : 13/11/2015 : Gestión Identificador Usuario del aplicativo para log en el movimiento
	function Set_User_ID($post_array) {
  		$post_array['user_id'] = $this->session->userdata('user_id');
 		
  		return $post_array;
	}

	// ALA : 19/11/2015 : Procesos post movimiento
	function ProcessCharge($post_array) {
		require_once(APPPATH.'models/Sales_Model.php');
		$sales = new Sales_Model();

		$ProdID = $this->input->post('ProductID');
		$balance = $sales->GetProductBalance($ProdID, $this->input->post('Value'), $this->input->post('ChargeType'));

		// Actualiza Saldo Producto
		if (!$sales->UpdateProductBalance($ProdID, $balance)) 
			return false;
		else 
			return true;
	}

	function ValidBalance() {
		require_once(APPPATH.'models/Sales_Model.php');
		$sales = new Sales_Model();

		$ProdID = $this->input->post('ProductID');
		$balance = $sales->GetProductBalance($ProdID, $this->input->post('Value'), $this->input->post('ChargeType'));

		// Actualiza Saldo Producto
		if ($balance < 0) {
			$this->form_validation->set_message('ValidBalance', 'El Saldo del Producto ' . $ProdID . ' no puede quedar Negativo : ' . $balance);
        	return false;
    	}

    	return true;
	}
}