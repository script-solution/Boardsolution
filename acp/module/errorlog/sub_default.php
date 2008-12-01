<?php
/**
 * Contains the default-submodule for errorlog
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the errorlog-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_errorlog_default extends BS_ACP_SubModule
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
		$renderer->add_action(BS_ACP_ACTION_DELETE_ERRORLOGS,'delete');
		$renderer->add_action(BS_ACP_ACTION_DELETE_ALL_ERRORLOGS,'deleteall');
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
		$order = $input->correct_var(BS_URL_ORDER,'get',FWS_Input::STRING,
			array('error','date','user'),'date');
		$ad = $input->correct_var(BS_URL_AD,'get',FWS_Input::STRING,array('ASC','DESC'),'DESC');
		$search = $input->get_var('search','get',FWS_Input::STRING);
		$site = $input->get_var('site','get',FWS_Input::INTEGER);
		
		$url = BS_URL::get_acpsub_url();
		$url->set('order',$order);
		$url->set('ad',$ad);
		$url->set('search',$search);
		$url->set('site',$site);
		
		if($input->isset_var('delete','post'))
		{
			$ids = $input->get_var('delete','post');
			$names = array();
			foreach(BS_DAO::get_logerrors()->get_by_ids($ids) as $data)
				$names[] = $data['message'];
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$yurl = clone $url;
			$yurl->set('at',BS_ACP_ACTION_DELETE_ERRORLOGS);
			$yurl->set('ids',implode(',',$ids));
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$yurl->to_url(),
				$url->to_url()
			);
		}
		else if($input->get_var('ask','get',FWS_Input::STRING) == 'deleteall')
		{
			$yurl = clone $url;
			$yurl->set('at',BS_ACP_ACTION_DELETE_ALL_ERRORLOGS);
			$functions->add_delete_message(
				$locale->lang('delete_all_question'),
				$yurl->to_url(),
				$url->to_url()
			);
		}
		
		if($search != '')
			$num = BS_DAO::get_logerrors()->get_count_by_keyword($search);
		else
			$num = BS_DAO::get_logerrors()->get_count();
		$end = 15;
		$pagination = new BS_ACP_Pagination($end,$num);
		
		$baseurl = clone $url;
		$baseurl->set('search',$search);
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		
		$askurl = clone $url;
		$askurl->set('ask','deleteall');
		$tpl->add_variables(array(
			'form_url' => $url->to_url(),
			'col_error' => BS_ACP_Utils::get_instance()->get_order_column(
				$locale->lang('error_msg'),'error','ASC',$order,$baseurl
			),
			'col_date' => BS_ACP_Utils::get_instance()->get_order_column(
				$locale->lang('date'),'date','DESC',$order,$baseurl
			),
			'col_user' => BS_ACP_Utils::get_instance()->get_order_column(
				$locale->lang('username'),'user','ASC',$order,$baseurl
			),
			'search_url' => $input->get_var('PHP_SELF','server',FWS_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search,
			'delete_all_url' => $askurl->to_url()
		));
		
		switch($order)
		{
			case 'error':
				$sql_order = 'l.message';
				break;
			case 'date';
				$sql_order = 'l.id';
				break;
			case 'user':
				$sql_order = 'u.`'.BS_EXPORT_USER_NAME.'`';
				break;
		}
		
		$logs = array();
		if($search != '')
		{
			$loglist = BS_DAO::get_logerrors()->get_list_by_keyword(
				$search,$sql_order,$ad,$pagination->get_start(),$end
			);
		}
		else
			$loglist = BS_DAO::get_logerrors()->get_list($sql_order,$ad,$pagination->get_start(),$end);
		
		foreach($loglist as $data)
		{
			if($data['user_id'] > 0)
				$user = BS_ACP_Utils::get_instance()->get_userlink($data['user_id'],$data['user_name']);
			else
				$user = '<i>'.$locale->lang('guest').'</i>';
			
			$backtrace = '<ul>';
			foreach(explode("\n",$data['backtrace']) as $call)
			{
				if(trim($call) != '')
					$backtrace .= '	<li>'.$call.'</li>'."\n";
			}
			$backtrace .= '</ul>';
			
			$logs[] = array(
				'user' => $user,
				'id' => $data['id'],
				'date' => FWS_Date::get_date($data['date']),
				'query' => htmlspecialchars($data['query'],ENT_QUOTES),
				'error_msg' => $data['message'],
				'backtrace' => $backtrace
			);
		}
		
		$tpl->add_array('logs',$logs);
		$tpl->add_variables(array(
			'count' => count($loglist)
		));
		
		$murl = BS_URL::get_acpmod_url();
		$murl->set('search',$search);
		$murl->set('order',$order);
		$murl->set('ad',$ad);
		$pagination->populate_tpl($murl);
	}
}
?>