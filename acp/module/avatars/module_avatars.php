<?php
/**
 * Contains the avatar module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The avatar-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_avatars extends BS_ACP_Module
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
		
		$doc->add_action(BS_ACP_ACTION_DELETE_AVATARS,'delete');
		$doc->add_action(BS_ACP_ACTION_IMPORT_AVATARS,'import');

		$doc->add_breadcrumb($locale->lang('acpmod_avatars'),$url->get_acpmod_url());
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

		$delete = $input->get_var('delete','post');
		if($delete != null)
		{
			$ids = $input->get_var('delete','post');
			$names = array();
			foreach(BS_DAO::get_avatars()->get_by_ids($ids) as $data)
				$names[] = $data['av_pfad'];
			$namelist = PLIB_StringHelper::get_enum($names,$locale->lang('and'));
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$url->get_acpmod_url(0,
					'&amp;at='.BS_ACP_ACTION_DELETE_AVATARS.'&amp;ids='.implode(',',$ids)
				),
				$url->get_acpmod_url()
			);
		}
		
		$search = $input->get_var('search','get',PLIB_Input::STRING);
		if($search != '')
			$num = BS_DAO::get_avatars()->get_count_for_keyword($search);
		else
			$num = BS_DAO::get_avatars()->get_count();
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variables(array(
			'num' => $num,
			'action_type_import' => BS_ACP_ACTION_IMPORT_AVATARS,
			'search_url' => $input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search
		));

		$end = 10;
		$pagination = new BS_ACP_Pagination($end,$num);
		
		$avatars = array();
		if($search != '')
			$list = BS_DAO::get_avatars()->get_list_for_keyword($search,$pagination->get_start(),$end);
		else
			$list = BS_DAO::get_avatars()->get_list($pagination->get_start(),$end);
		
		foreach($list as $data)
		{
			if($data['user'] == 0)
				$data['owner'] = $locale->lang('administrator');
			else
				$data['owner'] = BS_ACP_Utils::get_instance()->get_userlink($data['user'],$data['user_name']);
			$avatars[] = $data;
		}

		$tpl->add_array('avatars',$avatars);
		$murl = $url->get_acpmod_url(0,'&amp;search='.$search.'&amp;site={d}');
		$functions->add_pagination($pagination,$murl);
	}
}
?>