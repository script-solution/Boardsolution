<?php
/**
 * Contains the bbcode-section class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Represents a BBCode-tag. The most important components are the name, the parameter, the sub-tags
 * and the content
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Section extends PLIB_Object
{
	/**
	 * The id of the tag
	 *
	 * @var integer
	 */
	private $_tag_id = -1;
	
	/**
	 * The name of the tag
	 *
	 * @var string
	 */
	private $_name = '';
	
	/**
	 * The parameter of the tag
	 *
	 * @var string
	 */
	private $_param = '';
	
	/**
	 * The sub-sections of this tag
	 *
	 * @var array
	 */
	private $_sub_sections = array();
	
	/**
	 * The number of sub-sections
	 *
	 * @var integer
	 */
	private $_sub_sec_count = 0;
	
	/**
	 * The content of this tag
	 *
	 * @var string
	 */
	private $_content = '';

	/**
	 * constructor
	 *
	 * @param int $tag_id the id of the tag
	 * @param string $name the name of the tag
	 * @param string $param the parameter of the tag
	 * @param string $content the content of the tag
	 */
	public function __construct($tag_id,$name = '',$param = '',$content = '')
	{
		parent::__construct();
		
		$this->_tag_id = $tag_id;
		$this->_name = $name;
		$this->_param = $param;
		$this->_content = $content;
	}

	/**
	 * @return int the number of sub-sections
	 */
	public function get_sub_section_count()
	{
		return $this->_sub_sec_count;
	}

	/**
	 * appends the given string to the content
	 *
	 * @param string $string the string to append
	 */
	public function append($string)
	{
		$this->_content .= $string;
	}
	
	/**
	 * @return object the last subsection of this section
	 */
	public function get_last_subsection()
	{
		return $this->_sub_sec_count > 0 ? $this->_sub_sections[$this->_sub_sec_count - 1] : null;
	}

	/**
	 * adds the given section to the sub-sections
	 *
	 * @param object $section the section to add
	 */
	public function add_sub_section($section)
	{
		$this->_sub_sections[] = $section;
		$this->_sub_sec_count++;
	}

	/**
	 * Builds the content (recursively)
	 *
	 * @return string the html-code
	 */
	public function get_content()
	{
		// if this tag has no sub-tags simply return the content
		if($this->_sub_sec_count == 0)
			return $this->_content;

		// collect the content from the sub-sections
		$result = '';
		foreach($this->_sub_sections as $section)
			$result .= $section->get_content();

		// if this tag has no name return the content of the sub-sections
		if($this->_name == '')
			return $result;

		// is the parameter valid?
		$replacement = $this->_param == '' ? 'replacement' : 'replacement_param';
		$valid_param = false;
		$tag_config = BS_BBCode_Helper::get_instance()->get_tag($this->_name);

		if($tag_config['param'] == 'required')
			$replacement = 'replacement_param';
		
		$conclass = 'BS_BBCode_Content_'.$tag_config['content'];
		if(class_exists($conclass))
		{
			// if the parameter is optional, it is valid
			if($this->_param == '' && $tag_config['param'] != 'required')
				$valid_param = true;
			// if the param has been specified but is not allowed, it is not valid
			else if($this->_param != '' && $tag_config['param'] == 'no')
				$valid_param = false;
			// otherwise we check it depending on the type
			else
			{
				switch($tag_config['param_type'])
				{
					case 'identifier':
						$valid_param = preg_match('/^[a-z0-9_-]+$/i',$this->_param);
						break;
					case 'integer':
						$valid_param = preg_match('/^[0-9]+$/',$this->_param);
						break;
					case 'color':
						$valid_param = preg_match('/^#[a-f0-9]{6}|[a-z-]+$/i',$this->_param);
						break;
					case 'url':
						$param = PLIB_StringHelper::correct_homepage($this->_param);
						if($param === false)
							$valid_param = false;
						else
						{
							$valid_param = true;
							$this->_param = $param;
						}
						break;
					case 'mail':
						$valid_param = PLIB_StringHelper::is_valid_email($this->_param);
						break;
					
					// by default it is valid
					default:
						$valid_param = true;
						break;
				}
			}
			
			// valid parameter?
			if($valid_param)
			{
				$conobj = new $conclass($this->_tag_id);
				$param = $conobj->get_param($this->_param);
				// additional check by get_param()
				if($param !== false)
				{
					$content = $conobj->get_text($result,$param);
		
					$result = str_replace('{TEXT}',$content,$tag_config[$replacement]);
		
					// replace parameter
					if($param != '' && $tag_config['param'] != 'no')
						$result = str_replace('{PARAM}',$param,$result);
					else if($tag_config['param'] == 'optional')
						$result = str_replace('{PARAM}','',$result);
		
					return $result;
				}
			}
		}

		// no valid bbcode-tag...
		$param = $this->_param != '' ? '='.$this->_param : '';
		return '['.$this->_name.$param.']'.$result.'[/'.$this->_name.']';
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>