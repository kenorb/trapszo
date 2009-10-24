<?php
/**
 * Controller for the Descendancy Page
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
 *
 * @package Genmod
 * @subpackage Charts
 * @version $Id$
 */

if (stristr($_SERVER["SCRIPT_NAME"],basename(__FILE__))) {
	require "../../intrusion.php";
}
 
class DescendancyController extends ChartController {
	
	public $classname = "DescendancyController";	// Name of this class
	private $personcount = 0;
	private $dabo = array();						// Helper for keeping track of SOSA numbers

	
	public function __construct() {
		
		parent::__construct();

		if (!isset($_REQUEST["num_generations"]) || $_REQUEST["num_generations"] == "") $this->num_generations = 2;
		else $this->num_generations = $_REQUEST["num_generations"];
		
		if ($this->num_generations > GedcomConfig::$MAX_DESCENDANCY_GENERATIONS) {
			$this->num_generations = GedcomConfig::$MAX_DESCENDANCY_GENERATIONS;
		}
		
		if ($this->num_generations < 2) {
			$this->num_generations = 2;
		}
		
		if (!isset($_REQUEST["box_width"]) || $_REQUEST["box_width"] == "") $this->box_width = "100";
		else $this->box_width = $_REQUEST["box_width"];
		$this->box_width = max($this->box_width, 50);
		$this->box_width = min($this->box_width, 300);
		
		global $box_width;
		$box_width = $this->box_width;
		
		if (isset($_REQUEST["rootid"])) $this->xref = $_REQUEST["rootid"];
		$this->xref = ChartFunctions::CheckRootId(CleanInput($this->xref));
	}

	public function __get($property) {
		switch($property) {
			default:
				return parent::__get($property);
				break;
		}
	}

	protected function GetPageTitle() {
		global $gm_lang;
		
		if (is_null($this->pagetitle)) {
			$this->pagetitle = $this->GetRootObject()->name;
			if (GedcomConfig::$SHOW_ID_NUMBERS) $this->pagetitle .= " - ".$this->xref;
			$this->pagetitle .= " - ".$gm_lang["descend_chart"];
		}
		return $this->pagetitle;
	}
	
