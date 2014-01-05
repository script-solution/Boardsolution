<?php
/**
 * Contains constants for the dbbackup-script
 * 
 * @package			Boardsolution
 * @subpackage	config
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
define('BS_DBA_ENABLE_GZIP',false);

/**
 * The number of SQL-statements per file
 */
define('BS_DBA_OPERATIONS_PER_CYCLE',200);

/**
 * The version of BSDBA
 */
define('BS_DBA_VERSION','Boardsolution Database Admin v1.10');

/**
 * The version-id of BSDBA
*/
define('BS_DBA_VERSION_ID','110');
?>