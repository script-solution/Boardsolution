<?php
/**
 * Contains the atom-feed-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.feed
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The Atom implementation of the feed
 * 
 * @package			Boardsolution
 * @subpackage	src.feed
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Feed_Atom extends BS_Front_Feed_Base
{
	protected function get_news_XML($news)
	{
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();

		$date = FWS_Date::get_formated_date('Y-m-d\TH:i:s\Z',time());
		
		$xml = '<?xml version="1.0" encoding="'.BS_RSS_FEED_ENCODING.'"?>'."\n";
		$xml .= '<feed xmlns="http://www.w3.org/2005/Atom">'."\n";
		$xml .= '	<title>'.$cfg['forum_title'].' :: '.$locale->lang('news').'</title>'."\n";
		$xml .= '	<updated>'.$date.'</updated>'."\n";
		$xml .= '	<id>'.md5(FWS_Path::outer()).'</id>'."\n";
		$xml .= "\n";
		
		foreach($news as $data)
		{
			// author
			if($data['post_user'] > 0)
				$username = $data['user_name'];
			else
				$username = $data['post_an_user'];
			
			// date
			$pub_date = FWS_Date::get_formated_date('Y-m-d\TH:i:s\Z',$data['post_time']);
			
			$xml .= '	<entry>'."\n";
			$xml .= '		<title>'.$data['name'].'</title>'."\n";
			$xml .= '		<summary type="html"><![CDATA['.$this->get_formated_text($data).']]></summary>'."\n";
			$xml .= '		<author>'."\n";
			$xml .= '			<name>'.$username.'</name>'."\n";
			$xml .= '		</author>'."\n";
			$xml .= '		<link href="'.$this->get_topic_url($data['rubrikid'],$data['threadid']).'"/>'."\n";
			$xml .= '		<updated>'.$pub_date.'</updated>'."\n";
			$xml .= '		<id>'.$data['threadid'].'</id>'."\n";
			$xml .= '	</entry>'."\n";
		}
		
		$xml .= '</feed>'."\n";
		
		return $xml;
	}
}
?>