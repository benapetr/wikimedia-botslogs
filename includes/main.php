<?
//This file contains code for index page
//last modified by Petrb

//Copyright (C) 2011-2012 Huggle team

//This program is free software: you can redistribute it and/or modify
//it under the terms of the GNU General Public License as published by
//the Free Software Foundation, either version 3 of the License, or
//(at your option) any later version.

//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details

if ( !defined( 'HUGGLE' ) ) {
	echo "This is a part of huggle wa, unable to load config";
	die (1);
}

include ("includes/variables.php");

class Core {
	public static $action = null;
	private static $no = 0;
	// Return a translated text
	public static function GetMessage ( $text ) {
		global $hgwa_Message, $hgwa_Debugging;
		if ( !isset ($hgwa_Message["$text"]) ) {
			return "undefined: $text";
		}
		return $hgwa_Message["$text"];
	}
	
	public static function isLanguage( $code ) {
		switch ($code) {
			case "en":
			case "ru":
				return true;
		}
		return false;
	}

	public static function getOverrides() {
		global $hgwa_Language, $hgwa_Skin;
		if ( isset ($_GET['uselang']) ) {
			if (Core::isLanguage($_GET['uselang'])) {
				$hgwa_Language = $_GET['uselang'];
				self::LoadLanguage();
			}
			Core::Info("Override for language is triggered");
		}
	}

	private static function LoadLanguage () {
		global $hgwa_Message, $hgwa_Locals, $hgwa_Language;
		if (Core::isLanguage( $hgwa_Language )) {
			include ( "$hgwa_Locals" . $hgwa_Language . "_main.php" );
			Core::Info( "Loading $hgwa_Language" );
			return true;
		}
		Core::Info( "Error: Invalid language $hgwa_Language" );
		include ( "$hgwa_Locals" . 'en.php' );
		return true;
	}

	private static function getAction() {
		global $hgwa_Username;
		if ( isset ($_GET['action'] ) ) {
			switch($_GET['action'])  {
				case "logout":
				case "login":
				case "search":
				case "about":
				case "editbar":
					Core::$action = $_GET['action'];
					break;
				default:
					Core::Info('unknown "$action"' );
					break;
			}
			return 0;
		}
		Html::$_page="Wikimedia irc channel log browser, pick a channel from a menu";
	}
	
	public static function Auth() {
		global $hgwa_Username;
		return true;
	}

	public static function ListAll() {
		global $channelpath;
		$cn = array();
		if (! is_dir ($channelpath) ) {
			Html::$_page = "Unable to read contents of channel dir";
			return false;
		}
		if ($channellist = opendir($channelpath)) {
			while (($x = readdir($channellist)) != false ) {
				if ( strpos ( $x, "#" ) !== false ) {
					if ( $list = opendir($channelpath . "/" .$x) ) {
						$d = 0;
						while (($b = readdir($list)) != false ) {
							$d++;
						}	
						if ( $d >= 3 ) {
							$cn[$x] = $x;
						}
					}
				}
			}
		}
		sort ( $cn );
		return $cn;
	}
	
	public static function ETime() {
		global $hgwa_Exec;
		$d = ( microtime(true) - $hgwa_Exec );
		if ( $d <= 0 ) {
			return 0;
		}
		return (microtime(true) - $hgwa_Exec);
	}
	private static function Logout() {
		global $hgwa_Username;
		$hgwa_Username = null;
		Html::$_page = Core::GetMessage( 'logout-done' );
	        return;	
	}

	private static function cbLogin($data) {
		Html::$_page = $data;	
	}
	
	private static function Login() {
		global $hgwa_Username;
		Html::ChangeTitle( Core::GetMessage( 'title-login' ) );
		Core::Info ( "User login" );
		if ( $hgwa_Username != null ) {
			Core::Info( "User is already logged in" );
			Html::$_page = Core::GetMessage( 'loggedfail' );
			return 0;
		}
		ob_start("Html::getBuffer");
		include( "html/script_login" );
		ob_end_clean();
		return 0;
	}

