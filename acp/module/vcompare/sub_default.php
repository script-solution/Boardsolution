<?php
/**
 * Contains the default-submodule for vcompare
 * 
 * @version			$Id: sub_default.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the vcompare-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_vcompare_default extends BS_ACP_SubModule
{
	public function run()
	{
		$this->_request_formular();
		
		$http = new PLIB_HTTP('www.script-solution.de');
		$versions = $http->get('/bsversions/versions.xml');
		if($versions === false)
		{
			$this->_report_error(
				PLIB_Messages::MSG_TYPE_ERROR,$http->get_error_code().': '.$http->get_error_message()
			);
			return;
		}
		
		$xml = new SimpleXMLElement($versions);
		$cbversions = array();
		foreach($xml->version as $v)
			$cbversions[(string)$v['id']] = (string)$v;
		
		$this->tpl->add_variables(array(
			'current_version' => BS_VERSION,
			'versions' => $cbversions
		));
	}
	
	public function get_location()
	{
		return array();
	}
}
?>