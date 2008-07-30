<?php
/**
 * Contains the topics-utils-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Contains several methods for topics. This class is realized as singleton.
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_TopicUtils extends FWS_Singleton
{
	/**
	 * @return BS_TopicUtils the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Checks wether the given type is locked for the current user
	 *
	 * @param int $locked the locked-status of the topic
	 * @param int $type the type: BS_LOCK_TOPIC_*
	 * @param int $post_locked is the post locked?
	 * @return boolean true if it's locked for the current user
	 */
	public function is_locked($locked,$type,$post_locked = 0)
	{
		$auth = FWS_Props::get()->auth();

		// nothing locked?
		if($locked == 0)
			return false;
		
		// if the user has permission to lock topics, it is not locked for her/him
		if($auth->has_current_forum_perm(BS_MODE_LOCK_TOPICS))
			return false;
		
		switch($type)
		{
			case BS_LOCK_TOPIC_EDIT:
			case BS_LOCK_TOPIC_OPENCLOSE;
				return ($locked & $type) != 0;
			
			case BS_LOCK_TOPIC_POSTS:
				if(($locked & $type) != 0)
					return true;
				
				return $post_locked == 1;
		}
		
		return false;
	}
	
	/**
	 * shortens a topic to the given length
	 *
	 * @param string $title the title of the topic
	 * @param int $length the max. length of the topic.
	 * 	<code>(0 = FWS_Props::get()->cfg()['thread_max_title_len'])</code>
	 * @return array an array of the form:
	 * 	<code>
	 * 		array(
	 * 			'displayed' => <displayedURL>,
	 * 			'complete' => <competeURL> // will be empty if it is short enough
	 * 		)
	 * 	</code>
	 */
	public function get_displayed_name($title,$length = 0)
	{
		$cfg = FWS_Props::get()->cfg();

		$max_length = ($length === 0) ? $cfg['thread_max_title_len'] : $length;
		return FWS_StringHelper::get_limited_string($title,$max_length);
	}
	
	/**
	 * determines the topic-status-images and -titles
	 *
	 * @param array $cache an associative array which contains some constant variables
	 * @param array $topic_data the topic-data
	 * @param boolean $is_unread is it an unread topic?
	 * @return array an associative array with all titles and images
	 */
	public function get_status_data($cache,$topic_data,$is_unread)
	{
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();

		$result = array();
		$is_hot = $topic_data['posts'] >= $cfg['thread_hot_posts_count'] ||
							$topic_data['views'] >= $cfg['thread_hot_views_count'];
		$unread = $is_unread ? '_new' : '';
	
		$important = ($topic_data['important'] == 1) ? '_en' : '_dis';
		$moved = ($topic_data['moved'] == 1) ? '_en' : '_dis';
		$closed = ($topic_data['thread_closed'] == 1) ? '_en' : '_dis';
		$hot = $is_hot ? '_en' : '_dis';
	
		$result['important_title'] = $locale->lang('important'.$unread.$important);
		$result['important_image'] = $cache['important'.$unread.$important];
	
		$result['moved_title'] = $locale->lang('moved'.$unread.$moved);
		$result['moved_image'] = $cache['moved'.$unread.$moved];
	
		$result['closed_title'] = $locale->lang('closed'.$unread.$closed);
		$result['closed_image'] = $cache['closed'.$unread.$closed];
	
		$result['hot_title'] = $locale->lang('hot'.$unread.$hot);
		$result['hot_image'] = $cache['hot'.$unread.$hot];
	
		return $result;
	}
	
	/**
	 * Builds a list with the selected topics
	 * 
	 * @param array $topics an array with the data of all selected topics
	 * @return array all selected topics: <code>array(array('symbol' => ...,'topic' => ...),...)</code>
	 */
	public function get_selected_topics($topics)
	{
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();

		$cache = array(
			'symbol_poll' =>				$user->get_theme_item_path('images/thread_type/poll.gif'),
			'symbol_event' =>				$user->get_theme_item_path('images/thread_type/event.gif')
		);
		
		$res = array();
		foreach($topics as $data)
		{
			$symbol = $this->get_symbol($cache,$data['type'],$data['symbol']);
			if($data['important'] == 1)
				$symbol .= ' <b>'.$locale->lang('important').': </b>';

			$topic = $this->get_displayed_name($data['name']);

			$topic_id = ($data['moved_tid'] > 0) ? $data['moved_tid'] : $data['id'];
			$forum_id = ($data['moved_rid'] > 0) ? $data['moved_rid'] : $data['rubrikid'];
			$murl = BS_URL::get_posts_url($forum_id,$topic_id);
			
			$topic_path = BS_ForumUtils::get_instance()->get_forum_path($data['rubrikid'],false);
			$topic_path .= ' &raquo; <a href="'.$murl.'">';
			$topic_path .= '<span title="'.$topic['complete'].'">';
			if($data['moved_tid'] > 0)
				$topic_path .= '<i>'.$topic['displayed'].'</i>';
			else
				$topic_path .= $topic['displayed'];
			$topic_path .= '</span></a>';

			$res[] = array('symbol' => $symbol,'topic' => $topic_path);
		}
		
		return $res;
	}
	
	/**
	 * determines the symbol of a topic
	 *
	 * @param array $cache an associative array which contains some constant variables
	 * @param int $topic_type the type of the topic
	 * @param int $symbol the symbol (may be 0)
	 * @return string the image
	 */
	public function get_symbol($cache,$topic_type,$symbol)
	{
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();

		$result = '&nbsp;';
	
		if($symbol != 0)
		{
			$img = $user->get_theme_item_path('images/thread_type/symbol_'.$symbol.'.gif');
			$result = '<img src="'.$img.'" title="'.$locale->lang('thread').'"';
			$result .= ' alt="'.$locale->lang('thread').'" />'."\n";
		}
		else if($topic_type > 0)
		{
			$result = '<img src="'.$cache['symbol_poll'].'" title="'.$locale->lang('poll').'"';
			$result .= ' alt="'.$locale->lang('poll').'" />'."\n";
		}
		else if($topic_type == -1)
		{
			$result = '<img src="'.$cache['symbol_event'].'" title="'.$locale->lang('event').'"';
			$result .= ' alt="'.$locale->lang('event').'" />'."\n";
		}
	
		return $result;
	}
	
	/**
	 * generates the symbols for topics
	 *
	 * @param BS_HTML_Formular $form the formular
	 * @param int $select the symbol to select
	 * @return string the symbols
	 */
	public function get_symbols($form,$select = 0)
	{
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();

		$symbols = '';
		for($i = 0;$i <= BS_NUMBER_OF_TOPIC_ICONS;$i++)
		{
			$selected = $form->get_radio_value('symbol',$i,$select == $i);
			$symbols .= '<input id="t'.$i.'" '.$selected.' type="radio" name="symbol"';
			$symbols .= ' value="'.$i.'" />'."\n".'<label for="t'.$i.'">';
	
			if($i == 0)
				$symbols .= $locale->lang('noicon');
			else
			{
				$img = $user->get_theme_item_path('images/thread_type/symbol_'.$i.'.gif');
				$symbols .= '<img src="'.$img.'" alt="" />';
			}
	
			$symbols .= '</label>';
			if($i < BS_NUMBER_OF_TOPIC_ICONS)
				$symbols .= '&nbsp;';
		}
	
		return $symbols;
	}
	
	/**
	 * generates the topic-moderation-combobox
	 *
	 * @param string $location your location (topics / posts)
	 * @param boolean $is_closed is the topic closed? (just if $location == 'posts')
	 * @return string the result
	 */
	public function get_action_combobox($location = 'topics',$is_closed = false)
	{
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();

		$edit_perm				= $cfg['display_denied_options'] ||
												$auth->has_global_permission('edit_own_threads') ||
												$auth->has_current_forum_perm(BS_MODE_EDIT_TOPIC);
		$openclose_perm		= $cfg['display_denied_options'] ||
												$auth->has_global_permission('openclose_own_threads') ||
												$auth->has_current_forum_perm(BS_MODE_OPENCLOSE_TOPICS);
		$delete_perm			= $cfg['display_denied_options'] ||
												$auth->has_global_permission('delete_own_threads') ||
												$auth->has_current_forum_perm(BS_MODE_DELETE_TOPICS);
		$move_perm				= $cfg['display_denied_options'] ||
												$auth->has_current_forum_perm(BS_MODE_MOVE_TOPICS);
		$mark							= $cfg['display_denied_options'] ||
												$user->is_loggedin();
		$lock_perm				= $cfg['display_denied_options'] ||
												$auth->has_current_forum_perm(BS_MODE_LOCK_TOPICS);
	
		$disabled = ' disabled="disabled" style="color: #AAAAAA;"';
	
		$var = '<select id="topic_action" name="topic_action"';
		$var .= ' onchange="performModAction(getModActionURL())">'."\n";
		$var .= '	<option value=""> - '.$locale->lang('please_choose').' - </option>'."\n";
		$var .= '	<option value="edit"'.($edit_perm ? '' : $disabled).'>';
		$var .= $locale->lang('edit_topic').'</option>'."\n";
	
		if($location == 'topics' || $is_closed)
		{
			$var .= '	<option value="open"'.($openclose_perm ? '' : $disabled).'>';
			$var .= $locale->lang($location == 'posts' ? 'open_topic' : 'open_topics');
			$var .= '</option>'."\n";
		}
	
		if($location == 'topics' || !$is_closed)
		{
			$var .= '	<option value="close"'.($openclose_perm ? '' : $disabled).'>';
			$var .= $locale->lang($location == 'posts' ? 'close_topic' : 'close_topics');
			$var .= '</option>'."\n";
		}
		
		$var .= ' <option value="lock"'.($lock_perm ? '' : $disabled).'>';
		$var .= $locale->lang($location == 'posts' ? 'lock_topic' : 'lock_topics');
		$var .= '</option>'."\n";
	
		$var .= '	<option value="delete"'.($delete_perm ? '' : $disabled).'>';
		$var .= $locale->lang($location == 'posts' ? 'delete_topic' : 'delete_topics');
		$var .= '</option>'."\n";
	
		$var .= '	<option value="move"'.($move_perm ? '' : $disabled).'>';
		$var .= $locale->lang($location == 'posts' ? 'move_topic' : 'move_topics');
		$var .= '</option>'."\n";
	
		if($location == 'topics')
		{
			$var .= '	<option value="mark_read"'.($mark ? '' : $disabled).'>';
			$var .= $locale->lang('mark_topics_read').'</option>'."\n";
			$var .= '	<option value="mark_unread"'.($mark ? '' : $disabled).'>';
			$var .= $locale->lang('mark_topics_unread').'</option>'."\n";
		}
		$var .= '</select>'."\n";
	
		return $var;
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>