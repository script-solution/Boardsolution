<?php
/**
 * Contains the base-editor-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The base-editor-class
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_ACP_Module_Themes_Editor_Base extends PLIB_FullObject
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
		parent::__construct();
		
		$theme = $this->input->get_var('theme','get',PLIB_Input::STRING);
		if($theme == null || !BS_DAO::get_themes()->theme_exists($theme))
			PLIB_Helper::error($this->locale->lang('theme_invalid'));
		
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