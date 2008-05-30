<?php
/**
 * Contains the topic-factory
 * 
 * @version			$Id: topicfactory.php 728 2008-05-22 22:09:30Z nasmussen $
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
	 * Builds similar topics based on the given title
	 *
	 * @param string $title the title of the current topic
	 * @param int $tid the id of the current topic
	 * @param string $current_url the current url
	 */
	public function add_similar_topics($title,$tid,$current_url)
	{
		// change similar-topics-display-state?
		if($this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING) == 'clap_similar_topics')
			$this->functions->clap_area('similar_topics');
	
		$search_string = '';
		$search_words = array();
		$words = PLIB_StringHelper::get_words($title);
		$ignore = $this->_get_search_ignore_words();
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
		$url = $this->url->get_url(
			'search','&amp;'.BS_URL_MODE.'=similar_topics&amp;'.BS_URL_KW.'='.implode(' ',$search_words)
		);
		$topics_title = sprintf($this->locale->lang('similar_topics'),$title);
		$topics_title = '<a href="'.$url.'">'.$topics_title.'</a>';
	
		// display the topics
		$topics = new BS_Front_Topics(
			$topics_title,$sql,'lastpost','DESC',$this->cfg['similar_topic_num'],0,true
		);
		$topics->set_show_topic_action(false);
		$topics->set_show_important_first(false);
		$topics->set_show_forum(true);
		$topics->set_middle_width(60);
	
		$clap_data = $this->functions->get_clap_data(
			'similar_topics',$current_url.'&amp;'.BS_URL_LOC.'=clap_similar_topics'
		);
		$topics->set_tbody_content($clap_data['divparams']);
		$topics->set_left_content($clap_data['link']);
		$topics->add_topics();
	}
	
	/**
	 * returns the search-ignore words
	 *
	 * @return array an associative array with all words to ignore:
	 * 	<code>
	 * 		array(<word> => true)
	 * 	</code>
	 */
	private function _get_search_ignore_words()
	{
		// we use the default-forum-language, because we guess that most of the posts will be in
		// this language
		$data = $this->cache->get_cache('languages')->get_element($this->cfg['default_forum_lang']);
		$lang = $data['lang_folder'];
		$file = PLIB_Path::inner().'language/'.$lang.'/search_words.txt';
	
		if(!file_exists($file))
			return array();
	
		$words = array();
		$lines = file($file);
		foreach($lines as $l)
		{
			$line = trim($l);
			if($line != '')
				$words[$line] = true;
		}
	
		return $words;
	}
	
	/**
	 * Builds the latest topics for a full view. (The latest_topics-module)
	 *
	 * @param int $fid the forum-id from which you want to display the latest topics
	 */
	public function add_latest_topics_full($fid = 0)
	{
		$infos = $this->_get_latest_topics_infos($fid);
		
		$num = $this->cfg['threads_per_page'];
		$topics = new BS_Front_Topics($infos['title'],$infos['sql'],'lastpost','DESC',$num);
		$topics->set_show_topic_action(false);
		$topics->set_show_important_first(false);
		$topics->set_show_forum(true);
		$topics->set_middle_width(60);
		$topics->add_topics();
	
		$url = $this->url->get_url(0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_SITE.'={d}');
		$num = BS_DAO::get_topics()->get_count_by_search($topics->get_user_where_clause());
		$pagination = new BS_Pagination($this->cfg['threads_per_page'],$num);
		$this->functions->add_pagination($pagination,$url);
	}
	
	/**
	 * Builds the small version of the latest topics.
	 *
	 * @param int $fid the forum-id from which you want to display the latest topics
	 */
	public function add_latest_topics_small($fid = 0)
	{
		if($this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING) == 'clap_current_topics')
			$this->functions->clap_area('current_topics');
		
		$infos = $this->_get_latest_topics_infos($fid);
		
		$fid_param = $fid > 0 ? '&amp;'.BS_URL_FID.'='.$fid : '';
		$url = $this->url->get_url('latest_topics',$fid_param);
		$title = '<a href="'.$url.'">'.$infos['title'].'</a>';
		
		// display the topics
		$num = $this->cfg['current_topic_num'];
		$topics = new BS_Front_Topics($title,$infos['sql'],'lastpost','DESC',$num,0,true);
		$topics->set_show_topic_action(false);
		$topics->set_show_important_first(false);
		$topics->set_show_forum(true);
		$topics->set_middle_width(60);
		
		$current_url = $this->url->get_url('forums',$fid > 0 ? '&amp;'.BS_URL_FID.'='.$fid : '');
		$clap_data = $this->functions->get_clap_data(
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
		if($fid > 0)
		{
			$ids = array($fid);
			$subforums = $this->forums->get_sub_nodes($fid);
			$len = count($subforums);
			for($i = 0;$i < $len;$i++)
				$ids[] = $subforums[$i]->get_id();
	
			$sql = ' t.rubrikid IN ('.implode(',',$ids).') AND moved_tid = 0';
			$title = sprintf($this->locale->lang('current_topics_in'),$this->forums->get_forum_name($fid));
		}
		else
		{
			$sql = ' moved_tid = 0';
			$title = $this->locale->lang('current_topics');
		}
		
		return array(
			'sql' => $sql,
			'title' => $title
		);
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>