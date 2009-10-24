<?php
/**
 * Popup window for Editing news items
 *
 * Genmod: Genealogy Viewer
 * Copyright (C) 2005 - 2008 Genmod Development Team
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package Genmod
 * @version $Id$
 */

/**
 * Inclusion of the configuration file
*/
require("config.php");

/**
 * Inclusion of the FCK Editor
*/
$useFCK = file_exists("./modules/FCKeditor/fckeditor.php");
if($useFCK){
	include("./modules/FCKeditor/fckeditor.php");
}

$username = $gm_username;
if (empty($username)) {
	PrintSimpleHeader("");
	print $gm_lang["access_denied"];
	PrintSimpleFooter();
	exit;
}

if (!isset($action)) $action="compose";

PrintSimpleHeader($gm_lang["edit_news"]);

if (empty($uname)) $uname = get_gedcom_from_id($GEDCOMID);

if ($action=="compose") {
	print '<span class="subheaders">'.$gm_lang["edit_news"].'</span>';
	?>
	<script language="JavaScript" type="text/javascript">
	<!--
		function checkForm(frm) {
			if (frm.title.value=="") {
				alert('<?php print $gm_lang["enter_title"]; ?>');
				document.messageform.title.focus();
				return false;
			}
			<?php if (! $useFCK) { //will be empty for FCK. FIXME, use FCK API to check for content.
			?>
			if (frm.text.value=="") {
				alert('<?php print $gm_lang["enter_text"]; ?>');
				document.messageform.text.focus();
				return false;
			}
			<?php } ?>
			return true;
		}
	//-->
	</script>
	<?php
	print "<br /><form name=\"messageform\" method=\"post\" onsubmit=\"return checkForm(this);";
	print "\">\n";
	if (isset($news_id)) {
		$news = NewsController::getNewsItem($news_id);
	}
	else {
		$news_id="";
		$news = new News();
		$news->username = $uname;
		$news->date = time()-$_SESSION["timediff"];
		$news->title = "";
		$news->text = "";
	}
	print "<input type=\"hidden\" name=\"action\" value=\"save\" />\n";
	print "<input type=\"hidden\" name=\"uname\" value=\"".$news->username."\" />\n";
	print "<input type=\"hidden\" name=\"news_id\" value=\"".$news->id."\" />\n";
	print "<input type=\"hidden\" name=\"date\" value=\"".$news->date."\" />\n";
	print "<table>\n";
	print "<tr><td align=\"right\">".$gm_lang["title"]."</td><td><input type=\"text\" name=\"title\" size=\"50\" value=\"".$news->title."\" /><br /></td></tr>\n";
	print "<tr><td valign=\"top\" align=\"right\">".$gm_lang["article_text"]."<br /></td>";
	print "<td>";
	if ($useFCK) { // use FCKeditor module
		$trans = get_html_translation_table(HTML_SPECIALCHARS);
		$trans = array_flip($trans);
		$news->text = strtr($news->text, $trans);
		$news->text = nl2br($news->text);
		
		$oFCKeditor = new FCKeditor('text') ;
		$oFCKeditor->BasePath =  './modules/FCKeditor/';
		$oFCKeditor->Value = $news->text;
		$oFCKeditor->Width = 700;
		$oFCKeditor->Height = 450;
		$oFCKeditor->Config['AutoDetectLanguage'] = false ;
		$oFCKeditor->Config['DefaultLanguage'] = $language_settings[$LANGUAGE]["lang_short_cut"];
		$oFCKeditor->Create() ;
	} else { //use standard textarea
		print "<textarea name=\"text\" cols=\"80\" rows=\"10\">".$news->text."</textarea>";
	}
	print "<br /></td></tr>\n";
	print "<tr><td></td><td><input type=\"submit\"  value=\"".$gm_lang["save"]."\" /></td></tr>\n";
	print "</table>\n";
	print "</form>\n";
}
else if ($action=="save") {
	$news = new News($news_id);
	$date=time()-$_SESSION["timediff"];
	if (empty($title)) $title="No Title";
	if (empty($text)) $text="No Text";
	$news->username = $uname;
	$news->date=$date;
	$news->title = $title;
	$news->text = $text;
	if ($news->addNews()) {
		print $gm_lang["news_saved"];
	}
}
else if ($action=="delete") {
	if (NewsController::DeleteNews($news_id)) print $gm_lang["news_deleted"];
}
print "<center><br /><br /><a href=\"#\" onclick=\"if (window.opener.refreshpage) window.opener.refreshpage(); window.close();\">".$gm_lang["close_window"]."</a><br /></center>";

PrintSimpleFooter();
?>