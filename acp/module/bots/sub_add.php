<?php
/**
 * Contains the add-submodule for bots
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
 * The submodule for adding bots
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_bots_add extends BS_ACP_SubModule
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
		
		$renderer->add_action(BS_ACP_ACTION_ADD_BOT,'add');
		$renderer->set_template('bots_edit.htm');
		$renderer->add_breadcrumb($locale->lang('add_bot'),BS_URL::build_acpsub_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
		$data = array(
			'bot_name' => '',
			'bot_match' => '',
			'bot_ip_start' => '',
			'bot_ip_end' => '',
			'bot_access' => 1
		);
		
		$this->request_formular();
		
		$site = $input->get_var('site','get',FWS_Input::INTEGER);
		$url = BS_URL::get_acpsub_url();
		$url->set('site',$site);
		$tpl->add_variables(array(
			'default' => $data,
			'site' => $site,
			'action_type' => BS_ACP_ACTION_ADD_BOT,
			'title' => $locale->lang('add_bot'),
			'form_target' => $url->to_url()
		));
	}
}
?>