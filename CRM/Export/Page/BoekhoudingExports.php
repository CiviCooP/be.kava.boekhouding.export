<?php
require_once 'CRM/Core/Page.php';
class CRM_Export_Page_BoekhoudingExports extends CRM_Core_Page {
	
	public function run() {
		CRM_Utils_System::setTitle(ts('Boekhouding Exports'));
		if(isset($_GET['action']) && $_GET['action'] == "verwijderen" && !empty($_GET['rid'])) CRM_Export_BAO_BoekhoudingExports::delete_by_id($_GET['rid']);
		$exports = $this->get_boekhouding_exports();
		$this->assign('exports', $exports);
		$this->assign('nieuw_url', CRM_Utils_System::url('civicrm/boekhouding-export-form'));
		parent::run();
	}
	
	protected function get_boekhouding_exports() {
		return $this->alter_display(CRM_Export_BAO_BoekhoudingExports::get_values());
	}
	
	private function alter_display($rows) {
		foreach($rows as &$row) {
			$row['contact_id'] 		= $this->fetch_identity($row['contact_id']);
			$row['periode_start'] 	= date("d-m-Y", strtotime($row['periode_start']));
			$row['periode_stop'] 	= date("d-m-Y", strtotime($row['periode_stop']));
			$row['created_at'] 		= date("d-m-Y", strtotime($row['created_at']));
			$row['verwijderen']		= CRM_Utils_System::url('civicrm/boekhouding-exports', 'rid='.$row['id'].'&action=verwijderen');
		}
		return $rows;
	}
	
	private function fetch_identity($contact_id) {
		try {
			$contact = civicrm_api3("contact", "getsingle", array("id" => $contact_id));
			$contact_url = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$contact_id);
			return '<a href="'.$contact_url.'" target="_BLANK">'.$contact['display_name'].'</a>';
		} catch(Exception $e) {
			return "#VERWIJDERD";
		}
	}

}