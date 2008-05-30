<?php
/**
 * This file contains the definitions of the BBCode of Boardsolution.
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	config
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

# Die verfuegbaren BBCodes. Sie werden nach dem folgenden Schema definiert:
# The available BBCodes. They are defined like the following:
# <tagName> => array(
#   'tag'                 => <tagName>,             // der Name des Tags
#   'type'                => <bbcodeType>,          // hier sind die Werte 'inline','url','block' moeglich
#   'content'             => <contentType>,         // hier sind die Werte 'text','url','image','code','list'
#                                                   // moeglich
#   'replacement'         => <replacement>,         // hier wird festgelegt wie der Tag bei nicht
#                                                   // vorhandenem Parameter ersetzt wird. Die Variablen 
#                                                   // <!--TEXT--> und <!--PARAM--> stehen zur Verfuegung
#                                                   // Wobei diese folgendermassen zustandekommen:
#                                                   // [tag=<!--PARAM-->]<!--TEXT-->[/tag]
#   'replacement_param'   => <replacement>,         // das gleiche wie 'replacement'; wird benutzt
#                                                   // sofern ein Parameter erlaubt und vorhanden ist
#   'param'               => <paramType>,           // Hier sind die Werte 'no','optional','required' moeglich
#   'param_pattern'       => <pregMatchPattern>,    // Das regex-Pattern fuer preg_match(). Kann auch leer sein
#   'ignore_whitespace'   => <ignoreWhitespace>,    // Falls aktiviert, werden Leerzeichen, Zeilenumbrueche etc.
#                                                   // innerhalb dieses Tags ignoriert-
#   'ignore_unknown_tags' => <ignoreUnknownTags>    // Falls aktiviert, werden unbekannte Tags innerhalb
#                                                   // dieses Tags ignoriert
#   'allowed_content'     => array(                 // die erlaubten Content-Types. (Siehe 'content')
#     <contentType>	=> true,
#     ...
#   )
# )
#
# Bitte gucken Sie sich die bereits vorhandenen Tags an um zu verstehen wie es funktioniert :)
# Please look at the available tags to understand how it works :)

$bbcode = array(
	'b' => array(
		'tag' => 'b',
		'type' => 'inline',
		'content' => 'text',
		'replacement' => '<b><!--TEXT--></b>',
		'param' => 'no',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true,
			'url' => true
		)
	),
	
	'i' => array(
		'tag' => 'i',
		'type' => 'inline',
		'content' => 'text',
		'replacement' => '<i><!--TEXT--></i>',
		'param' => 'no',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true,
			'url' => true
		)
	),
	
	'u' => array(
		'tag' => 'u',
		'type' => 'inline',
		'content' => 'text',
		'replacement' => '<u><!--TEXT--></u>',
		'param' => 'no',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true,
			'url' => true
		)
	),
	
	's' => array(
		'tag' => 's',
		'type' => 'inline',
		'content' => 'text',
		'replacement' => '<strike><!--TEXT--></strike>',
		'param' => 'no',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true,
			'url' => true
		)
	),
	
	'font' => array(
		'tag' => 'font',
		'type' => 'inline',
		'content' => 'text',
		'replacement_param' => '<span style="font-family: <!--PARAM-->;"><!--TEXT--></span>',
		'param' => 'required',
		'param_pattern' => '/^[ a-z0-9]+$/i',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true,
			'url' => true
		)
	),
	
	'color' => array(
		'tag' => 'color',
		'type' => 'inline',
		'content' => 'text',
		'replacement_param' => '<span style="color: <!--PARAM-->;"><!--TEXT--></span>',
		'param' => 'required',
		'param_pattern' => '/^#?[a-f0-9]{6}$/i',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true,
			'url' => true
		)
	),
	
	'size' => array(
		'tag' => 'size',
		'type' => 'inline',
		'content' => 'text',
		'replacement_param' => '<span style="font-size: <!--PARAM-->px;"><!--TEXT--></span>',
		'param' => 'required',
		'param_pattern' => '/^[1-2]?[0-9]{1}$/',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true,
			'url' => true
		)
	),
	
	'url' => array(
		'tag' => 'url',
		'type' => 'url',
		'content' => 'url',
		'replacement' => '<a target="_blank" href="<!--TEXT-->"><!--TEXT--></a>',
		'replacement_param' => '<a target="_blank" href="<!--PARAM-->"><!--TEXT--></a>',
		'param' => 'optional',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true
		)
	),
	
	'mail' => array(
		'tag' => 'mail',
		'type' => 'url',
		'content' => 'text',
		'replacement' => '<a href="mailto:<!--TEXT-->"><!--TEXT--></a>',
		'replacement_param' => '<a href="mailto:<!--PARAM-->"><!--TEXT--></a>',
		'param' => 'optional',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true
		)
	),
	
	'img' => array(
		'tag' => 'img',
		'type' => 'inline',
		'content' => 'image',
		'replacement' => '<!--IS="<!--TEXT-->"--><img src="<!--TEXT-->" alt="<!--TEXT-->" style="max-width: 100%;" /><!--IE-->',
		'param' => 'no',
		'allow_nesting' => false,
		'allowed_content' => array()
	),
	
	'quote' => array(
		'tag' => 'quote',
		'type' => 'block',
		'content' => 'text',
		'replacement' => "\n<div class=\"bs_quote_section\">"
										."<div class=\"bs_quote_section_top\"><b><!--L[quote]--></b>:</div>"
										."<div class=\"bs_quote_section_main\"><!--TEXT--></div>"
										."</div>",
		'replacement_param' => "\n<div class=\"bs_quote_section\">"
													."<div class=\"bs_quote_section_top\"><b><!--PARAM--></b> <!--L[wrotethefollowing]-->:</div>"
													."<div class=\"bs_quote_section_main\"><!--TEXT--></div>"
													."</div>",
		'param' => 'optional',
		'param_pattern' => '',
		'allow_nesting' => true,
		'allowed_content' => array(
			'inline' => true,
			'url' => true,
			'block' => true
		)
	),
	
	'code' => array(
		'tag' => 'code',
		'type' => 'block',
		'content' => 'code',
		'replacement' => "\n<div class=\"bs_quote_section\" style=\"overflow: hidden;\">"
										."<div class=\"bs_quote_section_top\"><b><!--L[code]-->:</b></div>"
										."<div class=\"bs_quote_section_main\" style=\"overflow: auto;\">"
										."<!--TEXT-->"
										."</div>"
										."</div>",
		'replacement_param' => "\n<div class=\"bs_quote_section\" style=\"overflow: hidden;\">"
										."<div class=\"bs_quote_section_top\"><b><!--PARAM-->:</b></div>"
										."<div class=\"bs_quote_section_main\" style=\"overflow: auto;\">"
										."<!--TEXT-->"
										."</div>"
										."</div>",
		'param' => 'optional',
		'allow_nesting' => false,
		'allowed_content' => array()
	),
	
	'list' => array(
		'tag' => 'list',
		'type' => 'block',
		'content' => 'list',
		'replacement' => '<!--TEXT-->',
		'replacement_param' => '<!--TEXT-->',
		'param' => 'optional',
		'open_tags_allowed' => true,
		'allow_nesting' => true,
		'allowed_content' => array(
			'inline' => true,
			'url' => true,
			'block' => true
		)
	),
	
	'topic' => array(
		'tag' => 'topic',
		'type' => 'url',
		'content' => 'text',
		'replacement_param' => '<a target="_blank" href="<!--BSF-->'.BS_URL_ACTION.'=redirect&amp;'.BS_URL_LOC.'=show_topic&amp;'.BS_URL_TID.'=<!--PARAM-->"><!--TEXT--></a>',
		'param' => 'required',
		'param_pattern' => '/^\d+$/',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true
		)
	),
	
	'post' => array(
		'tag' => 'post',
		'type' => 'url',
		'content' => 'text',
		'replacement_param' => '<a target="_blank" href="<!--BSF-->'.BS_URL_ACTION.'=redirect&amp;'.BS_URL_LOC.'=show_post&amp;'.BS_URL_ID.'=<!--PARAM-->"><!--TEXT--></a>',
		'param' => 'required',
		'param_pattern' => '/^\d+$/',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true
		)
	),
	
	'sub' => array(
		'tag' => 'sub',
		'type' => 'inline',
		'content' => 'text',
		'replacement' => '<sub><!--TEXT--></sub>',
		'param' => 'no',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true,
			'url' => true
		)
	),
	
	'sup' => array(
		'tag' => 'sup',
		'type' => 'inline',
		'content' => 'text',
		'replacement' => '<sup><!--TEXT--></sup>',
		'param' => 'no',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true,
			'url' => true
		)
	),
	
	'left' => array(
		'tag' => 'left',
		'type' => 'block',
		'content' => 'text',
		'replacement' => '<div align="left"><!--TEXT--></div>',
		'param' => 'no',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true,
			'url' => true
		)
	),
	
	'center' => array(
		'tag' => 'center',
		'type' => 'block',
		'content' => 'text',
		'replacement' => '<div align="center"><!--TEXT--></div>',
		'param' => 'no',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true,
			'url' => true
		)
	),
	
	'right' => array(
		'tag' => 'right',
		'type' => 'block',
		'content' => 'text',
		'replacement' => '<div align="right"><!--TEXT--></div>',
		'param' => 'no',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true,
			'url' => true
		)
	),
	
	'att' => array(
		'tag' => 'att',
		'type' => 'inline',
		'content' => 'attachment',
		'replacement_param' => '<!--TEXT-->',
		'param' => 'required',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true
		)
	),
	
	'attimg' => array(
		'tag' => 'attimg',
		'type' => 'inline',
		'content' => 'attachmentimage',
		'replacement' => '<!--TEXT-->',
		'param' => 'no',
		'allow_nesting' => false,
		'allowed_content' => array()
	),
	
	/*
	'table' => array(
		'tag' => 'table',
		'type' => 'block',
		'content' => 'text',
		'replacement' => '<table cellpadding="0" cellspacing="0" bgcolor="#7F90AE">'
												.'<tr>'
													.'<td>'
													.'<table cellpadding="2" cellspacing="1">'
													.'	<!--TEXT-->'
													.'</table>'
													.'</td>'
												.'</tr>'
											.'</table>',
		'param' => 'no',
		'allow_nesting' => false,
		'ignore_whitespace' => true,
		'ignore_unknown_tags' => true,
		'allowed_content' => array(
			'tr' => true
		)
	),
	
	'tr' => array(
		'tag' => 'tr',
		'type' => 'tr',
		'content' => 'text',
		'replacement' => '<tr><!--TEXT--></tr>',
		'param' => 'no',
		'allow_nesting' => false,
		'ignore_whitespace' => true,
		'ignore_unknown_tags' => true,
		'allowed_content' => array(
			'td' => true
		)
	),
	
	'td' => array(
		'tag' => 'td',
		'type' => 'td',
		'content' => 'text',
		'replacement' => '<td bgcolor="#EBEBEB"><!--TEXT--></td>',
		'param' => 'no',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true,
			'block' => true,
			'url' => true
		)
	)*/
);


