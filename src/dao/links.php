<?php
/**
 * Contains the links-dao-class
 * 
 * @package			Boardsolution
 * @subpackage	src.dao
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
 * The DAO-class for the links-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Links extends FWS_Singleton
{
	/**
	 * @return BS_DAO_Links the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns the total number of links. Activated, not activated or all.
	 *
	 * @param int $active wether they are activated: -1 = indifferent, 0 = no, 1 = yes
	 * @return int the link-count
	 */
	public function get_count($active = -1)
	{
		return $this->get_count_by_keyword('',$active);
	}
	
	/**
	 * Returns the total number of links. Activated, not activated or all.
	 *
	 * @param string $keyword the keyword
	 * @param int $active wether they are activated: -1 = indifferent, 0 = no, 1 = yes
	 * @return int the link-count
	 */
	public function get_count_by_keyword($keyword,$active = -1)
	{
		$db = FWS_Props::get()->db();

		if(!in_array($active,array(-1,0,1)))
			FWS_Helper::def_error('inarray','active',array(-1,0,1),$active);
		
		$where = '';
		if($keyword)
			$where .= 'LEFT JOIN '.BS_TB_USER.' u ON l.user_id = u.`'.BS_EXPORT_USER_ID.'`';
		$where .= ' WHERE 1';
		if($active >= 0)
			$where .= ' AND active = '.$active;
		if($keyword)
		{
			$where .= ' AND (u.`'.BS_EXPORT_USER_NAME.'` LIKE "%'.$keyword.'%"';
			$where .= ' OR category LIKE "%'.$keyword.'%" OR link_url LIKE "%'.$keyword.'%"';
			$where .= ' OR link_desc_posted LIKE "%'.$keyword.'%")';
		}
		return $db->get_row_count(BS_TB_LINKS.' l','l.id',$where);
	}
	
	/**
	 * Checks wether the given URL already exists
	 *
	 * @param string $url the URL to check
	 * @return boolean true if it exists
	 */
	public function url_exists($url)
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(BS_TB_LINKS,'id',' WHERE link_url = "'.$url.'"') > 0;
	}
	
	/**
	 * @return array all existing categories
	 */
	public function get_categories()
	{
		$db = FWS_Props::get()->db();

		$rows = $db->get_rows(
			'SELECT category FROM '.BS_TB_LINKS.'
			 WHERE active = 1
			 GROUP BY category
			 ORDER BY category ASC'
		);
		$categories = array();
		foreach($rows as $row)
			$categories[] = $row['category'];
		return $categories;
	}
	
	/**
	 * Returns the data of the link with given id
	 *
	 * @param int $id the link-id
	 * @return array|bool the data or false if not found
	 */
	public function get_by_id($id)
	{
		$rows = $this->get_by_ids(array($id));
		if(count($rows) == 0)
			return false;
		
		return $rows[0];
	}
	
	/**
	 * Returns all links with the given ids
	 *
	 * @param array $ids the ids
	 * @return array the found links
	 */
	public function get_by_ids($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_LINKS.' WHERE id IN ('.implode(',',$ids).')'
		);
	}
	
	/**
	 * Returns all links that are activated, not activated or both. You can also specify the sort
	 * and range.
	 *
	 * @param int $active wether they are activated: -1 = indifferent, 0 = no, 1 = yes
	 * @param string $sort the sort-column
	 * @param string $order the order: ASC or DESC
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array the found links
	 */
	public function get_list($active = -1,$sort = 'l.id',$order = 'ASC',$start = 0,$count = 0)
	{
		return $this->get_list_by_keyword('',$active,$sort,$order,$start,$count);
	}
	
	/**
	 * Returns all links that match the keyword and are activated, not activated or both.
	 * You can also specify the sort and range.
	 *
	 * @param string $keyword the keyword
	 * @param int $active wether they are activated: -1 = indifferent, 0 = no, 1 = yes
	 * @param string $sort the sort-column
	 * @param string $order the order: ASC or DESC
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array the found links
	 */
	public function get_list_by_keyword($keyword,$active = -1,$sort = 'l.id',$order = 'ASC',
		$start = 0,$count = 0)
	{
		$db = FWS_Props::get()->db();

		if(!in_array($active,array(-1,0,1)))
			FWS_Helper::def_error('inarray','active',array(-1,0,1),$active);
		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		$where = 'WHERE 1';
		if($active >= 0)
			$where .= ' AND l.active = '.$active;
		if($keyword)
		{
			$where .= ' AND (user_name LIKE "%'.$keyword.'%" OR category LIKE "%'.$keyword.'%"';
			$where .= ' OR link_url LIKE "%'.$keyword.'%" OR link_desc_posted LIKE "%'.$keyword.'%")';
		}
		return $db->get_rows(
			'SELECT l.*,u.`'.BS_EXPORT_USER_NAME.'` user_name,p.user_group
			 FROM '.BS_TB_LINKS.' l
			 LEFT JOIN '.BS_TB_USER.' u ON l.user_id = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' p ON l.user_id = p.id
			 '.$where.'
			 ORDER BY '.$sort.' '.$order.'
			 '.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * @return array all link-ids that have an invalid text
	 */
	public function get_invalid_link_ids()
	{
		$db = FWS_Props::get()->db();

		return $db->get_rows(
			'SELECT id FROM '.BS_TB_LINKS.' WHERE link_desc = "" AND link_desc_posted != ""'
		);
	}
	
	/**
	 * Creates a link with given fields
	 *
	 * @param array $fields the fields to set
	 * @return int the used id
	 */
	public function create($fields)
	{
		$db = FWS_Props::get()->db();

		return $db->insert(BS_TB_LINKS,$fields);
	}
	
	/**
	 * Sets wether the links with given ids are active or not
	 *
	 * @param array $ids the link-ids
	 * @param int $active wether they are active or not
	 * @return int the number of affected rows
	 */
	public function set_active($ids,$active)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		if($active !== 1 && $active !== 0)
			FWS_Helper::error('Active should be 0 or 1');
		
		return $db->update(BS_TB_LINKS,'WHERE id IN ('.implode(',',$ids).')',array(
			'active' => $active
		));
	}
	
	/**
	 * Increases the clicks of the given link by one
	 *
	 * @param int $id the link-id
	 * @return int the number of affected rows
	 */
	public function increase_clicks($id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->update(BS_TB_LINKS,'WHERE id = '.$id,array(
			'clicks' => array('clicks + 1')
		));
	}
	
	/**
	 * Votes for the given link.
	 *
	 * @param int $id the link-id
	 * @param int $vote the number of points to vote (1..6)
	 * @return int the number of affected rows
	 */
	public function vote($id,$vote)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		if(!FWS_Helper::is_integer($vote) || $vote < 1 || $vote > 6)
			FWS_Helper::def_error('numbetween','vote',1,6,$vote);
		
		return $db->update(BS_TB_LINKS,'WHERE id = '.$id,array(
			'votes' => array('votes + 1'),
			'vote_points' => array('vote_points + '.$vote)
		));
	}
	
	/**
	 * Updates the text of the link with given id
	 *
	 * @param int $id the link-id
	 * @param string $text the text
	 * @param string $text_posted the posted text
	 * @return int the number of affected rows
	 */
	public function update_text($id,$text,$text_posted)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->update(BS_TB_LINKS,'WHERE id = '.$id,array(
			'link_desc' => $text,
			'link_desc_posted' => $text_posted
		));
	}
	
	/**
	 * Updates the link with given id
	 *
	 * @param int $id the link-id
	 * @param array $fields the fields to set
	 * @return int the number of affected rows
	 */
	public function update($id,$fields)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->update(BS_TB_LINKS,'WHERE id = '.$id,$fields);
	}
	
	/**
	 * Deletes the links with given ids
	 *
	 * @param array $ids the link-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		return $this->delete_by('id',$ids);
	}
	
	/**
	 * Deletes the links that have been added by the given users
	 *
	 * @param array $ids the user-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_users($ids)
	{
		return $this->delete_by('user_id',$ids);
	}
	
	/**
	 * Deletes the links by the given field
	 *
	 * @param string $field the field-name
	 * @param array $ids the ids
	 * @return int the number of affected rows
	 */
	protected function delete_by($field,$ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_LINKS.' WHERE '.$field.' IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>