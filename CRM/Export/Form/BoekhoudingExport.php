<?php
require_once 'CRM/Core/Form.php';
class CRM_Export_Form_BoekhoudingExport extends CRM_Core_Form {
	
	private $contributions = array(), $fileRows = array(), $export = array(), $customGroups = array(), $customFields = array(), $statusses = array(), $skipRow = false;
	
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
	
	function getFieldsAndSettings() {
		$this->statusses['event_fee'] 						= $this->civiapi('FinancialType', 'GetSingle', array("name" => "Event Fee"));
		$this->statusses['contribution']['pending'] 		= $this->civiapi('OptionValue', 'GetSingle', array('option_group_id' => 'contribution_status', 'name' => 'Pending'));
		$this->statusses['contribution']['in_progress'] 	= $this->civiapi('OptionValue', 'GetSingle', array('option_group_id' => 'contribution_status', 'name' => 'In Progress'));
		$this->statusses['contribution']['partially_paid'] 	= $this->civiapi('OptionValue', 'GetSingle', array('option_group_id' => 'contribution_status', 'name' => 'Partially paid'));
		$this->statusses['contribution']['pending_refund'] 	= $this->civiapi('OptionValue', 'GetSingle', array('option_group_id' => 'contribution_status', 'name' => 'Pending refund'));
		$this->customGroups['aanvullende_info']				= $this->civiapi('CustomGroup', 'GetSingle', array('name' => 'aanvullende_info'));
		$this->customFields['volgnummer']					= $this->civiapi('CustomField', 'GetSingle', array('name' => 'volgnummer', "custom_group_id" => $this->customGroups['aanvullende_info']['id']));
		$this->customGroups['event_registration']			= $this->civiapi('CustomGroup', 'GetSingle', array('name' => 'event_registration'));
		$this->customFields['artikelcode']					= $this->civiapi('CustomField', 'GetSingle', array('name' => 'artikelcode', "custom_group_id" => $this->customGroups['event_registration']['id']));
		$this->customGroups['betalingsgegevens']			= $this->civiapi('CustomGroup', 'GetSingle', array('name' => 'participant_betalingsgegevens'));
		$this->customFields['betaalwijze']					= $this->civiapi('CustomField', 'GetSingle', array('name' => 'betaalwijze', "custom_group_id" => $this->customGroups['betalingsgegevens']['id']));
		$this->customFields['apbnummer']					= $this->civiapi('CustomField', 'GetSingle', array('name' => 'APB_nummer', "custom_group_id" => $this->customGroups['betalingsgegevens']['id']));
		$this->customGroups['persoonsgegevens']				= $this->civiapi('CustomGroup', 'GetSingle', array('name' => 'contact_individual'));
		$this->customFields['klantnummer']					= $this->civiapi('CustomField', 'GetSingle', array('name' => 'klantnummer', "custom_group_id" => $this->customGroups['persoonsgegevens']['id']));
		
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
		if($this->fetchContributions()) {
			$this->generateFileRows();
			$this->generateImp();
			$this->addExportToDatabase();
			parent::postProcess();
			CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/boekhouding-exports'));
		} else {
			$this->assign("errorMessage", "Geen participanten gevonden in de opgegeven datum range.");
		}
	}
	
