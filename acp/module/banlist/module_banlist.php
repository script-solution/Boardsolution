<?php
/**
 * Contains the banlist module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The banlist-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_banlist extends BS_ACP_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		
		$doc->add_action(BS_ACP_ACTION_DELETE_BANS,'delete');
		$doc->add_action(BS_ACP_ACTION_ADD_BAN,'add');
		$doc->add_action(BS_ACP_ACTION_UPDATE_BANS,'update');

		$doc->add_breadcrumb($locale->lang('acpmod_bans'),$url->get_acpmod_url());
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$cache = PLIB_Props::get()->cache();
		$locale = PLIB_Props::get()->locale();
		$functions = PLIB_Props::get()->functions();
		$url = PLIB_Props::get()->url();
		$tpl = PLIB_Props::get()->tpl();

		if(($delete = $input->get_var('delete','post')) != null)
		{
			$names = $cache->get_cache('banlist')->get_field_vals_of_keys($delete,'bann_name');
			$namelist = PLIB_StringHelper::get_enum($names,$locale->lang('and'));
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$url->get_acpmod_url(
					0,'&amp;ids='.implode(',',$delete).'&amp;at='.BS_ACP_ACTION_DELETE_BANS
				),
				$url->get_acpmod_url()
			);
		}
		
		$search = $input->get_var('search','get',PLIB_Input::STRING);
		$this->request_formular();
		
		$type_array = array(
			'ip' => $locale->lang('ip_address'),
			'user' => $locale->lang('username'),
			'mail' => $locale->lang('email_adress')
		);
	
		$entries = array();
		foreach($cache->get_cache('banlist')->get_elements() as $lang)
		{
			if(!$search || stripos($lang['bann_name'],$search) !== false ||
					stripos($lang['bann_type'],$search) !== false)
				$entries[] = $lang;
		}
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['at']);
		unset($hidden['search']);
		$tpl->add_variables(array(
			'types' => $type_array,
			'entries' => $entries,
			'action_type_update' => BS_ACP_ACTION_UPDATE_BANS,
			'action_type_add' => BS_ACP_ACTION_ADD_BAN,
			'search_url' => $input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search
		));
	}
}
?>