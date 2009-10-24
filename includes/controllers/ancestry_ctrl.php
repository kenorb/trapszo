<?php
/**
 * Controller for the Ancestry Page
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
 
class AncestryController extends ChartController {
	
	public $classname = "AncestryController";	// Name of this class
	
	public function __construct() {
		
		parent::__construct();
		if ($this->action = "") $this->action = "find";
		
		if (!isset($_REQUEST["num_generations"]) || $_REQUEST["num_generations"] == "") $this->num_generations = GedcomConfig::$DEFAULT_PEDIGREE_GENERATIONS;
		else $this->num_generations = $_REQUEST["num_generations"];
		
		if ($this->num_generations > GedcomConfig::$MAX_PEDIGREE_GENERATIONS) {
			$this->num_generations = GedcomConfig::$MAX_PEDIGREE_GENERATIONS;
			$this->max_generation = true;
		}
		
		if ($this->num_generations < 2) {
			$this->num_generations = 2;
			$this->min_generation = true;
		}
		
		if (!isset($_REQUEST["box_width"]) || $_REQUEST["box_width"] == "") $this->box_width = "100";
		else $this->box_width = $_REQUEST["box_width"];
		$this->box_width = max($this->box_width, 50);
		$this->box_width = min($this->box_width, 300);
		
		global $box_width;
		$box_width = $this->box_width;
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
			$this->pagetitle .= " - ".$gm_lang["ancestry_chart"];
		}
		return $this->pagetitle;
	}
	
	/**
	 * print a child ascendancy
	 *
	 * @param string $pid individual Gedcom Id
	 * @param int $sosa child sosa number
	 * @param int $depth the ascendancy depth to show
	 */
	public function PrintChildAscendancy($person, $sosa, $depth) {
		global $gm_lang;
		global $GM_IMAGES, $Dindent;
		
		static $pidarr;
		if (!isset($pidarr)) $pidarr = array();
	
		// child
		print "<li>";
		print "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td style=\"vertical-align:middle;\"><a name=\"sosa".$sosa."\"></a>";
		$new = (!isset($pidarr[$person->xref]));
		if ($sosa==1) print "<img src=\"".GM_IMAGE_DIR."/".$GM_IMAGES["spacer"]["other"]."\" height=\"2\" width=\"".$Dindent."\" border=\"0\" alt=\"\" /></td><td>\n";
		else print "<img src=\"".GM_IMAGE_DIR."/".$GM_IMAGES["hline"]["other"]."\" height=\"2\" width=\"".$Dindent."\" border=\"0\" alt=\"\" /></td><td>\n";
		PersonFunctions::PrintPedigreePerson($person, 1, 1, 0, 1, $this->view, $this->num_generations, $this->chart_style);
		
		print "</td>";
		print "<td style=\"vertical-align:middle;\">";
		if ($sosa>1) ChartFunctions::PrintUrlArrow($person->xref, "?rootid=".$person->xref."&amp;num_generations=".$this->num_generations."&amp;show_details=".$this->show_details."&amp;box_width=".$this->box_width."&amp;chart_style=".$this->chart_style."", $gm_lang["ancestry_chart"], 3);
		print "</td>";
		print "<td class=\"details1\" style=\"vertical-align:middle;\">&nbsp;<span class=\"person_box". (($sosa==1) ? "NN" : (($sosa%2) ? "F" : "")) . "\">&nbsp;$sosa&nbsp;</span>&nbsp;";
		print "</td><td class=\"details1\" style=\"vertical-align:middle;\">";
		$relation ="";
		if (!$new) $relation = "<br />[=<a href=\"#sosa".$pidarr[$person->xref]."\">".$pidarr[$person->xref]."</a> - ".GetSosaName($pidarr[$person->xref])."]";
		else $pidarr[$person->xref] = $sosa;
		print GetSosaName($sosa).$relation;
		print "</td>";
		print "</tr></table>";
	
//		// parents
		foreach($person->childfamilies as $key => $family) {
			if ($family->showprimary || $family->pedigreetype == "") {
				if (($family->husb_id != "" || $family->wife_id != "" || GedcomConfig::$SHOW_EMPTY_BOXES) && $new && $depth>0) {
					// print marriage info
					print "<span class=\"details1\" style=\"white-space: nowrap;\" >";
					print "<img src=\"".GM_IMAGE_DIR."/".$GM_IMAGES["spacer"]["other"]."\" height=\"2\" width=\"$Dindent\" border=\"0\" align=\"middle\" alt=\"\" /><a href=\"javascript: ".$gm_lang["view_family"]."\" onclick=\"expand_layer('sosa_".$sosa."'); return false;\" class=\"top\"><img id=\"sosa_".$sosa."_img\" src=\"".GM_IMAGE_DIR."/".$GM_IMAGES["minus"]["other"]."\" align=\"middle\" hspace=\"0\" vspace=\"3\" border=\"0\" alt=\"".$gm_lang["view_family"]."\" /></a> ";
					print "&nbsp;<span class=\"person_box\">&nbsp;".($sosa*2)."&nbsp;</span>&nbsp;".$gm_lang["and"];
			 		print "&nbsp;<span class=\"person_boxF\">&nbsp;".($sosa*2+1)." </span>&nbsp;";
					if ($family->disp) FactFunctions::PrintSimpleFact($family->marr_fact, false, false); 
					print "</span>";
					// display parents recursively
					print "<ul style=\"list-style: none; display: block;\" id=\"sosa_$sosa\">";
					if (is_object($family->husb)) $this->PrintChildAscendancy($family->husb, $sosa*2, $depth-1);
					if (is_object($family->wife)) $this->PrintChildAscendancy($family->wife, $sosa*2+1, $depth-1);
					print "</ul>\r\n";
				}
			}
		}
		print "</li>\r\n";
	}
}
?>