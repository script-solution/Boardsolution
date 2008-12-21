<?php
/**
 * The standalone-entry-point for Boardsolution
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	main
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

// We can simply include the index.php since we just want to ensure that boardsolution
// will be shown exclusivly. That means if somebody includes the index.php this will not affect
// standalone-modules.
define('BS_PATH','');
include('index.php');
?>