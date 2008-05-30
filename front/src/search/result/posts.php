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
final class BS_Front_Search_Result_Posts extends PLIB_FullObject implements BS_Front_Search_Result
{
	public function get_name()
	{
		return 'posts';
	}
	
	public function display_result($search,$request)
	{
		/* @var $search BS_Front_Search_Manager */
		/* @var $request BS_Front_Search_Request */
		
		list($order,$ad) = $request->get_order();

		$ids = $search->get_result_ids();
		$idstr = implode(',',$ids);
		if($idstr == '')
			return;
		
		$end = $this->cfg['posts_per_page'];
		$pagination = new BS_Pagination($end,count($ids));
		$url = $this->url->get_url(
			0,'&amp;'.BS_URL_ID.'='.$search->get_search_id().'&amp;'.BS_URL_MODE.'='.$request->get_name()
				.'&amp;'.BS_URL_ORDER.'='.$order.'&amp;'.BS_URL_AD.'='.$ad.'&amp;'.BS_URL_SITE.'={d}'
		);
		$small_page_split = $this->functions->get_pagination_small($pagination,$url);

		$this->tpl->set_template('search_result_posts.htm');
		$this->tpl->add_variables(array(
			'small_page_split' => $this->locale->lang('page').' '.$small_page_split,
			'result_title' => '',
			'result_title' => $request->get_title($search)
		));
		
		$posts = array();
		$sql_order = BS_Front_Search_Utils::get_sql_order($order,$ad,'posts');
		$postcon = new BS_Front_Post_Container(0,0,$ids,$pagination,$sql_order);
		$keywords = $request->get_highlight_keywords();
		$postcon->set_highlight_keywords($keywords);
		$hl = new PLIB_KeywordHighlighter($keywords,'<span class="bs_highlight">');
		
		foreach($postcon->get_posts() as $post)
		{
			$post_url = $post->get_post_url();
			$location = BS_ForumUtils::get_instance()->get_forum_path($post->get_field('rubrikid'),false);
			$topic = $post->get_field('name');
			$topic = $hl->highlight($topic);
			$location .= ' &raquo; <a href="'.$post_url.'">'.$topic.'</a>';
			
			/* @var $post BS_Front_Post_Data */
			$posts[] = array(
				'user_name' => $post->get_username(),
				'user_group' => $post->get_user_group(),
				'location' => $location,
				'date' => PLIB_Date::get_date($post->get_field('post_time'),true),
				'posts_main_class' => $post->get_css_class('main'),
				'posts_left_class' => $post->get_css_class('left'),
				'posts_var_class' => $post->get_css_class('bar'),
				'avatar' => $post->get_avatar(),
				'show_separator' => !$post->is_last_post(),
				'text' => $post->get_post_text(false,false,false)
			);
		}
		
		$this->functions->add_pagination($pagination,$url);
		$this->tpl->add_array('posts',$posts);
		
		$this->tpl->restore_template();
	}
	
	public function get_template()
	{
		return 'search_result_posts.htm';
	}
	
	public function get_noresults_message()
	{
		return $this->locale->lang('no_posts_found');
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>