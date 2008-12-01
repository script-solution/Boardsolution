<?php
/**
 * Contains the topic-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * A class to display the topics. You have various options to manipulate and control the output
 * 
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Topics extends FWS_Object
{
	/**
	 * do you want to limit the query to a specific forum?
	 *
	 * @var integer
	 */
	private $_fid;

	/**
	 * the number of topics to display ( 0 = all )
	 *
	 * @var integer
	 */
	private $_number_of_topics;

	/**
	 * additional conditions for the query
	 *
	 * @var string
	 */
	private $_where_clause;

	/**
	 * the order: lastpost, topic_name, topic_type, topic_start, replies, views, relevance
	 *
	 * @var string
	 */
	private $_order;

	/**
	 * ascending or descending order? (ASC vs. DESC)
	 *
	 * @var string
	 */
	private $_ad;

	/**
	 * the title of the table with the topics
	 *
	 * @var string
	 */
	private $_title;

	/**
	 * the total number of topics (if known)
	 *
	 * @var integer
	 */
	private $_total_topic_num = -1;

	/**
	 * the content on the left side of the table-header
	 *
	 * @var string
	 */
	private $_left_content = '';

	/**
	 * the content of the tbody-tag
	 *
	 * @var string
	 */
	private $_tbody_content = '';

	/**
	 * do you want to show the search-forum formular?
	 *
	 * @var boolean
	 */
	private $_show_search_forum = false;

	/**
	 * the width of the middle column in the header
	 * the other two will have (100 - $middle_width) / 2
	 *
	 * @var integer
	 */
	private $_middle_width = 50;

	/**
	 * display to which forum the topic belongs?
	 *
	 * @var boolean
	 */
	private $_show_forum = false;

	/**
	 * display important topics first?
	 *
	 * @var boolean
	 */
	private $_show_important_first = true;

	/**
	 * display the topic-action?
	 *
	 * @var boolean
	 */
	private $_show_topic_action = true;
	
	/**
	 * display the topic-opening-time?
	 *
	 * @var boolean
	 */
	private $_show_topic_opening = true;
	
	/**
	 * display the number of topic-views?
	 *
	 * @var boolean
	 */
	private $_show_views = true;
	
	/**
	 * Wether the relevance should be displayed
	 *
	 * @var boolean
	 */
	private $_show_relevance = false;

	/**
	 * an array with the keywords to highlight
	 *
	 * @var array
	 */
	private $_keywords = null;

	/**
	 * contains the used where-clause (after the display-call)
	 *
	 * @var string
	 */
	private $_user_where_clause = '';
	
	/**
	 * Wether just the first page may be displayed
	 *
	 * @var boolean
	 */
	private $_firstsite;

	/**
	 * constructor
	 *
	 * @param string $title the title of the topics-table
	 * @param string $where additional conditions for the query
	 * @param string $order the order: lastpost, topic_name, topic_type, topic_start, replies, views,
	 * 	relevance
	 * @param string $ad ascending or descending order? (ASC vs. DESC)
	 * @param int $number the number of topics to display (0 = all)
	 * @param int $fid the forum-id - if you want to limit it to one forum; otherwise 0
	 * @param boolean $firstsite wether just the first page may be displayed
	 */
	public function __construct($title,$where = '',$order = 'lastpost',$ad = 'DESC',
		$number = 0,$fid = 0,$firstsite = false)
	{
		parent::__construct();
		
		$this->_title = $title;
		$this->_where_clause = $where;
		$orders = array('lastpost','topic_name','topic_type','topic_start','replies','views','relevance');
		if(in_array($order,$orders))
			$this->_order = $order;
		else
			$this->_order = 'lastpost';
		$this->_ad = in_array($ad,array('ASC','DESC')) ? $ad : 'DESC';
		$this->_number_of_topics = FWS_Helper::is_integer($number) ? $number : 0;
		$this->_fid = FWS_Helper::is_integer($fid) ? $fid : 0;
		$this->_firstsite = $firstsite;
	}

	/**
	 * sets the content of the left header-side
	 *
	 * @param string $content the content to set
	 */
	public function set_left_content($content)
	{
		$this->_left_content = $content;
	}

	/**
	 * sets the content of the tbody-tag
	 *
	 * @param string $content the content to set
	 */
	public function set_tbody_content($content)
	{
		$this->_tbody_content = $content;
	}

	/**
	 * do you want to show the search-forum formular?
	 *
	 * @param boolean $val the new value
	 */
	public function set_show_search_forum($val)
	{
		$this->_show_search_forum = $val;
	}

	/**
	 * sets the width of the middle column in the header
	 * the other two will have (100 - $middle_width) / 2
	 *
	 * @param int $width the width to use
	 */
	public function set_middle_width($width)
	{
		$w = (FWS_Helper::is_integer($width) && $width >= 0 && $width <= 100) ? $width : 50;
		$this->_middle_width = $w;
	}
	
	/**
	 * Sets wether the relevance should be displayed
	 *
	 * @param boolean $show the new value
	 */
	public function set_show_relevance($show)
	{
		$this->_show_relevance = (bool)$show;
	}

	/**
	 * do you want to show the forum to which the topic belongs?
	 *
	 * @param boolean $show_forum show the forum?
	 */
	public function set_show_forum($show_forum)
	{
		$this->_show_forum = $show_forum ? true : false;
	}

	/**
	 * sets wether the important topics will be shown first or not
	 *
	 * @param boolean $first show them first?
	 */
	public function set_show_important_first($first)
	{
		$this->_show_important_first = $first ? true : false;
	}

	/**
	 * sets wether the topic-action should be displayed
	 *
	 * @param boolean $show show the topic-action?
	 */
	public function set_show_topic_action($show)
	{
		$this->_show_topic_action = $show ? true : false;
	}

	/**
	 * sets wether the topic-opening-time should be displayed
	 *
	 * @param boolean $show show the topic-opening-time?
	 */
	public function set_show_topic_opening($show)
	{
		$this->_show_topic_opening = $show ? true : false;
	}

	/**
	 * sets wether the number of views should be displayed
	 *
	 * @param boolean $show show the views?
	 */
	public function set_show_topic_views($show)
	{
		$this->_show_views = $show ? true : false;
	}

	/**
	 * sets the total topic num of the query
	 * (to display a page-split)
	 * so this is just required if you want limit the result-set.
	 *
	 * @see $this->_number_of_topics
	 * @param int $num the total num
	 */
	public function set_total_topic_num($num)
	{
		$this->_total_topic_num = $num;
	}

	/**
	 * sets the keywords to highlight words in the topic-titles
	 *
	 * @param array $keywords an array with the keywords to highlight
	 */
	public function set_keywords($keywords)
	{
		$this->_keywords = is_array($keywords) ? $keywords : null;
	}

	/**
	 * Note that this information is only available _after_ the call of display()
	 *
	 * @return string the user where-clause
	 */
	public function get_user_where_clause()
	{
		return $this->_user_where_clause;
	}

	/**
	 * Builds the topics and adds all required stuff to the template inc_topics.htm so that
	 * you can include it
	 */
	public function add_topics()
	{
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();
		$input = FWS_Props::get()->input();
		$unread = FWS_Props::get()->unread();
		if($this->_total_topic_num != 0)
		{
			// generate sorting
			switch($this->_order)
			{
				case 'topic_name':
					$sql_order = 't.name '.$this->_ad;
					break;
				case 'topic_type':
					$sql_order = 't.type '.$this->_ad;
					break;
				case 'topic_start':
					$sql_order = 't.post_time '.$this->_ad;
					break;
				case 'replies':
					$sql_order = 't.posts '.$this->_ad;
					break;
				case 'views':
					$sql_order = 't.views '.$this->_ad;
					break;
				case 'relevance':
					if($this->_keywords !== null)
						$sql_order = 'relevance '.$this->_ad;
					else
						$sql_order = 't.lastpost_id '.$this->_ad;
					break;
				default:
					$sql_order = 't.lastpost_id '.$this->_ad;
					break;
			}

			if($this->_show_important_first)
				$sql_order = 't.important DESC,'.$sql_order;

			// generate where
			$sql_where = ' WHERE 1';
			if($cfg['hide_denied_forums'] == 1)
			{
				$denied = BS_ForumUtils::get_instance()->get_denied_forums(false);
				if(count($denied) > 0)
					$sql_where .= ' AND t.rubrikid NOT IN ('.implode(',',$denied).')';
			}
			
			if($this->_fid > 0)
				$sql_where .= ' AND t.rubrikid = '.$this->_fid;
			if($this->_where_clause != '')
				$sql_where .= ' AND '.$this->_where_clause;

			$this->_user_where_clause = $sql_where;

			// generate limit
			$start = 0;
			$count = 0;
			if($this->_number_of_topics > 0)
			{
				$num = $this->_total_topic_num >= 0 ? $this->_total_topic_num : 0;
				// ensure that the first site is displayed?
				if($this->_firstsite)
					$pagination = new FWS_Pagination($this->_number_of_topics,$num,1);
				else
					$pagination = new BS_Pagination($this->_number_of_topics,$num);
				$start = $pagination->get_start();
				$count = $this->_number_of_topics;
			}
			
			$topiclist = BS_DAO::get_topics()->get_list_by_search(
				$sql_where,$sql_order,$start,$count,$this->_keywords
			);
			
			$kws = '';
			if($this->_keywords !== null)
			{
				foreach($this->_keywords as $kw)
					$kws .= '"'.$kw.'" ';
				$kws = rtrim($kws);
			}

			$cache = array(
				'symbol_poll' =>				$user->get_theme_item_path(
					'images/thread_type/poll.gif'
				),
				'symbol_event' =>				$user->get_theme_item_path(
					'images/thread_type/event.gif'
				),

				'important_en' =>				$user->get_theme_item_path(
					'images/thread_status/important_en.gif'
				),
				'important_dis' =>			$user->get_theme_item_path(
					'images/thread_status/important_dis.gif'
				),
				'important_new_en' =>		$user->get_theme_item_path(
					'images/thread_status/important_new_en.gif'
				),
				'important_new_dis' =>	$user->get_theme_item_path(
					'images/thread_status/important_new_dis.gif'
				),

				'hot_en' =>							$user->get_theme_item_path(
					'images/thread_status/hot_en.gif'
				),
				'hot_dis' =>						$user->get_theme_item_path(
					'images/thread_status/hot_dis.gif'
				),
				'hot_new_en' =>					$user->get_theme_item_path(
					'images/thread_status/hot_new_en.gif'
				),
				'hot_new_dis' =>				$user->get_theme_item_path(
					'images/thread_status/hot_new_dis.gif'
				),

				'closed_en' =>					$user->get_theme_item_path(
					'images/thread_status/closed_en.gif'
				),
				'closed_dis' =>					$user->get_theme_item_path(
					'images/thread_status/closed_dis.gif'
				),
				'closed_new_en' =>			$user->get_theme_item_path(
					'images/thread_status/closed_new_en.gif'
				),
				'closed_new_dis' =>			$user->get_theme_item_path(
					'images/thread_status/closed_new_dis.gif'
				),

				'moved_en' =>						$user->get_theme_item_path(
					'images/thread_status/moved_en.gif'
				),
				'moved_dis' =>					$user->get_theme_item_path(
					'images/thread_status/moved_dis.gif'
				),
				'moved_new_en' =>				$user->get_theme_item_path(
					'images/thread_status/moved_new_en.gif'
				),
				'moved_new_dis' =>			$user->get_theme_item_path(
					'images/thread_status/moved_new_dis.gif'
				),

				'lastpost_image' =>			$user->get_theme_item_path(
					'images/lastpost.gif'
				)
			);
		}
		
		$tpl->set_template('inc_topics.htm');

		// determine required colspans
		$total_colspan = 8;
		$title_colspan = 4;
		if(!$this->_show_topic_action)
		{
			$total_colspan--;
			$title_colspan--;
		}
		if(!$this->_show_topic_opening)
			$total_colspan--;
		if(!$this->_show_views)
			$total_colspan--;

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$redirect_url = '';
		if(isset($pagination) && $pagination !== null)
		{
			$url = BS_URL::get_mod_url('redirect');
			$url->set(BS_URL_LOC,'topic_action');
			$url->set(BS_URL_FID,$fid);
			$url->set(BS_URL_SITE,$pagination->get_page());
			$redirect_url = $url->to_url();
		}
		
		// display header		
		$tpl->add_variables(array(
			'tbody_content' => $this->_tbody_content,
			'show_thread_opening' => $this->_show_topic_opening,
			'show_views' => $this->_show_views, 
			'show_thread_action' => $this->_show_topic_action,
			'thread_title' => $this->_title,
			'left_content' => $this->_left_content,
			'show_search_forum' => $this->_show_search_forum,
			'num' => isset($topiclist) ? count($topiclist) : 0,
			'total_colspan' => $total_colspan,
			'title_colspan' => $title_colspan,
			'left_col_width' => (100 - $this->_middle_width) / 2,
			'middle_col_width' => $this->_middle_width,
			'right_col_width' => (100 - $this->_middle_width) / 2,
			'fid' => $fid,
			'quick_search_target' => BS_URL::build_mod_url('search'),
			'redirect_url' => $redirect_url,
		));
		
		$topics = array();
		
		if($this->_total_topic_num != 0)
		{
			if(!is_null($this->_keywords))
				$kwhl = new FWS_KeywordHighlighter($this->_keywords,'<span class="bs_highlight">');
			
			$rurl = BS_URL::get_mod_url('redirect');
			$rurl->set(BS_URL_LOC,'show_post');
			
			$purl = BS_URL::get_mod_url('posts');
			$purl->set_sef(true);
			$psurl = BS_URL::get_mod_url('posts');
			if($kws)
				$psurl->set(BS_URL_HL,$kws);
			
			$posts_url = clone $psurl;
			$posts_url->set(BS_URL_SITE,1);
			$posts_url->set_sef(true);
			
			$post_order = BS_PostingUtils::get_instance()->get_posts_order();
			$is_important = false;
			foreach($topiclist as $data)
			{
				$important_title = '';
				$important_colspan = 0;
				if($this->_show_important_first && $is_important != $data['important'])
				{
					$important_title = $data['important'] ? 'Wichtige Themen' : 'Themen';
					$important_colspan = $this->_show_topic_action ? 8 : 7;
				}

				$pages = BS_PostingUtils::get_instance()->get_post_pages($data['posts'] + 1);
				$is_unread = $unread->is_unread_thread($data['id']);
				$first_unread_url = '';
				if($is_unread)
				{
					$fup = $unread->get_first_unread_post($data['id']);
					if($pages > 1)
						$first_unread_url = $rurl->set(BS_URL_ID,$fup)->to_url();
					else
					{
						$purl->set(BS_URL_FID,$data['rubrikid']);
						$purl->set(BS_URL_TID,$data['id']);
						$purl->set_anchor('b_'.$fup);
						$first_unread_url = $purl->to_url();
					}
				}

				// generate page-split for topics with multiple pages
				$psurl->set(BS_URL_FID,$data['rubrikid']);
				$psurl->set(BS_URL_TID,$data['id']);
				
				$forum_path = '';
				if($this->_show_forum)
					$forum_path = BS_ForumUtils::get_instance()->get_forum_path($data['rubrikid'],false);
				
				$relevance = false;
				if($this->_show_relevance)
					$relevance = round($data['relevance'],3);
				
				// highlight keywords and cut topic-name
				if(!is_null($this->_keywords))
				{
					$data['name'] = $kwhl->highlight($data['name']);
					$ls = new FWS_HTML_LimitedString($data['name'],$cfg['thread_max_title_len']);
					$res = $ls->get();
					if($ls->has_cut())
						$topic_name = array('displayed' => $res,'complete' => strip_tags($data['name']));
					else
						$topic_name = array('displayed' => $data['name'],'complete' => '');
				}
				else
					$topic_name = BS_TopicUtils::get_instance()->get_displayed_name($data['name']);
				
				// special values for shadow-topics
				if($data['moved_tid'] != 0 && $data['moved_rid'] != 0)
				{
					$topic_id = $data['moved_tid'];
					$forum_id = $data['moved_rid'];
					$topic_starter = '';
					$lastpost = '';
				}
				else
				{
					$topic_id = $data['id'];
					$forum_id = $data['rubrikid'];
					$lastpost = $this->_get_topic_lastpost($data,$post_order,$pages);
					$topic_starter = $this->_get_topic_starter($data);
				}

				// build url
				$posts_url->set(BS_URL_FID,$forum_id);
				$posts_url->set(BS_URL_TID,$topic_id);
				$sposts_url = $posts_url->to_url();

				$tinypagi = new BS_Pagination($cfg['posts_per_page'],$data['posts'] + 1);
				
				// display template
				$topics[] = array(
					'is_unread' => $is_unread,
					'first_unread_url' => $first_unread_url,
					'is_important' => $data['important'] == 1,
					'is_moved' => $data['moved_tid'] != 0 && $data['moved_rid'] != 0,
					'name_complete' => $topic_name['complete'],
					'name' => $topic_name['displayed'],
					'topic_url' => $sposts_url,
					'show_forum' => $this->_show_forum,
					'forum_path' => $forum_path,
					'page_split' => $tinypagi->get_tiny($psurl),
					'important_title' => $important_title,
					'important_colspan' => $important_colspan,
					'show_important' => $this->_show_important_first && $is_important != $data['important'],
					'topic_id' => $data['id'],
					'lastpost' => $lastpost,
					'topicstart' => $topic_starter,
					'posts' => $data['posts'],
					'views' => $data['views'],
					'topic_status' => BS_TopicUtils::get_instance()->get_status_data(
						$cache,$data,$unread->is_unread_thread($data['id'])
					),
					'thread_pic' => BS_TopicUtils::get_instance()->get_symbol(
						$cache,$data['type'],$data['symbol']
					),
					'posts_url' => $sposts_url,
					'show_relevance' => $this->_show_relevance,
					'relevance' => $relevance
				);
				
				$is_important = $data['important'] == 1;
			}
		}

		$tpl->add_array('topics',$topics);
		
		$tpl->restore_template();
	}

	/**
	 * generates the topic-starter
	 *
	 * @param array $data the topic-data
	 * @return string the result-string
	 */
	private function _get_topic_starter(&$data)
	{
		if($data['post_user'] != 0)
		{
			$username = BS_UserUtils::get_instance()->get_link(
				$data['post_user'],$data['username'],$data['user_group']
			);
		}
		else
			$username = $data['post_an_user'];
		
		return array(
			'date' => FWS_Date::get_date($data['post_time']),
			'username' => $username
		);
	}

	/**
	 * generates the lastpost-data
	 *
	 * @param array $data the topic-data
	 * @param string $post_order the post-order: ASC or DESC
	 * @param int $pages the number of pages
	 * @return string the result-string
	 */
	private function _get_topic_lastpost(&$data,$posts_order,$pages)
	{
		if($data['lastpost_id'] == 0)
			return false;

		// generate lastpost-URL
		$site = 1;
		if($posts_order == 'ASC' && $pages > 1)
			$site = $pages;
		$murl = BS_URL::build_posts_url($data['rubrikid'],$data['id'],$site);

		// determine username
		if($data['lastpost_user'] != 0)
		{
			$user_name = BS_UserUtils::get_instance()->get_link(
				$data['lastpost_user'],$data['lp_username'],$data['lastpost_user_group']
			);
		}
		else
			$user_name = $data['lastpost_an_user'];

		return array(
			'date' => FWS_Date::get_date($data['lastpost_time']),
			'username' => $user_name,
			'url' => $murl.'#b_'.$data['lastpost_id']
		);
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>