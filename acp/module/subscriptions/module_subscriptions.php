<?php
/**
 * Contains the subscriptions module for the ACP
 * 
 * @version			$Id: module_subscriptions.php 737 2008-05-23 18:26:46Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The subscriptions-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_subscriptions extends BS_ACP_Module
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_DELETE_SUBSCRIPTIONS => 'delete'
		);
	}
	
	public function run()
	{
		$end = 15;
		$num = BS_DAO::get_subscr()->get_count();
		$pagination = new BS_ACP_Pagination($end,$num);
		$site = $pagination->get_page();
		
		$order_vals = array('username','date','lastlogin','lastpost');
		$order = $this->input->correct_var('order','get',PLIB_Input::STRING,$order_vals,'date');
		$ad = $this->input->correct_var('ad','get',PLIB_Input::STRING,array('ASC','DESC'),'DESC');

		// display delete-message?
		$delete = $this->input->get_var('delete','post');
		if($delete != null && PLIB_Array_Utils::is_integer($delete))
		{
			$ids = PLIB_Array_Utils::advanced_implode(',',$delete);
			$def_params = '&amp;site='.$site.'&amp;order='.$order.'&amp;ad='.$ad;
			$yes_url = $this->url->get_acpmod_url(
				0,'&amp;at='.BS_ACP_ACTION_DELETE_SUBSCRIPTIONS.'&amp;ids='.$ids.$def_params
			);
			$no_url = $this->url->get_acpmod_url(0,$def_params);
			$this->functions->add_delete_message(
				$this->locale->lang('delete_subscriptions_question'),$yes_url,$no_url,''
			);
		}
	
		$base_url = $this->url->get_acpmod_url(0,'&amp;site='.$site.'&amp;');
		$this->tpl->add_variables(array(
			'target_url' => $base_url.'order='.$order.'&amp;ad='.$ad,
			'username_col' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('username'),'username','ASC',$order,$base_url
			),
			'date_col' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('date'),'date','DESC',$order,$base_url
			),
			'lastlogin_col' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('lastlogin'),'lastlogin','DESC',$order,$base_url
			),
			'lastpost_col' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('lastpost'),'lastpost','DESC',$order,$base_url
			)
		));

		switch($order)
		{
			case 'username':
				$sql_order = 'user_name';
				break;
			case 'date':
				$sql_order = 's.sub_date';
				break;
			case 'lastlogin':
				$sql_order = 'p.lastlogin';
				break;
			default:
				$sql_order = 't.lastpost_time';
				break;
		}
		
		$subscriptions = array();
		$sublist = BS_DAO::get_subscr()->get_all($sql_order,$ad,$pagination->get_start(),$end);
		foreach($sublist as $data)
		{
			if($data['forum_id'] > 0)
			{
				$url = $this->url->get_frontend_url(
					'&amp;'.BS_URL_ACTION.'=topics&amp;'.BS_URL_FID.'='.$data['forum_id']
				);
				$info = BS_TopicUtils::get_instance()->get_displayed_name($data['forum_name'],22);
				$name = '[<b>F</b>] <a target="_blank" href="'.$url.'"';
				$name .= ' title="'.$info['complete'].'">'.$info['displayed'].'</a>';
				if($data['flastpost_time'] == 0)
					$lastpost = $this->locale->lang('notavailable');
				else
					$lastpost = PLIB_Date::get_date($data['flastpost_time']);
			}
			else
			{
				$url = $this->url->get_frontend_url(
					'&amp;'.BS_URL_ACTION.'=redirect&amp;'.BS_URL_LOC.'=show_topic&amp;'.BS_URL_TID.'='.$data['topic_id']
				);
				$info = BS_TopicUtils::get_instance()->get_displayed_name($data['name'],22);
				$name = '[<b>T</b>] <a target="_blank" href="'.$url.'"';
				$name .= ' title="'.$info['complete'].'">'.$info['displayed'].'</a>';
				if($data['lastpost_time'] == 0)
					$lastpost = $this->locale->lang('notavailable');
				else
					$lastpost = PLIB_Date::get_date($data['lastpost_time']);
			}
			
			$subscriptions[] = array(
				'id' => $data['id'],
				'name' => $name,
				'username' => BS_ACP_Utils::get_instance()->get_userlink($data['user_id'],$data['user_name']),
				'subscription_date' => PLIB_Date::get_date($data['sub_date']),
				'lastlogin' => PLIB_Date::get_date($data['lastlogin']),
				'lastpost' => $lastpost
			);
		}

		$this->tpl->add_array('subscriptions',$subscriptions);

		$url = $this->url->get_acpmod_url(0,'&amp;order='.$order.'&amp;ad='.$ad.'&amp;site={d}');
		$this->functions->add_pagination($pagination,$url);
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('acpmod_subscriptions') => $this->url->get_acpmod_url()
		);
	}
}
?>