<?php
/**
 * Contains the bots module for the ACP
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
 * The bots-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_bots extends BS_ACP_SubModuleContainer
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct('bots',array('default','add','edit'),'default');
	}

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

		$renderer->add_breadcrumb($locale->lang('acpmod_bots'),BS_URL::build_acpmod_url());
		
		// init submodule
		$this->_sub->init($doc);
	}
	
	/**
	 * Checks the formular-values and returns the error-message, if any, or an array
	 * with the values
	 * 
	 * @return array|string the values
	 */
	public static function check_values()
	{
		$input = FWS_Props::get()->input();
		
		$bot_name = $input->get_var('bot_name','post',FWS_Input::STRING);
		$bot_match = $input->get_var('bot_match','post',FWS_Input::STRING);
		$bot_ip_start = $input->get_var('bot_ip_start','post',FWS_Input::STRING);
		$bot_ip_end = $input->get_var('bot_ip_end','post',FWS_Input::STRING);
		$bot_access = $input->get_var('bot_access','post',FWS_Input::INT_BOOL);

		if(trim($bot_name) == '')
			return 'bot_name_empty';

		if(trim($bot_match) == '')
			return 'bot_match_empty';

		if($bot_ip_start != '' && !preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',$bot_ip_start))
			return 'bot_invalid_start_ip';

		if($bot_ip_end != '' && !preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',$bot_ip_end))
			return 'bot_invalid_end_ip';
		
		if(($bot_ip_start != '' && $bot_ip_end == '') || ($bot_ip_start == '' && $bot_ip_end != ''))
			return 'bot_invalid_ip_range';
		
		return array(
			'bot_name' => $bot_name,
			'bot_match' => $bot_match,
			'bot_ip_start' => $bot_ip_start,
			'bot_ip_end' => $bot_ip_end,
			'bot_access' => $bot_access
		);
	}
}
?>