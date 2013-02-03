<?php
/**
 * Contains utilities for the front-search
 * 
 * @package			Boardsolution
 * @subpackage	front.src.search
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
 * Provides some static methods for the search
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Search_Utils extends FWS_UtilBase
{
	/**
	 * Returns the SQL-order for posts or topics.
	 *
	 * @param string $order the order-value
	 * @param string $ad the ad-value
	 * @param string $type the type: posts or topics
	 * @return string the SQL-order statement (without 'ORDER BY')
	 */
	public static function get_sql_order($order,$ad,$type = 'posts')
	{
		switch($order)
		{
			case 'topic_name':
				return 't.name '.$ad;
			case 'topic_type':
				return 't.type '.$ad;
			case 'replies':
				return 't.posts '.$ad;
			case 'views':
				return 't.views '.$ad;
			case 'relevance':
				return 'relevance '.$ad;
			default:
				return ($type == 'posts' ? 'p.post_time ' : 't.lastpost_time ').$ad;
		}
	}
	
	/**
	 * Checks wether the given keyword is valid. If not a message will be added to
	 * <var>FWS_Props::get()->msgs()</var> and false will be returned.
	 *
	 * @param string $keyword the entered keyword
	 * @return boolean true if ok, false otherwise
	 */
	public static function is_valid_keyword($keyword)
	{
		$locale = FWS_Props::get()->locale();
		$msgs = FWS_Props::get()->msgs();
		
		$keyword_len = FWS_String::strlen($keyword);
		if($keyword_len == 0)
		{
			$msgs->add_error(
				sprintf($locale->lang('search_missing_keyword'),BS_SEARCH_MIN_KEYWORD_LEN)
			);
			return false;
		}

		if($keyword_len > 255)
		{
			$msgs->add_error($locale->lang('keyword_max_length'));
			return false;
		}
		
		return true;
	}
	
	/**
	 * Expects that the given string comes from e.g. <var>FWS_Props::get()->input()->get_var()</var>
	 * and escapes / prepares it to be passed to {@link extract_keywords}.
	 * That means the method expects that addslashes() and htmlspecialchars() have been done.
	 * 
	 * @param string $input the input-string
	 * @return string the escaped / prepared string for {@link extract_keywords}
	 */
	public static function escape($input)
	{
		$input = stripslashes($input);
		$input = FWS_StringHelper::htmlspecialchars_back($input);
		return str_replace('\'','\\\'',$input);
	}
	
	/**
	 * Finds and splits the entered keywords into sections.
	 * For example:
	 * <code>test "foo bar" -> array("test","foo bar");</code>
	 *
	 * @param string $input the entered keywords
	 * @return array an numeric array with the sections in the given input-string
	 */
	public static function extract_keywords($input)
	{
		$functions = FWS_Props::get()->functions();
		
		static $ignore = null;
		if($ignore === null)
			$ignore = self::get_ignore_words();
		
		$sections = array();
		while(($start = FWS_String::strpos($input,'"')) !== false)
		{
			if(($inter_section = trim(FWS_String::substr($input,0,$start))) != '')
				self::_add_words($sections,$ignore,$inter_section);

			$ende = FWS_String::strpos(substr($input,$start + 1),'"');
			if($ende !== false)
			{
				if($start > 0)
					$sec = FWS_String::substr($input,$start - 1,1).FWS_String::substr($input,$start + 1,$ende);
				else
					$sec = FWS_String::substr($input,$start + 1,$ende);

				$sec = trim($sec);
				if(FWS_String::strlen($sec) >= BS_SEARCH_MIN_KEYWORD_LEN &&
						!isset($ignore[strtolower($sec)]))
					$sections[] = $sec;

				$input = FWS_String::substr($input,$start + 2 + $ende);
			}
			else
			{
				self::_add_words($sections,$ignore,trim($input));
				$input = '';
				break;
			}
		}

		if($input != '')
			self::_add_words($sections,$ignore,trim($input));

		return $sections;
	}
	
	/**
	 * returns the search-ignore words
	 *
	 * @return array an associative array with all words to ignore:
	 * 	<code>
	 * 		array(<word> => true)
	 * 	</code>
	 */
	public static function get_ignore_words()
	{
		$functions = FWS_Props::get()->functions();

		// we use the default-forum-language, because we guess that most of the posts will be in
		// this language
		$lang = $functions->get_def_lang_folder();
		$file = FWS_Path::server_app().'language/'.$lang.'/search_words.txt';
	
		if(!file_exists($file))
			return array();
	
		$words = array();
		$lines = file($file);
		foreach($lines as $l)
		{
			$line = strtolower(trim($l));
			if($line != '')
				$words[$line] = true;
		}
	
		return $words;
	}
	
	/**
	 * Determines the search-keywords and returns them
	 *
	 * @return array an numeric array with the keywords
	 */
	public static function get_keywords()
	{
		$input = FWS_Props::get()->input();

		$hl = $input->get_var(BS_URL_HL,'get',FWS_Input::STRING);
		if($hl !== null)
		{
			// undo the stuff of the input-class
			$hl = stripslashes(str_replace('&quot;','"',$hl));
			// backslashes are not supported here
			$hl = str_replace('\\','',$hl);
			
			$temp = explode('"',$hl);
			$keywords = array();
			for($i = 0;$i < count($temp);$i++)
			{
				$temp[$i] = trim($temp[$i]);
				if($temp[$i] != '')
					$keywords[] = $temp[$i];
			}
			return $keywords;
		}
		
		return null;
	}
	
	/**
	 * Builds a search-condition for the where-clause for the given fields from the given keywords.
	 * The char <var>*</var> will be considered as wildcard. Everything will be compared in lowercase.
	 *
	 * @param array $keywords a numeric array with all keywords
	 * @param array $fields a numeric array with all field-names that should be used for searching
	 * @param string $link the link of the keywords: OR or AND
	 * @return string the search-condition
	 */
	public static function build_search_cond($keywords,$fields,$link = 'AND')
	{
		$sql = '';
		if(count($keywords) > 0)
		{
			$sql .= '(';

			$i = 0;
			foreach($keywords as $string)
			{
				$string = str_replace('*','%',trim($string));
				if($i++ > 0)
					$sql .= ' '.$link.' ';
				
				$sql .= '(';
				$j = 0;
				foreach($fields as $fname)
				{
					if($j++ > 0)
						$sql .= ' OR ';
					$sql .= 'LOWER('.$fname.') LIKE \'%'.$string.'%\'';
				}
				$sql .= ')';
			}
			$sql .= ')';
		}
		
		return $sql;
	}
	
	/**
	 * Get the needed value of the keyword mode
	 * 
	 * @return sring the keyword mode 'AND' or 'OR'
	 */
	public static function get_keyword_mode()
	{
		$input = FWS_Props::get()->input();
	
		$keyword_mode = $input->get_var('keyword_mode','post',FWS_Input::STRING);
	
		if($keyword_mode === null)
			$keyword_mode = $input->get_var(BS_URL_SEARCH_MODE,'get',FWS_Input::STRING);
	
		$keyword_mode = (FWS_String::strtolower($keyword_mode) == 'and') ? 'AND' : 'OR';
	
		return $keyword_mode;
	}

	/**
	 * splits the given string by ' ' and adds all parts to the existing sections-array
	 *
	 * @param array $sections the sections-array
	 * @param array $ignore words to ignore
	 * @param string $string the string to parse
	 */
	private static function _add_words(&$sections,$ignore,$string)
	{
		$split = explode(' ',$string);
		$len = count($split);
		for($i = 0;$i < $len;$i++)
		{
			if(($trimmed = trim($split[$i])) != '' &&
				FWS_String::strlen($trimmed) >= BS_SEARCH_MIN_KEYWORD_LEN &&
				!isset($ignore[strtolower($trimmed)]))
			{
				// we delete all &, < and > to prevent problems
				$sections[] = htmlspecialchars(str_replace(array('&','<','>'),'',$trimmed),ENT_QUOTES);
			}
		}
	}
}
?>