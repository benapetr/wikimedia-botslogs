<?php
//This is a source code or part of Huggle project
//
//This file contains code for index page
//last modified by Addshore

//Copyright (C) 2011-2012 Huggle team

//This program is free software: you can redistribute it and/or modify
//it under the terms of the GNU General Public License as published by
//the Free Software Foundation, either version 3 of the License, or
//(at your option) any later version.

//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.
/* Huggle WA - includes/renderapp.php */

if ( !defined( 'HUGGLE' ) ) {
	echo "This is a part of huggle wa, unable to load config";
	die (1);
}

class Html {
public static $_page = "";
public static $_toolbar;
public static $_statusbar;
private static function Header() {
	global $hgwa_HtmlTitle;
	include "html/template_header";
}
private static function Menubuttons() {
	echo "\n<div class='menu'><div class='menubuttons'>";
}

public static function getBuffer ($html) {
	global $hgwa_HtmlTitle;
	self::$_page = self::$_page . $html;
}


private static function Menulogin() {
	global $hgwa_Username;
}

public static function ChangeTitle( $content ) {
	global $hgwa_HtmlTitle;
	$hgwa_HtmlTitle = $content;
}

private static function Content() {
	global $hgwa_Username, $hgwa_QueueWidth;
	echo "<div class=\"interface\">";
	// queue
	echo '<div class="queue">Channels:<br>';
	foreach ( Core::ListAll() as $chan ) {
		                echo "<a href=index.php?action=search&channel=". str_replace("#", "%23", $chan) .">$chan</a><br>";
				        }

	// body
	echo "</div><div class=\"content\">";
	echo self::$_page;
	echo "</div></div>";

}

private static function Footer() {
	include "html/template_footer";
}

public static function LoadContent() {
	self::Header();
	self::Menubuttons();
	self::Menulogin();
	self::Content();
	self::Footer();
	return 0;
}
}
