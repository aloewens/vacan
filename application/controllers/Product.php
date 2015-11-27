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


	function admin_by_client($ID)
	{
		if (!$this->tank_auth->is_logged_in()) {
			redirect('/auth/login/');
		} 

		try{
			$this->administracion($ID, 'client', 'PrClientID', 'Ident');
		}catch(Exception $e){
			/* Si algo sale mal cachamos el error y lo mostramos */
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}


	function admin_by_vendor($ID)
	{
		if (!$this->tank_auth->is_logged_in()) {
			redirect('/auth/login/');
		} 

		try{
			$this->administracion($ID, 'vendor', 'PrVendorID', '{Name} {Lastname}');
		}catch(Exception $e){
			/* Si algo sale mal cachamos el error y lo mostramos */
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}


	/*
	 *
	 **/
	function administracion($ID = NULL, $parent_table = NULL, $fieldID = NULL, $field_show = NULL)
	{
		if (!$this->tank_auth->is_logged_in()) {
			redirect('/auth/login/');
		} 

		try{
			/* Creamos el objeto */
			$crud = new grocery_CRUD();

			/* Seleccionamos el tema */
			//$crud->set_theme('flexigrid');
			$crud->set_theme('datatables');

			/* Seleccionamos el nombre de la tabla de nuestra base de datos*/
			$crud->set_table('product');

			/* Le asignamos un nombre */
			$crud->set_subject('Producto');

			/* Asignamos el idioma español */
			$crud->set_language('spanish');

			/* ALA - 10/11/2015 : Condiciona invocación maestro detalle */
			$state = $crud->getState();

			//echo " ID = " . $ID . ", state = " . $state . ", parent_table = " . $parent_table . "FieldID = " . $fieldID;

			switch ($state) {
			    case 'success':
			    	if (is_numeric($ID)) {
						//echo "success ID = " . $ID . ", state = " . $state;
						$crud->where($fieldID, $ID);
			    	}
			    	$crud->set_relation('PrClientID', 'client', 'Ident');
			    	$crud->set_relation('PrVendorID', 'vendor', '{Name} {Lastname}');
			    break;
			    case 'list':
			    	if (is_numeric($ID)) {
						//echo "list ID = " . $ID . ", state = " . $state;
						$crud->where($fieldID, $ID);	
			    	}
			    	$crud->set_relation('PrClientID', 'client', 'Ident');
			    	$crud->set_relation('PrVendorID', 'vendor', '{Name} {Lastname}');
			    break;
			    case 'add':
			    	if ($parent_table == NULL) {
			    		$crud->set_relation('PrClientID', 'client', 'Ident');
			    		$crud->set_relation('PrVendorID', 'vendor', '{Name} {Lastname}');	
			    	}
					elseif ($parent_table == 'client') {
				    	$crud->field_type('PrClientID', 'hidden', $ID);
			    		$crud->set_relation('PrVendorID', 'vendor', '{Name} {Lastname}');
					}
				    elseif ($parent_table == 'vendor') {
				    	$crud->field_type('PrVendorID', 'hidden', $ID);
				    	$crud->set_relation('PrClientID', 'client', 'Ident');
					}
					//echo "add ID = " . $ID . ", state = " . $state;
				break;
				case "read":
			    	$crud->set_relation('PrClientID', 'client', 'Ident');
			    	$crud->set_relation('PrVendorID', 'vendor', '{Name} {Lastname}');
			}

			$crud->set_relation('PrProductCatalogID','productcatalog','Description');

			/* Aqui le decimos a grocery que estos campos son obligatorios */
			$crud->required_fields(
					'ProductID',
					'PrProductCatalogID',					
					'Contract',
					'PrClientID',
					'PrVendorID',
					'StartDate',
					'EndDate',
					'Balance'
					);

			/* Aqui le indicamos que campos deseamos mostrar */
			$crud->columns(
					'ProductID',
					'PrProductCatalogID',
					'Contract',
					'PrClientID',
					'PrVendorID',
					'StartDate',
					'EndDate',
					'Balance'
			);

			$crud->display_as('ProductID','ID');
			$crud->display_as('PrProductCatalogID','Producto');
			$crud->display_as('Contract','Contrato');
			$crud->display_as('PrClientID','Cliente');
			$crud->display_as('PrVendorID','Vendedor');
			$crud->display_as('StartDate','Fecha Inicio Vigencia');
			$crud->display_as('EndDate','Fecha Fin Vigencia');
			$crud->display_as('Balance','Saldo');

			$crud->set_rules('EndDate','Fecha Vigencia','callback_ValidProd'); //http://www.grocerycrud.com/documentation/options_functions/set_rules

			$crud->unset_add_fields('Balance');
			$crud->unset_edit_fields('Balance');

			$crud->add_action('Beneficiarios',base_url() . 'assets/uploads/detalle.png','Beneficiary/administracion');
			$crud->add_action('Movimientos',base_url() . 'assets/uploads/money.png','Charge/administracion');

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
    	$this->db->where('ProductCatalogID', $this->input->post('PrProductCatalogID'));
    	
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
	
	function ValidProd() {
		$f1 = $this->input->post('StartDate');
		$f2 = $this->input->post('EndDate');

		//log_message('error', 'dates = ' . $f1 . ", " . $f2); 
		if ($f1 > $f2) { 
			$this->form_validation->set_message('ValidProd', 'Fecha Inicial debe ser menor a Fecha Final');
        	return false;
        }
        	
    	return true;
	}
}