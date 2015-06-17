<?php
require_once 'CRM/Core/Form.php';
class CRM_Export_Form_BoekhoudingExport extends CRM_Core_Form {
	
	private $participants = array(), $fileRows = array(), $export = array(), $customFields;
	
	function buildQuickForm() {
		CRM_Utils_System::setTitle(ts('Boekhouding Export Formulier'));
		$this->addDate('periode_start', 'Periode Start', array("required" => TRUE));
		$this->addDate('periode_eind', 'Periode Eind', array("required" => TRUE));
		$this->addButtons(array(array('type' => 'submit', 'name' => ts('Exporteren'), 'isDefault' => TRUE)));
		$this->assign('elementNames', $this->getRenderableElementNames());
		parent::buildQuickForm();
	}
	
	function getRenderableElementNames() {
		$elementNames = array();
		foreach ($this->_elements as $element) {
			$label = $element->getLabel();
			if (!empty($label)) {
				$elementNames[] = $element->getName();
			}
		}
		return $elementNames;
	}
	
	function validate() {
		if(empty($_POST['periode_start']) || empty($_POST['periode_eind'])) {
			$this->assign("errorMessage", "Periode start en periode eind zijn verplichte velden.");
			return false;
		} else if ($_POST['periode_start'] > $_POST['periode_eind']) {
			$this->assign("errorMessage", "Periode start mag niet achter periode eind liggen.");
			return false;
		}
		$sessionData 					= CRM_Core_Session::singleton();
		$this->export['contact_id'] 	= $sessionData->get('userID');
		$this->export['periode_start'] 	= date("Ymd", strtotime($_POST['periode_start']));
		$this->export['periode_stop'] 	= date("Ymd", strtotime($_POST['periode_eind']));
		$this->export['created_at']		= date("Ymd");
		return true;
	}
	
	function postProcess() {
		if($this->fetchParticipants()) {
			$this->generateFileRows();
			$this->generateImp();
			$this->addExportToDatabase();
			parent::postProcess();
			CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/boekhouding-exports'));
		} else {
			$this->assign("errorMessage", "Geen participanten gevonden in de opgegeven datum range.");
		}
	}
	
	private function fetchParticipants() {
		$query = "
			SELECT * FROM `civicrm_participant`
			WHERE `register_date` BETWEEN '".$this->export['periode_start']."' AND '".$this->export['periode_stop']."'
		";
		$dbData = CRM_Core_DAO::executeQuery($query);
		if($dbData->N) {
			$this->participants = $dbData;
			return true;
		}
		return false;
	}
	
	private function generateFileRows() {
		while($this->participants->fetch()) {
			$this->fileRows[] = array(
				'"',
				$this->participants->contact_id,
				'12345678',
				'ZYXWERT'
			);
		}
	}
	
	private function generateImp() {
		$this->export['filename'] = $this->export['periode_start'].$this->export['periode_stop'].date("YmdHis").$this->export['contact_id'].".imp";
		foreach($this->fileRows as $k => $fileRow) {
			file_put_contents(substr(__DIR__, 0, strpos(__DIR__, "export"))."export/files/".$this->export['filename'], implode("", $fileRow)."\r\n", FILE_APPEND);
		}
	}
	
	private function addExportToDatabase() {
		/* current civicrm user identifier */
		CRM_Export_BAO_BoekhoudingExports::add($this->export);
	}
	

}