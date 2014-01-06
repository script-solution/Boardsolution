<?php
/**
 * Contains the posts-search-result-class
 * 
 * @package			Boardsolution
 * @subpackage	front.src.search
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
 * The search-result displayed as posts
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Search_Result_Posts extends FWS_Object implements BS_Front_Search_Result
{
	public function get_name()
	{
		return 'posts';
	}
	
	public function display_result($search,$request)
	{
		$cfg = FWS_Props::get()->cfg();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
		/* @var $search BS_Front_Search_Manager */
		/* @var $request BS_Front_Search_Request */
		
		list($order,$ad) = $request->get_order();
		
		$ids = $search->get_result_ids();
		$idstr = implode(',',$ids);
		if($idstr == '')
			return;
		
		$end = $cfg['posts_per_page'];
		$pagination = new BS_Pagination($end,count($ids));
		$murl = BS_URL::get_mod_url();
		$murl->set(BS_URL_ID,$search->get_search_id());
		$murl->set(BS_URL_ORDER,$order);
		$murl->set(BS_URL_AD,$ad);
		$murl->set(BS_URL_SEARCH_MODE,BS_Front_Search_Utils::get_keyword_mode());
		$murl->set(BS_URL_MODE,$request->get_name());
		$murl->set(BS_URL_LOC,$this->get_name());
		foreach($request->get_url_params() as $name => $value)
			$murl->set($name,$value);
		
		$small_page_split = $pagination->get_small($murl);

		$public_url = clone $murl;
		$public_url->remove(BS_URL_ID);
		$public_url->remove(BS_URL_SITE);
		$public_url->remove(BS_URL_SID);
		
		$tpl->set_template('search_result_posts.htm');
		$tpl->add_variables(array(
			'small_page_split' => $locale->lang('page').' '.$small_page_split,
			'result_title' => '',
			'result_title' => $request->get_title($search),
			'publish_result_url' => $public_url->to_url()
		));
		
		$posts = array();
		$sql_order = BS_Front_Search_Utils::get_sql_order($order,$ad,'posts');
		$keywords = $request->get_highlight_keywords();
		$postcon = new BS_Front_Post_Container(0,0,$ids,$pagination,$sql_order,'',$keywords);
		$hl = new FWS_KeywordHighlighter($keywords,'<span class="bs_highlight">');
		
		// build highlight-param
		$kws = '';
		foreach($keywords as $kw)
			$kws .= '"'.$kw.'" ';
		$kws = rtrim($kws);
		
		foreach($postcon->get_posts() as $post)
		{
			/* @var $post BS_Front_Post_Data */
			
			$post_url = $post->get_post_url($kws);
			$location = BS_ForumUtils::get_forum_path($post->get_field('rubrikid'),false);
			$topic = $post->get_field('name');
			$topic = $hl->highlight($topic);
			$location .= ' &raquo; <a href="'.$post_url.'">'.$topic.'</a>';
			
			$posts[] = array(
				'user_name' => $post->get_username(),
				'user_name_plain' => $post->get_username(false),
				'user_group' => $post->get_user_group(),
				'location' => $location,
				'post_url' => $post_url,
				'date' => FWS_Date::get_date($post->get_field('post_time'),true),
				'posts_main_class' => $post->get_css_class('main'),
				'posts_left_class' => $post->get_css_class('left'),
				'posts_var_class' => $post->get_css_class('bar'),
				'avatar' => $post->get_avatar(),
				'show_separator' => !$post->is_last_post(),
				'text' => $post->get_post_text(false,false,false),
				'relevance' => round($post->get_field('relevance'),3)
			);
		}
		
		$pagination->populate_tpl($murl);
		$tpl->add_variable_ref('posts',$posts);
		
		$tpl->restore_template();
	}
	
	public function get_template()
	{
		return 'search_result_posts.htm';
	}
	
	public function get_noresults_message()
	{
		$locale = FWS_Props::get()->locale();

		return $locale->lang('no_posts_found');
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>