<?php
/**
 * Contains the simple theme-editor-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The simple theme-editor
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_Themes_Editor_Simple extends BS_ACP_Module_Themes_Editor_Base
{
	/**
	 * The CSS-object which contains the classes and attributes
	 *
	 * @var PLIB_CSS_SimpleParser
	 */
	private $_css;
	
	/**
	 * All attributes:
	 * <code>
	 * 	array(<attribute> => <name>,...)
	 * </code>
	 *
	 * @var array
	 */
	private $_attributes = array(
		'font-style' => 'attr_font-style',
		'font-size' => 'attr_font-size',
		'font-weight' => 'attr_font-weight',
		'font-family' => 'attr_font-family',
		'color' => 'attr_color',
		'background-color' => 'attr_background-color',
		'background-image' => 'attr_background-image',
		'background-repeat' => 'attr_background-repeat',
		'background-position' => 'attr_background-position',
		'background-attachment' => 'attr_background-attachment',
		'text-decoration' => 'attr_text-decoration',
		'border-left' => 'attr_border-left',
		'border-right' => 'attr_border-right',
		'border-top' => 'attr_border-top',
		'border-bottom' => 'attr_border-bottom',
		'border-style' => 'attr_border-style',
		'border-color' => 'attr_border-color',
		'border-left-width' => 'attr_border-left-width',
		'border-right-width' => 'attr_border-right-width',
		'border-top-width' => 'attr_border-top-width',
		'border-bottom-width' => 'attr_border-bottom-width',
		'margin' => 'attr_margin',
		'margin-left' => 'attr_margin-left',
		'margin-right' => 'attr_margin-right',
		'margin-top' => 'attr_margin-top',
		'margin-bottom' => 'attr_margin-bottom',
		'padding' => 'attr_padding',
		'padding-left' => 'attr_padding-left',
		'padding-right' => 'attr_padding-right',
		'padding-top' => 'attr_padding-top',
		'padding-bottom' => 'attr_padding-bottom',
		'width' => 'attr_width',
		'height' => 'attr_height',
		'cursor' => 'attr_cursor'
	);
	
	/**
	 * The categories:
	 * <code>
	 * 	array(
	 * 		<category> => array(
	 * 			<class> => <name>,
	 * 			...
	 * 		)
	 * 		,...
	 * 	)
	 * </code>
	 *
	 * @var array
	 */
	private $_categories = array(
		'group_main' => array(
			'class_body' => 'bs_body',
			'class_body' => 'bs_body',
			'class_main' => 'bs_main',
			'class_main_no_pad' => 'bs_main_no_pad',
			'class_topic' => 'bs_topic',
			'class_coldesc' => 'bs_coldesc',
			'class_desc' => 'bs_desc',
			'class_categories' => 'bs_categories',
			'class_forums' => 'bs_forums',
			'class_forums_small' => 'bs_forums_small',
			'class_top_menu' => 'bs_top_menu',
			'class_head_line' => 'bs_headline',
		),
		'group_posts' => array(
			'class_post_seperator' => 'bs_post_separator',
			'class_posts_bar_1' => 'bs_posts_bar_1',
			'class_posts_bar_2' => 'bs_posts_bar_2',
			'class_posts_left_1' => 'bs_posts_left_1',
			'class_posts_left_2' => 'bs_posts_left_2',
			'class_posts_main_1' => 'bs_posts_main_1',
			'class_posts_main_2' => 'bs_posts_main_2'
		),
		'group_border' => array(
			'class_table_top_left' => 'bs_tbl_top_left',
			'class_table_top' => 'bs_tbl_top',
			'class_table_top_right' => 'bs_tbl_top_right',
			'class_table_left' => 'bs_tbl_left',
			'class_table_right' => 'bs_tbl_right',
			'class_table_bottom_left' => 'bs_tbl_bottom_left',
			'class_table_bottom' => 'bs_tbl_bottom',
			'class_table_bottom_right' => 'bs_tbl_bottom_right',
		),
		'group_calendar' => array(
			'class_calendar' => 'bs_calendar',
			'class_calendar_today' => 'bs_calendar_today',
			'class_calendar_empty' => 'bs_calendar_empty',
			'class_calendar_empty_today' => 'bs_calendar_empty_today',
			'class_calendar_border' => 'bs_calendar_border',
			'class_calendar_border_today' => 'bs_calendar_border_today',
		),
		'group_other' => array(
			'class_buttons' => 'bs_button',
			'class_buttons_big' => 'bs_button_big',
			'class_pm_unread' => 'bs_unread',
			'class_quote_section' => 'bs_quote_section',
			'class_quote_section_top' => 'bs_quote_section_top',
			'class_quote_section_main' => 'bs_quote_section_main',
			'class_explain' => 'bs_bbcode_notice',
			'class_search_keywords' => 'bs_search_keywords',
			'class_form' => 'form',
			'class_forms' => 'input,select,textarea',
			'class_checkbox' => 'bs_checkbox',
			'class_list' => 'ul',
			'class_list_item' => 'li',
			'class_label' => 'label',
		)
	);

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_css = new PLIB_CSS_SimpleParser($this->_theme.'/style.css',$this->_theme.'/style.css');
	
		// add unknown classes to its own group
		$used_classes = array();
		foreach($this->_categories as $items)
		{
			foreach($items as $class)
				$used_classes[] = $class;
		}
		
		$classes = $this->_css->get_classes($used_classes);
		if(count($classes) > 0)
		{
			$this->_categories['group_unknown'] = array();
			foreach($classes as $name)
				$this->_categories['group_unknown'][$name] = $name;
		}
	}
	
	public function get_template()
	{
		return 'themes_editor_simple.htm';
	}

	/**
	 * Displays the "editor"
	 */
	public function display()
	{
		$class = $this->input->get_var('class','get',PLIB_Input::STRING);
		$theme = $this->input->get_var('theme','get',PLIB_Input::STRING);

		$del = $this->input->get_var('del','post');
		if($this->input->isset_var('delete','post') && $del != null)
		{
			$text = '';
			$ids = '';
			$num = count($del);
			for($i = 0;$i < $num;$i++)
			{
				$ids .= $del[$i].',';
				$split = explode('|',$del[$i]);
				$text .= $this->locale->lang('theattribute').' "'.$split[1].'" ';
				$text .= $this->locale->lang('of').' "';
				$text .= $this->_get_attribute_name(str_replace('.'.$class,'',$split[0])).'"';
				if($i < $num - 2)
					$text .= ', ';
				else if($i == $num - 2)
					$text .= ' '.$this->locale->lang('and').' ';
			}

			$yes_url = $this->url->get_acpmod_url(
				0,'&amp;action=editor&amp;theme='.$theme
					.'&amp;class='.$class.'&amp;at='.BS_ACP_ACTION_THEME_EDITOR_SIMPLE_DELETE.'&ids='.$ids
			);
			$no_url = $this->url->get_acpmod_url(
				0,'&amp;action=editor&amp;theme='.$theme.'&amp;class='.$class
			);
			
			$this->functions->add_delete_message(
				sprintf($this->locale->lang('delete_message'),$text),$yes_url,$no_url
			);
		}

		$classes = $this->_css->get_classes();
		if($class == null)
			$class = $this->input->set_var('class','get',current($classes));

		$base_url = $this->url->get_acpmod_url(
			0,'&amp;action=editor&amp;theme='.$theme.'&amp;mode=simple'
		);

		$this->tpl->set_template('themes_editor_simple.htm');
		$this->tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_THEME_EDITOR_SIMPLE_SAVE,
			'target_url' => $base_url.'&amp;class='.$class
		));

		$cats = array();
		foreach($this->_categories as $name => $items)
		{
			$cat = array(
				'name' => $this->locale->lang($name),
				'items' => array(),
			);

			foreach($items as $iname => $iclass)
			{
				if($class == $iclass)
					$menu_item = '- '.$this->locale->lang($iname,false);
				else
				{
					$menu_item = '- <a href="'.$base_url.'&amp;class='.$iclass.'">';
					$menu_item .= $this->locale->lang($iname,false).'</a>';
				}

				$cat['items'][] = array(
					'menu_item' => $menu_item
				);
			}
			
			$cats[] = $cat;
		}
		
		$this->tpl->add_array('cats',$cats);

		if($class != null)
		{
			$this->tpl->add_variables(array(
				'explanation_picture' => $this->_get_picture_exlain($class)
			));

			$all_attributes = array();
			foreach($this->_attributes as $k => $v)
				$all_attributes[$k] = $this->locale->lang($v);
			
			$form = new BS_HTML_Formular(false,false);
			$tplgroups = array();
			$groups = $this->_css->get_group_classes($class);
			if(is_array($groups))
			{
				foreach($groups as $class_name => $attributes)
				{
					if(is_array($attributes))
					{
						$real_class_name = str_replace(".".$class,"",$class_name);

						$group = array(
							'tag_name' => $this->_get_tag_name($real_class_name),
							'add_attribute_combo' => $form->get_combobox(
								'attribute['.$class_name.']',$all_attributes,''
							),
							'class_name' => $class_name,
							'attributes' => array()
						);

						foreach($attributes as $name => $value)
						{
							$cname = str_replace(".","%",$class_name);
							$group['attributes'][] = array(
								'id' => $class_name.'|'.$name,
								'name' => $this->_get_attribute_name($name),
								'form_element' => $this->_get_form_element($cname,$name,$value)
							);
						}
						
						$tplgroups[] = $group;
					}
				}
			}
		}
		
		$this->tpl->add_array('groups',$tplgroups);
		$this->tpl->restore_template();
	}
	
	/**
	 * Builds the name of the given attribute
	 *
	 * @param string $attr the attribute
	 * @return string the name of it
	 */
	private function _get_attribute_name($attr)
	{
		if($this->locale->contains_lang('attr_'.$attr))
			return $this->locale->lang('attr_'.$attr);
		return $attr;
	}

	/**
	 * grabs the tag-name of the given attribute from the language-variable
	 *
	 * @param string $tag the name of the tag
	 * @return string the tag-name
	 */
	private function _get_tag_name($tag)
	{
		switch($tag)
		{
			case '':
				return $this->locale->lang('tag_all_tags');

			case 'a':
			case 'td':
			case 'table':
			case 'body':
			case 'div':
			case 'span':
			case 'input':
			case 'select':
			case 'textarea':
			case 'ul':
			case 'li':
			case 'label':
				return $this->locale->lang('tag_'.$tag);

			case 'a:hover':
				return $this->locale->lang('tag_ahover');

			case 'td:hover':
				return $this->locale->lang('tag_tdhover');

			case 'div_small':
				return $this->locale->lang('tag_small');

			case 'input,select,textarea':
				return $this->locale->lang('tag_form_elements');

			default:
				return $tag;
		}
	}

	/**
	 * builds the explanation-table
	 *
	 * @param string $title_top the top-text
	 * @param string $image_name the name of the image
	 * @param string $title_the bottom-text
	 * @return string the table
	 */
	private function _get_explanation_table($title_top,$image_name,$title_bottom)
	{
		$res = '<table align="left" cellpadding="0" cellspacing="0">'."\n";
		if($title_top[0] != '' || $title_top[1] != '')
		{
			$res .= '	<tr>'."\n";
			$res .= '		<td class="a_main">'.($title_top[0] == '' ? '&nbsp;' : $title_top[0]).'</td>'."\n";
			$res .= '		<td class="a_main" align="right">';
			$res .= ($title_top[1] == '' ? '&nbsp;' : $title_top[1]).'</td>'."\n";
			$res .= '	</tr>'."\n";
		}
		$res .= '	<tr>'."\n";
		$res .= '		<td class="a_main" colspan="2"><img border="1" src="acp/images/design/';
		$res .= $image_name.'" alt="" />';
		$res .= '</td>'."\n";
		$res .= '	</tr>'."\n";
		if($title_bottom[0] != '' || $title_bottom[1] != '')
		{
			$res .= '	<tr>'."\n";
			$res .= '		<td class="a_main">';
			$res .= ($title_bottom[0] == '' ? '&nbsp;' : $title_bottom[0]).'</td>'."\n";
			$res .= '		<td class="a_main" align="right">';
			$res .= ($title_bottom[1] == '' ? '&nbsp;' : $title_bottom[1]);
			$res .= '</td>'."\n";
			$res .= '	</tr>'."\n";
		}
		$res .= '</table>'."\n";
		return $res;
	}

	/**
	 * builds the explanation divider
	 *
	 * @param int $width the width of the divider
	 * @return string the divider
	 */
	private function _get_explanation_divider($width = 10)
	{
		$res = '<table align="left" width="'.$width.'" cellpadding="0" cellspacing="0">'."\n";
		$res .= '	<tr>'."\n";
		$res .= '		<td class="a_main">&nbsp;</td>'."\n";
		$res .= '	</tr>'."\n";
		$res .= '</table>'."\n";
		return $res;
	}

	/**
	 * builds the picture-explanation for the given class-name
	 *
	 * @param string $class the name of the class
	 * @return string the html-code
	 */
	private function _get_picture_exlain($class)
	{
		switch($class)
		{
			case 'bs_body':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_a').'", "'.$this->locale->lang('tag_ahover').'"',''),
					'body.gif',
					array('"'.$this->locale->lang('tag_body').'"',
								'"'.$this->locale->lang('tag_td').'", "'.$this->locale->lang('tag_div').'"')
				);
			
			case 'bs_main_no_pad':
			case 'bs_main':
				$res = $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_td').'"',''),
					'main1.gif',
					array('','"'.$this->locale->lang('tag_a').'", "'.$this->locale->lang('tag_ahover').'"')
				);
				$res .= $this->_get_explanation_divider();
				$res .= $this->_get_explanation_table(
					array('',''),
					'main2.gif',
					array('"'.$this->locale->lang('tag_td').'"','')
				);
				return $res;
			
			case 'bs_coldesc':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_td').'"',''),
					'coldesc.gif',
					array('"'.$this->locale->lang('tag_a').'", "'.$this->locale->lang('tag_ahover').'"','')
				);
			
			case 'bs_topic':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_a').'", "'.$this->locale->lang('tag_ahover').'"',''),
					'topic.gif',
					array('"'.$this->locale->lang('tag_td').'"','')
				);
			
			case 'bs_desc':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_div').'"',''),
					'desc.gif',
					array('','')
				);
			
			case 'bs_forums':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_a').'", "'.$this->locale->lang('tag_ahover').'"',''),
					'forums.gif',
					array('"'.$this->locale->lang('tag_td').'"','')
				);
			
			case 'bs_forums_small':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_a').'", "'.$this->locale->lang('tag_ahover').'"',''),
					'forums_small.gif',
					array('','')
				);
			
			case 'bs_categories':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_a').'", "'.$this->locale->lang('tag_ahover').'", "'
						.$this->locale->lang('tag_td').'"',''),
					'categories.gif',
					array('','')
				);
			
			case 'bs_top_menu':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_a').'", "'.$this->locale->lang('tag_ahover').'",
					"'.$this->locale->lang('tag_td').'", "'.$this->locale->lang('tag_tdhover').'"',''),
					'top_menu.gif',
					array('','')
				);
			
			case 'bs_headline':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_div').'"',''),
					'headline.gif',
					array('','')
				);
			
			case 'bs_post_separator':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_div').'"',''),
					'post_separator.gif',
					array('','')
				);
			
			case 'bs_posts_bar_1':
			case 'bs_posts_bar_2':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_a').'", "'.$this->locale->lang('tag_ahover').'"',''),
					'post_bar.gif',
					array('"'.$this->locale->lang('tag_td').'"','')
				);
			
			case 'bs_posts_left_1':
			case 'bs_posts_left_2':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_td').'"',''),
					'post_left.gif',
					array('"'.$this->locale->lang('tag_a').'", "'.$this->locale->lang('tag_ahover').'"','')
				);
			
			case 'bs_posts_main_1':
			case 'bs_posts_main_2':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_td').'"',''),
					'post_main.gif',
					array('"'.$this->locale->lang('tag_a').'", "'.$this->locale->lang('tag_ahover').'"','')
				);
			
			case 'bs_tbl_top':
			case 'bs_tbl_top_left':
			case 'bs_tbl_top_right':
			case 'bs_tbl_left':
			case 'bs_tbl_right':
			case 'bs_tbl_bottom':
			case 'bs_tbl_bottom_left':
			case 'bs_tbl_bottom_right':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_td').'"',''),
					'tbl_border.gif',
					array('','')
				);
			
			case 'bs_button':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_a').'", "'.$this->locale->lang('tag_ahover').'"',''),
					'button.gif',
					array('','')
				);
			
			case 'bs_button_big':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_a').'", "'.$this->locale->lang('tag_ahover').'"',''),
					'button_big.gif',
					array('','')
				);
			
			case 'bs_calendar':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_td').'"',''),
					'calendar.gif',
					array('"'.$this->locale->lang('tag_a').'", "'.$this->locale->lang('tag_ahover').'"','')
				);
			
			case 'bs_calendar_today':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_td').'"',''),
					'calendar_today.gif',
					array('','','')
				);
			
			case 'bs_calendar_empty':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_td').'"',''),
					'calendar_empty.gif',
					array('','','')
				);
			
			case 'bs_calendar_empty_today':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_td').'"',''),
					'calendar_empty_today.gif',
					array('','','')
				);
			
			case 'bs_calendar_border':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_td').'"',''),
					'calendar_border.gif',
					array('','','')
				);
			
			case 'bs_calendar_border_today':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_td').'"',''),
					'calendar_border_today.gif',
					array('','','')
				);
			
			case 'bs_unread':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_div').'"',''),
					'unread_pms.gif',
					array('','')
				);
			
			case 'bs_quote_section':
				return $this->_get_explanation_table(
					array('',''),
					'quote_section.gif',
					array('"'.$this->locale->lang('tag_all_tags').'"','')
				);
			
			case 'bs_quote_section_top':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_all_tags').'"',''),
					'quote_section.gif',
					array('','')
				);
			
			case 'bs_quote_section_main':
				return $this->_get_explanation_table(
					array('',''),
					'quote_section.gif',
					array('','"'.$this->locale->lang('tag_all_tags').'"')
				);
			
			case 'bs_bbcode_notice':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_input').'"',''),
					'bbcode_notice.gif',
					array('','')
				);
			
			case 'bs_search_keywords':
				return $this->_get_explanation_table(
					array('"'.$this->locale->lang('tag_div').'"',''),
					'search_keywords.gif',
					array('','')
				);
			
			default:
			  return '';
		}
	}

	/**
	 * builds the color-form for the given attribute
	 *
	 * @param string $class the name of the class
	 * @param string $attribute_name the attribute
	 * @param string $value the value of the attribute
	 * @return string the color-form
	 */
	private function _get_color_form($class,$attribute_name,$value)
	{
		$id = $class.'|'.$attribute_name;
		$clearid = preg_replace('/[^a-z0-9_]/i','_',$id);
		$res = '<script type="text/javascript">'."\n";
		$res .= '<!--'."\n";
		$res .= 'var cp_'.$clearid.' = new PLIB_ColorPicker("'.PLIB_Path::lib().'","'.$id.'",';
		$res .= 'function(color) {'."\n";
		$res .= '	PLIB_getElement(\''.$id.'|preview\').style.backgroundColor = "#"+color;'."\n";
		$res .= '});'."\n";
		$res .= '//-->'."\n";
		$res .= '</script>'."\n";
		
		$res .= '<table align="left" bgcolor="#000000" width="16" cellpadding="0" cellspacing="1">'."\n";
		$res .= '	<tr>'."\n";
		$res .= '		<td onclick="cp_'.$clearid.'.toggle(this.id);"';
		$res .= ' title="'.$this->locale->lang('color_picker_hint').'" height="16"';
		$res .= ' bgcolor="'.$value.'" id="'.$id.'|preview"></td>'."\n";
		$res .= '	</tr>'."\n";
		$res .= '</table>'."\n";

		$res .= '&nbsp;&nbsp;<input type="text" name="'.$id.'" id="'.$id.'" size="8" value="'.$value.'"';
		$res .= ' maxlength="7" />'."\n";
		
		$res .= '<img id="cp_img_'.$clearid.'" src="acp/images/color_picker.gif"';
		$res .= ' title="'.$this->locale->lang('color_picker_hint').'"';
		$res .= ' alt="'.$this->locale->lang('color_picker_hint').'"';
		$res .= ' onmouseover="this.style.cursor = \'pointer\';"';
		$res .= ' onmouseout="this.style.cursor = \'default\';"';
		$res .= ' onclick="cp_'.$clearid.'.toggle(this.id);';
		$res .= ' PLIB_getElement(\''.$id.'|preview\').style.backgroundColor =';
		$res .= ' PLIB_getElement(\''.$id.'\').value;" />';
		
		return $res;
	}

	/**
	 * Returns the form-element of the given attribute
	 *
	 * @param string $class the name of the class
	 * @param string $attribute the attribute
	 * @param string $value the value of the attribute
	 * @return string the form-element
	 */
	private function _get_form_element($class,$attribute,$value)
	{
		$form = new BS_HTML_Formular(false,false);
		switch($attribute)
		{
			case 'height':
			case 'width':
			case 'padding-top':
			case 'padding-bottom':
			case 'padding-left':
			case 'padding-right':
			case 'font-size':
				$match = array();
				if(preg_match('/^(\d+)(pt|pc|in|mm|cm|px|em|ex|%)$/',$value,$match))
				{
					$res = $form->get_textbox($class.'|'.$attribute.'|val',$match[1],4,255).' ';
					$type = array(
						'pt' => $this->locale->lang('type_pt'),
						'pc' => $this->locale->lang('type_pc'),
						'in' => $this->locale->lang('type_in'),
						'mm' => $this->locale->lang('type_mm'),
						'cm' => $this->locale->lang('type_cm'),
						'px' => $this->locale->lang('type_px'),
						'em' => $this->locale->lang('type_em'),
						'ex' => $this->locale->lang('type_ex'),
						'%' => $this->locale->lang('type_percent')
					);
					$res .= $form->get_combobox($class.'|'.$attribute.'|type',$type,$match[2]);
				}
				else
					$res = $form->get_textbox($class.'|'.$attribute,$value,6,255);
				
				return $res;

			case 'text-decoration':
				$options = array(
					'underline' => $this->locale->lang('value_underline'),
					'overline' => $this->locale->lang('value_overline'),
					'line-through' => $this->locale->lang('value_line-through'),
					'blink' => $this->locale->lang('value_blink'),
					'none' => $this->locale->lang('value_none')
				);
				return $form->get_combobox($class.'|'.$attribute,$options,$value);

			case 'font-weight':
				$array = array(
					'bold' => $this->locale->lang('value_bold'),
					'bolder' => $this->locale->lang('value_bolder'),
					'lighter' => $this->locale->lang('value_lighter'),
					'normal' => $this->locale->lang('value_normal')
				);
				for($i = 100;$i < 1000;$i += 100)
					$array[$i] = $i;

				return $form->get_combobox($class.'|'.$attribute,$array,$value);

			case 'font-style':
				$array = array(
					'italic' => $this->locale->lang('value_italic'),
					'oblique' => $this->locale->lang('value_oblique'),
					'normal' => $this->locale->lang('value_normal')
				);
				return $form->get_combobox($class.'|'.$attribute,$array,$value);

			case 'background-color':
			case 'color':
				return $this->_get_color_form($class,$attribute,$value);

			case 'cursor':
				$array = array(
					'default' => $this->locale->lang('value_default'),
					'pointer' => $this->locale->lang('value_pointer')
				);
				return $form->get_combobox($class.'|'.$attribute,$array,$value);

			case 'border-style':
				$array = array(
					'none' => $this->locale->lang('value_none'),
					'hidden' => $this->locale->lang('value_hidden'),
					'dotted' => $this->locale->lang('value_dotted'),
					'dashed' => $this->locale->lang('value_dashed'),
					'solid' => $this->locale->lang('value_solid'),
					'double' => $this->locale->lang('value_double'),
					'groove' => $this->locale->lang('value_groove'),
					'ridge' => $this->locale->lang('value_ridge'),
					'inset' => $this->locale->lang('value_inset'),
					'outset' => $this->locale->lang('value_outset')
				);
				return $form->get_combobox($class.'|'.$attribute,$array,$value);

			case 'background-position':
				$array = array(
					'top' => $this->locale->lang('value_top'),
					'center' => $this->locale->lang('value_center'),
					'middle' => $this->locale->lang('value_middle'),
					'bottom' => $this->locale->lang('value_bottom'),
					'left' => $this->locale->lang('value_left'),
					'right' => $this->locale->lang('value_right')
				);
				return $form->get_combobox($class.'|'.$attribute,$array,$value);

			case 'background-image':
				return $form->get_textbox($class.'|'.$attribute,$value,35,255);

			case 'background-attachment':
				$array = array(
					'fixed' => $this->locale->lang('value_fixed'),
					'scroll' => $this->locale->lang('value_scroll')
				);
				return $form->get_combobox($class.'|'.$attribute,$array,$value);

			case 'background-repeat':
				$array = array(
					'no-repeat' => $this->locale->lang('value_norepeat'),
					'repeat' => $this->locale->lang('value_repeat'),
					'repeat-x' => $this->locale->lang('value_x-repeat'),
					'repeat-y' => $this->locale->lang('value_y-repeat')
				);
				return $form->get_combobox($class.'|'.$attribute,$array,$value);

			default:
				return $form->get_textbox($class.'|'.$attribute,$value,35,255);
		}
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>