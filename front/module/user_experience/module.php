<?php
/**
 * Contains the user-experience-module
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
 * The width of the image
 */
define('BS_IMG_WIDTH',150);
/**
 * The height of the image
 */
define('BS_IMG_HEIGHT',11);

/**
 * Just a shortcut for the half-image-height :)
 */
define('BS_HALF_HEIGHT',(int)(BS_IMG_HEIGHT / 2));

/**
 * The background-color (hexadecimal)
 */
define('BS_BACKGROUND_COLOR','#EBEBEB');
/**
 * The line-color (hexadecimal)
 */
define('BS_LINE_COLOR','#7F90AE');

/**
 * The progress-bar-start-color (hexadecimal)
 */
define('BS_PROGRESS_BAR_START_COLOR','#FF0000');
/**
 * The progress-bar-end-color (hexadecimal)
 */
define('BS_PROGRESS_BAR_END_COLOR','#00C800');
/**
 * The empty color for the progress-bar (hexadecimal)
 */
define('BS_PROGRESS_BAR_EMPTY_COLOR','#FFFFFF');

/**
 * The color for the circle (hexadecimal)
 */
define('BS_PROGRESS_CIRCLE_COLOR','#646464');
/**
 * The empty color for the circle (hexadecimal)
 */
define('BS_PROGRESS_CIRCLE_EMPTY_COLOR','#FFFFFF');

