<?php
class CRM_Export_DAO_BoekhoudingExports extends CRM_Core_DAO {
	
	static $_fields = null, $_fieldKeys = null, $_export = null;
	
	static function getTableName() {
		return 'civicrm_boekhouding_exports';
	}
	
	static function &fields() {
		if(!(self::$_fields)) {
			self::$_fields = array(
				'id' 			=> array(
					'name' 		=> 'id',
					'type' 		=> CRM_Utils_Type::T_INT,
					'required' 	=> true
				),
				'contact_id' 	=> array(
					'name' 		=> 'contact_id',
					'type' 		=> CRM_Utils_Type::T_INT,
					'required' 	=> true
				),
				'periode_start' => array(
					'name' 		=> 'periode_start',
					'type' 		=> CRM_Utils_Type::T_DATE,
					'required' 	=> true
				),
				'periode_stop' 	=> array(
					'name' 		=> 'periode_stop',
					'type' 		=> CRM_Utils_Type::T_DATE,
					'required' 	=> true
				),
				'created_at' 	=> array(
					'name' 		=> 'created_at',
					'type' 		=> CRM_Utils_Type::T_DATE,
					'required' 	=> true
				),
				'filename' 		=> array(
					'name' 		=> 'filename',
					'type' 		=> CRM_Utils_Type::T_STRING,
					'maxlength' => 50,
					'required' 	=> true
				),
			);
		}
		return self::$_fields;
	}
	
	static function &fieldKeys() {
		if(!(self::$_fieldKeys)) {
			self::$_fieldKeys = array(
				'id' 			=> 'id',
				'contact_id' 	=> 'contact_id',
				'periode_start' => 'periode_start',
				'periode_stop' 	=> 'periode_stop',
				'created_at' 	=> 'created_at',
				'filename' 		=> 'filename'
			);
		}
		return self::$_fieldKeys;
	}
	
}