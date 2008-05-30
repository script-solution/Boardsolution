<?php
/**
 * Contains the bbcode-content-code class
 *
 * @version			$Id: code.php 795 2008-05-29 18:22:45Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The code-content-type which performs special changes for the code-tag and highlights
 * the code.
 *
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Content_Code extends BS_BBCode_Content_Default
{
	/**
	 * The original parameter
	 *
	 * @var string
	 */
	private $_old_param;
	
	/**
	 * @see BS_BBCode_Content_Default::get_param()
	 *
	 * @param unknown_type $param
	 * @return unknown
	 */
	public function get_param($param)
	{
		$this->_old_param = PLIB_String::strtolower($param);
		if($param && $this->cfg['msgs_code_highlight'])
		{
			$hldir = PLIB_Path::inner().'bbceditor/highlighter/';
			PLIB_Highlighting_Languages::ensure_inited($hldir.'languages.xml');
			$name = PLIB_Highlighting_Languages::get_language_name($this->_old_param);
			if($name === null)
				return $param;
			return $name;
		}
		
		return $param;
	}

	public function get_text($inner,$param)
	{
		$code = $this->_get_trimmed_code($inner);
		
		if($this->cfg['msgs_code_line_numbers'])
			$line_count = count(PLIB_Array_Utils::advanced_explode("\n",$code));
							
		// highlight code?
		$use_highlighting = $this->cfg['msgs_code_highlight'] && $param;
		if($use_highlighting)
		{
			$hldir = PLIB_Path::inner().'bbceditor/highlighter/';
			PLIB_Highlighting_Languages::ensure_inited($hldir.'languages.xml');
			
			// does the language exist?
			if(!PLIB_Highlighting_Languages::contains_lang($this->_old_param))
				$use_highlighting = false;
			else
			{
				$helper = BS_BBCode_Helper::get_instance();
				$code = PLIB_StringHelper::htmlspecialchars_back($code);
				$code_length = PLIB_String::strlen($code);
				$current_length = $helper->get_variable('code_total_length');
				// determine if we already have highlighted too much
				if($current_length + $code_length > BS_CODE_HIGHLIGHT_LIMIT)
				{
					$use_highlighting = false;
					$code = htmlspecialchars($code,ENT_QUOTES);
				}
				else
				{
					// ok, highlight the code
					$helper->set_variable('code_total_length',$current_length + $code_length);
	
					$lang = new PLIB_Highlighting_Language_XML($hldir.$this->_old_param.'.xml');
					$dec = new PLIB_Highlighting_Decorator_HTML();
					$hl = new PLIB_Highlighting_Processor($code,$lang,$dec);
					$code = $hl->highlight();
					$code = '<code style="white-space: nowrap;">'.$code.'</code>';
				}
			}
		}
		
		// no highlighting?
		if(!$use_highlighting)
		{
			$code = str_replace(' ','&nbsp;',$code);
			$code = str_replace("\n",'<br />',$code);
			$code = str_replace("\t",'&nbsp;&nbsp;&nbsp;',$code);
			$code = '<code style="white-space: nowrap;">'.$code.'</code>';
		}
		
		// add line numbers?
		if($this->cfg['msgs_code_line_numbers'])
		{
			$fcode = '<table width="100%" cellpadding="0" cellspacing="0">';
			$fcode .= '	<tr>';
			$fcode .= '		<td class="bs_lcode"><code>';
			for($i = 1,$len = $line_count;$i <= $len;$i++)
				$fcode .= $i.'<br />';
			$fcode .= '</code></td>';
			$fcode .= '		<td class="bs_rcode">'.$code.'</td>';
			$fcode .= '	</tr>';
			$fcode .= '</table>';
			
			$code = $fcode;
		}

		// we will insert the code-section afterwards
		// therefore will can perform stuff like wordwrap, smiley-replacements, ...
		// in the complete text but not in the code-sections
		
		BS_BBCode_Helper::get_instance()->add_replacement('<CODE'.$this->_id.'>',$code);
		return '<CODE'.$this->_id.'>';
	}

	/**
	 * A custom trim-function for code-sections.
	 * Spaces and tabs are only allowed in the line which also contains non-whitespace.
	 * This method should just be called by BS_BBCode_Section!
	 *
	 * @param string $code the input-code
	 * @return string the trimmed code
	 */
	private function _get_trimmed_code($code)
	{
		// walk forward until we've found a non-whitespace-character
		$pos = 0;
		$start = 0;
		$len = PLIB_String::strlen($code);
		while($pos < $len)
		{
			$c = PLIB_String::substr($code,$pos,1);
			if(!PLIB_String::is_whitespace($c))
				break;
			
			// have we found a line-wrap? so we'll remove everything in front this position
			if($c == "\n")
				$start = $pos + 1;
			
			$pos++;
		}

		// cut the beginning, if necessary
		if($start > 0)
		{
			$code = PLIB_String::substr($code,$start);
			$len -= $start;
		}

		// the same as above. just at the end :)
		$pos = $len - 1;
		$end = $len - 1;
		while($pos >= 0)
		{
			$c = PLIB_String::substr($code,$pos,1);
			if(!PLIB_String::is_whitespace($c))
				break;
			
			if($c == "\n")
				$end = $pos;
			
			$pos--;
		}

		// cut the end if necessary
		if($end < $len - 1)
			$code = PLIB_String::substr($code,0,$end);

		return $code;
	}
}
?>