<?php
/**
 * Contains the base-document-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The base-document for all documents in Boardsolution
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Document extends PLIB_Document
{
	/**
	 * @see PLIB_Document::prepare_rendering()
	 */
	protected function prepare_rendering()
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
		
		parent::prepare_rendering();
	}
}
?>