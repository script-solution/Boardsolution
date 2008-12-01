<?php
/**
 * Contains the bbcode-helper-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	 * Resets everything for a new text to parse
	 */
	public function reset()
	{
		$this->_variables = array();
		$this->_replacements = array();
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
		
		$sallowed = BS_PostingUtils::get_instance()->get_message_option('allowed_tags',$location);
		$allowed = FWS_Array_Utils::advanced_explode(',',$sallowed);
		foreach(array_keys($this->_tags) as $name)
		{
			if(!in_array($name,$allowed))
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
	 * The callback-function to parse an URL.
	 *
	 * @param string $url the URL
	 * @return string the HTML-Code for the given URL
	 */
	public function parse_url($url)
	{
		$url = trim($url);
		$url = str_replace("\n",'',$url);
		// prevent javascript
		$url = preg_replace('/javascript:/i','java_script_',$url);

		// prepend http:// if necessary
		if(!preg_match('/^(http|https|ftp|news):\/\//i',$url))
		{
			if(preg_match('/^www\./i',$url))
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
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>