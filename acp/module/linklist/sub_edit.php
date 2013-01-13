<?php
/**
 * Contains the edit-submodule for the linklist
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
 * The edit submodule for the linklist
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_linklist_edit extends BS_ACP_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_EDIT_LINK,'edit');
		
		$id = $input->get_var('id','get',FWS_Input::ID);
		$url = BS_URL::get_acpsub_url();
		$url->set('id',$id);
		$renderer->add_breadcrumb($locale->lang('edit'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();

		$id = $input->get_var('id','get',FWS_Input::ID);
		if($id == null)
		{
			$this->report_error();
			return;
		}
		
		$data = BS_DAO::get_links()->get_by_id($id);
		if($data === false)
		{
			$this->report_error();
			return;
		}
		
		$options = array();
		foreach(BS_DAO::get_links()->get_categories() as $name)
			$options[$name] = $name;

		$this->request_formular();
		
		// load posting-form
		$form = new BS_PostingForm(
			$locale->lang('email_text').':',$data['link_desc_posted'],'desc'
		);
		
		// set colspan for the post-form-template
		$tpl->set_template('inc_post_form.htm');
		$tpl->add_variables(array(
			'colspan_main' => 1
		));
		$tpl->restore_template();
		
		$form->add_form();

		$tpl->add_variable_ref('data',$data);
		$tpl->add_variables(array(
			'id' => $id,
			'action_type' => BS_ACP_ACTION_EDIT_LINK,
			'categories' => $options
		));
	}
}
?>