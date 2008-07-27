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
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->add_action(BS_ACP_ACTION_DELETE_BBCODES,'delete');
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$functions = PLIB_Props::get()->functions();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();

		$site = $input->get_var('site','get',PLIB_Input::ID);
		if($site == null)
			$site = 1;
	
		// display delete-message?
		if($input->isset_var('delete','post'))
		{
			$ids = $input->get_var('delete','post');
			$names = array();
			foreach(BS_DAO::get_bbcodes()->get_by_ids($ids) as $row)
				$names[] = $row['name'];
			$namelist = PLIB_StringHelper::get_enum($names,$locale->lang('and'));
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$url->get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_DELETE_BBCODES.'&amp;ids='.implode(',',$ids).'&amp;site='.$site
				),
				$url->get_acpmod_url(0,'&amp;site='.$site)
			);
		}
		
		$search = $input->get_var('search','get',PLIB_Input::STRING);
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
		
		$murl = $url->get_acpmod_url(0,'&amp;search='.$search.'&amp;site={d}');
		$functions->add_pagination($pagination,$murl);
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_array('tags',$tags);
		$tpl->add_variables(array(
			'site' => $site,
			'search_url' => $input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search
		));
	}
}
?>