<?php
/**
 * Contains the user-profile-helper-class
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * The helper-class for the user-profile
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_UserProfile_Helper extends FWS_Singleton
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
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();

		$loc = $input->get_var(BS_URL_SUB,'get',FWS_Input::STRING);
		if($loc == 'pminbox' || $loc == 'pmoutbox' || $loc == 'pmoverview' || $loc == 'pmsearch' ||
			$loc == 'pmdetails')
		{
			$delete = $input->get_var('delete','post');
			if($delete == null)
			{
				$delstr = $input->get_var(BS_URL_DEL,'get',FWS_Input::STRING);
				$delete = FWS_Array_Utils::advanced_explode(',',$delstr);
			}
	
			$operation = $input->get_var('operation','post',FWS_Input::STRING);
			if($operation == null)
				$operation = $input->get_var(BS_URL_MODE,'get',FWS_Input::STRING);
	
			if($operation == 'delete' && $delete != null && FWS_Array_Utils::is_integer($delete))
			{
				$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
				$loc = $input->get_var(BS_URL_SUB,'get',FWS_Input::STRING);
				
				$yes_url = BS_URL::get_sub_url('userprofile',$loc == 'pmdetails' ? 'pmoverview' : $loc);
				$yes_url->set_sid_policy(BS_URL::SID_FORCE);
				$yes_url->set(BS_URL_AT,BS_ACTION_DELETE_PMS);
				$yes_url->set(BS_URL_SITE,$site);
				$yes_url->set(BS_URL_DEL,implode(',',$delete));
				
				if($back_url === null)
				{
					$url = BS_URL::get_sub_url('userprofile',$loc);
					if($loc == 'pmdetails')
						$url->set(BS_URL_ID,$input->get_var(BS_URL_ID,'get'));
					$url->set(BS_URL_SUB,$loc);
					$back_url = $url->to_url();
				}
				else
					$yes_url->set(BS_URL_ID,$input->get_var(BS_URL_ID,'get'));
				
				$names = array();
				foreach(BS_DAO::get_pms()->get_pms_of_user_by_ids($user->get_user_id(),$delete) as $data)
					$names[] = $data['pm_title'];
				$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
				
				$target_url = BS_URL::get_mod_url('redirect');
				$target_url->set(BS_URL_LOC,'del_pms');
				$target_url->set(BS_URL_ID,implode(',',$delete));
				$target_url->set(BS_URL_SITE,$site);
				
				$functions->add_delete_message(
					sprintf($locale->lang('pm_delete'),$namelist),
					$yes_url->to_url(),$back_url,$target_url->to_url()
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
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$img_pm_read = $user->get_theme_item_path('images/unread/pm_read.gif');
		$img_pm_unread = $user->get_theme_item_path('images/unread/pm_unread.gif');
		
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

		$tpl->set_template('userprofile_pm'.$type.'.htm');

		$loc = $input->get_var(BS_URL_SUB,'get',FWS_Input::STRING);
		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);

		$pmlist = BS_DAO::get_pms()->get_pms_in_folder(
			$type,$user->get_user_id(),$pagination->get_start(),$end
		);

		if($loc == 'pmoverview')
			$folder_num = count($pmlist).' '.$locale->lang('of').' '.$num;
		else
			$folder_num = '';

		if($mode == 'overview')
		{
			$title_url = BS_URL::build_sub_url('userprofile','pm'.$type);
			$title = '<a href="'.$title_url.'">'.$locale->lang('pm'.$type).'</a>';
		}
		else
			$title = $locale->lang('pm'.$type);
		
		if($mode != 'overview' || $type == 'inbox')
		{
			$murl = BS_URL::get_sub_url('userprofile',$loc);
			$murl->set_separator('&');
			$murl->set(BS_URL_SITE,$site);
			
			$tpl->set_template('inc_userprofile_pmjs.htm');
			$tpl->add_variables(array(
				'pm_target_url' => $murl->to_url(),
				'delete_add' => '&'.BS_URL_MODE.'=delete',
				'at_mark_read' => '&'.BS_URL_AT.'='.BS_ACTION_MARK_PMS_READ,
				'at_mark_unread' => '&'.BS_URL_AT.'='.BS_ACTION_MARK_PMS_UNREAD
			));
			$tpl->restore_template();
		}

		if($type == 'inbox')
		{
			$murl = BS_URL::get_mod_url('redirect');
			$murl->set(BS_URL_LOC,'pms');
			$murl->set(BS_URL_MODE,$loc);
			$murl->set(BS_URL_SITE,$site);
		}
		else
		{
			$murl = BS_URL::get_sub_url('userprofile',$loc);
			$murl->set(BS_URL_SITE,$site);
		}
		
		$tpl->add_variables(array(
			'title' => $title,
			$type.'_num' => $folder_num,
			'num' => $num,
			$type.'_url' => $murl->to_url()
		));
		
		$rurl = BS_URL::get_sub_url('userprofile','pmcompose');
		$durl = BS_URL::get_sub_url('userprofile','pmdetails');
		
		$pms = array();
		foreach($pmlist as $data)
		{
			$title = $data['pm_title'];
			$complete_title = '';
			if(FWS_String::strlen($title) > BS_MAX_PM_TITLE_LEN)
			{
				$complete_title = $title;
				$title = FWS_String::substr($title,0,BS_MAX_PM_TITLE_LEN) . ' ...';
			}

			if($data['user_name'] != '')
			{
				$sender = BS_UserUtils::get_link(
					$data[$other_uid_field],$data['user_name'],$data['user_group']
				);

				$reply = '';
				if($type == 'inbox')
				{
					$rurl->set(BS_URL_PID,$data['id']);
					$reply = '<a href="'.$rurl->to_url().'">'.$locale->lang('answer').'</a>';
				}
			}
			else
			{
				$sender = 'Boardsolution';
				$reply = $locale->lang('notavailable');
			}

			if($type == 'inbox' && $data['pm_read'] == 0)
			{
				$status_picture = $img_pm_unread;
				$status_title = $locale->lang('unread_pm');
			}
			else
			{
				$status_picture = $img_pm_read;
				$status_title = $locale->lang('read_pm');
			}
			
			$pms[] = array(
				'prefix' => $functions->get_pm_attachment_prefix($data['attachment_count']),
				'pm_title' => $title,
				'complete_title' => $complete_title,
				'date' => FWS_Date::get_date($data['pm_date']),
				'details_link' => $durl->set(BS_URL_ID,$data['id'])->to_url(),
				'status_title' => $status_title,
				'status_picture' => $status_picture,
				'sender' => $sender,
				'reply' => $reply,
				'pm_id' => $data['id']
			);
		}

		$tpl->add_variable_ref('pms',$pms);
		
		if($mode != 'overview')
		{
			$murl = BS_URL::get_mod_url('userprofile');
			$murl->set(BS_URL_SUB,'pm'.$type);
			$pagination->populate_tpl($murl);
		}
		
		$tpl->add_variables(array(
			'mode' => $mode
		));
		
		$tpl->restore_template();
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
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();

		if($this->_pms_perm == 0)
		{
			if(!$user->is_loggedin())
				$this->_pms_perm = -1;
			else if($user->get_profile_val('allow_pms') == 0 || $cfg['enable_pms'] == 0)
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
		$user = FWS_Props::get()->user();

		if($this->_inbox_num == -1)
			$this->_inbox_num = BS_DAO::get_pms()->get_count_in_folder('inbox',$user->get_user_id());
		
		return $this->_inbox_num;
	}
	
	/**
	 * @return int he number of PMs in the outbox
	 */
	public function get_outbox_num()
	{
		$user = FWS_Props::get()->user();

		if($this->_outbox_num == -1)
			$this->_outbox_num = BS_DAO::get_pms()->get_count_in_folder('outbox',$user->get_user_id());
		
		return $this->_outbox_num;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>