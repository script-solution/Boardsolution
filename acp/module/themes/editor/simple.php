<?php
/**
 * Contains the simple theme-editor-class
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
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
	 * @var FWS_CSS_SimpleParser
	 */
	private $_css;
	
	/**
	 * The categories in the CSS-file
	 *
	 * @var array
	 */
	private $_categories;
	
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
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_css = new FWS_CSS_StyleSheet(FWS_FileUtils::read($this->_theme.'/basic.css'));
		$this->_categories = array();
		$cat = '';
		$group = '';
		foreach($this->_css->get_blocks() as $k => $block)
		{
			if($block->get_type() == FWS_CSS_Block::COMMENT)
			{
				$matches = array();
				if(preg_match('/\/\*\s*\{\{(.*?)\}\}\s*\*\//',$block->get_content(),$matches) && $cat)
				{
					$group = $matches[1];
					$this->_categories[$cat][$group] = array();
				}
				else if(preg_match('/\/\*\s*\[\[(.*?)\]\]\s*\*\//',$block->get_content(),$matches))
				{
					$cat = $matches[1];
					$this->_categories[$cat] = array();
				}
			}
			else if($block->get_type() == FWS_CSS_Block::RULESET && $cat && $group)
			{
				$this->_categories[$cat][$group][] = array($k,$block);
			}
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
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();
		
		$theme = $input->get_var('theme','get',FWS_Input::STRING);
		
		if(count($this->_categories) == 0)
		{
			$doc = FWS_Props::get()->doc();
			$msgs = FWS_Props::get()->msgs();
			$doc->get_module()->set_error();
			$url = BS_URL::get_acpsub_url();
			$url->set('theme',$theme);
			$url->set('mode','advanced');
			$msgs->add_error(sprintf($locale->lang('css_has_no_categories'),$url->to_url()));
			return;
		}

		$cgroup = $input->get_var('group','get',FWS_Input::STRING);
		$groupa = $cgroup !== null ? explode('::',$cgroup) : array();
		if(count($groupa) == 2)
			list($cat,$group) = $groupa;
		else
		{
			$cat = key($this->_categories);
			$group = key($this->_categories[$cat]);
		}

		$del = $input->get_var('del','post');
		if($input->isset_var('delete','post') && $del != null)
		{
			$text = '';
			$ids = '';
			$num = count($del);
			for($i = 0;$i < $num;$i++)
			{
				$split = explode('|',$del[$i]);
				$block = $this->_css->get_block($split[0]);
				if($block !== null && $block->get_type() == FWS_CSS_Block::RULESET)
				{
					$ids .= $del[$i].',';
					$text .= $locale->lang('theattribute').' "'.$this->_get_attribute_name($split[1]).'" ';
					$text .= $locale->lang('of').' "';
					$text .= $block->get_name().'"';
					if($i < $num - 2)
						$text .= ', ';
					else if($i == $num - 2)
						$text .= ' '.$locale->lang('and').' ';
				}
			}
			
			$url = BS_URL::get_acpsub_url(0,'editor');
			$url->set('theme',$theme);
			$url->set('group',$cgroup);
			
			$yurl = clone $url;
			$yurl->set('at',BS_ACP_ACTION_THEME_EDITOR_SIMPLE_DELETE);
			$yurl->set('ids',$ids);

			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$text),$yurl->to_url(),$url->to_url()
			);
		}

		$baseurl = BS_URL::get_acpsub_url(0,'editor');
		$baseurl->set('theme',$theme);
		$baseurl->set('mode','simple');

		$tpl->set_template('themes_editor_simple.htm');
		$tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_THEME_EDITOR_SIMPLE_SAVE,
			'target_url' => $baseurl->set('group',$cat.'::'.$group)->to_url()
		));
		
		$cats = array();
		foreach($this->_categories as $catname => $groups)
		{
			$tplcat = array(
				'name' => $locale->lang('cat_'.$catname,false),
				'items' => array(),
			);

			foreach(array_keys($groups) as $gname)
			{
				if($group == $gname)
					$menu_item = '&rarr; '.$locale->lang('group_'.$gname,false);
				else
				{
					$menu_item = '&rarr; <a href="'.$baseurl->set('group',$catname.'::'.$gname)->to_url().'">';
					$menu_item .= $locale->lang('group_'.$gname,false).'</a>';
				}

				$tplcat['items'][] = array(
					'menu_item' => $menu_item
				);
			}
			
			$cats[] = $tplcat;
		}
		
		$tpl->add_variable_ref('cats',$cats);

		$tplgroups = array();
		if($group !== null && $cat !== null)
		{
			$tpl->add_variables(array(
				'explanation_picture' => $this->_get_picture_exlain($group)
			));

			$all_attributes = array();
			foreach($this->_attributes as $k => $v)
				$all_attributes[$k] = $locale->lang($v);
			
			$form = new BS_HTML_Formular(false,false);
			$rulesets = $this->_categories[$cat][$group];
			if(is_array($rulesets))
			{
				foreach($rulesets as $info)
				{
					list($key,$ruleset) = $info;
					/* @var $ruleset FWS_CSS_Block_Ruleset */
					$name = $ruleset->get_name();
					$group = array(
						'tag_name' => $name,
						'add_attribute_combo' => $form->get_combobox(
							'attribute['.$key.']',$all_attributes,''
						),
						'name' => $key,
						'attributes' => array()
					);

					foreach($ruleset->get_properties() as $aname => $avalue)
					{
						$group['attributes'][] = array(
							'id' => 'cb_'.$key.'_'.preg_replace('/[^a-z0-9_]/i','_',$aname),
							'cbname' => $key.'|'.$aname,
							'name' => $this->_get_attribute_name($aname),
							'form_element' => $this->_get_form_element('attrval_'.$key,$aname,$avalue)
						);
					}
					
					$tplgroups[] = $group;
				}
			}
		}
		
		$tpl->add_variable_ref('groups',$tplgroups);
		$tpl->restore_template();
	}
	
	/**
	 * Builds the name of the given attribute
	 *
	 * @param string $attr the attribute
	 * @return string the name of it
	 */
	private function _get_attribute_name($attr)
	{
		$locale = FWS_Props::get()->locale();

		if($locale->contains_lang('attr_'.$attr))
			return $locale->lang('attr_'.$attr);
		return $attr;
	}

	/**
	 * builds the explanation-table
	 *
	 * @param array $title_top the top-text
	 * @param string $image_name the name of the image
	 * @param array $title_bottom the bottom-text
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
		$res .= '		<td class="a_main" colspan="2"><img style="border: 1px solid #000;" src="acp/images/design/';
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
		$locale = FWS_Props::get()->locale();
		switch($class)
		{
			case 'body':
				return $this->_get_explanation_table(
					array('"body.bs_body"',''),
					'body.png',
					array('".bs_body a", ".bs_body a:hover"',
								'"body.bs_body"')
				);
			
			case 'main':
				return $this->_get_explanation_table(
					array('".bs_main"',''),
					'main.png',
					array('','".bs_main a", ".bs_main a:hover"')
				);
			
			case 'coldesc':
				$res = $this->_get_explanation_table(
					array('".bs_coldesc"',''),
					'coldesc.png',
					array('".bs_coldesc a", ".bs_coldesc a:hover"','')
				);
				$res .= $this->_get_explanation_divider();
				$res .= $this->_get_explanation_table(
					array('".bs_left"',''),
					'left.png',
					array('','')
				);
				return $res;
			
			case 'topic':
				return $this->_get_explanation_table(
					array('".bs_topic a", ".bs_topic a:hover"',''),
					'topic.png',
					array('".bs_topic", "td.bs_topic", "h1.bs_topic"','')
				);
			
			case 'desc':
				return $this->_get_explanation_table(
					array('".bs_desc"',''),
					'desc.png',
					array('','')
				);
			
			case 'border':
				return $this->_get_explanation_table(
					array('".bs_border"',''),
					'border.png',
					array('','')
				);
			
			case 'headline':
				$res = $this->_get_explanation_table(
					array('"div.bs_headline"','".bs_menu_wrapper"'),
					'headline_left.png',
					array('".bs_menu a", ".bs_menu a:hover"','"li.bs_welcome_left"')
				);
				$res .= $this->_get_explanation_divider();
				$res .= $this->_get_explanation_table(
					array('"li.bs_welcome_right"','".bs_welcome"'),
					'headline_right.png',
					array('','')
				);
				return $res;
			
			case 'bottom':
				return $this->_get_explanation_table(
					array('"div.bs_bottom"','"li.bs_bottom"'),
					'bottom.png',
					array('','')
				);
			
			case 'form':
				$res = $this->_get_explanation_table(
					array('"fieldset.bs_form dl"',''),
					'formular.png',
					array('"fieldset.bs_form dt"','"fieldset.bs_form dd"')
				);
				$res .= $this->_get_explanation_divider();
				$res .= $this->_get_explanation_table(
					array('"fieldset.bs_subform dl"',''),
					'formular_sub.png',
					array('"fieldset.bs_subform dt"','"fieldset.bs_subform dd"')
				);
				return $res;
			
			case 'forums':
				return $this->_get_explanation_table(
					array('".bs_forums a", ".bs_forums a:hover"',''),
					'forums.png',
					array('"td.bs_forums"','')
				);
			
			case 'forums_small':
				return $this->_get_explanation_table(
					array('"td.bs_forums_small"',''),
					'forums_small.png',
					array('','".bs_forums_small a", ".bs_forums_small a:hover"')
				);
			
			case 'categories':
				return $this->_get_explanation_table(
					array('".bs_categories a", ".bs_categories a:hover"','"td.bs_categories"'),
					'categories.png',
					array('','')
				);
			
			case 'topics_small':
				return $this->_get_explanation_table(
					array('".bs_topics_small a", ".bs_topics_small a:hover"',''),
					'topics_small.png',
					array('','')
				);
			
			case 'post':
				$res = $this->_get_explanation_table(
					array('".bs_posts_bar_X a"','".bs_posts_bar_X"'),
					'posts1.png',
					array('".bs_posts_left_X"','".bs_posts_left_X a"')
				);
				$res .= $this->_get_explanation_divider();
				$res .= $this->_get_explanation_table(
					array('".bs_post_separator"',''),
					'posts2.png',
					array('".bs_posts_main_X"','".bs_posts_main_X a"')
				);
				return $res;
			
			case 'bbcode':
				return $this->_get_explanation_table(
					array('"img.bs_bbcode"',''),
					'bbcode.png',
					array('','"input.bs_bbcode"')
				);
			
			case 'bbcode_popup':
				return $this->_get_explanation_table(
					array('".bs_bbcode_popup"','".bs_bbcode_popup li a", ".bs_bbcode_popup li a:hover"'),
					'bbcode_popup.png',
					array('".bs_bbcode_popup li"','')
				);
			
			case 'quote':
				return $this->_get_explanation_table(
					array('".bs_quote_section"','".bs_quote_section_top"'),
					'quote.png',
					array('".bs_quote_section_main"','')
				);
			
			case 'code':
				return $this->_get_explanation_table(
					array('".bs_lcode"','".bs_rcode"'),
					'code.png',
					array('','')
				);
			
			case 'calendar':
				return $this->_get_explanation_table(
					array('"td.bs_calendar"',''),
					'calendar.png',
					array('".bs_calendar a", ".bs_calendar a:hover"','"td.bs_calendar_today"')
				);
			
			case 'calendar_empty':
				return $this->_get_explanation_table(
					array('"td.bs_calendar_empty"','"td.bs_calendar_empty_today"'),
					'calendar_empty.png',
					array('','','')
				);
			
			case 'calendar_border':
				return $this->_get_explanation_table(
					array('"td.bs_calendar_border"',''),
					'calendar_border.png',
					array('','"td.bs_calendar_border_today"')
				);
			
			case 'unread':
				return $this->_get_explanation_table(
					array('"span.bs_unread"',''),
					'unread.png',
					array('','')
				);
			
			case 'highlight':
				return $this->_get_explanation_table(
					array('"span.bs_highlight"',''),
					'highlight.png',
					array('','')
				);
			
			case 'formelements':
				return $this->_get_explanation_table(
					array('"input,select,textarea"',''),
					'formelements.png',
					array('','')
				);
			
			case 'pagination':
				return $this->_get_explanation_table(
					array('".bs_pagecurrent"','".bs_pageno"'),
					'pagination.png',
					array('','".bs_pageno a", ".bs_pageno a:hover"')
				);
			
			case 'button':
				$res = $this->_get_explanation_table(
					array('"a.bs_button_selected", "a.bs_button_selected:hover"',''),
					'button.png',
					array('"a.bs_button", "a.bs_button:hover"','')
				);
				$res .= $this->_get_explanation_divider();
				$res .= $this->_get_explanation_table(
					array('"a.bs_button_big", "a.bs_button_big:hover"',''),
					'button_big.png',
					array('','')
				);
				return $res;
			
			default:
				return '';
		}
	}

	/**
	 * builds the color-form for the given attribute
	 *
	 * @param string $name the name of the class
	 * @param string $attribute_name the attribute
	 * @param string $value the value of the attribute
	 * @return string the color-form
	 */
	private function _get_color_form($name,$attribute_name,$value)
	{
		$locale = FWS_Props::get()->locale();
		
		$id = $name.$attribute_name;
		$clearid = preg_replace('/[^a-z0-9_]/i','_',$id);
		$res = '<script type="text/javascript">'."\n";
		$res .= '<!--'."\n";
		$res .= 'var cp_'.$clearid.' = new FWS_ColorPicker("'.FWS_Path::client_fw().'",null,';
		$res .= 'function(color) {'."\n";
		$res .= '	FWS_getElement(\''.$clearid.'___preview\').style.backgroundColor = "#"+color;'."\n";
		$res .= '	FWS_getElement(\''.$clearid.'\').value = "#"+color;'."\n";
		$res .= '});'."\n";
		$res .= '//-->'."\n";
		$res .= '</script>'."\n";
		
		$res .= '<div style="float:left; width: 20px; height: 20px;';
		$res .= ' background-color: '.$value.'; border: 1px solid #000;"';
		$res .= ' onclick="cp_'.$clearid.'.toggle(this.id);"';
		$res .= ' title="'.$locale->lang('color_picker_hint').'"';
		$res .= ' id="'.$clearid.'___preview">&nbsp;</div>'."\n";

		$res .= '&nbsp;&nbsp;<input type="text" name="'.$name.'|'.$attribute_name;
		$res .= '" id="'.$clearid.'" size="8" value="'.$value.'" maxlength="7" />'."\n";
		
		$res .= '<img id="cp_img_'.$clearid.'" src="acp/images/color_picker.gif"';
		$res .= ' title="'.$locale->lang('color_picker_hint').'"';
		$res .= ' alt="'.$locale->lang('color_picker_hint').'"';
		$res .= ' onmouseover="this.style.cursor = \'pointer\';"';
		$res .= ' onmouseout="this.style.cursor = \'default\';"';
		$res .= ' onclick="cp_'.$clearid.'.toggle(this.id,\'rt\');';
		$res .= ' FWS_getElement(\''.$clearid.'___preview\').style.backgroundColor =';
		$res .= ' FWS_getElement(\''.$clearid.'\').value;" />';
		
		return $res;
	}

	/**
	 * Returns the form-element of the given attribute
	 *
	 * @param string $name the name of the class
	 * @param string $attribute the attribute
	 * @param string $value the value of the attribute
	 * @return string the form-element
	 */
	private function _get_form_element($name,$attribute,$value)
	{
		$locale = FWS_Props::get()->locale();

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
			case 'line-height':
			case 'margin-left':
			case 'margin-right':
			case 'margin-top':
			case 'margin-bottom':
				$match = array();
				if(preg_match('/^([\.\d]+)(pt|pc|in|mm|cm|px|em|ex|%)$/',$value,$match))
				{
					$res = $form->get_textbox($name.'|'.$attribute.'|val',htmlspecialchars($match[1]),4,255).' ';
					$type = array(
						'pt' => $locale->lang('type_pt'),
						'pc' => $locale->lang('type_pc'),
						'in' => $locale->lang('type_in'),
						'mm' => $locale->lang('type_mm'),
						'cm' => $locale->lang('type_cm'),
						'px' => $locale->lang('type_px'),
						'em' => $locale->lang('type_em'),
						'ex' => $locale->lang('type_ex'),
						'%' => $locale->lang('type_percent')
					);
					$res .= $form->get_combobox($name.'|'.$attribute.'|type',$type,$match[2]);
				}
				else
					$res = $form->get_textbox($name.'|'.$attribute,htmlspecialchars($value),6,255);
				
				return $res;

			case 'text-decoration':
				$options = array(
					'underline' => $locale->lang('value_underline'),
					'overline' => $locale->lang('value_overline'),
					'line-through' => $locale->lang('value_line-through'),
					'blink' => $locale->lang('value_blink'),
					'none' => $locale->lang('value_none')
				);
				return $form->get_combobox($name.'|'.$attribute,$options,$value);

			case 'font-weight':
				$array = array(
					'bold' => $locale->lang('value_bold'),
					'bolder' => $locale->lang('value_bolder'),
					'lighter' => $locale->lang('value_lighter'),
					'normal' => $locale->lang('value_normal')
				);
				for($i = 100;$i < 1000;$i += 100)
					$array[$i] = $i;

				return $form->get_combobox($name.'|'.$attribute,$array,$value);

			case 'font-style':
				$array = array(
					'italic' => $locale->lang('value_italic'),
					'oblique' => $locale->lang('value_oblique'),
					'normal' => $locale->lang('value_normal')
				);
				return $form->get_combobox($name.'|'.$attribute,$array,$value);

			case 'background-color':
			case 'color':
				return $this->_get_color_form($name,$attribute,$value);

			case 'cursor':
				$array = array(
					'default' => $locale->lang('value_default'),
					'pointer' => $locale->lang('value_pointer')
				);
				return $form->get_combobox($name.'|'.$attribute,$array,$value);

			case 'border-style':
				$array = array(
					'none' => $locale->lang('value_none'),
					'hidden' => $locale->lang('value_hidden'),
					'dotted' => $locale->lang('value_dotted'),
					'dashed' => $locale->lang('value_dashed'),
					'solid' => $locale->lang('value_solid'),
					'double' => $locale->lang('value_double'),
					'groove' => $locale->lang('value_groove'),
					'ridge' => $locale->lang('value_ridge'),
					'inset' => $locale->lang('value_inset'),
					'outset' => $locale->lang('value_outset')
				);
				return $form->get_combobox($name.'|'.$attribute,$array,$value);

			case 'vertical-align':
				$array = array(
					'left' => $locale->lang('value_left'),
					'middle' => $locale->lang('value_center'),
					'right' => $locale->lang('value_right'),
					'baseline' => $locale->lang('value_baseline'),
					'sub' => $locale->lang('value_sub'),
					'super' => $locale->lang('value_super'),
					'text-top' => $locale->lang('value_text-top'),
					'text-bottom' => $locale->lang('value_text-bottom')
				);
				return $form->get_combobox($name.'|'.$attribute,$array,$value);
			
			case 'text-align':
				$array = array(
					'left' => $locale->lang('value_left'),
					'center' => $locale->lang('value_center'),
					'right' => $locale->lang('value_right'),
					'justify' => $locale->lang('value_justify')
				);
				return $form->get_combobox($name.'|'.$attribute,$array,$value);
			
			case 'background-position':
				$array = array(
					'top' => $locale->lang('value_top'),
					'center' => $locale->lang('value_center'),
					'middle' => $locale->lang('value_middle'),
					'bottom' => $locale->lang('value_bottom'),
					'left' => $locale->lang('value_left'),
					'right' => $locale->lang('value_right')
				);
				return $form->get_combobox($name.'|'.$attribute,$array,$value);

			case 'background-image':
				return $form->get_textbox($name.'|'.$attribute,htmlspecialchars($value),35,255);

			case 'background-attachment':
				$array = array(
					'fixed' => $locale->lang('value_fixed'),
					'scroll' => $locale->lang('value_scroll')
				);
				return $form->get_combobox($name.'|'.$attribute,$array,$value);

			case 'background-repeat':
				$array = array(
					'no-repeat' => $locale->lang('value_norepeat'),
					'repeat' => $locale->lang('value_repeat'),
					'repeat-x' => $locale->lang('value_x-repeat'),
					'repeat-y' => $locale->lang('value_y-repeat')
				);
				return $form->get_combobox($name.'|'.$attribute,$array,$value);

			default:
				return $form->get_textbox($name.'|'.$attribute,htmlspecialchars($value),35,255);
		}
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>