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
					'EndDate'
					);

			/* Aqui le indicamos que campos deseamos mostrar */
			$crud->columns(
					'ProductID',
					'ProductCatalogID',
					'Contract',
					'ClientID',
					'VendorID',
					'StartDate',
					'EndDate'
			);

			$crud->add_action('Beneficiarios',base_url() . 'assets/uploads/detalle.png','Beneficiary/administracion');

			$crud->display_as('ProductID','ID');
			$crud->display_as('ProductCatalogID','ID Producto');


			/* Generamos la tabla */
			$output = $crud->render();

			/* La cargamos en la vista situada en
			 /applications/views/product/administracion.php */
			$this->load->view('Product/administracion', $output);

		}catch(Exception $e){
			/* Si algo sale mal cachamos el error y lo mostramos */
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}


	function administracion_ori()
	{
		if (!$this->tank_auth->is_logged_in()) {
			redirect('/auth/login/');
		} 

		try{

			/* Creamos el objeto */
			$crud = new grocery_CRUD();

			/* Seleccionamos el tema */
			$crud->set_theme('datatables');

			/* Seleccionmos el nombre de la tabla de nuestra base de datos*/
			$crud->set_table('product');

			/* Le asignamos un nombre */
			$crud->set_subject('Producto Cliente');

			/* Asignamos el idioma español */
			$crud->set_language('spanish');

   			$crud->set_relation('ProductCatalogID','productcatalog','Description');
    		$crud->set_relation('ClientID','client','Ident');
    		$crud->set_relation('VendorID','vendor','Name');

			/* Aqui le decimos a grocery que estos campos son obligatorios */
			$crud->required_fields(
					'ProductID',
					'ProductCatalogID',					
					'Contract',
					'ClientID',
					'VendorID',
					'StartDate',
					'EndDate'
					);

			/* Aqui le indicamos que campos deseamos mostrar */
			$crud->columns(
					'ProductID',
					'ProductCatalogID',
					'Contract',
					'ClientID',
					'VendorID',
					'StartDate',
					'EndDate'
			);

			$crud->display_as('ProductID','ID');
			$crud->display_as('ProductCatalogID','ID Producto');


			/* Generamos la tabla */
			$output = $crud->render();

			/* La cargamos en la vista situada en */
			$this->load->view('Product/administracion', $output);

		}catch(Exception $e){
			/* Si algo sale mal cachamos el error y lo mostramos */
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
}