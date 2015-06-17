<?php

require_once 'export.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function export_civicrm_config(&$config) {
  _export_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function export_civicrm_xmlMenu(&$files) {
  _export_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function export_civicrm_install() {
  return _export_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function export_civicrm_uninstall() {
  return _export_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function export_civicrm_enable() {
  return _export_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function export_civicrm_disable() {
  return _export_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function export_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _export_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function export_civicrm_managed(&$entities) {
  return _export_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook civicrm_navigationMenu
 * 
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function export_civicrm_navigationMenu(&$params) {
  $boekhoudingExport = array (
    'name'          =>  ts('Boekhouding Export'),
    'url'           =>  CRM_Utils_System::url('civicrm/boekhouding-exports', '', true),
    'permission'    => 'administer CiviCRM',
  );
  _export_civix_insert_navigation_menu($params, 'Events', $boekhoudingExport);
}
