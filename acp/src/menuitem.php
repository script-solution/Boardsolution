<?php
/**
 * Contains the acp-menu-item-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Represents an menu-item in the ACP-menu.
 * 
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_MenuItem extends PLIB_Object
{
	/**
	 * The name of the module
	 *
	 * @var string
	 */
	private $_module;
	
	/**
	 * The name of the frame or null if the current should be used
	 *
	 * @var string
	 */
	private $_frame;
	
	/**
	 * The URL for the menu-item
	 *
	 * @var string
	 */
	private $_url;
	
	/**
	 * Constructor
	 *
	 * @param string $module the name of the module
	 * @param string $frame the name of the frame or null if the current should be used
	 * @param string $url the URL (optional)
	 */
	public function __construct($module,$frame = null,$url = null)
	{
		$user = PLIB_Props::get()->user();

		parent::__construct();
		
		if(!is_string($module))
			PLIB_Helper::def_error('string','module',$module);
		if($frame !== null && !is_string($frame))
			PLIB_Helper::def_error('string','frame',$frame);
		if($url !== null && !is_string($url))
			PLIB_Helper::def_error('string','url',$url);
		
		$this->_module = $module;
		$this->_frame = $frame;
		if($url === null)
			$this->_url = 'admin.php?page=content&amp;loc='.$this->_module.'&amp;'
				.BS_URL_SID.'='.$user->get_session_id();
		else
			$this->_url = $url;
	}
	
	/**
	 * @return string the name of the frame
	 */
	public function get_frame()
	{
		return $this->_frame !== null ? 'target="'.$this->_frame.'" ' : '';
	}
	
	/**
	 * @return string the URL to load the module
	 */
	public function get_url()
	{
		return $this->_url;
	}
	
	/**
	 * @return string the javascript-code to load the module
	 */
	public function get_javascript()
	{
		if($this->_frame === null)
			return "parent.location.href = '".$this->get_url()."';";

		return "parent.".$this->_frame.".location.href = '".$this->get_url()."'";
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>