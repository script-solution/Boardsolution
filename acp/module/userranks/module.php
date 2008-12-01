<?php
/**
 * Contains the user-ranks module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The user-ranks-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_userranks extends BS_ACP_Module
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
		
		$renderer->add_action(BS_ACP_ACTION_UPDATE_USERRANKS,'update');
		$renderer->add_action(BS_ACP_ACTION_ADD_USERRANK,'add');
		$renderer->add_action(BS_ACP_ACTION_DELETE_USERRANKS,'delete');

		$renderer->add_breadcrumb($locale->lang('acpmod_userranks'),BS_URL::build_acpmod_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();

		if(($ids = $input->get_var('delete','post')) != null)
		{
			$names = $cache->get_cache('user_ranks')->get_field_vals_of_keys($ids,'rank');
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$url = BS_URL::get_acpmod_url();
			$url->set('at',BS_ACP_ACTION_DELETE_USERRANKS);
			$url->set('ids',implode(',',$ids));
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),$url->to_url(),BS_URL::build_acpmod_url()
			);
		}
		
		$tpl->add_variables(array(
			'at_update' => BS_ACP_ACTION_UPDATE_USERRANKS,
			'at_add' => BS_ACP_ACTION_ADD_USERRANK
		));
	
		$ranks = $cache->get_cache('user_ranks')->get_elements();
		$tpl->add_array('ranks',$ranks);
	}
}
?>