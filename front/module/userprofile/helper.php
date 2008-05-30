<?php
/**
 * Contains the user-profile-helper-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The helper-class for the user-profile
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_UserProfile_Helper extends PLIB_Singleton
{
	/**
	 * @return BS_Front_Module_UserProfile_Helper the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * The number of PMs in the inbox
	 *
	 * @var int
	 */
	private $_inbox_num = -1;
	
	/**
	 * The number of PMs in the outbox
	 *
	 * @var int
	 */
	private $_outbox_num = -1;
	
	/**
	 * The permission for the PMs
	 *
	 * @var int
	 */
	private $_pms_perm = 0;
	
	/**
	 * Adds the pm-delete-message if necessary
	 * 
	 * TODO this is bullshit ;)
	 * 
	 * @param string $back_url the URL to cancel the delete-operation (null = default
	 */
	public function add_pm_delete_message($back_url = null)
	{
		$loc = $this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING);
		if($loc == 'pminbox' || $loc == 'pmoutbox' || $loc == 'pmoverview' || $loc == 'pmsearch')
		{
			$delete = $this->input->get_var('delete','post');
			if($delete == null)
			{
				$delstr = $this->input->get_var(BS_URL_DEL,'get',PLIB_Input::STRING);
				$delete = PLIB_Array_Utils::advanced_explode(',',$delstr);
			}
	
			$operation = $this->input->get_var('operation','post',PLIB_Input::STRING);
			if($operation == null)
				$operation = $this->input->get_var(BS_URL_MODE,'get',PLIB_Input::STRING);
	
			if($operation == 'delete' && $delete != null && PLIB_Array_Utils::is_integer($delete))
			{
				$add = '';
				if($back_url === null)
				{
					$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);
					$site_param = '&amp;'.BS_URL_SITE.'='.$site;
					$back_url = $this->url->get_url(0,'&amp;'.BS_URL_LOC.'='.$loc.$site_param);
				}
				else
					$add = '&amp;'.BS_URL_ID.'='.$this->input->get_var(BS_URL_ID,'get');
				
				$names = array();
				foreach(BS_DAO::get_pms()->get_pms_of_user_by_ids($this->user->get_user_id(),$delete) as $data)
					$names[] = $data['pm_title'];
				$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
				
				$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);
				$loc = $this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING);
				
				$site_param = '&amp;'.BS_URL_SITE.'='.$site;
				$action_param = '&amp;'.BS_URL_AT.'='.BS_ACTION_DELETE_PMS.'&amp;';
				$action_param .= BS_URL_DEL.'='.implode(',',$delete);
				
				$yes_url = $this->url->get_url(
					'userprofile','&amp;'.BS_URL_LOC.'='.$loc.$action_param.$site_param.$add,
					'&amp;',true
				);
				$target_url = $this->url->get_url(
					'redirect','&amp;'.BS_URL_LOC.'=del_pms&amp;'.BS_URL_ID.'='.implode(',',$delete).$site_param
				);
				
				$this->functions->add_delete_message(
					sprintf($this->locale->lang('pm_delete'),$namelist),
					$yes_url,$back_url,$target_url
				);
			}
		}
	}
	
	/**
	 * Builds the content of the PM-inbox
	 *
	 * @param string $type the type: inbox, outbox
	 * @param string $mode overview or detail
	 */
	public function add_folder($type = 'inbox',$mode = 'overview')
	{
		$img_pm_read = $this->user->get_theme_item_path('images/unread/pm_read.gif');
		$img_pm_unread = $this->user->get_theme_item_path('images/unread/pm_unread.gif');
		
		$num = $type == 'inbox' ? $this->get_inbox_num() : $this->get_outbox_num();
		$other_uid_field = $type == 'inbox' ? 'sender_id' : 'receiver_id';
		
		if($mode == 'overview')
		{
			$end = BS_PMS_OVERVIEW_PER_PAGE;
			$pagination = new BS_Pagination($end,$num);
		}
		else
		{
			$end = BS_PMS_FOLDER_PER_PAGE;
			$pagination = new BS_Pagination($end,$num);
		}

		$this->tpl->set_template('userprofile_pm'.$type.'.htm');

		$loc = $this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING);
		$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);

		$pmlist = BS_DAO::get_pms()->get_pms_in_folder(
			$type,$this->user->get_user_id(),$pagination->get_start(),$end
		);

		if($loc == 'pmoverview')
			$folder_num = count($pmlist).' '.$this->locale->lang('of').' '.$num;
		else
			$folder_num = '';

		if($mode == 'overview')
		{
			$title_url = $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=pm'.$type);
			$title = '<a href="'.$title_url.'">'.$this->locale->lang('pm'.$type).'</a>';
		}
		else
			$title = $this->locale->lang('pm'.$type);
		
		if($mode != 'overview' || $type == 'inbox')
		{
			$url = $this->url->get_url('userprofile','&'.BS_URL_LOC.'='.$loc.'&'.BS_URL_SITE.'='.$site,'&');
			
			$this->tpl->set_template('inc_userprofile_pmjs.htm');
			$this->tpl->add_variables(array(
				'pm_target_url' => $url,
				'delete_add' => '&'.BS_URL_MODE.'=delete',
				'at_mark_read' => '&'.BS_URL_AT.'='.BS_ACTION_MARK_PMS_READ,
				'at_mark_unread' => '&'.BS_URL_AT.'='.BS_ACTION_MARK_PMS_UNREAD
			));
			$this->tpl->restore_template();
		}

		if($type == 'inbox')
		{
			$url = $this->url->get_url(
				'redirect','&amp;'.BS_URL_LOC.'=pms&amp;'.BS_URL_MODE.'='.$loc.'&amp;'.BS_URL_SITE.'='.$site
			);
		}
		else
		{
			$url = $this->url->get_url(
				'userprofile','&amp;'.BS_URL_LOC.'='.$loc.'&amp;'.BS_URL_SITE.'='.$site
			);
		}
		
		$this->tpl->add_variables(array(
			'title' => $title,
			$type.'_num' => $folder_num,
			'num' => $num,
			$type.'_url' => $url
		));
		
		$pms = array();
		foreach($pmlist as $data)
		{
			$title = $data['pm_title'];
			$complete_title = '';
			if(PLIB_String::strlen($title) > BS_MAX_PM_TITLE_LEN)
			{
				$complete_title = $title;
				$title = PLIB_String::substr($title,0,BS_MAX_PM_TITLE_LEN) . ' ...';
			}

			if($data['user_name'] != '')
			{
				$sender = BS_UserUtils::get_instance()->get_link(
					$data[$other_uid_field],$data['user_name'],$data['user_group']
				);

				$reply = '';
				if($type == 'inbox')
				{
					$reply_url = $this->url->get_url(
						'userprofile','&amp;'.BS_URL_LOC.'=pmcompose&amp;'.BS_URL_PID.'='.$data['id']
					);
					$reply = '<a href="'.$reply_url.'">'.$this->locale->lang('answer').'</a>';
				}
			}
			else
			{
				$sender = 'Boardsolution';
				$reply = $this->locale->lang('notavailable');
			}

			if($type == 'inbox' && $data['pm_read'] == 0)
			{
				$status_picture = $img_pm_unread;
				$status_title = $this->locale->lang('unread_pm');
			}
			else
			{
				$status_picture = $img_pm_read;
				$status_title = $this->locale->lang('read_pm');
			}
			
			$pms[] = array(
				'prefix' => $this->functions->get_pm_attachment_prefix($data['attachment_count']),
				'pm_title' => $title,
				'complete_title' => $complete_title,
				'date' => PLIB_Date::get_date($data['pm_date']),
				'details_link' => $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=pmdetails&amp;'.BS_URL_ID.'='.$data['id']),
				'status_title' => $status_title,
				'status_picture' => $status_picture,
				'sender' => $sender,
				'reply' => $reply,
				'pm_id' => $data['id']
			);
		}

		$this->tpl->add_array('pms',$pms);
		
		if($mode != 'overview')
		{
			$url = $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=pm'.$type.'&amp;'.BS_URL_SITE.'={d}');
			$this->functions->add_pagination($pagination,$url);
		}
		
		$this->tpl->add_variables(array(
			'mode' => $mode
		));
		
		$this->tpl->restore_template();
	}
	
	/**
	 * @return int the permission-type:
	 * 	<pre>
	 * 		1		=> ok
	 * 		-1	=> guest,
	 * 		-2	=> pms disabled
	 * 	</pre>
	 */
	public function get_pm_permission()
	{
		if($this->_pms_perm == 0)
		{
			if(!$this->user->is_loggedin())
				$this->_pms_perm = -1;
			else if($this->user->get_profile_val('allow_pms') == 0 || $this->cfg['enable_pms'] == 0)
				$this->_pms_perm = -2;
			else
				$this->_pms_perm = 1;
		}
		
		return $this->_pms_perm;
	}
	
	/**
	 * @return int he number of PMs in the inbox
	 */
	public function get_inbox_num()
	{
		if($this->_inbox_num == -1)
			$this->_inbox_num = BS_DAO::get_pms()->get_count_in_folder('inbox',$this->user->get_user_id());
		
		return $this->_inbox_num;
	}
	
	/**
	 * @return int he number of PMs in the outbox
	 */
	public function get_outbox_num()
	{
		if($this->_outbox_num == -1)
			$this->_outbox_num = BS_DAO::get_pms()->get_count_in_folder('outbox',$this->user->get_user_id());
		
		return $this->_outbox_num;
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>