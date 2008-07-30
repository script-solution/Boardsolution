<?php
/**
 * Contains utilities for the front-search
 *
 * @version			$Id$
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
			$ignore = $functions->get_search_ignore_words();
		
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
				if(FWS_String::strlen($sec) >= BS_SEARCH_MIN_KEYWORD_LEN && !isset($ignore[$sec]))
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
	 * @param array words to ignore
	 * @param string $string the string to parse
	 */
	private static function _add_words(&$sections,$ignore,$string)
	{
		$split = explode(' ',$string);
		$len = count($split);
		for($i = 0;$i < $len;$i++)
		{
			if(($trimmed = trim($split[$i])) != '' &&
					FWS_String::strlen($trimmed) >= BS_SEARCH_MIN_KEYWORD_LEN && !isset($ignore[$trimmed]))
				$sections[] = $trimmed;
		}
	}
}
?>