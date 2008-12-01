<?php
/**
 * Contains the pagination-class for the ACP
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The pagination for the ACP of Boardsolution. Determines the page-number automaticly.
 * 
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Pagination extends BS_Pagination
{
	/**
	 * @see BS_Pagination::get_page_param()
	 *
	 * @return string
	 */
	protected function get_page_param()
	{
		return 'site';
	}
}
?>