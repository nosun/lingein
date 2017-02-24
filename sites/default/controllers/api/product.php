<?php
require_once("api_common.php");

class Api_Product_Controller extends Api_Model
{
	private $_productInstance;
	private $_userInstance;

	public static function __funcs()
	{
		return array(
		'product_update',
		);
	}

	public function __construct()
	{
		$this->_userInstance = User_Model::getInstance();
		$this->_productInstance = Product_Model::getInstance();
		$this->_taxonomy = Taxonomy_Model::getInstance();
	}
	
	public function product_update($updateData, $sn){
		$pids = $this->_productInstance->getProductIdsBySn($sn);
		foreach($pids as $pidObj){
			$this->_productInstance->updateProduct($pidObj->pid, $updateData);
		}
		return $pids;
	}
}

