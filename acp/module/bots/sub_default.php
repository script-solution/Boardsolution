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
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_ACP_ACTION_DELETE_BOTS,'delete');
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
		// display delete-message?
		if($input->isset_var('delete','post'))
		{
			$ids = $input->get_var('delete','post');
			$site = $input->get_var('site','get',FWS_Input::ID);
			$names = $cache->get_cache('bots')->get_field_vals_of_keys($ids,'bot_name');
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$yurl = BS_URL::get_acpsub_url();
			$yurl->set('at',BS_ACP_ACTION_DELETE_BOTS);
			$yurl->set('ids',implode(',',$ids));
			$yurl->set('site',$site);
			
			$nurl = BS_URL::get_acpsub_url();
			$nurl->set('site',$site);
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$yurl->to_url(),
				$nurl->to_url()
			);
		}
		
		$search = $input->get_var('search','get',FWS_Input::STRING);
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
					'access' => BS_ACP_Utils::get_yesno($data['bot_access'])
				);
			}

			$row++;
		}
		
		$tpl->add_variable_ref('bots',$tplbots);
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variables(array(
			'site' => $site,
			'search_url' => 'admin.php',
			'hidden' => $hidden,
			'search_val' => $search
		));
		
		$murl = BS_URL::get_acpmod_url();
		$murl->set('search',$search);
		$pagination->populate_tpl($murl);
	}
}
?>