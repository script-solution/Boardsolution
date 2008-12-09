<?php
/**
 * Contains the default-submodule for themes
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the themes-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_themes_default extends BS_ACP_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_ACP_ACTION_UPDATE_THEMES,'update');
		$renderer->add_action(BS_ACP_ACTION_DELETE_THEMES,'delete');
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

		$delete = $input->get_var('delete','post');
		if($delete != null)
		{
			$names = $cache->get_cache('themes')->get_field_vals_of_keys($delete,'theme_name');
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$url = BS_URL::get_acpsub_url();
			$url->set('at',BS_ACP_ACTION_DELETE_THEMES);
			$url->set('ids',implode(',',$delete));
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$url->to_url(),
				BS_URL::build_acpmod_url()
			);
		}
		
		$search = $input->get_var('search','get',FWS_Input::STRING);
		$this->request_formular();
		
		$tpl->add_variables(array(
			'at_update' => BS_ACP_ACTION_UPDATE_THEMES
		));
		
		$eurl = BS_URL::get_acpsub_url(0,'editor');
		
		$themes = array();
		foreach($cache->get_cache('themes') as $data)
		{
			if(!$search || stripos($data['theme_name'],$search) !== false ||
				stripos($data['theme_folder'],$search) !== false)
			{
				$eurl->set('theme',$data['theme_folder']);
				$themes[] = array(
					'id' => $data['id'],
					'theme_name' => $data['theme_name'],
					'theme_folder' => $data['theme_folder'],
					'edit_url' => $eurl->to_url()
				);
			}
		}
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variable_ref('themes',$themes);
		$tpl->add_variables(array(
			'search_url' => $input->get_var('PHP_SELF','server',FWS_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search
		));
	}
}
?>