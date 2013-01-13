<?php
/**
 * Contains the security-code-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
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
 * The security-code
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_security_code extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->use_gdimage_renderer();
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$user = FWS_Props::get()->user();
		$doc = FWS_Props::get()->doc();
		
		$imagedir = FWS_Path::server_app().'images/gd/';
		$captcha = new FWS_GD_Captcha();
		$captcha->add_ttf_font(new FWS_GD_Font_TTF($imagedir.'veramono.ttf'),35);
		$captcha->add_ttf_font(new FWS_GD_Font_TTF($imagedir.'thros.ttf'),40);
		$captcha->add_ttf_font(new FWS_GD_Font_TTF($imagedir.'holstein.ttf'),40);
		$captcha->create_image();
		
		$renderer = $doc->use_gdimage_renderer();
		$renderer->set_image($captcha->get_image());
		
		$user->set_session_data('security_code',$captcha->get_chars());
	}
}
?>
