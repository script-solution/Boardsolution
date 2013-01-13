<?php
/**
 * Contains the news-feed-module
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
 * Outputs the news in a given feed-syntax.
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_news_feed extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->use_raw_renderer();
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$cfg = FWS_Props::get()->cfg();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$doc = FWS_Props::get()->doc();

		if(!$cfg['enable_news_feeds'])
		{
			$this->report_error();
			return;
		}
		
		$mode = $input->correct_var(BS_URL_MODE,'get',FWS_Input::STRING,array('rss20','atom'),'rss20');
		
		// build feed document
		$fdoc = new FWS_Feed_Document(
			$cfg['forum_title'].' :: '.$locale->lang('news'),time(),BS_RSS_FEED_ENCODING
		);
		$fdoc->set_link(BS_URL::build_frontend_url(null,'&amp;',false));
		$fdoc->set_id(md5(FWS_Path::outer()));
		
		// add items
		foreach($this->get_items() as $item)
			$fdoc->add_item($item);
		
		// create formatter
		if($mode == 'rss20')
			$format = new FWS_Feed_Format_RSS20();
		else
			$format = new FWS_Feed_Format_Atom();
		
		// set header
		$doc->set_mimetype('application/xml');
		$doc->set_header('Content-Type','application/xml');
		
		// render xml
		$renderer = $doc->use_raw_renderer();
		$renderer->set_content($format->render($fdoc));
	}
	
	/**
	 * Returns the RSS-feed for the latest news
	 *
	 * @return array the feed-items
	 */
	private function get_items()
	{
		$cfg = FWS_Props::get()->cfg();

		$fids = FWS_Array_Utils::advanced_explode(',',$cfg['news_forums']);
		if(!FWS_Array_Utils::is_integer($fids) || count($fids) == 0)
			return array();
		
		// ensure that no one can see denied forums
		$denied = BS_ForumUtils::get_denied_forums(false);
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
			$url = BS_URL::get_frontend_url('posts','&amp;',false);
			$use_bbcode = BS_PostingUtils::get_message_option('enable_bbcode');
			$use_smileys = BS_PostingUtils::get_message_option('enable_smileys');
			foreach(BS_DAO::get_posts()->get_news_from_forums($myfids,$cfg['news_count']) as $data)
			{
				$bbcode = new BS_BBCode_Parser(
					$data['text'],'posts',$use_bbcode && $data['use_bbcode'],$use_smileys && $data['use_smileys']
				);
				$bbcode->set_board_path(FWS_Path::outer());
				
				if($data['post_user'] > 0)
					$username = $data['user_name'];
				else
					$username = $data['post_an_user'];
				
				$news[] = new FWS_Feed_Item(
					$data['threadid'],
					$data['name'],
					$bbcode->get_message_for_output(true),
					$url->set(BS_URL_FID,$data['rubrikid'])->set(BS_URL_TID,$data['threadid'])->to_url(),
					$username,
					$data['post_time']
				);
			}
		}
		
		return $news;
	}
}
?>