<?php
/**
 * Contains the posts-search-result-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
		$functions = FWS_Props::get()->functions();
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
		$murl = BS_URL::get_url(
			0,'&amp;'.BS_URL_ID.'='.$search->get_search_id().'&amp;'.BS_URL_MODE.'='.$request->get_name()
				.'&amp;'.BS_URL_ORDER.'='.$order.'&amp;'.BS_URL_AD.'='.$ad.'&amp;'.BS_URL_SITE.'={d}'
		);
		foreach($request->get_url_params() as $name => $value)
			$murl .= '&amp;'.$name.'='.$value;
		$small_page_split = $functions->get_pagination_small($pagination,$murl);

		$tpl->set_template('search_result_posts.htm');
		$tpl->add_variables(array(
			'small_page_split' => $locale->lang('page').' '.$small_page_split,
			'result_title' => '',
			'result_title' => $request->get_title($search)
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
		$highlight_param = '&amp;'.BS_URL_HL.'='.urlencode($kws);
		
		foreach($postcon->get_posts() as $post)
		{
			$post_url = $post->get_post_url().$highlight_param;
			$location = BS_ForumUtils::get_instance()->get_forum_path($post->get_field('rubrikid'),false);
			$topic = $post->get_field('name');
			$topic = $hl->highlight($topic);
			$location .= ' &raquo; <a href="'.$post_url.'">'.$topic.'</a>';
			
			/* @var $post BS_Front_Post_Data */
			$posts[] = array(
				'user_name' => $post->get_username(),
				'user_group' => $post->get_user_group(),
				'location' => $location,
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
		
		$functions->add_pagination($pagination,$murl);
		$tpl->add_array('posts',$posts);
		
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
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>