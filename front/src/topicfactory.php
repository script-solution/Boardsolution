<?php
/**
 * Contains the topic-factory
 * 
 * @package			Boardsolution
 * @subpackage	front.src
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
 * This class is intended to provide methods that build lists of topics.
 * Note that this class is implemented as singleton.
 *
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_TopicFactory extends FWS_UtilBase
{
	/**
	 * The topic-data (to grab it just once from the db)
	 *
	 * @var array
	 */
	private static $_topic = false;
	
	/**
	 * Grabs the data of the current topic (uses the topic- and forum-id got via GET), just once,
	 * from the database and returns it.
	 *
	 * @return array the topic-data or null if the parameters are not available
	 */
	public static function get_current_topic()
	{
		$input = FWS_Props::get()->input();

		if(self::$_topic !== false)
			return self::$_topic;
		
		self::$_topic = null;
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
	
		if($tid != null && $fid != null)
		{
			self::$_topic = BS_DAO::get_topics()->get_topic_for_cache($fid,$tid);
			if(self::$_topic === false)
			{
				self::$_topic = null;
				return null;
			}
			return self::$_topic;
		}
		
		return null;
	}
	
	/**
	 * Builds similar topics based on the given title
	 * 
	 * @param string $title the title of the current topic
	 * @param int $tid the id of the current topic
	 * @param BS_URL $curl the current url
	 */
	public static function add_similar_topics($title,$tid,$curl)
	{
		if(!($curl instanceof BS_URL))
			FWS_Helper::def_error('instance','curl','BS_URL',$curl);
		
		$input = FWS_Props::get()->input();
		$functions = FWS_Props::get()->functions();
		$locale = FWS_Props::get()->locale();
		$cfg = FWS_Props::get()->cfg();
		// change similar-topics-display-state?
		if($input->get_var(BS_URL_LOC,'get',FWS_Input::STRING) == 'clap_similar_topics')
			$functions->clap_area('similar_topics');
	
		$search_string = '';
		$search_words = array();
		$words = FWS_StringHelper::get_words($title);
		$ignore = BS_Front_Search_Utils::get_ignore_words();
		foreach(array_keys($words) as $k)
		{
			if(isset($ignore[strtolower($k)]))
				continue;
	
			$search_string .= " OR t.name LIKE '%".$k."%'";
			$search_words[] = $k;
		}
	
		if($search_string != '')
			$search_string = ' AND ('.FWS_String::substr($search_string,4).')';
		else
			$search_string = ' AND t.name LIKE \'%'.$title.'%\'';
	
		$sql = ' t.id != '.$tid.' AND t.moved_tid = 0'.$search_string;
	
		// build search link
		$murl = BS_URL::get_mod_url('search');
		$murl->set(BS_URL_MODE,'similar_topics');
		$murl->set(BS_URL_KW,implode(' ',$search_words));
		$topics_title = sprintf($locale->lang('similar_topics'),$title);
		$topics_title = '<a href="'.$murl->to_url().'">'.$topics_title.'</a>';
	
		// display the topics
		$topics = new BS_Front_Topics(
			$topics_title,$sql,'lastpost','DESC',$cfg['similar_topic_num'],0,true
		);
		$topics->set_show_topic_action(false);
		$topics->set_show_important_first(false);
		$topics->set_show_forum(true);
		$topics->set_middle_width(60);
	
		$curl->set(BS_URL_LOC,'clap_similar_topics');
		$clap_data = $functions->get_clap_data('similar_topics',$curl->to_url());
		
		$topics->set_tbody_content($clap_data['divparams']);
		$topics->set_left_content($clap_data['link']);
		$topics->add_topics();
	}
	
	/**
	 * Builds the latest topics for a full view. (The latest_topics-module)
	 *
	 * @param int $fid the forum-id from which you want to display the latest topics
	 */
	public static function add_latest_topics_full($fid = 0)
	{
		$cfg = FWS_Props::get()->cfg();
		$infos = self::_get_latest_topics_infos($fid);
		
		$num = $cfg['threads_per_page'];
		$topics = new BS_Front_Topics($infos['title'],$infos['sql'],'lastpost','DESC',$num);
		$topics->set_show_topic_action(false);
		$topics->set_show_important_first(false);
		$topics->set_show_forum(true);
		$topics->set_middle_width(60);
		$topics->add_topics();
	
		$num = BS_DAO::get_topics()->get_count_by_search($topics->get_user_where_clause());
		$pagination = new BS_Pagination($cfg['threads_per_page'],$num);
		
		$murl = BS_URL::get_mod_url();
		$murl->set(BS_URL_FID,$fid);
		$pagination->populate_tpl($murl);
	}
	
	/**
	 * Builds the small version of the latest topics.
	 *
	 * @param int $fid the forum-id from which you want to display the latest topics
	 */
	public static function add_latest_topics_small($fid = 0)
	{
		$input = FWS_Props::get()->input();
		$functions = FWS_Props::get()->functions();
		$cfg = FWS_Props::get()->cfg();
		if($input->get_var(BS_URL_LOC,'get',FWS_Input::STRING) == 'clap_current_topics')
			$functions->clap_area('current_topics');
		
		$infos = self::_get_latest_topics_infos($fid);
		
		$murl = BS_URL::get_mod_url('latest_topics');
		if($fid > 0)
			$murl->set(BS_URL_FID,$fid);
		$title = '<a href="'.$murl->to_url().'">'.$infos['title'].'</a>';
		
		// display the topics
		$num = $cfg['current_topic_num'];
		$topics = new BS_Front_Topics($title,$infos['sql'],'lastpost','DESC',$num,0,true);
		$topics->set_show_topic_action(false);
		$topics->set_show_important_first(false);
		$topics->set_middle_width(60);
		
		$murl = BS_URL::get_mod_url('forums');
		if($fid > 0)
			$murl->set(BS_URL_FID,$fid);
		$murl->set(BS_URL_LOC,'clap_current_topics');
		$clap_data = $functions->get_clap_data('current_topics',$murl->to_url());
		
		$topics->set_tbody_content($clap_data['divparams']);
		$topics->set_left_content($clap_data['link']);
		$topics->add_topics();
	}
	
	/**
	 * Collects some infos for the latest topics
	 * 
	 * @param int $fid the forum-id
	 * @return array an array of the form: <code>array('sql' => ...,'title' => ...)</code>
	 */
	private static function _get_latest_topics_infos($fid)
	{
		$forums = FWS_Props::get()->forums();
		$locale = FWS_Props::get()->locale();

		if($fid > 0)
		{
			$ids = array($fid);
			$subforums = $forums->get_sub_nodes($fid);
			$len = count($subforums);
			for($i = 0;$i < $len;$i++)
				$ids[] = $subforums[$i]->get_id();
	
			$sql = ' t.rubrikid IN ('.implode(',',$ids).') AND t.moved_tid = 0';
			$title = sprintf($locale->lang('current_topics_in'),$forums->get_forum_name($fid));
		}
		else
		{
			$sql = ' t.moved_tid = 0';
			$title = $locale->lang('current_topics');
		}
		
		return array(
			'sql' => $sql,
			'title' => $title
		);
	}
}
?>