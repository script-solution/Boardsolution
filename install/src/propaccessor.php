<?php
/**
 * Contains the property-accessor-class
 *
 * @version			$Id: propaccessor.php 43 2008-07-30 10:47:55Z nasmussen $
 * @package			Boardsolution
 * @subpackage	install.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The property-accessor for the install-script
 *
 * @package			Boardsolution
 * @subpackage	install.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_PropAccessor extends BS_PropAccessor
{
	/**
	 * @see BS_PropAccessor::doc()
	 *
	 * @return BS_Install_Document
	 */
	public function doc()
	{
		return $this->get('doc');
	}
}
?>