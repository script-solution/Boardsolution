<?php
/**
 * Contains the languages module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The languages-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_languages extends BS_ACP_Module
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
		
		$doc->add_action(BS_ACP_ACTION_ADD_LANGUAGE,'add');
		$doc->add_action(BS_ACP_ACTION_DELETE_LANGUAGES,'delete');
		$doc->add_action(BS_ACP_ACTION_UPDATE_LANGUAGES,'update');

		$doc->add_breadcrumb($locale->lang('acpmod_languages'),$url->get_acpmod_url());
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

		if($input->isset_var('delete','post'))
		{
			$ids = $input->get_var('delete','post');
			$names = $cache->get_cache('languages')->get_field_vals_of_keys($ids,'lang_name');
			$namelist = PLIB_StringHelper::get_enum($names,$locale->lang('and'));
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$url->get_acpmod_url(0,
					'&amp;at='.BS_ACP_ACTION_DELETE_LANGUAGES.'&amp;ids='.implode(',',$ids)
				),
				$url->get_acpmod_url()
			);
		}
		
		$search = $input->get_var('search','get',PLIB_Input::STRING);
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_UPDATE_LANGUAGES,
			'action_type_add' => BS_ACP_ACTION_ADD_LANGUAGE,
			'search_url' => $input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search
		));

		$languages = array();
		foreach($cache->get_cache('languages')->get_elements() as $lang)
		{
			if(!$search || stripos($lang['lang_name'],$search) !== false ||
					stripos($lang['lang_folder'],$search) !== false)
				$languages[] = $lang;
		}
		$tpl->add_array('languages',$languages);
	}
}
?>