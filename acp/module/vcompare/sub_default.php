<?php
/**
 * Contains the default-submodule for vcompare
 * 
 * @version			$Id$
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
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();

		$this->request_formular();
		
		$http = new FWS_HTTP('www.script-solution.de');
		$versions = $http->get('/bsversions/versions.xml');
		if($versions === false)
		{
			$this->report_error(
				FWS_Document_Messages::ERROR,$http->get_error_code().': '.$http->get_error_message()
			);
			return;
		}
		
		$xml = new SimpleXMLElement($versions);
		$cbversions = array();
		foreach($xml->version as $v)
			$cbversions[(string)$v['id']] = (string)$v;
		
		$tpl->add_variables(array(
			'current_version' => BS_VERSION,
			'versions' => $cbversions
		));
	}
}
?>