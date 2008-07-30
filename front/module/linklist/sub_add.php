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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_ADD_LINK,'addlink');

		$renderer->add_breadcrumb(
			$locale->lang('addnewlink'),
			$url->get_url('linklist','&amp;'.BS_URL_LOC.'=add')
		);
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
		$url = FWS_Props::get()->url();

		if(!$auth->has_global_permission('add_new_link'))
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}
		
		$form = $this->request_formular(false,true);
	
		// show the preview
		if($input->isset_var('preview','post'))
			BS_PostingUtils::get_instance()->add_post_preview('desc');
	
		// collect the categories
		$categories = array();
		foreach(BS_DAO::get_links()->get_categories() as $name)
			$categories[$name] =$name;
	
		$pform = new BS_PostingForm($locale->lang('description'),'','desc');
		$pform->set_textarea_height('100px');
		$pform->add_form();
		
		$tpl->add_variables(array(
			'target_url' => $url->get_url(0,'&amp;'.BS_URL_LOC.'=add'),
			'category_combo' => $form->get_combobox('link_category',$categories,''),
			'action_type' => BS_ACTION_ADD_LINK,
			'back_url' => $url->get_url(0)
		));
	}
	
}
?>