# Hier kann festgelegt werden welche Buttons in welcher Reihenfolge angezeigt werden
# Die Struktur sieht folgendermassen aus:
# array(
#  // zeile 1
#  array(
#    'tag1' => array(
#      'eigenschaft1' => 'wert1',
#      ...
#    ),
#    ...
#  ),
#  // zeile 2
#  ...
# )
#
# Die Eigenschaften richten sich nach dem Typ (Parameter 'type') des Buttons.
# Es gibt "image","button" und "combo".
#
# Fuer "image":
#  - 'image' => 'pfadZumBild' // beginnend in themes/<theme>/images/
#  - 'width' => 'breiteDesBildes'
#  - 'height' => 'hoeheDesBildes'
#
# Fuer "button":
#  - 'style' => 'zusaetzliche Attribute',
#  - 'accesskey' => 'der Wert des Tag-Attributes "accesskey"
#
# Fuer "combo":
#  - 'options' => array( // die Elemente der Combobox
#      'key1' => 'wert1',
#      ...
#     )
#
# Alle haben folgende Eigenschaften:
#  - 'prompt_text' => [bbcode_<langKey>]        // Fuer den "einfachen Modus"; das JS-Prompt
#																								// Wird aus dem Array $LANG geholt. Angegeben werden muss
#  - 'prompt_param_text' => [bbcode_<langKey>]	// Das gleiche wie oben, nur fuer den Parameter (z.B. bei URL)
#
# Ausserdem gibt es noch den "separator", welcher benutzt werden kann um Buttons zu gruppieren
#
# Schauen Sie sich bitte die bisher vorhandenen Tags unten an um Beispiele zu sehen.

