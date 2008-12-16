<?php
/**
 * Contains the default-submodule for bbcode
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the bbcode-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_bbcode_default extends BS_ACP_SubModule
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
		$renderer->add_action(BS_ACP_ACTION_DELETE_BBCODES,'delete');
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();
		$site = $input->get_var('site','get',FWS_Input::ID);
		if($site == null)
			$site = 1;
	
		// display delete-message?
		if($input->isset_var('delete','post'))
		{
			$ids = $input->get_var('delete','post');
			$names = array();
			foreach(BS_DAO::get_bbcodes()->get_by_ids($ids) as $row)
				$names[] = $row['name'];
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$yurl = BS_URL::get_acpmod_url();
			$yurl->set('ids',implode(',',$ids));
			$yurl->set('at',BS_ACP_ACTION_DELETE_BBCODES);
			$yurl->set('site',$site);
			
			$nurl = BS_URL::get_acpmod_url();
			$nurl->set('site',$site);
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$yurl->to_url(),
				$nurl->to_url()
			);
		}
		
		$search = $input->get_var('search','get',FWS_Input::STRING);
		if($search != '')
			$num = BS_DAO::get_bbcodes()->get_count_by_keyword($search);
		else
			$num = BS_DAO::get_bbcodes()->get_count();
		$end = 15;
		$pagination = new BS_ACP_Pagination($end,$num);
		
		if($search != '')
			$bbclist = BS_DAO::get_bbcodes()->get_list_by_keyword($search,$pagination->get_start(),$end);
		else
			$bbclist = BS_DAO::get_bbcodes()->get_list($pagination->get_start(),$end);
		
		$tags = array();
		foreach($bbclist as $data)
		{
			if($locale->contains_lang('tag_type_'.$data['type']))
				$type = $locale->lang('tag_type_'.$data['type']);
			else
				$type = $data['type'];
			
			$tags[] = array(
				'id' => $data['id'],
				'name' => $data['name'],
				'type' => $type,
				'content' => $data['content'],
				'param' => $locale->lang('tag_param_'.$data['param'])
			);
		}
		
		$murl = BS_URL::get_acpmod_url();
		$murl->set('search',$search);
		$pagination->populate_tpl($murl);
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variable_ref('tags',$tags);
		$tpl->add_variables(array(
			'site' => $site,
			'search_url' => 'admin.php',
			'hidden' => $hidden,
			'search_val' => $search
		));
	}
}
?>