<?php
/**
 * Contains the add-linklist-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The add submodule for the linklist
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_linklist_add extends BS_Front_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACTION_ADD_LINK => 'addlink'
		);
	}
	
	/**
	 * Displays the formular to add a link
	 */
	public function run()
	{
		if(!$this->auth->has_global_permission('add_new_link'))
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}
		
		$form = $this->_request_formular(false,true);
	
		// show the preview
		if($this->input->isset_var('preview','post'))
			BS_PostingUtils::get_instance()->add_post_preview('lnkdesc');
	
		// collect the categories
		$categories = array();
		foreach(BS_DAO::get_links()->get_categories() as $name)
			$categories[$name] =$name;
	
		$pform = new BS_PostingForm($this->locale->lang('description'),'','lnkdesc');
		$pform->set_textarea_height('100px');
		$pform->add_form();
		
		$this->tpl->add_variables(array(
			'target_url' => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=add'),
			'category_combo' => $form->get_combobox('link_category',$categories,''),
			'action_type' => BS_ACTION_ADD_LINK,
			'back_url' => $this->url->get_url(0)
		));
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('addnewlink') => $this->url->get_url('linklist','&amp;'.BS_URL_LOC.'=add')
		);
	}
}
?>