<?php
/**
 * Contains the default-submodule for bots
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default submodule for bots
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_bots_default extends BS_ACP_SubModule
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->add_action(BS_ACP_ACTION_DELETE_BOTS,'delete');
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
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();

		// display delete-message?
		if($input->isset_var('delete','post'))
		{
			$ids = $input->get_var('delete','post');
			$site = $input->get_var('site','get',PLIB_Input::ID);
			$names = $cache->get_cache('bots')->get_field_vals_of_keys($ids,'bot_name');
			$namelist = PLIB_StringHelper::get_enum($names,$locale->lang('and'));
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$url->get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_DELETE_BOTS.'&amp;ids='.implode(',',$ids).'&amp;site='.$site
				),
				$url->get_acpmod_url(0,'&amp;site='.$site)
			);
		}
		
		$search = $input->get_var('search','get',PLIB_Input::STRING);
		$bots = array();
		foreach($cache->get_cache('bots') as $data)
		{
			if(!$search || stripos($data['bot_name'],$search) !== false ||
				stripos($data['bot_match'],$search) !== false ||
				stripos($data['bot_ip_start'],$search) !== false ||
				stripos($data['bot_ip_end'],$search) !== false)
				$bots[] = $data;
		}
		
		$num = count($bots);
		$end = 15;
		$pagination = new BS_ACP_Pagination($end,$num);
		$site = $pagination->get_page();

		$row = 0;
		$tplbots = array();
		$start = $site > 1 ? (($site - 1) * $end) : 0;
		foreach($bots as $data)
		{
			if($row >= $start + $end)
				break;

			if($row >= $start)
			{
				if($data['bot_ip_start'] == '')
					$ip_range = $locale->lang('notavailable');
				else if($data['bot_ip_start'] == $data['bot_ip_end'])
					$ip_range = $data['bot_ip_start'];
				else
					$ip_range = $data['bot_ip_start'].' ... '.$data['bot_ip_end'];
				
				$tplbots[] = array(
					'id' => $data['id'],
					'name' => $data['bot_name'],
					'match' => $data['bot_match'],
					'ip_range' => $ip_range,
					'access' => BS_ACP_Utils::get_instance()->get_yesno($data['bot_access'])
				);
			}

			$row++;
		}
		
		$tpl->add_array('bots',$tplbots);
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variables(array(
			'site' => $site,
			'search_url' => $input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search
		));
		
		$murl = $url->get_acpmod_url(0,'&amp;search='.$search.'&amp;site={d}');
		$functions->add_pagination($pagination,$murl);
	}
}
?>