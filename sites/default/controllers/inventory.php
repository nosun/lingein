<?php
class Inventory_Controller extends Bl_Controller
{
	public function ajaxgetinventoryAction(){
	  	if($this->isPost()){
	  		$post = $_POST;
	  		$inventoryInstance = Inventory_Model::getInstance();
	  		if(isset($post['cart_item_id'])){
				$inventory = $inventoryInstance->checkCartItemInventory($post['cart_item_id']);
				echo $inventory;
	  		}
		  	else if(isset($post['sn']) && isset($post['values'])){
				$inventory = $inventoryInstance->checkProductItemInventory($post['sn'], $post['values']);
				echo $inventory;
		  	}
		  	else{
		  		echo '0';
		  	}
  		}
	}
}