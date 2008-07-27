<?php
/**
 * Contains the phpinfo module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The phpinfo-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_phpinfo extends BS_ACP_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		
		$doc->add_action(BS_ACP_ACTION_ACPACCESS_MODULE,'module');
		
		// disable gzip here
		$doc->set_gzip(false);

		$doc->add_breadcrumb($locale->lang('acpmod_phpinfo'),$url->get_acpmod_url());
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$tpl = PLIB_Props::get()->tpl();

		ob_start();
		phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES | INFO_VARIABLES);
		$phpinfo = ob_get_contents();
		ob_clean();

		// extract the interesting part
		$bodypos = PLIB_String::strpos($phpinfo,'<body>');
		$endbodypos = PLIB_String::strpos($phpinfo,'</body>');
		$phpinfo = PLIB_String::substr($phpinfo,$bodypos + 6,$endbodypos - ($bodypos + 6));
		
		// format to our own style
		$phpinfo = str_replace('<td>','<td class="a_main">',$phpinfo);
		$phpinfo = str_replace('td class="e"','td class="a_main"',$phpinfo);
		$phpinfo = str_replace('td class="v"','td class="a_main"',$phpinfo);
		$phpinfo = str_replace('<table border="0" cellpadding="3" width="600">',
														'<div class="a_border">'."\n"
													 .'<table class="a_table" cellpadding="0" cellspacing="0">',$phpinfo);
		$phpinfo = str_replace('</table>',
													 '		</table>'."\n"
													.'</div>',$phpinfo);
		$phpinfo = str_replace('<tr class="h">','<tr>',$phpinfo);
		$phpinfo = str_replace('<th colspan="2">','<td class="a_coldesc" colspan="2">',$phpinfo);
		$phpinfo = str_replace('<th>','<td class="a_coldesc">',$phpinfo);
		$phpinfo = str_replace('</th>','</td>',$phpinfo);
		$phpinfo = str_replace('<a ','<a target="_blank" ',$phpinfo);
		$phpinfo = str_replace('alt="Zend logo" /></a>','alt="Zend logo" /></a><br />',$phpinfo);
		$phpinfo = str_replace('<font','<span',$phpinfo);
		$phpinfo = str_replace('</font>','</span>',$phpinfo);
		
		$tpl->add_variables(array(
			'phpinfo' => $phpinfo
		));
	}
}
?>