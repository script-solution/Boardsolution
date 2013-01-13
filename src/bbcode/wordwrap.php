<?php
/**
 * Contains the wordwrap-class
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
 * The wordwrap for the bbcode-package
 *
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_WordWrap extends FWS_UtilBase
{
	/**
	 * A special wordwrap function which takes care of HTML-tags and &...;
	 *
	 * @param string $input the string to wrap
	 * @param int $length the maximum length of one line
	 * @return string the wrapped text
	 */
	public static function word_wrap_special($input,$length)
	{
		if($length == 0)
			return $input;

		$split = explode("\n",$input);
		$lines = count($split);
		for($i = 0;$i < $lines;$i++)
		{
			$line = $split[$i];
			$complete = '';
			$pos = 0;
			while(FWS_String::strlen($line) > $length)
			{
				$pos = self::_get_break_pos_in_line($line,$length);
				$complete .= FWS_String::substr($line,0,$pos[0]);
				$line = FWS_String::substr($line,$pos[1]);
				if($line != '')
					$complete .= "\n";
			}

			$complete .= $line;
			$split[$i] = $complete;
		}

		$result = implode("\n",$split);
		return $result;
	}

	/**
	 * Determines the position in the given line where to break. The given length is the maximum
	 * length of a line. HTML-tags and HTML-special-chars will be ignored. But the tags li,ol,ul,div
	 * and CODE will be treaten as line-wraps (the browser performs a wrap!)
	 * 
	 * @param string $line the line to wrap
	 * @param int $length the maximum length of a line
	 * @return array an array of the form:
	 * 	<code>
	 * 		array(<breakPos>,<continuePos>)
	 * 	</code>
	 * You have to add all chars until &lt;breakPos&gt; to a line and continue at the continue-pos
	 */
	private static function _get_break_pos_in_line($line,$length)
	{
		$len = FWS_String::strlen($line);
		$break_pos = 0;
		$continue_pos = 0;
		$c = 0;
		for($a = 0;$a < $len;$a++)
		{
			$char = FWS_String::substr($line,$a,1);

			// skip tags
			if($char == '<')
			{
				$end = FWS_String::substr($line,$a);
				$end_len = FWS_String::strlen($end);

				// we can't simply search for the next ">" because there may be comments in the tag
				// for example: <img src="<!--EMP-->images/..." ... />
				$open = 0;
				$tag_end_pos = false;
				for($p = 1;$p < $end_len;$p++)
				{
					$echar = FWS_String::substr($end,$p,1);
					if($echar == '<')
						$open++;
					else if($echar == '>')
					{
						if($open == 0)
						{
							$tag_end_pos = $p;
							break;
						}
						
						$open--;
					}
				}

				// if there is no tag-end, we treat the "<" as a normal char
				if($tag_end_pos === false)
				{
					$c++;
					continue;
				}

				// check if it is a block-tag or a code-tag (code-tags are represented as <CODEx>
				// at this point of time)
				$tag = FWS_String::substr($end,1,$tag_end_pos - 1);
				if(in_array($tag,array('/li','/ol','/ul','/div')) || preg_match('/^CODE\d+$/',$tag))
				{
					// it is a block-tag therefore this will result in a wordwrap by the browser
					// so we reset the counter
					$c = 0;
				}

				$a += $tag_end_pos;
				
				// are we finished now? so we have to set the break-position
				if($a == $len - 1 && $c <= $length)
				{
					$break_pos = $a + 1;
					$continue_pos = $a + 1;
				}
				continue;
			}

			// skip &...;
			if($char == '&')
			{
				$end = FWS_String::substr($line,$a);
				$tag_end_pos = FWS_String::strpos($end,';');
				$a += $tag_end_pos;
				$c++;
				continue;
			}

			$c++;

			// a space is a potential break position
			if(FWS_String::is_whitespace($char))
			{
				if($c <= $length)
					$break_pos = $a;
				else
					break;
			}
			// is it the last char and does this fit into the line?
			else if($a == $len - 1 && $c <= $length + 1)
			{
				$break_pos = $a + 1;
				$continue_pos = $a + 1;
			}
			// have we already reached the maximum number of chars?
			else if($c >= $length + 1 && $break_pos == 0)
			{
				$break_pos = $a;
				$continue_pos = $a;
				break;
			}
		}

		// no end-position found? so we can add the complete line
		if($break_pos == 0)
			$break_pos = $len;

		// no continue-position yet? the default-case is to skip the whitespace after the break-pos
		if($continue_pos == 0)
			$continue_pos = $break_pos + 1;

		return array($break_pos,$continue_pos);
	}
}
?>