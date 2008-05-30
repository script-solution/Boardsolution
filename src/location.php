<?php
/**
 * Contains the location-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
final class BS_Location extends PLIB_FullObject
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
		$input = PLIB_Object::get_prop('input');
		$db = PLIB_Object::get_prop('db');
		$cache = PLIB_Object::get_prop('cache');
		$doc = PLIB_Object::get_prop('doc');
		
		$fid = $input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$action = $input->get_var(BS_URL_ACTION,'get',PLIB_Input::STRING);
	
		$location = '';
		if($doc->is_acp())
		{
			$loc = $input->get_var('loc','get',PLIB_Input::STRING);
			$location = 'acp:'.PLIB_FileUtils::get_name($loc,false);
		}
		else
		{
			if($fid == null && $tid == null)
			{
				switch($action)
				{
					case 'userdetails':
						$id = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
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
				$topic_data = $cache->get_cache('topic')->current();
				$location = $action.':'.$fid.':'.$tid.':'.$topic_data['name'];
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
		$parts = explode(':',$this->_location);
		if(count($parts) < 1)
			return $this->locale->lang('loc_index');
	
		if($parts[0] == 'acp')
		{
			// we don't want to display the acp-location in the frontend
			if(!$this->doc->is_acp())
				return $this->locale->lang('loc_index');
			
			if($this->locale->contains_lang('loc_acp_'.$parts[1]))
				return $this->locale->lang('loc_acp_'.$parts[1]);
	
			return $this->locale->lang('loc_acp_index');
		}
		
		switch($parts[0])
		{
			case 'userdetails':
				if(!isset($parts[1]) || !isset($parts[2]))
					return $this->locale->lang('loc_index');
				
				if($enable_links)
				{
					$url = $this->url->get_url('userdetails','&amp;'.BS_URL_ID.'='.$parts[1]);
					$link = '<a href="'.$url.'">'.$parts[2].'</a>';
				}
				else
					$link = $parts[2];
				
				return sprintf($this->locale->lang('loc_'.$parts[0]),$link);
			
			case 'topics':
			case 'new_topic':
			case 'new_poll':
			case 'new_event':
				if(!isset($parts[1]))
					return $this->locale->lang('loc_index');
	
				$forum_data = $this->forums->get_node_data($parts[1]);
				if($forum_data === null)
					return $this->locale->lang('loc_index');
	
				// intern and disallowed forum?
				if($this->cfg['hide_denied_forums'] == 1 && !$this->auth->has_access_to_intern_forum($parts[1]))
					return $this->locale->lang('loc_hidden');
	
				if($enable_links)
				{
					$forum_url = $this->url->get_topics_url($parts[1]);
					$forum = '<a href="'.$forum_url.'">'.$forum_data->get_name().'</a>';
				}
				else
					$forum = $forum_data->get_name();
	
				return sprintf($this->locale->lang('loc_'.$parts[0]),$forum);
	
			case 'posts':
			case 'new_post':
			case 'edit_post':
			case 'print':
				if(!isset($parts[1]) || !isset($parts[2]) || !isset($parts[3]))
					return $this->locale->lang('loc_index');
	
				// intern and disallowed forum?
				if($this->cfg['hide_denied_forums'] == 1 && !$this->auth->has_access_to_intern_forum($parts[1]))
					return $this->locale->lang('loc_hidden');
	
				// append the rest of the parts to the topic-title
				// because the title may contain ":"
				$topic_title = $parts[3];
				for($i = 4;$i < count($parts);$i++)
					$topic_title .= ':'.$parts[$i];
	
				if($enable_links)
				{
					$topic_url = $this->url->get_posts_url($parts[1],$parts[2]);
					$topic = '<a href="'.$topic_url.'">'.$topic_title.'</a>';
				}
				else
					$topic = $topic_title;
	
				return sprintf($this->locale->lang('loc_'.$parts[0]),$topic);
	
			default:
				if(!$this->locale->contains_lang('loc_'.$parts[0]))
					return $this->locale->lang('loc_index');
	
				return $this->locale->lang('loc_'.$parts[0]);
		}
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>