<?php
/**
 * Contains the password-class
 * 
 * @package     Boardsolution
 * @subpackage  src
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
 * This is responsible for generating password hashes and verifying them
 * 
 * @package     Boardsolution
 * @subpackage  src
 * @author      Nils Asmussen <nils@script-solution.de>
 */
final class BS_Password
{
	public static function hash($pw)
	{
		return password_hash($pw,PASSWORD_DEFAULT);
	}

	public static function needs_rehash($hash)
	{
		return password_needs_rehash($hash,PASSWORD_DEFAULT);
	}

	public static function verify($pw,$hash)
	{
		return password_verify($pw,$hash);
	}
}
?>