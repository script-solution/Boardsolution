<?php
/**
 * Contains constants for the dbbackup-script
 *
 * @version			$Id: dbbackup.php 687 2008-05-10 16:04:07Z nasmussen $
 * @package			Boardsolution
 * @subpackage	config
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The used language
 */
define('BS_DBA_LANGUAGE','ger_du');

/**
 * The used line-wrap
 */
define('BS_DBA_LINE_WRAP',"\n");

/**
 * Do you want to enable GZip?
 */
define('BS_DBA_ENABLE_GZIP',true);

/**
 * The number of SQL-statements per file
 */
define('BS_DBA_OPERATIONS_PER_CYCLE',200);
?>