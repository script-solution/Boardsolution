<?php
/**
 * Contains the add-linklist-submodule
 * 
 * @package			Boardsolution
 * @subpackage	front.module
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
 * The add submodule for the linklist
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_linklist_add extends BS_Front_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_ADD_LINK,'addlink');
		$renderer->add_breadcrumb($locale->lang('addnewlink'),BS_URL::build_sub_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$auth = FWS_Props::get()->auth();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		if(!$auth->has_global_permission('add_new_link'))
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}
		
		$form = $this->request_formular(false,true);
	
		// show the preview
		if($input->isset_var('preview','post'))
			BS_PostingUtils::add_post_preview('desc');
	
		// collect the categories
		$categories = array();
		foreach(BS_DAO::get_links()->get_categories() as $name)
			$categories[$name] =$name;
	
		$pform = new BS_PostingForm($locale->lang('description'),'','desc');
		$pform->set_textarea_height('100px');
		$pform->add_form();
		
		$tpl->add_variables(array(
			'back_url' => BS_URL::build_mod_url(),
			'target_url' => BS_URL::build_sub_url(),
			'category_combo' => $form->get_combobox('link_category',$categories,''),
			'action_type' => BS_ACTION_ADD_LINK,
		));
	}
	
}
?>