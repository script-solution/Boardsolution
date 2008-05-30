<?php
/**
 * Contains the standalone-class for the statistics-diagram
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */


/**
 * The width of the image
 */
define('BS_IMG_WIDTH',930);
/**
 * The height of the image
 */
define('BS_IMG_HEIGHT',300);

/**
 * The number of days that should be displayed
 */
define('BS_NUMBER_OF_DAYS',20);

/**
 * The number of steps in the scale on the left
 */
define('BS_LEFT_SCALE_STEPS',4);

/**
 * The font-file to use (a TTF-font)
 */
define('BS_FONT_FILE','images/gd/veramono.ttf');

/**
 * The "normal" font-size
 */
define('BS_FONT_SIZE',8);

/**
 * The small font-size
 */
define('BS_FONT_SIZE_SMALL',7);

/**
 * The background-color (hexadecimal)
 */
define('BS_BACKGROUND_COLOR','#ebebeb7f');

/**
 * The diagram background-color (hexadecimal)
 */
define('BS_DIAGRAM_BG_COLOR','#FFFFFF');

/**
 * The border-color (hexadecimal)
 */
define('BS_BORDER_COLOR','#505050');

/**
 * The font-color (hexadecimal)
 */
define('BS_FONT_COLOR','#000000');

/**
 * The pattern-color (hexadecimal)
 */
define('BS_PATTERN_COLOR','#DCDCDC');

/**
 * The main-color for posts (hexadecimal)
 */
define('BS_POSTS_COLOR','#800000');

/**
 * The main-color for topics (hexadecimal)
 */
define('BS_TOPICS_COLOR','#008000');

/**
 * The main-color for polls (hexadecimal)
 */
define('BS_POLLS_COLOR','#000080');

/**
 * The main-color for events (hexadecimal)
 */
define('BS_EVENTS_COLOR','#808000');