	/**
	 * print a child descendancy
	 *
	 * @param string $pid individual Gedcom Id
	 * @param int $depth the descendancy depth to show
	 */
	public function PrintChildDescendancy($pid, $depth) {
		global $gm_lang;
		global $GM_IMAGES, $Dindent;
	
		// print child
		print "<li>";
		print "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td style=\"vertical-align:middle;\">";
		if ($depth == $this->num_generations) print "<img src=\"".GM_IMAGE_DIR."/".$GM_IMAGES["spacer"]["other"]."\" height=\"2\" width=\"$Dindent\" border=\"0\" alt=\"\" /></td><td style=\"vertical-align:middle;\">\n";
		else print "<img src=\"".GM_IMAGE_DIR."/".$GM_IMAGES["hline"]["other"]."\" height=\"2\" width=\"$Dindent\" border=\"0\" alt=\"\" /></td><td style=\"vertical-align:middle;\">\n";
		
		$child = Person::GetInstance($pid);
		PersonFunctions::PrintPedigreePerson($child, 1, true, '', $this->view);
		print "</td>";
	
		// check if child has parents and add an arrow
		print "<td>&nbsp;</td>";
		print "<td>";
		$pfam = Family::GetInstance($child->primaryfamily);
		$parid = "";
		if ($pfam->husb_id != "") $parid = $pfam->husb_id;
		else if ($pfam->wife_id != "") $parid = $pfam->wife_id;
		if ($parid!="") {
			ChartFunctions::PrintUrlArrow($parid.$this->personcount.$pid, "?rootid=".$parid."&amp;num_generations=".$this->num_generations."&amp;chart_style=".$this->chart_style."&amp;show_details=".$this->show_details."&amp;box_width=".$this->box_width, PrintReady($gm_lang["start_at_parents"]."&nbsp;-&nbsp;".$pfam->descriptor), 2);
			$this->personcount++;
		}
	
		// d'Aboville child number
		$level = $this->num_generations - $depth;
		if ($this->show_full) print "<br /><br />&nbsp;";
		print "<span dir=\"ltr\">"; //needed so that RTL languages will display this properly
		if (!isset($this->dabo[$level])) $this->dabo[$level]=0;
		$this->dabo[$level]++;
		$this->dabo[$level+1]=0;
		for ($i=0; $i<=$level;$i++) print $this->dabo[$i].".";
		print "</span>";
		print "</td></tr>";
	
		// empty descendancy
		print "</table>";
		print "</li>\r\n";
		if ($depth<1) return;
	
		if ($child->disp) {
			foreach($child->fams as $key => $sfamid) {
				$sfam =& Family::GetInstance($sfamid);
				$this->PrintFamilyDescendancy($pid, $sfam, $depth);
			}
		}
	}
	/**
	 * print a family descendancy
	 *
	 * @param string $pid individual Gedcom Id
	 * @param string $famid family Gedcom Id
	 * @param int $depth the descendancy depth to show
	 */
	public function PrintFamilyDescendancy($pid, &$family, $depth) {
		global $gm_lang, $bwidth;
		// Theme dependent globals
		global $GM_IMAGES, $Dindent;
	
		if ($family->isempty) return;
	
		if ($family->husb_id != "" || $family->wife_id != "") {
	
			// spouse id
			if ($family->wife_id != $pid) $spouse = $family->wife;
			else $spouse = $family->husb;
	
			// print marriage info
			print "<li>";
			print "<img src=\"".GM_IMAGE_DIR."/".$GM_IMAGES["spacer"]["other"]."\" height=\"2\" width=\"$Dindent\" border=\"0\" alt=\"\" />";
			print "<span class=\"details1\" style=\"white-space: nowrap; \" >";
			print "<a href=\"#\" onclick=\"expand_layer('".$family->xref.$this->personcount."'); return false;\" class=\"top\"><img id=\"".$family->xref.$this->personcount."_img\" src=\"".GM_IMAGE_DIR."/".$GM_IMAGES["minus"]["other"]."\" align=\"middle\" hspace=\"0\" vspace=\"3\" border=\"0\" alt=\"".$gm_lang["view_family"]."\" /></a> ";
			if ($family->disp) FactFunctions::PrintSimpleFact($family->marr_fact, false, false); 
			print "</span>";
	
			// print spouse
			print "<ul style=\"list-style: none; display: block;\" id=\"".$family->xref.$this->personcount."\">";
			print "<li>";
			print "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>";
			PersonFunctions::PrintPedigreePerson($spouse, 1, true, '', $this->view);
			print "</td>";
	
			// check if spouse has parents and add an arrow
			print "<td>&nbsp;</td>";
			print "<td>";
			if (is_object($spouse) && $spouse->disp) {
				$pfam = Family::GetInstance($spouse->primaryfamily);
				$parid = "";
				if ($pfam->husb_id != "") $parid = $pfam->husb_id;
				else if ($pfam->wife_id != "") $parid = $pfam->wife_id;
				if ($parid!="") {
					ChartFunctions::PrintUrlArrow($parid.$this->personcount.$spouse->xref, "?rootid=".$parid."&amp;num_generations=".$this->num_generations."&amp;chart_style=".$this->chart_style."&amp;show_details=".$this->show_details."&amp;box_width=".$this->box_width, PrintReady($gm_lang["start_at_parents"]."&nbsp;-&nbsp;".$pfam->descriptor), 2);
				}
			}
			if ($this->show_full) print "<br /><br />&nbsp;";
			print "</td></tr>";
			$this->personcount++;
	
			// children
			print "<tr><td colspan=\"3\" class=\"details1\" >&nbsp;";
			if ($family->disp) {
				if ($family->children_count < 1) print $gm_lang["no_children"];
				else print GM_FACT_NCHI.": ".$family->children_count;
			}
			print "</td></tr></table>";
			print "</li>\r\n";
			if ($family->disp) {
				foreach ($family->children as $indexval => $child) {
					$this->PrintChildDescendancy($child->xref, $depth-1);
				}
			}
			print "</ul>\r\n";
			print "</li>\r\n";
		}
	}
	/**
	 * print a child family
	 *
	 * @param string $pid individual Gedcom Id
	 * @param int $depth the descendancy depth to show
	 */
	public function PrintChildFamily($pid, $depth, $label="1.", $gpid="") {
	
		if ($depth<1) return;
		$person = Person::GetInstance($pid);
		
		foreach($person->spousefamilies as $key => $fam) {
			ChartFunctions::PrintSosaFamily($fam, "", -1, $label, $pid, $gpid, $this->view);
			$i=1;
			foreach ($fam->children as $childkey => $child) {
				$this->PrintChildFamily($child->xref, $depth-1, $label.($i++).".", $pid);
			}
		}
	}
}
?>