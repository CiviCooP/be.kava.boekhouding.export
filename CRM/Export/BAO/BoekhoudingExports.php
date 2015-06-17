<?php
class CRM_Export_BAO_BoekhoudingExports extends CRM_Export_DAO_BoekhoudingExports {
	
	public static function get_values($params = array()) {
		$result  = array();
		$exports = new CRM_Export_BAO_BoekhoudingExports();
		$exports->find();
		while ($exports->fetch()) {
			$row = array();
			self::storeValues($exports, $row);
			$result[$row['id']] = $row;
		}
		return $result;
	}
	
	public static function add($params) {
		$result = array();
		if (empty($params)) {
			throw new Exception('Params can not be empty when adding or updating a boekhouding export');
		}
		$exports = new CRM_Export_BAO_BoekhoudingExports();
		$fields  = self::fields();
		foreach ($params as $key => $value) {
			if (isset($fields[$key])) {
				$exports->$key = $value;
			}
		}
		$exports->save();
		self::storeValues($exports, $result);
		return $result;
	}
	
	public static function delete_by_id($exports_id) {
		if (empty($exports_id)) {
			throw new Exception('export_id can not be empty when attempting to delete a boekhouding export');
		}
		$exports     = new CRM_Export_BAO_BoekhoudingExports();
		$exports->id = $exports_id;
		$exports->delete();
		return;
	}
	
	public static function get_contact_count($contact_id) {
		$exports             = new CRM_Export_BAO_BoekhoudingExports();
		$exports->contact_id = $contact_id;
		return $exports->count();
	}
	
}