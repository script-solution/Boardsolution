<?php
/**
 * Contains the location-class
 * 
 * @package			Boardsolution
 * @subpackage	src
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
 * Represents the location of the user. Stores the location as string, may create
 * the location from the current location in the board and can decode it to a message
 * that can be displayed.
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Location extends FWS_Object
{
	/**
	 * Creates an instance of this class from the current location
	 * in the board.
	 *
	 * @return BS_Location the location-object
	 */
	public static function get_instance()
	{
		// cache some properties
		$input = FWS_Props::get()->input();
		
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		$action = $input->get_var(BS_URL_ACTION,'get',FWS_Input::STRING);
	
		$location = '';
		if(defined('BS_ACP'))
		{
			$loc = $input->get_var('loc','get',FWS_Input::STRING);
			$location = 'acp:'.FWS_FileUtils::get_name($loc,false);
		}
		else
		{
			if($fid == null && $tid == null)
			{
				switch($action)
				{
					case 'userdetails':
						$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
						if($id != null)
						{
							$user = BS_DAO::get_user()->get_user_by_id($id);
							$location = $action.':'.$id.':'.$user['user_name'];
						}
						else
							$location = $action;
						break;
					case '':
						$location = 'index';
						break;
	
					default:
						$location = $action;
						break;
				}
			}
			else if($fid != null && $tid == null)
				$location = $action.':'.$fid;
			else if($fid != null && $tid != null)
			{
				$topic_data = BS_Front_TopicFactory::get_current_topic();
				if($topic_data !== null)
					$location = $action.':'.$fid.':'.$tid.':'.$topic_data['name'];
				else
					$location = $action;
			}
			else
				$location = $action;
		}
	
		return new BS_Location($location);
	}
	
	/**
	 * The location
	 *
	 * @var string
	 */
	private $_location;
	
	/**
	 * Constructor
	 * 
	 * @param string $location the location as string
	 */
	public function __construct($location)
	{
		parent::__construct();
		
		$this->_location = $location;
	}
	
	/**
	 * @return string the location-string
	 */
	public function get_location()
	{
		return $this->_location;
	}
	
	/**
	 * Decodes this location and creates the message to display where the user currently is
	 *
	 * @param boolean $enable_links do you want to use links?
	 * @return string the message to display
	 */
	public function decode($enable_links = true)
	{
		$locale = FWS_Props::get()->locale();
		$forums = FWS_Props::get()->forums();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$parts = explode(':',$this->_location);
		if(count($parts) < 1)
			return $locale->lang('loc_index');
	
		if($parts[0] == 'acp')
		{
			// we don't want to display the acp-location in the frontend
			if(!defined('BS_ACP'))
				return $locale->lang('loc_index');
			
			if($locale->contains_lang('loc_acp_'.$parts[1]))
				return $locale->lang('loc_acp_'.$parts[1]);
	
			return $locale->lang('loc_acp_index');
		}
		
		switch($parts[0])
		{
			case 'userdetails':
				if(!isset($parts[1]) || !isset($parts[2]))
					return $locale->lang('loc_index');
				
				if($enable_links)
				{
					$murl = BS_URL::get_mod_url('userdetails');
					$murl->set(BS_URL_ID,$parts[1]);
					$link = '<a href="'.$murl->to_url().'">'.$parts[2].'</a>';
				}
				else
					$link = $parts[2];
				
				return sprintf($locale->lang('loc_'.$parts[0]),$link);
			
			case 'topics':
			case 'new_topic':
			case 'new_poll':
			case 'new_event':
				if(!isset($parts[1]))
					return $locale->lang('loc_index');
	
				$forum_data = $forums->get_node_data($parts[1]);
				if($forum_data === null)
					return $locale->lang('loc_index');
	
				// intern and disallowed forum?
				if($cfg['hide_denied_forums'] == 1 && !$auth->has_access_to_intern_forum($parts[1]))
					return $locale->lang('loc_hidden');
	
				if($enable_links)
					$forum = BS_ForumUtils::get_forum_path($parts[1],false);
				else
					$forum = strip_tags(BS_ForumUtils::get_forum_path($parts[1],false));
	
				return sprintf($locale->lang('loc_'.$parts[0]),$forum);
	
			case 'posts':
			case 'new_post':
			case 'edit_post':
			case 'print':
				if(!isset($parts[1]) || !isset($parts[2]) || !isset($parts[3]))
					return $locale->lang('loc_index');
	
				// intern and disallowed forum?
				if($cfg['hide_denied_forums'] == 1 && !$auth->has_access_to_intern_forum($parts[1]))
					return $locale->lang('loc_hidden');
	
				// append the rest of the parts to the topic-title
				// because the title may contain ":"
				$topic_title = $parts[3];
				for($i = 4;$i < count($parts);$i++)
					$topic_title .= ':'.$parts[$i];
	
				if($enable_links)
				{
					$topic_url = BS_URL::build_posts_url($parts[1],$parts[2],1);
					$topic = '<a href="'.$topic_url.'">'.$topic_title.'</a>';
				}
				else
					$topic = $topic_title;
	
				return sprintf($locale->lang('loc_'.$parts[0]),$topic);
	
			default:
				if(!$locale->contains_lang('loc_'.$parts[0]))
					return $locale->lang('loc_index');
	
				return $locale->lang('loc_'.$parts[0]);
		}
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>