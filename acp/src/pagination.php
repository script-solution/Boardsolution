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
final class BS_ACP_Pagination extends FWS_Pagination
{
	/**
	 * Constructor
	 * 
	 * @param int $per_page the number of entries per page
	 * @param int $num the total number of entries
	 */
	public function __construct($per_page,$num)
	{
		$input = FWS_Props::get()->input();

		$page = $input->get_var('site','get',FWS_Input::INTEGER);
		parent::__construct($per_page,$num,$page);
	}
}
?>