	private function fetchContributions() {
		$this->getFieldsAndSettings();
		$query = "
			SELECT 
				`cc`.`id` as `ccid`,
				`cc`.*,
				`cco`.*,				
				`cp`.*, 
				`ce`.*,
				`cli`.`label`, `cli`.`qty`, 
				`ccf`.`".$this->customFields['volgnummer']['column_name']."` as `volgnummer`, 
				`cer`.`".$this->customFields['artikelcode']['column_name']."` as `artikelcode`,
				`cbw`.`".$this->customFields['betaalwijze']['column_name']."` as `betaalwijze`,
				`cbw`.`".$this->customFields['apbnummer']['column_name']."` as `apbnummer`,
				`cpg`.`".$this->customFields['klantnummer']['column_name']."` as `klantnummer`
			FROM `civicrm_contribution` as `cc`
			LEFT JOIN `civicrm_participant_payment` as `cpp` ON `cpp`.`contribution_id` = `cc`.`id`
			LEFT JOIN `civicrm_participant` as `cp` ON `cp`.`id` = `cpp`.`participant_id`
			LEFT JOIN `civicrm_event` as `ce` ON `ce`.`id` = `cp`.`event_id`
			LEFT JOIN `civicrm_line_item` as `cli` ON `cli`.`contribution_id` = `cp`.`id`
			LEFT JOIN `civicrm_contact` as `cco` ON `cco`.`id` = `cc`.`contact_id`
			LEFT JOIN `".$this->customGroups['aanvullende_info']['table_name']."` as `ccf` ON `ccf`.`entity_id` = `cc`.`id`
			LEFT JOIN `".$this->customGroups['event_registration']['table_name']."` as `cer` ON `cer`.`entity_id` = `cp`.`event_id`
			LEFT JOIN `".$this->customGroups['betalingsgegevens']['table_name']."` as `cbw` ON `cbw`.`entity_id` = `cp`.`id`
			LEFT JOIN `".$this->customGroups['persoonsgegevens']['table_name']."` as `cpg` ON `cpg`.`entity_id` = `cc`.`contact_id`
			WHERE `receive_date` BETWEEN '".$this->export['periode_start']."' AND '".$this->export['periode_stop']."'
			AND `cc`.`financial_type_id` = '".$this->statusses['event_fee']['id']."'
			AND `cc`.`contribution_status_id` IN (".$this->statusses['contribution']['pending']['value'].", ".$this->statusses['contribution']['in_progress']['value'].", ".$this->statusses['contribution']['partially_paid']['value'].", ".$this->statusses['contribution']['pending_refund']['value'].")
		";
		$dbData = CRM_Core_DAO::executeQuery($query);
		if($dbData->N) {
			$this->contributions = $dbData;
			return true;
		}
		return false;
	}
	
	private function generateFileRows() {
		while($this->contributions->fetch()) {
			$this->fileRows[] = array(
				'"',																				// Regelstarter
				'1',																				// RecType = 1
				$this->contributions->volgnummer,													// Volgnummer
				date("m", strtotime($this->contributions->register_date))."00", 					// Maand van registratie gevolgd door 00
				date("Ymd"),																		// Aanmaakdatum (vandaag) facturatiebestand
				$this->contributions->artikelcode,													// Artikelcode
				$this->fetchCustom("klantnummer", array("contribution" => $this->contributions)),	// Klantnummer
				$this->fetchCustom("quantity", array("contribution" => $this->contributions)),		// Quantity
				'   ',																				// Groepcode
				$this->fetchCustom("text", array("contribution" => $this->contributions)),			// Text
				date("Ymd"),																		// Aanmaakdatum (vandaag) facturatiebestand
				'"',																				// Regelstopper				
			);
			try { civicrm_api3('Contribution', 'create', array('id' => $this->contributions->ccid, 'contribution_status_id' => "Completed")); } catch(Exception $e) {}
		}
	}
	
	private function fetchCustom($action, $params) {
		switch($action) {
			/* Klantnummer */
			case 'klantnummer':
				if($params['contribution']->betaalwijze == "factuur" || $params['contribution']->betaalwijze == "overboeking") {
					if(!empty($params['contribution']->klantnummer)) {
						$returnString = str_pad($params['contribution']->klantnummer, 16, " " , STR_PAD_RIGHT);
					} else {
						$returnString = "####ONBEKEND####";
						$this->skipRow = true;
					}
				} else if ($params['contribution']->betaalwijze == "afhouding") {
					if(!empty($params['contribution']->apbnummer)) {
						$returnString = str_pad($params['contribution']->klantnummer, 16, " " , STR_PAD_RIGHT);
					} else {
						$returnString = "####ONBEKEND####";
						$this->skipRow = true;
					}
				}
				return $returnString;
			break;
			/* Quantity */
			case 'quantity':
				$type = ($params['contribution']->contribution_status_id != $this->statusses['contribution']['pending_refund']['value']) ? " " : "-";
				return $type.str_pad(intVal($params['contribution']->qty), 11, "0" , STR_PAD_LEFT);
			break;
			/* Text */
			case 'text': 
				$returnString = $params['contribution']->title." "; 								// Event titel
				$returnString .= date("d-m-Y", strtotime($params['contribution']->start_date))." "; // Event datum
				$returnString .= $params['contribution']->display_name." "; 						// Participant naam
				return str_pad($returnString, 80, ' ', STR_PAD_RIGHT);
			break;
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
	
	private function civiapi($param1, $param2, $param3) {
		try {
			return civicrm_api3($param1, $param2, $param3);
		} catch(Exception $e) {
			die("Failed to do action ".$param2." on entity ".$param1."<br>".$e.print_r($param3));
		}
	}
	

}