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
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->add_action(BS_ACP_ACTION_DELETE_ERRORLOGS,'delete');
		$doc->add_action(BS_ACP_ACTION_DELETE_ALL_ERRORLOGS,'deleteall');
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

		$order = $input->correct_var(BS_URL_ORDER,'get',PLIB_Input::STRING,
			array('error','date','user'),'date');
		$ad = $input->correct_var(BS_URL_AD,'get',PLIB_Input::STRING,array('ASC','DESC'),'DESC');
		$search = $input->get_var('search','get',PLIB_Input::STRING);
		$site = $input->get_var('site','get',PLIB_Input::INTEGER);
		
		$params = '&amp;order='.$order.'&amp;ad='.$ad.'&amp;search='.$search.'&amp;site='.$site;
		
		if($input->isset_var('delete','post'))
		{
			$ids = $input->get_var('delete','post');
			$names = array();
			foreach(BS_DAO::get_logerrors()->get_by_ids($ids) as $data)
				$names[] = $data['message'];
			$namelist = PLIB_StringHelper::get_enum($names,$locale->lang('and'));
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$url->get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_DELETE_ERRORLOGS.$params.'&amp;ids='.implode(',',$ids)
				),
				$url->get_acpmod_url(0,$params)
			);
		}
		else if($input->get_var('ask','get',PLIB_Input::STRING) == 'deleteall')
		{
			$functions->add_delete_message(
				$locale->lang('delete_all_question'),
				$url->get_acpmod_url(0,'&amp;at='.BS_ACP_ACTION_DELETE_ALL_ERRORLOGS),
				$url->get_acpmod_url(0,$params)
			);
		}
		
		if($search != '')
			$num = BS_DAO::get_logerrors()->get_count_by_keyword($search);
		else
			$num = BS_DAO::get_logerrors()->get_count();
		$end = 15;
		$pagination = new BS_ACP_Pagination($end,$num);
		
		$baseurl = $url->get_acpmod_url(0,'&amp;search='.$search.'&amp;');
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variables(array(
			'form_url' => $url->get_acpmod_url(0,$params),
			'col_error' => BS_ACP_Utils::get_instance()->get_order_column(
				$locale->lang('error_msg'),'error','ASC',$order,$baseurl
			),
			'col_date' => BS_ACP_Utils::get_instance()->get_order_column(
				$locale->lang('date'),'date','DESC',$order,$baseurl
			),
			'col_user' => BS_ACP_Utils::get_instance()->get_order_column(
				$locale->lang('username'),'user','ASC',$order,$baseurl
			),
			'search_url' => $input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search,
			'delete_all_url' => $url->get_acpmod_url(0,$params.'&amp;ask=deleteall')
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
				'date' => PLIB_Date::get_date($data['date']),
				'query' => htmlspecialchars($data['query'],ENT_QUOTES),
				'error_msg' => $data['message'],
				'backtrace' => $backtrace
			);
		}
		
		$tpl->add_array('logs',$logs);
		$tpl->add_variables(array(
			'count' => count($loglist)
		));
		
		$murl = $url->get_acpmod_url(
			0,'&amp;order='.$order.'&amp;ad='.$ad.'&amp;search='.$search.'&amp;site={d}'
		);
		$functions->add_pagination($pagination,$murl);
	}
}
?>