<?php 
 
# Progressbar mediawiki extension
# Change log
# 0.1 - Initial version
#     - XSS vulnerability reported
# 0.2 - Probable fix.
# 0.3 - Fixed xss issue.

# Uh, the above is horseshit, there are still vulnerabilities

# Confirm MW environment
if (!defined('MEDIAWIKI')) die();
 
# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'Progressbar',
    'author'=>'Rohit Kumbhar',
    'url'=>'https://www.mediawiki.org/wiki/Extension:Progressbar',
    'description'=>' Allows to add a simple progressbar to a page ',
    'version'=>'0.3'
);
 
# Add Extension Functions
$wgExtensionFunctions[] = 'wfProgressbarSetup';
 
# Setup
function wfProgressbarSetup () {
    global $wgParser;
    $wgParser->setHook( 'progressbar', 'wfProgressbarParserHook' );
}
 
function wfProgressbarParserHook ( $text, $params = array(), $parser ) {
        global $wgScript;
 
        # Direction = horizontal,vertical. Default will be horizontal
        # Start = Numeric value. Default 0
        # End = Numeric value. Default 100
        # Current = Numeric value. Default 0
        # backgroundcolor = Background color for the filled up portion of the progressbar
        # Height = Height in px. Default 300 or 15 (horizontal or vertical)
        # Width = Width in px. Default 15 or 300 (horizontal or vertical)
        # $text is the caption

        # Horizontal if direction not specified
        $direction = $params['direction'] ? $params['direction'] : "horizontal";
 
        # Validate start
        $start_value = is_numeric($params["start"]) ? $params["start"] : 0;
 
        # Validate end
        $end_value = is_numeric($params['end']) ? $params['end'] : 100;
 
        # Validate current
        $current_value = is_numeric($params["current"]) ? $params["current"] : 0;
 
        $iStart = intval($end_value) - intval($start_value);
        $iCurr = intval($current_value) - intval($start_value);
        $actual_percent = round((100 * $iCurr) / $iStart,2);
        $iPercent = $actual_percent > 100 ? 100 : intval($actual_percent);
 
        # Background color. 
        $bgcolor = htmlspecialchars($params['backgroundcolor']);
 
        if($direction == "horizontal") {
 
                $out_ht =  $params["height"] ? $params["height"] : "15px";
                $out_wd = $params["width"] ? $params["width"] :  "300px";
                $in_ht = $params["height"] ? $params["height"] : "15px";
                $in_wd = $iPercent."%";
 
                $outerDivStyle = "width:".$out_wd.";height:".$out_ht.";border:1px solid black;";
                $innerDivStyle = "valign:bottom;background-color:".$bgcolor.";background-repeat:repeat;width:".$in_wd.";height:".$in_ht.";";    
                $labelStr = "<div style=\"width:".$out_wd.";text-align:center\" ><span style=\"float:left\">".$start_value."</span>Current: 
".$current_value."(".$actual_percent."%)<span style=\"float:right\">".$end_value."</span></div>";
                $divStr = $labelStr."<div style=\"width:".$out_wd.";\" ><div style=\"".$outerDivStyle."\" ><div style=\"".$innerDivStyle."\"></div></div><p 
style=\"text-align:center;\">".htmlspecialchars($text)."</p></div>";
 
        }
 
        if($direction == "vertical") {
 
                $out_ht =  $params["height"] ? $params["height"] : "300px";
                $out_wd = $params["width"] ? $params["width"] :  "15px";
                $in_spacer = (100 - $iPercent)."%";
                $in_ht = $iPercent."%";
                $in_wd = $params["width"] ? $params["width"] :  "15px";
 
                $outerDivStyle = "width:".$out_wd.";height:".$out_ht.";border:1px solid black;float:left";
                $innerDivStyle = "background-color:".$bgcolor.";background-repeat:repeat;width:".$in_wd.";height:".$in_ht.";";
                $spacerDivStyle= "width:".$in_width.";height:".$in_spacer.";";
 
                $out_wd_num = 10 + (2 * intval(str_replace("px","",strtolower($out_wd))));
                $barStr = "<div style=\"".$outerDivStyle."\" ><div style=\"".$spacerDivStyle."\"></div><div style=\"".$innerDivStyle."\"></div></div>";
                # I am really sorry for using tables here. Vertical align is a pita to get working. Took the easy way out.
                $divStr = "<table width=".$out_wd_num." cellpadding=0 cellspacing=2 ><tr><td width=".$out_wd." rowspan=3 >".$barStr."</td><td valign=top 
>".$end_value."</td></tr><tr><td valign=middle>Current:<br />".$current_value."(".$actual_percent."%)</td></tr><tr><td valign=bottom>".$start_value."</td></tr><tr><td 
colspan=2>".htmlspecialchars($text)."</td></tr></table>";
 
        }
 
        return $divStr;
 
}
