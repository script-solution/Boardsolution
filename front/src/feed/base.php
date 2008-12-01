<?php
/**
 * Contains the base-feed-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.feed
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The news-feed-base-class
 * 
 * @package			Boardsolution
 * @subpackage	src.feed
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Front_Feed_Base extends FWS_Object
{
	/**
	 * Returns the RSS-feed for the latest news
	 *
	 * @return string the XML-document
	 */
	public final function get_news()
	{
		$cfg = FWS_Props::get()->cfg();

		$fids = FWS_Array_Utils::advanced_explode(',',$cfg['news_forums']);
		if(!FWS_Array_Utils::is_integer($fids) || count($fids) == 0)
			return '';
		
		$denied = BS_ForumUtils::get_instance()->get_denied_forums(false);
		$denied = array_flip($denied);
		$myfids = array();
		foreach($fids as $fid)
		{
			if(!isset($denied[$fid]))
				$myfids[] = $fid;
		}
		
		$news = array();
		if(count($myfids) > 0)
		{
			$newslist = BS_DAO::get_posts()->get_news_from_forums($myfids,$cfg['news_count']);
			foreach($newslist as $data)
				$news[] = $data;
		}
		
		return $this->get_news_XML($news);
	}
	
	/**
	 * Builds the text from the given one
	 * 
	 * @param array $data the data of the news
	 * @return string the formated text to use
	 */
	protected final function get_formated_text($data)
	{
		// build text
		$use_bbcode = BS_PostingUtils::get_instance()->get_message_option('enable_bbcode') &&
			$data['use_bbcode'];
		$use_smileys = BS_PostingUtils::get_instance()->get_message_option('enable_smileys') &&
			$data['use_smileys'];
		$bbcode = new BS_BBCode_Parser($data['text'],'posts',$use_bbcode,$use_smileys);
		$bbcode->set_board_path(FWS_Path::outer());
		return $bbcode->get_message_for_output(true);
	}
	
	/**
	 * Builds the URL to the given topic
	 * 
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 * @return string the URL
	 */
	protected final function get_topic_url($fid,$tid)
	{
		$url = BS_URL::get_frontend_url('posts','&amp;',false);
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_TID,$tid);
		return $url->to_url();
	}
	
	/**
	 * Creates an XML-document in the appropriate syntax for the given news.
	 * The sub-classes should implement this method!
	 * 
	 * @param array $news all news with the data
	 * @return string the XML-document
	 */
	protected abstract function get_news_XML($news);
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>