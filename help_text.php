<?php
/**
 * Shows helptext to the users
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
 * This Page Is Valid XHTML 1.0 Transitional! > 12 September 2005
 * 
 * @author Genmod Development Team
 * @package Genmod
 * @subpackage Admin
 * @version $Id$
 */

/**
 * Inclusion of the configuration file
*/
require "config.php";

if (!isset($help)) $help = "";

/**
 * Inclusion of the help text variables
*/
require ("helptext_vars.php");
print_simple_header($gm_lang["help_header"]);
print "<a name=\"top\"></a><span class=\"helpheader\">".$gm_lang["help_header"]."</span><br /><br />\n<div class=\"left\">\n";
$actione = "";

if (isset($action)) $actione = $action;
if (($help == "help_useradmin.php")&& ($actione == "edituser")) $help = "edit_useradmin_help";
if (($help == "help_login_register.php")&& ($actione == "pwlost")) $help = "help_login_lost_pw.php";
if ($help == "help_contents_help") {
	if ($gm_user->userIsAdmin()) {
		$help = "admin_help_contents_help";
		print_text("admin_help_contents_head_help");
	}
	else print_text("help_contents_head_help");
	print_help_index($help);
}
else {
	$text = print_text($help, 0, 1);
	print $text;
	if ($gm_user->UserIsAdmin()) {
		$stat = GetLangvarStatus($help, $LANGUAGE, $type="help");
		// Already translated, edit it
		if ($stat == 0) print "<br /><a href=\"#\" onclick=\"window.name='help'; window.open('editlang_edit.php?ls01=$help&amp;ls02=$help&amp;language2=$LANGUAGE&amp;file_type=help_text&amp;realtime=true', '', 'top=50,left=50,width=700,height=400,scrollbars=1,resizable=1');\">".$gm_lang["thishelp_edit_trans"]."</a><br />";
		// Add translation in the current language
		if ($stat == 1) print "<br /><a href=\"#\" onclick=\"window.name='help'; window.open('editlang_edit.php?ls01=$help&amp;ls02=-1&amp;language2=$LANGUAGE&amp;file_type=help_text&amp;realtime=true', '', 'top=50,left=50,width=700,height=400,scrollbars=1,resizable=1');\">".$gm_lang["thishelp_add_trans"]."</a><br />";
		// Add English helptext, only if the var is truly not found (may be in helptext_vars)
		if ($stat == 2 && stristr($gm_lang['help_not_exist'], $text)) {
			print "<br /><a href=\"#\" onclick=\"window.name='help'; window.open('editlang_edit.php?ls01=$help&amp;ls02=-1&amp;language2=english&amp;file_type=help_text&amp;realtime=true', '', 'top=50,left=50,width=700,height=400,scrollbars=1,resizable=1');\">".$gm_lang["thishelp_add_text"]."</a><br />";
		}
	}
}

print "\n</div>\n";
print "<div class=\"left\">";
print "<a href=\"#top\" title=\"".$gm_lang["move_up"]."\">$UpArrow</a><br />";
print "<a href=\"help_text.php?help=help_contents_help\"><b>".$gm_lang["help_contents"]."</b></a><br />";
print "<a href=\"#\" onclick=\"window.close();\"><b>".$gm_lang["close_window"]."</b></a>";
print "</div>";
print_simple_footer();
?>
