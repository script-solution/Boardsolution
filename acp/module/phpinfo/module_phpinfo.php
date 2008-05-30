<?php
/**
 * Contains the phpinfo module for the ACP
 * 
 * @version			$Id: module_phpinfo.php 705 2008-05-15 10:14:58Z nasmussen $
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
	 * Constructor
	 */
	public function __construct()
	{
		// disable gzip here
		$this->doc->set_use_gzip(false);
	}
	
	public function run()
	{
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
		
		$this->tpl->add_variables(array(
			'phpinfo' => $phpinfo
		));
	}
	
	public function get_location()
	{
		return array($this->locale->lang('acpmod_phpinfo') => $this->url->get_acpmod_url());
	}
}
?>