/**
 * Draws the image with the user-experience
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_user_experience extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->use_gdimage_renderer();
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$functions = FWS_Props::get()->functions();
		$cfg = FWS_Props::get()->cfg();
		$cache = FWS_Props::get()->cache();

		// check parameter
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		
		// for the faq-example :)
		if($id == 0)
		{
			$userdata = array(
				'id' => 0,
				'exppoints' => 18
			);
			$rank = array(
				'pos' => 2,
				'post_to' => 25,
				'post_from' => 15
			);
		}
		else
		{
			if($id == null)
			{
				$this->report_error();
				return;
			}
			
			$userdata = BS_DAO::get_profile()->get_user_by_id($id,-1,-1);
			// does the user exist?
			if($userdata === false)
			{
				$this->report_error();
				return;
			}
			
			$rank = $functions->get_rank_data($userdata['exppoints']);
		}
		
		// determine experience
		if($userdata['exppoints'] == 0)
			$progress_in_rank = 0;
		else
		{
			$points_in_rank = $userdata['exppoints'] - $rank['post_from'];
			if($points_in_rank == 0)
				$progress_in_rank = 0;
			else
				$progress_in_rank = ($rank['post_to'] - $rank['post_from']) / $points_in_rank;
		}
		
		switch($cfg['post_stats_type'])
		{
			case 'current_rank':
				$bar_start = 0;
				$bar_length = BS_IMG_WIDTH - $bar_start;
				
				$bar_filled = min($bar_length,$progress_in_rank == 0 ? 0 : $bar_length / $progress_in_rank);
				break;
			
			case 'continuous':
				$bar_start = BS_HALF_HEIGHT;
				$bar_length = BS_IMG_WIDTH - $bar_start;
				
				if($id == 0)
					$last_rank = array('post_to' => 25);
				else
				{
					$cache->get_cache('user_ranks')->to_last();
					$last_rank = $cache->get_cache('user_ranks')->current();
				}
				
				if($last_rank['post_to'] == 0 || $userdata['exppoints'] == 0)
					$bar_filled = 0;
				else
					$bar_filled = min($bar_length,$bar_length / ($last_rank['post_to'] / $userdata['exppoints']));
				break;
			
			case 'newbie_friendly':
				$bar_start = BS_HALF_HEIGHT;
				$bar_length = BS_IMG_WIDTH - $bar_start;
				
				if($id == 0)
					$rank_num = 5;
				else
					$rank_num = $cache->get_cache('user_ranks')->get_element_count();
				
				$step_len = $rank_num == 0 ? 0 : ($bar_length / $rank_num);
				if($progress_in_rank == 0)
					$bar_filled = $rank['pos'] * $step_len;
				else
					$bar_filled = min($bar_length,($rank['pos'] * $step_len) + ($step_len / $progress_in_rank));
				break;
		}
		
		// initialization
		$img = new FWS_GD_Image(BS_IMG_WIDTH,BS_IMG_HEIGHT,false);
		$img->set_background(new FWS_GD_Color(BS_BACKGROUND_COLOR));
		$g = $img->get_graphics();
		
		$linecolor = new FWS_GD_Color(BS_LINE_COLOR);
		
		// draw total experience-progress
		if($cfg['post_stats_type'] != 'current_rank')
			$filled_end = BS_HALF_HEIGHT + $bar_filled;
		else
			$filled_end = $bar_filled;
		
		$rect = new FWS_GD_Rectangle($bar_start,0,$filled_end - $bar_start,BS_IMG_HEIGHT - 1);
		$cbar_start = new FWS_GD_Color(BS_PROGRESS_BAR_START_COLOR);
		$cbar_end = new FWS_GD_Color(BS_PROGRESS_BAR_END_COLOR);
		$colors = array($cbar_start->get_comps(false),$cbar_end->get_comps(false));
		
		// determine the color at the end-position and replace it in the colors
		if((int)$rect->get_size()->get_width() > 0)
		{
			$cf = new FWS_GD_ColorFade($bar_length,$bar_length,$colors);
			$filled_length = $filled_end - BS_HALF_HEIGHT;
			if($filled_length >= 0 && $filled_length < $bar_length)
				$colors[1] = $cf->get_color_at($filled_length)->get_comps(false);
			
			// draw the color-fade
			$g->get_rect_view($rect)->fill_colorfade($colors);
		}
		
		// fill the not-filled area :)
		$bar_empty_color = new FWS_GD_Color(BS_PROGRESS_BAR_EMPTY_COLOR);
		$bar_empty_rect = new FWS_GD_Rectangle($filled_end,0,BS_IMG_WIDTH - 1,BS_IMG_HEIGHT - 1);
		$g->get_rect_view($bar_empty_rect)->fill($bar_empty_color);
		
		// draw progress in the current rank
		if($cfg['post_stats_type'] != 'current_rank')
		{
			$circle_color = new FWS_GD_Color(BS_PROGRESS_CIRCLE_COLOR);
			$circle_empty_color = new FWS_GD_Color(BS_PROGRESS_CIRCLE_EMPTY_COLOR);
			
			$angle = $progress_in_rank == 0 ? 270 : 270 + (360 / $progress_in_rank);
			$angle_end = $angle > 360 ? $angle - 360 : $angle;
			
			$pos = new FWS_GD_Point(BS_HALF_HEIGHT,BS_HALF_HEIGHT);
			$size = new FWS_GD_Dimension(BS_IMG_HEIGHT - 1,BS_IMG_HEIGHT - 1);
			$ellipse = new FWS_GD_Ellipse($pos,$size);
			$eview = $g->get_ellipse_view($ellipse);
			if($angle == $angle_end || $angle_end == 270)
				$eview->fill_part($circle_empty_color,0,360);
			else
			{
				$eview->fill_part($circle_color,270,$angle_end);
				$eview->fill_part($circle_empty_color,$angle_end,270);
			}
			
			// draw border around the circle
			$eview->draw($linecolor);
		}
		
		// draw border around the image
		if($cfg['post_stats_type'] == 'current_rank')
		{
			$g->draw_line(
				new FWS_GD_Point($bar_start,0),new FWS_GD_Point($bar_start,BS_IMG_HEIGHT),$linecolor
			);
		}
		
		$start = new FWS_GD_Point($bar_start,0);
		$end = new FWS_GD_Point(BS_IMG_WIDTH,0);
		$g->draw_line($start,$end,$linecolor);
		$g->draw_line($start->derive(0,BS_IMG_HEIGHT - 1),$end->derive(0,BS_IMG_HEIGHT - 1),$linecolor);
		$g->draw_line($end->derive(-1,0),$end->derive(-1,BS_IMG_HEIGHT - 1),$linecolor);
		
		// set image to the renderer
		$doc = FWS_Props::get()->doc();
		$renderer = $doc->use_gdimage_renderer();
		$renderer->set_image($img);
	}
}
?>