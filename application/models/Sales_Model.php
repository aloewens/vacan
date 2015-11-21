<?php
/**
 * PHP Sales
 *
 * @package    	Sales - aloewens
 * @copyright  	Copyright (c) 2015 Andro Loewenstein
 * @license    	Andro Loewenstein
 * @version    	1.00
 * @author     	aloewens72@gmail.com
 */

// ------------------------------------------------------------------------

/**
 * Sales Model
 *
 *
 * @package     Sales - aloewens
 * @copyright   Copyright (c) 2015 Andro Loewenstein
 * @license     Andro Loewenstein
 * @version     1.00
 * @author      aloewens72@gmail.com
  */

class Sales_Model extends CI_Model  {

	function __construct()
    {
        parent::__construct();
    }

    public function RegisterCharge ($prodID, $ref, $type, $value, $method, $entity, $comment, $user)
    {
	  	$this->db->set('ProductID', $prodID); 
	  	$this->db->set('Reference', $ref); 
	  	$this->db->set('ChargeDate', "NOW()", FALSE); 
	  	$this->db->set('ChargeType', $type); 
	  	$this->db->set('Value', $value); 
	  	$this->db->set('MethodID', $method); 
	  	$this->db->set('Entity', $entity); 
	  	$this->db->set('Comment', $comment); 
	  	$this->db->set('user_id', $user); 
		$this->db->insert('charge');

		// Gestiona Saldo Producto
		$balance = $this->GetProductBalance($prodID, $value, $type);

		// Actualizar Saldo producto
		$this->UpdateProductBalance($prodID, $balance);

    	return true;
    }

    public function GetProductBalance ($ProdID, $value, $type) {
		// ConsultaProducto
	 	$this->db->select('Balance'); 
    	$this->db->from('product');  
    	$this->db->where('ProductID', $ProdID);
    	
	   	$ret = $this->db->get()->result();

		//log_message('error', 'ProductID = ' . $ProdID . ', value = ' . $value . ', type = ' . $type . ', saldo_ini = ' . $ret[0]->Balance); 

		if ($ret[0]->Balance === NULL) {
    		   		$balance = 0;
	   	} else
			$balance = $ret[0]->Balance;

		if (strcmp ("Debito", $type) == 0) {
    		$balance += $value;
    	} elseif (strcmp ("Credito", $type) == 0) {
    		$balance -= $value;
    	}

		//log_message('error', 'GetSaldo = ' . $balance); 

    	return $balance;
    }


    public function UpdateProductBalance ($ProdID, $balance) {
    	//log_message('error', 'UPD_Saldo = ' . $balance); 
    	if ($balance >= 0) {
	    	$data = array(
    	           'Balance' => $balance
        	);

			$this->db->where('ProductID', $ProdID);
			$this->db->update('product', $data); 

			return true;
		} else {
			//log_message('error', 'Actualizacion Saldo Negativo no Posible ProductID = ' . $ProdID . ' Saldo = ' . $balance); 	
			return false;
		}

    }
}
