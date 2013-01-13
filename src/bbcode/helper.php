<?php
/**
 * Contains the bbcode-helper-class
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
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
 * Some helper-methods for the bbcode-package
 *
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Helper extends FWS_Singleton
{
	/**
	 * @return BS_BBCode_Helper the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * The BBCode-tags
	 *
	 * @var array
	 */
	private $_tags = null;
	
	/**
	 * A copy of the tags, to be able to restore the loaded tags on reset
	 *
	 * @var array
	 */
	private $_tags_copy = null;
	
	/**
	 * The available fonts
	 *
	 * @var array
	 */
	private $_fonts = null;
	
	/**
	 * A storage for every kind of variable that should be valid for the whole text
	 *
	 * @var array
	 */
	private $_variables = array();
	
	/**
	 * Texts that should be replaced with something else later on.
	 *
	 * @var string
	 */
	private $_replacements = array();
	
	/**
	 * Wether the highlighting-limit has been reached
	 *
	 * @var boolean
	 */
	private $_reached_hl_limit = false;
	
	/**
	 * Resets everything for a new text to parse
	 */
	public function reset()
	{
		$this->_tags = $this->_tags_copy;
		$this->_reached_hl_limit = false;
		$this->_variables = array();
		$this->_replacements = array();
	}
	
	/**
	 * @return boolean wether a code-section could not be highlighted because the hl-limit has been
	 * 	reached
	 */
	public function reached_hl_limit()
	{
		return $this->_reached_hl_limit;
	}
	
	/**
	 * Sets that the highlighting-limit has been reached
	 */
	public function set_reached_hl_limit()
	{
		$this->_reached_hl_limit = true;
	}
	
	/**
	 * @return int the number of images
	 */
	public function get_image_count()
	{
		if(!isset($this->_variables['images']))
			return 0;
		return $this->_variables['images'];
	}
	
	/**
	 * Increments the number of images by 1
	 */
	public function increment_images()
	{
		if(!isset($this->_variables['images']))
			$this->_variables['images'] = 0;
		$this->_variables['images']++;
	}
	
	/**
	 * Returns the value of the variable with given name
	 *
	 * @param string $name the var-name
	 * @return mixed the value or null if not existing
	 */
	public function get_variable($name)
	{
		if(isset($this->_variables[$name]))
			return $this->_variables[$name];
		
		return null;
	}
	
	/**
	 * Sets the variable with given name to given value
	 *
	 * @param string $name the var-name
	 * @param mixed $value the new value
	 */
	public function set_variable($name,$value)
	{
		$this->_variables[$name] = $value;
	}
	
	/**
	 * Adds the given replacement to the list. The text will be replaced after smileys,badwords and
	 * URLs have been replaced and wordwrap has been performed.
	 *
	 * @param string $text the text to insert temporary
	 * @param string $replacement the text with which it should be replaced later on
	 */
	public function add_replacement($text,$replacement)
	{
		$this->_replacements[$text] = $replacement;
	}
	
	/**
	 * @return array the list of replacements that should be performed at the very end
	 */
	public function get_replacements()
	{
		return $this->_replacements;
	}
	
	/**
	 * Removes all disallowed tags for the given location. Note this method <b>has to</b> be
	 * called at first if you use the helper the first time!
	 *
	 * @param string $location the location
	 */
	public function remove_disallowed_tags($location)
	{
		if($this->_tags === null)
			$this->_load_tags();
		
		$sallowed = BS_PostingUtils::get_message_option('allowed_tags',$location);
		$allowed = FWS_Array_Utils::advanced_explode(',',strtolower($sallowed));
		foreach(array_keys($this->_tags) as $name)
		{
			if(!in_array(strtolower($name),$allowed))
				unset($this->_tags[$name]);
		}
	}
	
	/**
	 * @return array an array with all bbcode-tags
	 */
	public function get_tags()
	{
		if($this->_tags === null)
			$this->_load_tags();
		
		return $this->_tags;
	}
	
	/**
	 * Returns the definition of the given tag or null if it does not exist
	 *
	 * @param string $name the name of the tag
	 * @return array the definition of it
	 */
	public function get_tag($name)
	{
		if($this->_tags === null)
			$this->_load_tags();
		
		if(isset($this->_tags[$name]))
			return $this->_tags[$name];
		
		return null;
	}
	
	/**
	 * @return array the available fonts (in lower-case)
	 */
	public function get_fonts()
	{
		$cfg = FWS_Props::get()->cfg();
		if($this->_fonts === null)
			$this->_fonts = FWS_Array_Utils::advanced_explode(',',strtolower($cfg['post_font_pool']));
		return $this->_fonts;
	}
	
	/**
	 * The callback-function to parse an URL.
	 *
	 * @param string $url the URL
	 * @return string the HTML-Code for the given URL
	 */
	public function parse_url($url)
	{
		$url = trim($url);
		$url = str_replace(array("\n",'&quot;'),'',$url);
		// prevent javascript
		$url = preg_replace('/javascript:/i','java_script_',$url);

		// prepend http:// if necessary
		if(!preg_match('/^(http|https|ftp|news):\/\//i',$url))
		{
			if(!preg_match('/^magnet:/i',$url) && preg_match('/^www\./i',$url))
				$url = 'http://'.$url;
		}

		return $url;
	}
	
	/**
	 * Loads the tags from the database
	 */
	private function _load_tags()
	{
		$this->_tags = array();
		foreach(BS_DAO::get_bbcodes()->get_list() as $tag)
		{
			$con = FWS_Array_Utils::advanced_explode(',',$tag['allowed_content']);
			$tag['allowed_content'] = FWS_Array_Utils::get_fast_access($con);
			$this->_tags[$tag['name']] = $tag;
		}
		// save a copy
		$this->_tags_copy = $this->_tags;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>
