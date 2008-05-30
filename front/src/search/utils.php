<?php
/**
 * Contains utilities for the front-search
 *
 * @version			$Id: utils.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Provides some static methods for the search
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Search_Utils extends PLIB_UtilBase
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
			default:
				return ($type == 'posts' ? 'p.post_time ' : 't.lastpost_time ').$ad;
		}
	}
	
	/**
	 * Checks wether the given keyword is valid. If not a message will be added to
	 * <var>$this->msgs</var> and false will be returned.
	 *
	 * @param string $keyword the entered keyword
	 * @return boolean true if ok, false otherwise
	 */
	public static function is_valid_keyword($keyword)
	{
		$keyword_len = PLIB_String::strlen($keyword);
		if($keyword_len == 0)
		{
			$locale = PLIB_Object::get_prop('locale');
			PLIB_Object::get_prop('msgs')->add_error(
				sprintf($this->locale->lang('search_missing_keyword'),BS_SEARCH_MIN_KEYWORD_LEN)
			);
			return false;
		}

		if($keyword_len > 255)
		{
			$locale = PLIB_Object::get_prop('locale');
			PLIB_Object::get_prop('msgs')->add_error($locale->lang('keyword_max_length'));
			return false;
		}
		
		return true;
	}
	
	/**
	 * Expects that the given string comes from e.g. <var>$this->input->get_var()</var> and
	 * escapes / prepares it to be passed to {@link extract_keywords}.
	 * That means the method expects that addslashes() and htmlspecialchars() have been done.
	 * 
	 * @param string $input the input-string
	 * @return string the escaped / prepared string for {@link extract_keywords}
	 */
	public static function escape($input)
	{
		$input = stripslashes($input);
		$input = PLIB_StringHelper::htmlspecialchars_back($input);
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
		$sections = array();
		while(($start = PLIB_String::strpos($input,'"')) !== false)
		{
			if(($inter_section = trim(PLIB_String::substr($input,0,$start))) != '')
				self::_add_words($sections,$inter_section);

			$ende = PLIB_String::strpos(substr($input,$start + 1),'"');
			if($ende !== false)
			{
				if($start > 0)
					$sec = PLIB_String::substr($input,$start - 1,1).PLIB_String::substr($input,$start + 1,$ende);
				else
					$sec = PLIB_String::substr($input,$start + 1,$ende);

				$sec = trim($sec);
				if(PLIB_String::strlen($sec) >= BS_SEARCH_MIN_KEYWORD_LEN)
					$sections[] = $sec;

				$input = PLIB_String::substr($input,$start + 2 + $ende);
			}
			else
			{
				self::_add_words($sections,trim($input));
				$input = '';
				break;
			}
		}

		if($input != '')
			self::_add_words($sections,trim($input));

		return $sections;
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
	 * splits the given string by ' ' and adds all parts to the existing sections-array
	 *
	 * @param array $sections the sections-array
	 * @param string $string the string to parse
	 */
	private static function _add_words(&$sections,$string)
	{
		$split = explode(' ',$string);
		$len = count($split);
		for($i = 0;$i < $len;$i++)
		{
			if(($trimmed = trim($split[$i])) != '' &&
					PLIB_String::strlen($trimmed) >= BS_SEARCH_MIN_KEYWORD_LEN)
				$sections[] = $trimmed;
		}
	}
}
?>