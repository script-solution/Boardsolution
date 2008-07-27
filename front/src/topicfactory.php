<?php
/**
 * Contains the topic-factory
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * This class is intended to provide methods that build lists of topics.
 * Note that this class is implemented as singleton.
 *
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_TopicFactory extends PLIB_Singleton
{
	/**
	 * @return BS_Front_TopicFactory the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * The topic-data (to grab it just once from the db)
	 *
	 * @var array
	 */
	private $_topic = false;
	
	/**
	 * Grabs the data of the current topic (uses the topic- and forum-id got via GET), just once,
	 * from the database and returns it.
	 *
	 * @return array the topic-data or null if the parameters are not available
	 */
	public function get_current_topic()
	{
		$input = PLIB_Props::get()->input();

		if($this->_topic !== false)
			return $this->_topic;
		
		$this->_topic = null;
		$tid = $input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$fid = $input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
	
		if($tid != null && $fid != null)
		{
			$this->_topic = BS_DAO::get_topics()->get_topic_for_cache($fid,$tid);
			if($this->_topic === false)
			{
				$this->_topic = null;
				return null;
			}
			return $this->_topic;
		}
		
		return null;
	}
	
	/**
	 * Builds similar topics based on the given title
	 *
	 * @param string $title the title of the current topic
	 * @param int $tid the id of the current topic
	 * @param string $current_url the current url
	 */
	public function add_similar_topics($title,$tid,$current_url)
	{
		$input = PLIB_Props::get()->input();
		$functions = PLIB_Props::get()->functions();
		$locale = PLIB_Props::get()->locale();
		$cfg = PLIB_Props::get()->cfg();
		$url = PLIB_Props::get()->url();

		// change similar-topics-display-state?
		if($input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING) == 'clap_similar_topics')
			$functions->clap_area('similar_topics');
	
		$search_string = '';
		$search_words = array();
		$words = PLIB_StringHelper::get_words($title);
		$ignore = $functions->get_search_ignore_words();
		foreach(array_keys($words) as $k)
		{
			if(isset($ignore[$k]))
				continue;
	
			$search_string .= " OR t.name LIKE '%".$k."%'";
			$search_words[] = $k;
		}
	
		if($search_string != '')
			$search_string = ' AND ('.PLIB_String::substr($search_string,4).')';
		else
			$search_string = ' AND t.name LIKE \'%'.$title.'%\'';
	
		$sql = ' t.id != '.$tid.' AND moved_tid = 0'.$search_string;
	
		// build search link
		$murl = $url->get_url(
			'search','&amp;'.BS_URL_MODE.'=similar_topics&amp;'.BS_URL_KW.'='.implode(' ',$search_words)
		);
		$topics_title = sprintf($locale->lang('similar_topics'),$title);
		$topics_title = '<a href="'.$murl.'">'.$topics_title.'</a>';
	
		// display the topics
		$topics = new BS_Front_Topics(
			$topics_title,$sql,'lastpost','DESC',$cfg['similar_topic_num'],0,true
		);
		$topics->set_show_topic_action(false);
		$topics->set_show_important_first(false);
		$topics->set_show_forum(true);
		$topics->set_middle_width(60);
	
		$clap_data = $functions->get_clap_data(
			'similar_topics',$current_url.'&amp;'.BS_URL_LOC.'=clap_similar_topics'
		);
		$topics->set_tbody_content($clap_data['divparams']);
		$topics->set_left_content($clap_data['link']);
		$topics->add_topics();
	}
	
	/**
	 * Builds the latest topics for a full view. (The latest_topics-module)
	 *
	 * @param int $fid the forum-id from which you want to display the latest topics
	 */
	public function add_latest_topics_full($fid = 0)
	{
		$cfg = PLIB_Props::get()->cfg();
		$functions = PLIB_Props::get()->functions();
		$url = PLIB_Props::get()->url();

		$infos = $this->_get_latest_topics_infos($fid);
		
		$num = $cfg['threads_per_page'];
		$topics = new BS_Front_Topics($infos['title'],$infos['sql'],'lastpost','DESC',$num);
		$topics->set_show_topic_action(false);
		$topics->set_show_important_first(false);
		$topics->set_show_forum(true);
		$topics->set_middle_width(60);
		$topics->add_topics();
	
		$murl = $url->get_url(0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_SITE.'={d}');
		$num = BS_DAO::get_topics()->get_count_by_search($topics->get_user_where_clause());
		$pagination = new BS_Pagination($cfg['threads_per_page'],$num);
		$functions->add_pagination($pagination,$murl);
	}
	
	/**
	 * Builds the small version of the latest topics.
	 *
	 * @param int $fid the forum-id from which you want to display the latest topics
	 */
	public function add_latest_topics_small($fid = 0)
	{
		$input = PLIB_Props::get()->input();
		$functions = PLIB_Props::get()->functions();
		$cfg = PLIB_Props::get()->cfg();
		$url = PLIB_Props::get()->url();

		if($input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING) == 'clap_current_topics')
			$functions->clap_area('current_topics');
		
		$infos = $this->_get_latest_topics_infos($fid);
		
		$fid_param = $fid > 0 ? '&amp;'.BS_URL_FID.'='.$fid : '';
		$murl = $url->get_url('latest_topics',$fid_param);
		$title = '<a href="'.$murl.'">'.$infos['title'].'</a>';
		
		// display the topics
		$num = $cfg['current_topic_num'];
		$topics = new BS_Front_Topics($title,$infos['sql'],'lastpost','DESC',$num,0,true);
		$topics->set_show_topic_action(false);
		$topics->set_show_important_first(false);
		$topics->set_show_forum(true);
		$topics->set_middle_width(60);
		
		$current_url = $url->get_url('forums',$fid > 0 ? '&amp;'.BS_URL_FID.'='.$fid : '');
		$clap_data = $functions->get_clap_data(
			'current_topics',$current_url.'&amp;'.BS_URL_LOC.'=clap_current_topics'
		);
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
	private function _get_latest_topics_infos($fid)
	{
		$forums = PLIB_Props::get()->forums();
		$locale = PLIB_Props::get()->locale();

		if($fid > 0)
		{
			$ids = array($fid);
			$subforums = $forums->get_sub_nodes($fid);
			$len = count($subforums);
			for($i = 0;$i < $len;$i++)
				$ids[] = $subforums[$i]->get_id();
	
			$sql = ' t.rubrikid IN ('.implode(',',$ids).') AND moved_tid = 0';
			$title = sprintf($locale->lang('current_topics_in'),$forums->get_forum_name($fid));
		}
		else
		{
			$sql = ' moved_tid = 0';
			$title = $locale->lang('current_topics');
		}
		
		return array(
			'sql' => $sql,
			'title' => $title
		);
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>