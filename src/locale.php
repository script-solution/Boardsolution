<?php
/**
 * Contains the default locale
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
		
		$lang = $this->get_language_entries($file,$lang);
		
		if(!is_array($this->_lang))
			$this->_lang = array();
		
		$this->_language_files[$lang.$file] = true;
		$this->_lang = array_merge($this->_lang,$lang);
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
					$name = preg_replace('/{([a-z0-9_:]+?)}/ie','\\1',$name);
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
		$user = FWS_Props::get()->user();

		if(!method_exists($user,'get_language'))
			return ',';
		
		switch($user->get_language())
		{
			case 'ger_du':
			case 'ger_sie':
				return ',';
			
			default:
				return '.';
		}
	}
	
	public function get_thousands_separator()
	{
		$user = FWS_Props::get()->user();

		if(!method_exists($user,'get_language'))
			return '.';
		
		switch($user->get_language())
		{
			case 'ger_du':
			case 'ger_sie':
				return '.';
			
			default:
				return ',';
		}
	}
	
	public function get_date_order()
	{
		$user = FWS_Props::get()->user();

		if(!method_exists($user,'get_language'))
			return array('d','m','Y');
		
		switch($user->get_language())
		{
			case 'ger_du':
			case 'ger_sie':
				return array('d','m','Y');
			
			default:
				return array('m','d','Y');
		}
	}
	
	public function get_date_separator()
	{
		$user = FWS_Props::get()->user();

		if(!method_exists($user,'get_language'))
			return '.';
		
		switch($user->get_language())
		{
			case 'ger_du':
			case 'ger_sie':
				return '.';
			
			default:
				return '/';
		}
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