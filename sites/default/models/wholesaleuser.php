<?php
class WholesaleUser_Model extends Bl_Model {
	/**
	 * @return User_Model
	 */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}
	
	public function insert($set) {
		global $db;
		foreach ($set as $key => $value) {
			$set[$key] = $db->escape($value);
		}
		$db->insert('wholesale_user', $set);
		return $db->affected() > 0;
	}
}