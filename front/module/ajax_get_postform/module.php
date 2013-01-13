<?php
/**
 * Contains the post-form-change-module
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
 * Returns a post-form. This will be called via AJAX
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_ajax_get_postform extends BS_Front_Module
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
		$input = FWS_Props::get()->input();
		$doc = FWS_Props::get()->doc();
		
		$type = $input->correct_var(
			'type','get',FWS_Input::STRING,array('post','sig','desc','pm'),'post'
		);
		$mode = $input->correct_var(
			'mode','get',FWS_Input::STRING,array('simple','advanced','applet'),'simple'
		);
		$height = $input->get_var('height','get',FWS_Input::STRING);
		$path = $input->get_var('bspath','post',FWS_Input::STRING);
		
		$form = new BS_PostingForm('','',$type);
		$form->set_textarea_height($height);
		
		$renderer = $doc->use_raw_renderer();
		$renderer->set_content($form->get_textarea('',$mode == 'applet',$path));
	}
}
?>