	private static function getLogs($channel) {
		global $channelpath;
	                $cn = array();
	                if (! is_dir ($channelpath. "/" . "$channel") ) {
				Html::$_page = "Unable to read contents of channel dir";
			        return false;
			}
		        if ($channellist = opendir($channelpath. "/" . "$channel")) {
				while (($x = readdir($channellist)) != false ) {
				        if ( strpos ( $x, "txt" ) !== false ) {
			                                 $cn[$x] = $channelpath. "/". $channel ."/".$x;
			               	}
				}
			}else {
				Html::$_page = "error";
			}
	                return $cn;
	}

	private static function sanitize_text( $string ) {
	       	return htmlentities($string);	
	}

	private static function getResult( $query, $channel, $time ) {
		self::$no=0;
		$result = array();
		$logs = self::getLogs($channel);
		sort($logs);
		foreach ( $logs as $chan ) {
			$file =  @fopen( $chan, "r" );
			$line = 0;
			while (!feof($file)) {
				$line++;
				$buffer = fgets($file, 4096);
				if (strpos($buffer, $query)){
					$result[$line] = "<tr><td><a href=\"http://bots.wmflabs.org/~petrb/logs/". str_replace("#", "%23", str_replace("/mnt/public_html/petrb/logs/", "", $chan)) ."\">http://bots.wmflabs.org/~petrb/logs/". str_replace("/mnt/public_html/petrb/logs/", "", $chan) . "</a>:<br>On line $line:<br>" . str_replace($query, "<b>".$query. "</b>", Core::sanitize_text($buffer) ) . "<hr></td></tr>";	
					self::$no++;
				}
			}
			}	
		return $result;
	}

	public static function doAction() {
		switch (Core::$action)
		{
			case "logout":
				Core::Logout();
				break;
			case "login":
				Core::Login();
				break;
			case "search":
					if (!isset($_GET['channel']))
				       	{
						$chan = "#wikimedia";
					}
					else
					{	
						$chan = $_GET['channel'];
					}
					
					if (!isset ($_POST['query']) ) {
						$string = "";	
					}else {
					$string = $_POST['query'];
					}
					Html::$_page = "<form action=\"index.php?action=search&channel=". urlencode($chan) ."\" method=\"post\"><table border=\"1\"><tr><td>Query: </td><td width=100%><input name=\"query\" value=\"". $string ."\" style=\"width:600px;\" type=text></td></tr><tr><td>Channel:</td><td>". htmlentities($chan) ."</td></tr></table><input type=submit value=\"Search\"></form>";
				if (isset($_POST['query'])) {
					$item = self::getResult( $_POST['query'], $chan, "" );
					if (self::$no == 0) {
						Html::$_page .= "No results found";
						return true;
					}
					Html::$_page = Html::$_page . "Results:\n<br>Found ". self::$no ." in ". self::ETime() ." seconds\n<table border=0>";
					foreach ( $item as $result ) {
						Html::$_page = Html::$_page . $result;
					}
					Html::$_page = Html::$_page .  "</table>";
				}
				return true;
		}
		return false;
	}

	public static function Info($data) {
		global $hgwa_Debugging;
		if ( $hgwa_Debugging == false ) {
			return 0;
		}
		echo "<!-- Message: $data -->\n";
	}

	// Initialise
	public static function Initialise() {
		global $hgwa_Debugging, $hgwa_Version;
		Core::LoadLanguage();
		include("includes/renderapp.php");
		Core::getOverrides();
		Core::getAction();
		Core::doAction();
		if (Core::$action == null) {
			Core::Auth();
		}
	}

	// Load a web page
	public static function LoadContent() {
		global $hgwa_HtmlTitle, $hgwa_Version;
		Html::LoadContent();
		Core::Info( "Page generated ok, generation took " . Core::Etime() . " seconds" );
		return 1;
	}
}