/**
 * Displays the diagram for the statistics
 * 
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Standalone_stats_diagram extends BS_Standalone
{
	public function use_output_buffering()
	{
		return false;
	}
	
	public function run()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		$key = $this->input->get_var('key','get',PLIB_Input::STRING);
		
		// check if the parameters is valid
		if($id == null || $key == null)
			die('Invalid parameters');
		
		// this ensures that nobody can view the stats of other users
		if(!BS_DAO::get_sessions()->check_sessionip_key($id,$key))
			die('Invalid parameters');
		
		$this->locale->add_language_file('index',$this->cfg['lang_folder']);
		
		####################### grab data from db #######################
		
		$dnow = new PLIB_Date();
		$now = PLIB_Date::get_timestamp(
			array(12,0,0,$dnow->get_month(),$dnow->get_day(),$dnow->get_year()),false
		);
		$start = $now - (86400 * (BS_NUMBER_OF_DAYS - 1));
		
		$post_times = array();
		$topic_times = array();
		for($time = $start;$time <= $now;$time += 86400)
		{
			$date = PLIB_Date::get_formated_date('m/d/Y',$time);
			$post_times[$date] = 0;
			$topic_times['topic'][$date] = 0;
			$topic_times['poll'][$date] = 0;
			$topic_times['event'][$date] = 0;
		}
		
		// grab posts
		$max = array('type' => 'posts','index' => 0,'value' => 0);
		foreach(BS_DAO::get_posts()->get_posts_by_date($id,$start) as $data)
		{
			$date = PLIB_Date::get_formated_date('m/d/Y',$data['post_time']);
			$post_times[$date]++;
		
			if($post_times[$date] > $max['value'])
				$max = array('type' => 'posts','index' => $date,'value' => $post_times[$date]);
		}
		
		// grab topics
		foreach(BS_DAO::get_topics()->get_topics_by_date($id,$start) as $data)
		{
			if($data['type'] == 0)
				$type = 'topic';
			else if($data['type'] == -1)
				$type = 'event';
			else
				$type = 'poll';
		
			$date = PLIB_Date::get_formated_date('m/d/Y',$data['post_time']);
			$topic_times[$type][$date]++;
		
			if($topic_times[$type][$date] > $max['value'])
				$max = array('type' => 'topics','index' => $date,'value' => $topic_times[$type][$date]);
		}
		
		$max['value'] = sprintf('%.2f',$max['value']);
		
		####################### Generate the image #######################
		
		
		$font = new PLIB_GD_Font_TTF(PLIB_Path::inner().BS_FONT_FILE);
		$padding_left = 30;
		$padding_top = 1;
		$padding_right = 100;
		$padding_bottom = 45;
		
		$img = new PLIB_GD_Image(BS_IMG_WIDTH,BS_IMG_HEIGHT,true);
		$img->set_background(new PLIB_GD_Color(BS_BACKGROUND_COLOR));
		$g = $img->get_graphics();
		
		$rect = new PLIB_GD_Rectangle(
			$padding_left,
			$padding_top,
			BS_IMG_WIDTH - $padding_right - 1 - $padding_left,
			BS_IMG_HEIGHT - $padding_bottom - $padding_top
		);
		$diagram_bg = new PLIB_GD_Color(BS_DIAGRAM_BG_COLOR);
		$g->get_rect_view($rect)->fill($diagram_bg);
		
		// draw horizontal lines and the steps on the left side
		$colborder = new PLIB_GD_Color(BS_BORDER_COLOR);
		$colpattern = new PLIB_GD_Color(BS_PATTERN_COLOR);
		$fontcolor = new PLIB_GD_Color(BS_FONT_COLOR);
		$steppad = new PLIB_GD_Padding(2);
		$steppos = new PLIB_GD_BoxPosition(PLIB_GD_BoxPosition::LAST,PLIB_GD_BoxPosition::FIRST);
		$stepattr = new PLIB_GD_TextAttributes($font,BS_FONT_SIZE,$fontcolor);
		$steptext = new PLIB_GD_Text('dummy',$stepattr);
		$y = 0;
		$val = $max['value'];
		$y_step = (BS_IMG_HEIGHT - $padding_top - $padding_bottom) / BS_LEFT_SCALE_STEPS;
		$val_step = round($max['value'] / BS_LEFT_SCALE_STEPS,2);
		for($i = BS_LEFT_SCALE_STEPS;$i >= 0;$i--)
		{
			$val = sprintf('%.2f',$val);
			if($i > 0)
				$g->draw_line_int($padding_left - 10,$y,$padding_left,$y,$colborder);
			
			$steptext = new PLIB_GD_Text($val,$stepattr);
			$tview = $g->get_text_view($steptext);
			$steprect = new PLIB_GD_Rectangle(0,$y,$padding_left,$y_step);
			$tview->draw_in_rect($steprect,$steppad,$steppos);
			
			if($i > 0)
				$g->draw_line_int($padding_left,$y,BS_IMG_WIDTH - $padding_right - 1,$y,$colpattern);
		
			$val -= $val_step;
			$y += $y_step;
		}
		
		// draw axis
		$g->draw_line_int(
			$padding_left,0,$padding_left,BS_IMG_HEIGHT,$colborder
		);
		$g->draw_line_int(
			0,BS_IMG_HEIGHT - $padding_bottom,
			BS_IMG_WIDTH - $padding_right,BS_IMG_HEIGHT - $padding_bottom,
			$colborder
		);
		
		// posts
		$diagram = new BS_HorizontalDiagram(
			BS_IMG_WIDTH - $padding_left - $padding_right,
			BS_IMG_HEIGHT - $padding_top - $padding_bottom,
			$padding_left,
			$padding_top,
			count($post_times),
			$max['value']
		);
		$diagram->get_next_position(next($post_times));
		
		// draw posts-graph
		$colpostsline = new PLIB_GD_Color(BS_POSTS_COLOR);
		$dateattr = new PLIB_GD_TextAttributes($font,BS_FONT_SIZE_SMALL,$fontcolor);
		foreach($post_times as $date => $posts)
		{
			$last = $diagram->get_last_position();
			$pos = $diagram->get_next_position($posts);
			$xdiff = $pos->get_x() - $last->get_x();
		
			// draw 3d block
			if($posts > 0)
			{
				$rect_width = $xdiff / 4;
				$rect3d = new PLIB_GD_Rectangle(
					new PLIB_GD_Point($last->get_x(),$pos->get_y()),
					new PLIB_GD_Dimension($rect_width - 2,(BS_IMG_HEIGHT - $padding_bottom - 1) - $pos->get_y())
				);
				$g->get_rect_view($rect3d)->fill_3d($colpostsline);
			}
		
			// build date
			$str = PLIB_Date::get_formated_date('shortdate',$date);
			$daterect = new PLIB_GD_Rectangle(
				new PLIB_GD_Point($last->get_x(),BS_IMG_HEIGHT - $padding_bottom),
				new PLIB_GD_Dimension($xdiff,$padding_bottom)
			);
			$datetext = new PLIB_GD_Text($str,$dateattr);
			$g->get_text_view($datetext)->draw_in_rect($daterect,null,null,270);
			
			// draw pattern and border
			$xpos = $pos->get_x();
			$g->draw_line_int(
				$xpos,0,$xpos,BS_IMG_HEIGHT - $padding_bottom,$colpattern
			);
			$g->draw_line_int(
				$xpos,BS_IMG_HEIGHT - $padding_bottom,$xpos,(BS_IMG_HEIGHT - $padding_bottom) + 10,$colborder
			);
		}
		
		// topics, polls, events
		$i = 1;
		foreach($topic_times as $type => $dates)
		{
			$diagram = new BS_HorizontalDiagram(
				BS_IMG_WIDTH - $padding_left - $padding_right,
				BS_IMG_HEIGHT - $padding_top - $padding_bottom,
				$padding_left,
				$padding_top,
				count($dates),
				$max['value']
			);
			$diagram->get_next_position(next($dates));
			
			switch($type)
			{
				case 'topic':
					$blockcolor = new PLIB_GD_Color(BS_TOPICS_COLOR);
					break;
				case 'poll':
					$blockcolor = new PLIB_GD_Color(BS_POLLS_COLOR);
					break;
				case 'event':
					$blockcolor = new PLIB_GD_Color(BS_EVENTS_COLOR);
					break;
			}
			
			// draw corresponding graph
			foreach($dates as $date => $topics)
			{
				$last = $diagram->get_last_position();
				$pos = $diagram->get_next_position($topics);
		
				if($topics > 0)
				{
					$rect_width = ($pos->get_x() - $last->get_x()) / 4;
					$rect3d = new PLIB_GD_Rectangle(
						new PLIB_GD_Point($last->get_x() + $rect_width * $i,$pos->get_y()),
						new PLIB_GD_Dimension($rect_width - 2,(BS_IMG_HEIGHT - $padding_bottom - 1) - $pos->get_y())
					);
					$g->get_rect_view($rect3d)->fill_3d($blockcolor);
				}
			}
			
			$i++;
		}
		
		// the legend
		$legend = new PLIB_GD_Rectangle((BS_IMG_WIDTH - $padding_right) + 5,0,$padding_right - 10,100);
		$legendview = $g->get_rect_view($legend);
		$legendview->fill($diagram_bg);
		$legendview->draw($colborder);
		
		$legendattr = new PLIB_GD_TextAttributes($font,BS_FONT_SIZE,$fontcolor);
		$text = new PLIB_GD_Text(html_entity_decode($this->locale->lang('legend')),$legendattr);
		$tview = $g->get_text_view($text);
		$textheight = $text->get_height();
		$textpad = new PLIB_GD_Padding(4);
		
		// draw underlined title
		$legendattr->set_underline(true);
		$tview->draw_in_rect($legend,$textpad,PLIB_GD_BoxPosition::$TOP_CENTER);
		$legendattr->set_underline(false);
		
		// draw texts
		$textpos = PLIB_GD_BoxPosition::$TOP_LEFT;
		$text->set_text(html_entity_decode($this->locale->lang('posts')));
		$legendattr->set_foreground(new PLIB_GD_Color(BS_POSTS_COLOR));
		$legend->translate(0,$textheight + 9);
		$tview->draw_in_rect($legend,$textpad,$textpos);
		
		$text->set_text(html_entity_decode($this->locale->lang('threads')));
		$legendattr->set_foreground(new PLIB_GD_Color(BS_TOPICS_COLOR));
		$legend->translate(0,$textheight + 9);
		$tview->draw_in_rect($legend,$textpad,$textpos);
		
		$text->set_text(html_entity_decode($this->locale->lang('polls')));
		$legendattr->set_foreground(new PLIB_GD_Color(BS_POLLS_COLOR));
		$legend->translate(0,$textheight + 9);
		$tview->draw_in_rect($legend,$textpad,$textpos);
		
		$text->set_text(html_entity_decode($this->locale->lang('events')));
		$legendattr->set_foreground(new PLIB_GD_Color(BS_EVENTS_COLOR));
		$legend->translate(0,$textheight + 9);
		$tview->draw_in_rect($legend,$textpad,$textpos);
		
		// finish
		$img->send('png',false);
	}
}

/**
 * this class will be used to calculate the positions of the points in the graph
 * 
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_HorizontalDiagram extends PLIB_FullObject
{
	/**
	 * The width of the diagram-area
	 *
	 * @var integer
	 */
	private $_width;
	
	/**
	 * The height of the diagram-area
	 *
	 * @var integer
	 */
	private $_height;
	
	/**
	 * The x-start-position
	 *
	 * @var integer
	 */
	private $_x;
	
	/**
	 * The y-start-position
	 *
	 * @var integer
	 */
	private $_y;
	
	/**
	 * The top-padding
	 *
	 * @var integer
	 */
	private $_padding_top;
	
	/**
	 * The maximum value
	 *
	 * @var integer
	 */
	private $_max;
	
	/**
	 * The step-size
	 *
	 * @var integer
	 */
	private $_step_size;

	/**
	 * constructor
	 *
	 * @param int $width the width of the diagram-area
	 * @param int $height the height of the diagram-area
	 * @param int $startX the x-start-pos
	 * @param int $startY the y-start-pos (top-padding)
	 * @param int $count the total number of values
	 * @param int $max_value the maximum value
	 */
	public function __construct($width,$height,$startX,$startY,$count,$max_value)
	{
		parent::__construct();
		
		$this->_width = $width;
		$this->_height = $height;
		$this->_step_size = $width / $count;
		$this->_x = $startX - $this->_step_size;
		$this->_padding_top = $startY;
		$this->_max = $max_value;
	}

	/**
	 * @return integer the step-size
	 */
	public function get_step_size()
	{
		return $this->_step_size;
	}

	/**
	 * returns the last position
	 *
	 * @return PLIB_GD_Point the last position
	 */
	public function get_last_position()
	{
		return new PLIB_GD_Point($this->_x,$this->_y);
	}

	/**
	 * calculates the next position depending on the given value
	 *
	 * @param int $value the value at the next position
	 * @return PLIB_GD_Point an position
	 */
	public function get_next_position($value)
	{
		$this->_x += $this->_step_size;
		if($value == 0)
			$div = 0;
		else
			$div = $this->_max / $value;
		
		if($div == 0)
			$y_sub = 0;
		else
			$y_sub = $this->_height / $div;
		
		$this->_y = $this->_padding_top + $this->_height - $y_sub;
		return new PLIB_GD_Point($this->_x,$this->_y);
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>