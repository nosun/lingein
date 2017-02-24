<?php
class Inventory_Model extends Bl_Model
{
	
	private $_inventoryDB;
	
  /**
   * @return Inventory_Model
   */
  public static function getInstance()
  {
    return parent::getInstance(__CLASS__);
  }
  
  public function __construct()
  {
    $inventoryDBConfig = BL_Config::get('inventorydb');
	$this->_inventoryDB = new BL_Database();
	$this->_inventoryDB->connect($inventoryDBConfig);
  }

  public function checkProductItemInventory($sn, $properties){
  	$avid = $this->getAvid($properties);
  	if($avid == '-1'){
  		return '0';
  	}
	$this->_inventoryDB->select('stock_qty');
	$this->_inventoryDB->from('stock');
	$this->_inventoryDB->where('avid', $avid);
	$this->_inventoryDB->where('p_sn', $sn);
	$result = $this->_inventoryDB->get();
	$data = $result->one();
	if(!$data){
		return '0';
	}
	return $data;
  }
  
  public function checkCartItemInventory($cart_item_id){
  	$cartInstance = Cart_Model::getInstance();
  	$productInstance = Product_Model::getInstance();
  	$cartInfo = $cartInstance->getCartProductInfo($cart_item_id);
  	
  	$sn = $productInstance->getProductSnById($cartInfo->pid);
  	if(!$sn){
  		return '0';
  	}
  	$avid = $this->getAvid(unserialize($cartInfo->data));
  	
  	if($avid == '-1'){
  		return '0';
  	}
  	
	$this->_inventoryDB->select('stock_qty');
	$this->_inventoryDB->from('stock');
	$this->_inventoryDB->where('avid', $avid);
	$this->_inventoryDB->where('p_sn', $sn);
	$result = $this->_inventoryDB->get();
	$data = $result->one();
	if(!$data){
		return '0';
	}
	return $data;
  }
  
  
  public function checkProductInventory($sn){
	$this->_inventoryDB->select('stock.p_sn, stock.stock_qty, attr_values.data');
	$this->_inventoryDB->from('stock');
	$this->_inventoryDB->join('attr_values', 'stock.avid = attr_values.id');
	$this->_inventoryDB->where('p_sn', $sn);
	$result = $this->_inventoryDB->get();
	$data = $result->all();
	if($data && count($data) > 0){
		foreach($data as $k=>$v){
			$v->data = unserialize($v->data);
		}
	}
	return $data;
  }
  
  public function updateInventory($orderItem, $operation = '-'){
  	$avid = $this->getAvid($orderItem->data);
    if($avid == '-1'){
  		return;
  	}
  	$this->_inventoryDB->query('update stock set stock_qty = stock_qty '. $operation. ' '. $orderItem->qty . ' where p_sn = "'.$orderItem->sn.'" and avid = "'.$avid.'"');
  }
  
  
  private function getAvid($properties){
  	$avid = -1;
  	$result = $this->_inventoryDB->get('attr_values');
  	$data = $result->all();
  	foreach($data as $k=>$v){
  		$v->data = unserialize($v->data);
  		if($this->isPropertiesEqual($v->data, $properties)){
  			$avid = $v->id;
  			break;
  		}
  	}
  	return $avid;
  }

	/**
	 * Given 2 array of properties, test whether they are equal.
	 * Enter description here ...
	 * @param unknown_type $props1
	 * @param unknown_type $props2
	 */
	function isPropertiesEqual($props1, $props2){
		$isSame = true;
		if($props1 == false && $props2 == false){
			return true;
		}
		if($props1 == false || $props2 == false){
			return false;
		}
  		foreach($props1 as $pName => $pValue){
  			if($pName{0} === strtoupper($pName{0})){
  				//$pName is uppercase.
  				if((!key_exists($pName, $props2) || strtolower($props2[$pName]) != strtolower($pValue))
  				 && (!key_exists(strtolower($pName), $props2) || strtolower($props2[strtolower($pName)]) != strtolower($pValue))){
  				//属性不相等
	  				$isSame = false;
	  				break;
  				 }
  			}else{
  				//$pName{0} is lower case.
  			  	if((!key_exists($pName, $props2) || strtolower($props2[$pName]) != strtolower($pValue))
  			  	 && (!key_exists(ucwords($pName), $props2) || strtolower($props2[ucwords($pName)]) != strtolower($pValue))){
  				//属性不相等
	  				$isSame = false;
	  				break;
  				}
  			}
  		}
  		//属性全都相等才能判定为相等。
  		return $isSame;
  	}
	
	function getStockItem($p_sn, $avid){
		global $db;
		
		$db->where('avid', $avid);
		$db->where('p_sn', $p_sn);
		$result = $db->get('stock');
		$data = $result->row();
		return $data;
	}
}