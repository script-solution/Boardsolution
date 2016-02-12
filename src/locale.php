<?php
/**
 * Contains the default locale
 * 
 * @package			Boardsolution
 * @subpackage	src
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
 * The default locale for Boardsolution
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Locale extends FWS_Object implements FWS_Locale
{
	/**
	 * The language-entries
	 *
	 * @var array
	 */
	private $_lang = array();
	
	/**
	 * An array of all yet added language-files
	 *
	 * @var array
	 */
	private $_language_files = array();
	
	/**
	 * Includes the given language-file. The file is just the name. That means:
	 * <code>language/<lang>/$file</code>
	 *
	 * @param string $file the file to include
	 * @param string $lang optional you can specify the language-folder to use
	 */
	public function add_language_file($file,$lang = null)
	{
		// have we already added the file?
		if(isset($this->_language_files[$lang.$file]))
			return;
		
		$entries = $this->get_language_entries($file,$lang);
		
		if(!is_array($this->_lang))
			$this->_lang = array();
		
		$this->_language_files[$lang.$file] = true;
		$this->_lang = array_merge($this->_lang,$entries);
	}
	
	/**
	 * Retrieves all entries from the given language-file
	 * 
	 * @param string $file the file to include
	 * @param string $language optional you can specify the language-folder to use
	 * @return array the entries
	 */
	public function get_language_entries($file,$language = null)
	{
		$user = FWS_Props::get()->user();

		if($language === null)
			$folder = $user->get_language();
		else
			$folder = $language;
		
		$lang = array();
		$path = FWS_Path::server_app().'language/'.$folder.'/'.$file.'.ini';
		if(is_file($path))
		{
			$matches = array();
			$content = FWS_FileUtils::read($path);
			preg_match_all('/(\S+)\s*=\s*"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"/is',$content,$matches);
			
			foreach($matches[1] as $k => $name)
			{
				if(strrchr($name,'}') !== false)
				{
					$name = preg_replace_callback(
						'/{(BS_[A-Z0-9_:]+?)}/i',function($m) { return constant($m[1]); },$name);
				}
				if(strrchr($matches[2][$k],'}') !== false)
				{
					$matches[2][$k] = preg_replace_callback(
						'/{(BS_[A-Z0-9_:]+?)}/i',function($m) { return constant($m[1]); },$matches[2][$k]);
				}
				$lang[$name] = str_replace('\\"','"',$matches[2][$k]);
			}
		}
		
		return $lang;
	}
	
	public function contains_lang($name)
	{
		return isset($this->_lang[$name]);
	}
	
	public function lang($name,$mark_missing = true)
	{
		if(isset($this->_lang[$name]))
			return $this->_lang[$name];
		
		if($mark_missing)
			return '&lt;'.$name.'&gt;';
		
		return $name;
	}
	
	public function get_dateformat($type)
	{
		switch($type)
		{
			case FWS_Locale::FORMAT_DATE:
				return $this->lang('date_format');
			
			case FWS_Locale::FORMAT_DATE_SHORT:
				return $this->lang('date_short_format');
			
			case FWS_Locale::FORMAT_DATE_LONG:
				return $this->lang('date_long_format');
			
			case FWS_Locale::FORMAT_TIME:
				return $this->lang('time_format');
			
			case FWS_Locale::FORMAT_TIME_SEC:
				return $this->lang('time_sec_format');
			
			case FWS_Locale::FORMAT_DATE_TIME_SEP:
				return $this->lang('date_time_separator');
			
			default:
				FWS_Helper::error('Invalid type $type!');
				return '';
		}
	}
	
	public function get_dec_separator()
	{
		if($this->contains_lang('dec_separator'))
			return $this->lang('dec_separator');
		return ',';
	}
	
	public function get_thousands_separator()
	{
		if($this->contains_lang('thousand_separator'))
			return $this->lang('thousand_separator');
		return '.';
	}
	
	public function get_date_order()
	{
		if($this->contains_lang('date_comp_order'))
			return explode(',',$this->lang('date_comp_order'));
		return array('m','d','Y');
	}
	
	public function get_date_separator()
	{
		if($this->contains_lang('date_separator'))
			return $this->lang('date_separator');
		return '.';
	}
	
	public function get_timezone()
	{
		$user = FWS_Props::get()->user();

		if(!method_exists($user,'get_profile_val'))
			return 'Europe/Berlin';
		
		$tz = $user->get_profile_val('timezone');
		if($tz && @timezone_open($tz) !== false)
			return $tz;
		
		return 'Europe/Berlin';
	}
	
	public function set_timezone($timezone)
	{
		throw new FWS_Exception_UnsupportedMethod();
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>