$bbcode_bar = array(
	// line 1
	array(
		'b' => array(
			'type' => 'image',
			'image' => 'bbcode/bold.png',
			'width' => 20,
			'height' => 20,
			'prompt_text' => 'prompt_b'
		),
		'i' => array(
			'type' => 'image',
			'image' => 'bbcode/italic.png',
			'width' => 20,
			'height' => 20,
			'prompt_text' => 'prompt_i'
		),
		'u' => array(
			'type' => 'image',
			'image' => 'bbcode/underline.png',
			'width' => 20,
			'height' => 20,
			'prompt_text' => 'prompt_u'
		),
		's' => array(
			'type' => 'image',
			'image' => 'bbcode/strike.png',
			'width' => 20,
			'height' => 20,
			'prompt_text' => 'prompt_s'
		),
		'separator',
		'sub' => array(
			'type' => 'image',
			'image' => 'bbcode/subscript.png',
			'width' => 20,
			'height' => 20,
			'prompt_text' => 'prompt_sub'
		),
		'sup' => array(
			'type' => 'image',
			'image' => 'bbcode/supscript.png',
			'width' => 20,
			'height' => 20,
			'prompt_text' => 'prompt_sup'
		),
		'separator',
		'left' => array(
			'type' => 'image',
			'image' => 'bbcode/left.png',
			'width' => 20,
			'height' => 20,
			'prompt_text' => 'prompt_left'
		),
		'center' => array(
			'type' => 'image',
			'image' => 'bbcode/center.png',
			'width' => 20,
			'height' => 20,
			'prompt_text' => 'prompt_center'
		),
		'right' => array(
			'type' => 'image',
			'image' => 'bbcode/right.png',
			'width' => 20,
			'height' => 20,
			'prompt_text' => 'prompt_right'
		),
		'separator',
		'font' => array(
			'type' => 'combo',
			'options' => array(
				'Arial' => 'Arial',
				'Times' => 'Times',
				'Courier' => 'Courier',
				'Impact' => 'Impact',
				'Verdana' => 'Verdana'
			),
			'prompt_text' => 'prompt_size'
		),
		'color' => array(
			'type' => 'combo',
			'options' => array(
				'#FF0000' => 'col_red',
				'#33FF00' => 'col_green',
				'#0000FF' => 'col_blue',
				'#33CCFF' => 'col_lightblue',
				'#FFFF00' => 'col_yellow'
			),
			'prompt_text' => 'prompt_color'
		),
		'size' => array(
			'type' => 'combo',
			'options' => array(
				8 => 'size_verysmall',
				10 => 'size_small',
				12 => 'size_middle',
				14 => 'size_big',
				18 => 'size_verybig'
			),
			'prompt_text' => 'prompt_size'
		),
	),
	// line 2
	array(
		'quote' => array(
			'type' => 'image',
			'image' => 'bbcode/quote.png',
			'width' => 20,
			'height' => 20,
			'prompt_text' => ''
		),
		'code' => array(
			'type' => 'image',
			'image' => 'bbcode/code.png',
			'width' => 20,
			'height' => 20,
			'prompt_text' => ''
		),
		'list' => array(
			'type' => 'image',
			'image' => 'bbcode/list.png',
			'width' => 20,
			'height' => 20,
			'prompt_text' => ''
		),
		'separator',
		'url' => array(
			'type' => 'image',
			'image' => 'bbcode/link.png',
			'width' => 20,
			'height' => 20,
			'prompt_text' => 'prompt_url',
			'prompt_param_text' => 'prompt_url_param'
		),
		'mail' => array(
			'type' => 'image',
			'image' => 'bbcode/mail.png',
			'width' => 20,
			'height' => 20,
			'prompt_text' => 'prompt_mail',
			'prompt_param_text' => 'prompt_mail_param'
		),
		'img' => array(
			'type' => 'image',
			'image' => 'bbcode/image.png',
			'width' => 20,
			'height' => 20,
			'prompt_text' => 'prompt_img'
		)
	)
);
?>