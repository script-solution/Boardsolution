<?php
/**
 * Contains the base-editor-class
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
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
 * The base-editor-class
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_ACP_Module_Themes_Editor_Base extends FWS_Object
{
	/**
	 * The selected theme
	 *
	 * @var string
	 */
	protected $_theme;

	/**
	 * constructor
	 */
	public function __construct()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		parent::__construct();
		
		$theme = $input->get_var('theme','get',FWS_Input::STRING);
		if($theme == null || !BS_DAO::get_themes()->theme_exists($theme))
			FWS_Helper::error($locale->lang('theme_invalid'));
		
		$this->_theme = 'themes/'.$theme;
	}
	
	/**
	 * Should return the template to include
	 *
	 * @return string the template-name
	 */
	public abstract function get_template();
	
	/**
	 * displays the editor
	 */
	public abstract function display();
}
?>