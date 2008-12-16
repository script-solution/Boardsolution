<?php
/**
 * The BBCode-engine
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * This class parses the BBCode, creates the cached HTML-code to store in the db
 * and will also be used to finish the cached HTML-code so that it can be displayed.
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Parser extends FWS_Object
{
	/**
	 * Open tags at a opening-tag which does not allow open tags
	 */
	const ERR_DISALLOWED_OPEN_TAGS = 0;
	
	/**
	 * A nested tag (...[b]...[b]...[/b]...[/b]...)
	 */
	const ERR_NESTED_TAG = 1;
	
	/**
	 * More closing tags than opening tags
	 */
	const ERR_MISSING_OPENING_TAG = 2;
	
	/**
	 * Wrong closing-tag order
	 */
	const ERR_WRONG_CLOSE_ORDER = 3;
	
	/**
	 * The maximum nested level has been reached
	 */
	const ERR_MAX_NESTED_LEVEL = 4;
	
	/**
	 * List-point in another tag than "list"
	 */
	const ERR_INVALID_LIST_POINT_POSITION = 5;
	
	/**
	 * Missing closing tag
	 */
	const ERR_MISSING_CLOSING_TAG = 6;
	
	/**
	 * The text with which we will work
	 *
	 * @var string
	 */
	private $_text;

	/**
	 * The location: posts, sig, desc
	 *
	 * @var string
	 */
	private $_location;

	/**
	 * Is BBCode (in this message!) enabled?
	 *
	 * @var boolean
	 */
	private $_enable_bbcode;

	/**
	 * Are smileys (in this message!) enabled?
	 *
	 * @var boolean
	 */
	private $_enable_smileys;

	/**
	 * The number of smileys (will only be counted if you call the get_message_for_db()-method)
	 *
	 * @var integer
	 */
	private $_smiley_count = 0;

	/**
	 * The path to the board (to show messages outside of the board)
	 *
	 * @var string
	 */
	private $_board_path = '';

	/**
	 * constructor
	 * 
	 * @param string $text the text to format
	 * @param string $loc the location: posts, sig, desc
	 * @param boolean $enable_bbcode do you want to enable bbcode in this message?
	 * @param boolean $enable_smileys do you want to enable smileys in this message?
	 */
	public function __construct($text,$loc,$enable_bbcode,$enable_smileys)
	{
		parent::__construct();
		
		$this->_text = $text;
		$this->_location = $loc;
		$this->_enable_bbcode = $enable_bbcode;
		$this->_enable_smileys = $enable_smileys;
		$this->_board_path = FWS_Path::client_app();
		BS_BBCode_Helper::get_instance()->reset();
	}

	/**
	 * Sets the board-path to given value. This may be usefull if you want to print a message
	 * outside of the board.
	 *
	 * @param string $path the new value
	 */
	public function set_board_path($path)
	{
		$this->_board_path = $path;
	}

	/**
	 * NOTE: will only be counted if you call the get_message_for_db()-method
	 *
	 * @return int the number of smileys in the post
	 */
	public function get_number_of_smileys()
	{
		return $this->_smiley_count;
	}

	/**
	 * NOTE: will only be counted if you call the get_message_for_db()-method
	 *
	 * @return int the number of images in the post
	 */
	public function get_number_of_images()
	{
		return BS_BBCode_Helper::get_instance()->get_image_count();
	}
	
	/**
	 * Calls stripslashes($this->_text).
	 * This may be usefull if you want to parse a BBCode and want to output it directly
	 * via get_message_for_output().
	 */
	public function stripslashes()
	{
		$this->_text = stripslashes($this->_text);
	}

	/**
	 * prepares the message for storage in the database.
	 * it will parse the BBCode into HTML-Code which can nearly be printed out directly
	 * NOTE: the method will perform a stripslashes(&lt;text&gt;) at the beginning
	 * and addslashes(&lt;text&gt;) at the end
	 *
	 * @return string the string to store in the database
	 * @throws BS_BBCode_Exception if anything goes wrong
	 */
	public function get_message_for_db()
	{
		$this->_text = stripslashes($this->_text);

		// save performance if bbcode is disabled
		if(!$this->_enable_bbcode)
		{
			$this->_replace_remaining_stuff();
			$this->_text = addslashes($this->_text);
			return $this->_text;
		}

		// match all tags in the text
		$tags = array();
		preg_match_all(
			'/(.*?)(\[(\/?[a-z]+)(=?[^\]\[]*\]))|(.*?)$/si',$this->_text,$tags,PREG_OFFSET_CAPTURE
		);
		
		// correct the tags
		$this->_correct_tags($tags);
		
		// build the sections
		$root = $this->_build_sections($tags);

		// get html-code
		$this->_text = $root->get_content();
		$this->_replace_remaining_stuff();

		// replace all necessary stuff
		foreach(BS_BBCode_Helper::get_instance()->get_replacements() as $search => $replace)
			$this->_text = str_replace($search,$replace,$this->_text);

		$this->_text = addslashes($this->_text);

		return $this->_text;
	}

	/**
	 * prepares the message for output
	 * it performs the last changes: highlighting code-sections, replacing smileys and badwords
	 *
	 * @param boolean $wordwrap_codes do you want to perform a wordwrap in code-sections?
	 * @return string the string to output
	 */
	public function get_message_for_output($wordwrap_codes = false)
	{
		$cfg = FWS_Props::get()->cfg();
		$functions = FWS_Props::get()->functions();

		if($wordwrap_codes)
		{
			$this->_text = str_replace('<br />',"\n",$this->_text);
			$this->_text = BS_BBCode_WordWrap::word_wrap_special(
				$this->_text,$cfg['msgs_max_line_length']
			);
			$this->_text = str_replace("\n",'<br />',$this->_text);
		}

		// replace boardsolution-file and language-entries
		$this->_text = str_replace('{BSF}',
			$this->_board_path.$functions->get_board_file(true),$this->_text);
		$this->_text = preg_replace(
			'/{LANG=([^}]+?)}/e','FWS_Props::get()->locale()->lang("\\1")',$this->_text
		);

		// replace paths
		if($this->_enable_smileys)
			$this->_text = str_replace('{EMP}',$this->_board_path.'images/smileys/',$this->_text);

		return $this->_text;
	}

	/**
	 * replaces wordwrap, badwords, smileys, plain-text urls, ...
	 */
	private function _replace_remaining_stuff()
	{
		$cfg = FWS_Props::get()->cfg();

		$search = array();
		$replace = array();

		// convert plain-text urls and emails to links?
		if($cfg['msgs_parse_urls'] == 1)
		{
			$search[] = '/(\A|\s)((http(s?)|ftp):\/\/|www\.)([^\s,<"]+)/ise';
			$search[] = '/(\A|\s)([-_a-z0-9\.]+)@([-_a-z0-9]+).([a-z]+)/i';

			$replace[] = '"\\1<a target=\"_blank\" href=\""'
				.'.BS_BBCode_Helper::get_instance()->parse_url("\\2\\5")."\">\\2\\5</a>"';
			$replace[] = '\\1<a href="mailto:\\2@\\3.\\4">\\2@\\3.\\4</a>';
		}

		// replace the smileys
		if($this->_enable_smileys)
		{
			$smileys = $this->_get_smileys();
			$len = count($smileys);
			for($i = 0;$i < $len;$i++)
			{
				if(trim($smileys[$i]['code']) != '')
				{
					$search[] = "/".preg_quote($smileys[$i]['code'],'/').'($|\s|<)/ei';
					$replace[] = '$this->_get_smiley_code("'.$smileys[$i]['code'].'","'
						.$smileys[$i]['path'].'")."\\1"';
				}
			}
		}

		if(count($search) > 0)
			$this->_text = preg_replace($search,$replace,$this->_text);

		// wordwrap...
		$this->_text = BS_BBCode_WordWrap::word_wrap_special(
			$this->_text,$cfg['msgs_max_line_length']
		);

		// replace badwords
		if($cfg['enable_badwords'] == 1)
			$this->_text = $this->_replace_badwords($this->_text);

		$this->_text = str_replace("\n",'<br />',$this->_text);
	}

	/**
	 * Builds the html-code for the given smiley and increases the number of smileys.
	 * This method should just be called by BS_BBCode_Section!
	 *
	 * @param string $code the code of the smiley
	 * @param string $image the smiley-image
	 * @return string the html-code for the smiley
	 */
	private function _get_smiley_code($code,$image)
	{
		$this->_smiley_count++;
		return '<img title="'.$code.'" alt="'.$code.'" src="{EMP}'.$image.'" />';
	}

	/**
	 * the compare-function to sort the smileys
	 *
	 * @param array $a the first smiley-data
	 * @param array $b the second smiley-data
	 * @return int the sort-result: <pre>
	 * 		0 if they are equal
	 * 	 -1 if the first one is greater
	 * 		1 if the second one is greater
	 * 	</pre>
	 */
	private function _smiley_sort_cmp($a,$b)
	{
		$lena = FWS_String::strlen($a['code']);
		$lenb = FWS_String::strlen($b['code']);
		if($lena == $lenb)
			return 0;

		return $lena > $lenb ? -1 : 1;
	}

	/**
	 * collects all smileys and ensures that the order is correct
	 *
	 * @return an numeric array of the form:
	 * 	<code>
	 * 		array(
	 * 			'code' => <code>,
	 * 			'path' => <path>
	 *		)
	 * 	</code>
	 */
	private function _get_smileys()
	{
		static $smileys = null;
		if($smileys === null)
		{
			$smileys = array();
			foreach(BS_DAO::get_smileys()->get_list() as $row)
			{
				if($row['primary_code'] != '')
				{
					$smileys[] = array('code' => $row['primary_code'],'path' => $row['smiley_path']);
					if($row['secondary_code'] != '')
						$smileys[] = array('code' => $row['secondary_code'],'path' => $row['smiley_path']);
				}
			}
			usort($smileys,array($this,'_smiley_sort_cmp'));
		}
		
		return $smileys;
	}

	/**
	 * marks all badwords in the given string
	 *
	 * @param string $input the input-string
	 * @return string the result-string with all badwords marked
	 */
	private function _replace_badwords($input)
	{
		$cfg = FWS_Props::get()->cfg();

		$search = array();
		$replace = array();

		$badwords_highlight = FWS_StringHelper::htmlspecialchars_back($cfg['badwords_highlight']);
		foreach($this->_get_badwords() as $data)
		{
			if($data['word'] != '')
			{
				$replacement = str_replace('{value}',$data['replacement'],$badwords_highlight);

				if($cfg['badwords_spaces_around'] == 1)
				{
					$search[] = '/(^|\b)'.preg_quote($data['word'],'/').'(\b|$)/i';
					$replace[] = '\\1'.$replacement.'\\2';
				}
				else
				{
					$search[] = '/'.preg_quote($data['word'],'/').'/i';
					$replace[] = $replacement;
				}
			}
		}

		if(count($search > 0))
			$input = preg_replace($search,$replace,$input);

		return $input;
	}
	
	/**
	 * @return array the badword-list
	 */
	private function _get_badwords()
	{
		$cfg = FWS_Props::get()->cfg();

		static $badwords = null;
		if($badwords === null)
		{
			$badwords = array();
			$repl = $cfg['badwords_default_replacement'];
			$lines = explode("\n",$cfg['badwords_definitions']);
			foreach($lines as $line)
			{
				$split = explode('=',$line);
				if(isset($split[1]))
				{
					$badwords[] = array(
						'word' => trim($split[0]),
						'replacement' => trim($split[1])
					);
				}
				else
				{
					$badwords[] = array(
						'word' => trim($split[0]),
						'replacement' => $repl
					);
				}
			}
		}
		
		return $badwords;
	}

	/**
	 * Corrects all tags if possible. if it is not possible the method returns false
	 *
	 * @param array $tags the preg_match_all() result with the found tags
	 * @return array the error-code of the format <code>array(<pos>,<code>)</code> or true if
	 * 	everything is ok.
	 */
	private function _correct_tags(&$tags)
	{
		$helper = BS_BBCode_Helper::get_instance();
		$helper->remove_disallowed_tags($this->_location);
		$bbctags = $helper->get_tags();
		
		$sub_tags = array(
			array(
				array(
					'inline' => true,
					'block' => true,
					'link' => true
				),
				'block'
			)
		);
		$sub_count = 0;

		$list_open = 0;
		$open_tags = array();
		$open_count = 0;
		for($key = 0,$tagcount = count($tags[3]);$key < $tagcount;$key++)
		{
			$info = $tags[3][$key];
			if($info[0] == '')
				continue;

			$tag = FWS_String::strtolower($info[0]);
			$tags[3][$key][0] = $tag;
			$tag_name = ($tag[0] != '/') ? $tag : FWS_String::substr($tag,1);

			// if the tag is not known skip it
			if(!isset($bbctags[$tag_name]) || ($tags[4][$key][0][0] != '=' && $tags[4][$key][0] != ']'))
				continue;

			// opening tag?
			if($tag[0] != '/')
			{
				// if the sub-tag is not allowed treat it as plain-text
				$allowed = $this->_check_allowed_content($sub_tags,$sub_count,$bbctags[$tag_name]['type']);

				// error?
				if($allowed == 0)
				{
					throw new BS_BBCode_Exception_Syntax(
						$this->_text,$info[1] - 1,self::ERR_DISALLOWED_OPEN_TAGS
					);
				}

				// treat as plain-text?
				if($allowed == 1)
					continue;

				// nested tag and nesting disallowed?
				if(in_array($tag,$open_tags))
				{
					if(!$bbctags[$tag_name]['allow_nesting'])
					{
						throw new BS_BBCode_Exception_Syntax(
							$this->_text,$info[1] - 1,self::ERR_NESTED_TAG
						);
					}

					// limit the number of nested tags
					if($this->_get_nested_tag_num($open_tags,$open_count,$tag) > BS_BBCODE_MAX_NESTED_LEVEL)
					{
						throw new BS_BBCode_Exception_Syntax(
							$this->_text,$info[1] - 1,self::ERR_MAX_NESTED_LEVEL
						);
					}
				}

				// change allowed sub-tags
				$sub_tags[$sub_count + 1] = array(
					0 => $bbctags[$tag_name]['allowed_content'],
					1 => $bbctags[$tag_name]['type']
				);
				$sub_count++;

				// add it to the open tags
				if($tag == 'list')
					$list_open++;

				$open_tags[$open_count++] = $tag;
			}
			else
			{
				if($open_count > 0 && $open_tags[$open_count - 1] == $tag_name)
				{
					$allowed = $this->_check_allowed_content(
						$sub_tags,$sub_count - 1,$bbctags[$tag_name]['type']
					);
				}
				else
					$allowed = $this->_check_allowed_content($sub_tags,$sub_count,$bbctags[$tag_name]['type']);

				// treat as plain-text?
				if($allowed == 1)
					continue;

				// if there are no tags open anymore (we are about to close a tag) break here
				if($open_count == 0)
				{
					throw new BS_BBCode_Exception_Syntax(
						$this->_text,$info[1] - 1,self::ERR_MISSING_OPENING_TAG
					);
				}

				// ensure that the order of opening/closing tags is correct
				if($open_tags[$open_count - 1] != $tag_name)
				{
					// ok, the order is wrong. So we try to correct it. We do this by swapping the current
					// tag with the one that we except. If there is no such tag or an opening tag is
					// in front of it we report an error (missing closing tag).
					
					// search the following tags for the required closing-tag
					$target_tag = $open_tags[$open_count - 1];
					$swap_with = -1;
					for($k = $key + 1;$k < $tagcount;$k++)
					{
						$ktagname = FWS_String::strtolower($tags[3][$k][0]);
						// if it is an opening-tag we stop here
						if(!isset($ktagname[0]) || $ktagname[0] != '/')
							break;
						
						// have we found the tag?
						$ktagname = FWS_String::substr($ktagname,1);
						if($ktagname == $target_tag)
						{
							$swap_with = $k;
							break;
						}
					}
					
					// do we have found a tag to swap with?
					if($swap_with !== -1)
					{
						// swap all keys except 0 and 1
						foreach(array_keys($tags) as $k)
						{
							if($k > 1)
							{
								$t = $tags[$k][$key];
								$tags[$k][$key] = $tags[$k][$swap_with];
								$tags[$k][$swap_with] = $t;
							}
						}
						
						// we have to move the text from the one tag to the other
						$bracketpos = FWS_String::strpos($tags[0][$key][0],'[');
						$t = $tags[0][$key];
						$tags[0][$key] = array(
							FWS_String::substr($t[0],0,$bracketpos).$tags[0][$swap_with][0],
							$tags[0][$swap_with][1]
						);
						$tags[0][$swap_with] = array(
							FWS_String::substr($t[0],$bracketpos),
							$t[1]
						);
					}
					// otherwise report missing closing tag
					else
					{
						throw new BS_BBCode_Exception_Syntax(
							$this->_text,$info[1] - 1,self::ERR_MISSING_CLOSING_TAG
						);
					}
				}

				if($list_open > 0 && $open_tags[$open_count - 1] != 'list')
				{
					// if there is a list-point in a tag in a list, report an error
					if(FWS_String::strpos($tags[1][$key][0],'[*]') !== false)
					{
						throw new BS_BBCode_Exception_Syntax(
							$this->_text,$tags[1][$key][1],self::ERR_INVALID_LIST_POINT_POSITION
						);
					}
				}

				// restore last allowed sub-tags
				unset($sub_tags[$sub_count--]);

				// remove the tag from the open tags
				unset($open_tags[$open_count - 1]);
				$open_count--;

				if($tag_name == 'list')
					$list_open--;
			}
		}

		// if there are still open tags close them now
		while($open_count > 0)
		{
			$tags[0][] = array('[/'.$open_tags[$open_count - 1].']',-1);
			$tags[1][] = array('',-1);
			$tags[2][] = array('[/'.$open_tags[$open_count - 1].']',-1);
			$tags[3][] = array('/'.$open_tags[$open_count - 1],-1);
			$tags[4][] = array(']',-1);
			$tags[5][] = array('',-1);

			$this->_text .= '[/'.$open_tags[$open_count - 1].']';

			$open_count--;
		}

		return true;
	}

	/**
	 * counts the number of nested tags
	 *
	 * @param array $open_tags an array with all currently open tags
	 * @param int $open_count the number of open tags
	 * @param string $tag the name of the tag
	 * @return int the number of nested tags
	 */
	private function _get_nested_tag_num($open_tags,$open_count,$tag)
	{
		$count = 0;
		for($i = $open_count - 1;$i >= 0;$i--)
		{
			if($open_tags[$i] == $tag)
				$count++;
		}

		return $count;
	}

	/**
	 * calculates the next allowed content
	 *
	 * @param array $current the current allowed content
	 * @param array $new the new allowed content
	 */
	private function _get_allowed_content($current,$new)
	{
		foreach(array_keys($current) as $content)
		{
			if(!isset($new[$content]))
				unset($current[$content]);
		}

		return $current;
	}

	/**
	 * checks wether the given sub-tag is currently allowed
	 *
	 * @param array $sub_tags the sub-tags array
	 * @param int $sub_count the number of items in $sub_tags
	 * @param string $tag_type the type of the tag to check
	 * @return int -1 = allowed, 0 = error, 1 = treat as plain-text
	 */
	private function _check_allowed_content($sub_tags,$sub_count,$tag_type)
	{
		if(!isset($sub_tags[$sub_count][0][$tag_type]))
		{
			switch($sub_tags[$sub_count][1])
			{
				case 'inline':
					return 0;

				default:
					return 1;
			}
		}

		return -1;
	}

	/**
	 * builds the recursivly nested sections
	 *
	 * @param array $tags the preg_match_all() result with the found tags
	 * @return BS_BBCode_Section the root-section
	 */
	private function _build_sections($tags)
	{
		$helper = BS_BBCode_Helper::get_instance();
		$bbctags = $helper->get_tags();
		
		$next_id = 1;

		$sections = array();
		$sections[] = new BS_BBCode_Section($next_id++);
		$sec_len = 1;

		$sub_tags = array(
			array(
				array(
					'inline' => true,
					'block' => true,
					'link' => true
				),
				'block'
			)
		);
		$sub_count = 0;

		$open_tags = array();
		$open_count = 0;

		foreach($tags[3] as $key => $info)
		{
			$tag = $info[0];
			
			if($tag != '')
			{
				$tag = FWS_String::strtolower($tag);
				$tag_name = ($tag[0] != '/') ? $tag : FWS_String::substr($tag,1);
			}

			// is the bbcode-tag unknown?
			if($tag == '' || !isset($bbctags[$tag_name]) ||
				($tags[4][$key][0][0] != '=' && $tags[4][$key][0] != ']'))
			{
				$append = isset($tags[5][$key][0]) ? $tags[5][$key][0] : '';
				// so add it as plain-text
				$this->_add_to_last_section(
					$sections,$next_id,$sec_len,$tags[1][$key][0].$tags[2][$key][0].$append
				);
				continue;
			}

			// determine parent-tag
			$parent_tag = isset($open_tags[$open_count - 1]) ? $bbctags[$open_tags[$open_count - 1]] : null;

			// ignore whitespace
			if($parent_tag != null && isset($parent_tag['ignore_whitespace']) &&
					$parent_tag['ignore_whitespace'])
				$tags[1][$key][0] = ltrim($tags[1][$key][0]);

			// is it a opening-tag?
			if($tag[0] != '/')
			{
				// if the sub-tag is not allowed treat it as plain-text
				$allowed = $this->_check_allowed_content(
					$sub_tags,$sub_count,$bbctags[$tag_name]['type']
				);

				// treat as plain-text?
				if($allowed == 1)
				{
					// ignore tag?
					if($parent_tag != null && isset($parent_tag['ignore_unknown_tags']) &&
						 $parent_tag['ignore_unknown_tags'])
						continue;

					$sections[$sec_len - 1]->add_sub_section(
						new BS_BBCode_Section($next_id++,'','',$tags[1][$key][0].$tags[2][$key][0])
					);
				}
				// otherwise the tag is an allowed subtag
				// error is not possible because we would have found this earlier in the syntax-check
				else
				{
					// if the text before this tag is not empty add it to the last section
					if($tags[1][$key][0])
					{
						$sections[$sec_len - 1]->add_sub_section(
							new BS_BBCode_Section($next_id++,'','',$tags[1][$key][0])
						);
					}

					// build the new section for the current tag
					$param = '';
					$parts = explode('=',$tags[2][$key][0]);
					if(isset($parts[1]))
					{
						// if we have found more than 2 parts, we have to add them to the second part
						$param = $parts[1];
						$l = count($parts);
						for($i = 2;$i < $l;$i++)
							$param .= '='.$parts[$i];

						$param = FWS_String::substr($param,0,-1);
					}
					$sections[$sec_len++] = new BS_BBCode_Section($next_id++,$tag_name,$param,'');

					// add it as sub-section to the last tag
					$sections[$sec_len - 2]->add_sub_section($sections[$sec_len - 1]);

					// change allowed sub-tags
					$sub_tags[$sub_count + 1] = array(
						0 => $bbctags[$tag_name]['allowed_content'],
						1 => $bbctags[$tag_name]['type']
					);
					$sub_count++;

					// add it to the open tags
					$open_tags[$open_count++] = $tag;
				}
			}
			else
			{
				// check the content-allowed-status
				if($open_count == 0 || $open_tags[$open_count - 1] != $tag_name)
					$allowed = $this->_check_allowed_content($sub_tags,$sub_count,$bbctags[$tag_name]['type']);
				else
				{
					$allowed = -1;

					// remove the tag from the open tags
					unset($open_tags[$open_count - 1]);
					$open_count--;

					// restore last allowed sub-tags
					unset($sub_tags[$sub_count--]);
				}

				// treat as plain-text? so add the content to the last section
				if($allowed == 1)
				{
					// ignore tag?
					if($parent_tag != null && isset($parent_tag['ignore_unknown_tags']) &&
						 $parent_tag['ignore_unknown_tags'])
						continue;

					$this->_add_to_last_section($sections,$next_id,$sec_len,$tags[1][$key][0].$tags[2][$key][0]);
				}
				else
				{
					// add the previous text (if we don't want to skip unknown tags
					if($tags[1][$key][0] != '' && ($parent_tag == null ||
																			!isset($parent_tag['ignore_unknown_tags']) ||
						 													!$parent_tag['ignore_unknown_tags']))
					{
						$sections[$sec_len - 1]->add_sub_section(
							new BS_BBCode_Section($next_id++,'','',$tags[1][$key][0])
						);
					}

					// remove the last section
					$sec_len--;
					unset($sections[$sec_len]);
				}
			}
		}

		return $sections[0];
	}

	/**
	 * adds the given text to the last section
	 * will either create a new one or append the text to an existing section
	 *
	 * @param array $sections the sections
	 * @param int $next_id the next id
	 * @param int $sec_len the number of sections
	 * @param string $text the text to add
	 */
	private function _add_to_last_section(&$sections,&$next_id,$sec_len,$text)
	{
		$last_sec = $sections[$sec_len - 1];
		$last_sec_sub = $last_sec->get_last_subsection();
		if($last_sec_sub !== null && $last_sec_sub->get_sub_section_count() == 0)
			$last_sec_sub->append($text);
		else
			$sections[$sec_len - 1]->add_sub_section(new BS_BBCode_Section($next_id++,'','',$text));
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>