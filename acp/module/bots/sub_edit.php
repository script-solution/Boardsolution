<?php
/**
 * Contains the edit-submodule for bots
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
 * The submodule for editing bots
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_bots_edit extends BS_ACP_SubModule
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
		$input = FWS_Props::get()->input();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_EDIT_BOT,'edit');
		$renderer->set_template('bots_edit.htm');
		
		$id = $input->get_var('id','get',FWS_Input::ID);
		$url = BS_URL::get_acpsub_url();
		$url->set('id',$id);
		$renderer->add_breadcrumb($locale->lang('edit_bot'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
		$id = $input->get_var('id','get',FWS_Input::ID);
		if($id == null)
		{
			$this->report_error();
			return;
		}
		
		$data = $cache->get_cache('bots')->get_element($id);
		if($data === null)
		{
			$this->report_error();
			return;
		}
		
		$this->request_formular();
		
		$site = $input->get_var('site','get',FWS_Input::INTEGER);
		$url = BS_URL::get_acpsub_url();
		$url->set('id',$id);
		$url->set('site',$site);
		$tpl->add_variables(array(
			'default' => $data,
			'site' => $site,
			'action_type' => BS_ACP_ACTION_EDIT_BOT,
			'title' => $locale->lang('edit_bot'),
			'form_target' => $url->to_url()
		));
	}
}
?>