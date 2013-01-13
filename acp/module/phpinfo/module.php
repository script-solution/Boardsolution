<?php
/**
 * Contains the phpinfo module for the ACP
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		// disable gzip here
		$doc->set_gzip(false);
		
		$renderer->add_breadcrumb($locale->lang('acpmod_phpinfo'),BS_URL::build_acpmod_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();

		$phpinfo = new ReflectionFunction('phpinfo');
		if($phpinfo->isDisabled())
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('phpinfo_disabled'));
			return;
		}
		
		ob_start();
		phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES | INFO_VARIABLES);
		$phpinfo = ob_get_contents();
		ob_clean();

		// extract the interesting part
		$bodypos = FWS_String::strpos($phpinfo,'<body>');
		$endbodypos = FWS_String::strpos($phpinfo,'</body>');
		$phpinfo = FWS_String::substr($phpinfo,$bodypos + 6,$endbodypos - ($bodypos + 6));
		
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