<?php
/**
 * Contains the rss-feed-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.feed
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The RSS 2.0 implementation of the feed
 * 
 * @package			Boardsolution
 * @subpackage	src.feed
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Feed_RSS20 extends BS_Front_Feed_Base
{
	protected function _get_news_XML($news)
	{
		$date = PLIB_Date::get_formated_date('D, j M Y G:i:s T',time());
		
		$xml = '<?xml version="1.0" encoding="'.BS_RSS_FEED_ENCODING.'"?>'."\n";
		$xml .= '<rss version="2.0">'."\n";
		$xml .= '	<channel>'."\n";
		$xml .= '		<title>'.$this->cfg['forum_title'].' :: '.$this->locale->lang('news').'</title>'."\n";
		$xml .= '		<link>'.$this->url->get_frontend_url('','&amp;',false).'</link>'."\n";
		$xml .= '		<description></description>'."\n";
		$xml .= '		<pubDate>'.$date.'</pubDate>'."\n";
		$xml .= "\n";
		
		foreach($news as $data)
		{
			// author
			if($data['post_user'] > 0)
				$username = $data['user_name'];
			else
				$username = $data['post_an_user'];
			
			// date
			$pub_date = PLIB_Date::get_formated_date('D, j M Y G:i:s T',$data['post_time']);
			
			$xml .= '		<item>'."\n";
			$xml .= '			<title>'.$data['name'].'</title>'."\n";
			$xml .= '			<description><![CDATA['.$this->_get_formated_text($data).']]></description>'."\n";
			$xml .= '			<link>'.$this->_get_topic_url($data['rubrikid'],$data['threadid']).'</link>'."\n";
			$xml .= '			<author>'.$username.'</author>'."\n";
			$xml .= '			<pubDate>'.$pub_date.'</pubDate>'."\n";
			$xml .= '			<guid>'.$data['threadid'].'</guid>'."\n";
			$xml .= '		</item>'."\n";
		}
		
		$xml .= '	</channel>'."\n";
		$xml .= '</rss>'."\n";
		
		return $xml;
	}
}
?>