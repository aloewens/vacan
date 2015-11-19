<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* Heredamos de la clase CI_Controller */
class Product extends CI_Controller {

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
		redirect('Product/administracion');
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
			$crud->set_table('product');

			/* Le asignamos un nombre */
			$crud->set_subject('Producto Cliente');

			/* Asignamos el idioma español */
			$crud->set_language('spanish');

			/* ALA - 10/11/2015 : Condiciona invocación maestro detalle */
			$state = $crud->getState();

			switch ($state) {
			    case 'success':
			    	if ($ID != NULL && $ID != 'success') {
						//echo "success ID = " . $ID . ", state = " . $state;
						$crud->where('ClientID',$ID);	
			    	}
			    break;
			    case 'list':
			    	if ($ID != NULL) {
						//echo "list ID = " . $ID . ", state = " . $state;
						$crud->where('ClientID',$ID);	
			    	}
			    break;
			    case 'add':
			    	if ($ID != "add") {
			        	$crud->field_type('ClientID', 'hidden', $ID);
			    	}
			    	else
			    		$crud->set_relation('ClientID','client','Ident');
			    	//echo "add ID = " . $ID . ", state = " . $state;
				break;
			    default:
					//echo "default ID = " . $ID . ", state = " . $state;
					$crud->set_relation('ClientID','client','ClientID');
			}


    		$crud->set_relation('ProductCatalogID','productcatalog','Description');
    		$crud->set_relation('VendorID','vendor','Name');

			/* Aqui le decimos a grocery que estos campos son obligatorios */
			$crud->required_fields(
					'ProductID',
					'ProductCatalogID',					
					'Contract',
					'ClientID',
					'VendorID',
					'StartDate',
					'EndDate',
					'Balance'
					);

			/* Aqui le indicamos que campos deseamos mostrar */
			$crud->columns(
					'ProductID',
					'ProductCatalogID',
					'Contract',
					'ClientID',
					'VendorID',
					'StartDate',
					'EndDate',
					'Balance'
			);

			$crud->display_as('ProductID','ID');
			$crud->display_as('ProductCatalogID','Producto');
			$crud->display_as('Contract','Contrato');
			$crud->display_as('ClientID','ID Cliente');
			$crud->display_as('VendorID','ID Vendedor');
			$crud->display_as('StartDate','Fecha Inicio Vigencia');
			$crud->display_as('EndDate','Fecha Fin Vigencia');
			$crud->display_as('Balance','Saldo');

			$crud->unset_add_fields('Balance');
			$crud->unset_edit_fields('Balance');

			$crud->add_action('Beneficiarios',base_url() . 'assets/uploads/detalle.png','Beneficiary/administracion');
			$crud->add_action('Movimientos',base_url() . 'assets/uploads/money.png','Charge/administracion');

			$crud->display_as('ProductID','ID');
			$crud->display_as('ProductCatalogID','ID Producto');

			// Procesos posteriores a la venta
			$crud->callback_after_insert(array($this,'Process_Sale'));

			/* Generamos la tabla */
			$output = $crud->render();

			/* La cargamos en la vista situada en
			 /applications/views/product/administracion.php */
			$this->load->view('Product/administracion', $output);

		}catch(Exception $e){
			/* Si algo sale mal cachamos el error y lo mostramos */
			//log_message($e->getMessage().' --- '.$e->getTraceAsString());
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}

	// ALA : 19/11/2015 : Procesamiento necesario para una venta
	function Process_Sale($post_array) { 
		require_once(APPPATH.'models/Sales_Model.php');
		$sales = new Sales_Model();

	  	$prodID = $this->db->insert_id(); 
	  	$ref = "Cargo Venta";
	  	$type = "Debito";

	  	// Consulta Valor Tipo Producto 
	 	$this->db->select('Price'); 
    	$this->db->from('productcatalog');  
    	$this->db->where('ProductCatalogID', $this->input->post('ProductCatalogID'));
    	
	   	$ret = $this->db->get()->result();
		$value = $ret[0]->Price;

    	//log_message('error', 'Value = ' . $value); 

    	// Registro Cargo Venta
	  	$method = 1; 
	  	$entity = "OpenLife"; 
	  	$comment = "Cargo Venta"; 
	  	$user =  $this->session->userdata('user_id');

 	  	$sales->RegisterCharge($prodID, $ref, $type, $value, $method, $entity, $comment, $user);
    	
       	return true;
	}
	
}