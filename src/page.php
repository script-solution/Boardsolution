<?php
/**
 * Contains the base page-class for Boardsolution
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The base page-class which does all stuff that is necessary for all entry-points of boardsolution
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Page extends PLIB_Page
{
	/**
	 * @see PLIB_Page::_before_start()
	 */
	protected function before_start()
	{
		$cfg = PLIB_Props::get()->cfg();
		$locale = PLIB_Props::get()->locale();
		
		$this->set_charset(BS_HTML_CHARSET);
		PLIB_Path::set_outer($cfg['board_url'].'/');
		
		// set our error-logger
		PLIB_Error_Handler::get_instance()->set_logger(new BS_Error_Logger());
		
		// load language
		$locale->add_language_file('index');
		
		// run tasks
		$taskcon = new BS_Tasks_Container();
		$taskcon->run_tasks();
	}
	
	/**
	 * @return boolean wether the document is a acp-document (we are in ACP)
	 */
	public final function is_acp()
	{
		return $this instanceof BS_ACP_Page;
	}
	
	protected function load_action_perf()
	{
		return new BS_Front_Action_Performer();
	}
}
?>