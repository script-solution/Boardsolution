<?php
/**
 * Contains the pagination-class for the ACP
 *
 * @version			$Id: pagination.php 676 2008-05-08 09:02:28Z nasmussen $
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
final class BS_ACP_Pagination extends PLIB_Pagination
{
	/**
	 * Constructor
	 * 
	 * @param int $per_page the number of entries per page
	 * @param int $num the total number of entries
	 */
	public function __construct($per_page,$num)
	{
		$page = $this->input->get_var('site','get',PLIB_Input::INTEGER);
		parent::__construct($per_page,$num,$page);
	}
}
?>