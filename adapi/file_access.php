<?php

function read_audience_index() {
   $myfile = fopen("audience_index.sys", "r");
	$ar = array();
	if (file_exists("audience_index.sys")) {
		while(!feof($myfile)) {
			$line = fgets($myfile);
			if (trim($line)!='') {
				//echo $line;
				$pieces = explode("||", $line );
				//echo "read~~~~~".$line."\n";
				$pp = preg_replace('~[\r\n]+~', '', $pieces[1]);
				$ar[$pieces[0]] = $pp;
			}
		}
		fclose($myfile);
	}
	return $ar;			// return a array
}

function write_audience_index($audience_array) {
	$myfile = fopen("audience_index.sys", "w");
	foreach ($audience_array as $a_key => $a_name) {
	    $txt = $a_key."||".$a_name."\n";
	    //echo "write~~~~~".$txt."\n";
		fwrite($myfile, $txt);
	}
	fclose($myfile);
}

function read_ad_index() {
   $myfile = fopen("ad_index.sys", "r");
	if (file_exists("ad_index.sys")) {
		while(!feof($myfile)) {
			$line = fgets($myfile);
			if (trim($line)!='') {
				//echo $line;
				$spieces = explode("||", $line );
				if ($spieces[0]=="CAMPAIGN") {				// it is campaign information
					# code...
					$camp_ar[$spieces[1]] = array (
						$spieces[2],			// append campaign_id, campaign_name		
					);	
				} elseif ($spieces[0]=="ADSET") {
					# code...
					$adset_ar[$spieces[1]] = array (  	// adset id
						$spieces[2],						// campaign_id	
						$spieces[3],						// adset_name		
					);		
				} elseif ($spieces[0]=="ADGROUP") {
					# code...
					$adgroup_ar[$spieces[1]] = array (  	// adgrooup id
						$spieces[2],						// campaign_id		
						$spieces[3],						// adset id	
						$spieces[4],						// adgrop_name	
					);		
				} elseif ($spieces[0]=="CREATIVE") {
					# code...
					$creative_ar[$spieces[1]] = array (  // creative id
						$spieces[2],						// campaign_id		
						$spieces[3],						// adset_id	
						$spieces[3],						// adgroup_id	
						$spieces[4],						// creative_name	
					);		
				} else {
					// doesn't fprform any process
					// data should be a exception situation.
				}
			}
		}
		fclose($myfile);
	}
	return array($camp_ar, $adset_ar, $adgroup_ar, $creative_ar);			// return a array
}

function write_ad_index($camp_ar, $adset_ar, $adgroup_ar, $creative_ar) {
	$myfile = fopen("ad_index.sys", "w");
	if ($camp_ar !=null) {
		foreach ($camp_ar as $a_key => $a_name) {
		    $txt = "CAMPAIGN"."||".$a_key."||".$a_name[0]."\n";				// or use \r to instead of
			fwrite($myfile, $txt);
		}
	}
	if ($adset_ar !=null) {
		foreach ($adset_ar as $a_key => $a_name) {
		    $txt = "ADSET"."||".$a_key."||".$a_name[0]."||".$a_name[1]."\n";
			fwrite($myfile, $txt);
		}
	}
	if ($adgroup_ar !=null) {
		foreach ($adgroup_ar as $a_key => $a_name) {
		    $txt = "ADGROUP"."||".$a_key."||".$a_name[0]."||".$a_name[1]."||".$a_name[2]."\n";
			fwrite($myfile, $txt);
		}
	}
	if ($creative_ar !=null) {
		foreach ($creative_ar as $a_key => $a_name) {
		    $txt = "CREATIVE"."||".$a_key."||".$a_name[0]."||".$a_name[1]."||".$a_name[2]."||".$a_name[3]."\n";
			fwrite($myfile, $txt);
		}
	}
	fclose($myfile);
}


function read_append_audience_data($filename) {
   $myfile = fopen($filename, "r");
	$ar = array();
	if (file_exists($filename)) {
		while(!feof($myfile)) {
			$line = fgets($myfile);
			if ($line[0]!="#") {
				if (trim($line)!='') {
					//echo $line;
					$line = preg_replace('~[\r\n]+~', '', $line);
					$pieces = explode(",", $line );
					//echo "read~~~~~".$line."\n";
					$ar[] =$pieces;
				}
			}
		}
		fclose($myfile);
	}
	return $ar;			// return a array
}

function read_append_ad_data($filename) {
   $myfile = fopen($filename, "r");
	$ar = array();
	if (file_exists($filename)) {
		while(!feof($myfile)) {
			$line = fgets($myfile);
			if ($line[0]!="#") {
				if (trim($line)!='') {
					//echo $line;
					$line = preg_replace('~[\r\n]+~', '', $line);
					$pieces = explode(",", $line );
					//echo "read~~~~~".$line."\n";

					$ar[] =$pieces;
					switch ($pieces[0]){
						case "CMP":
							 //echo "campaign";
							 $cmp_act_ar[] = array (
							 	$pieces[1],
							 	$pieces[2],
							 );
							break;
						case "ADS":
							 //echo "adset";
							break;
						case "CRT":
							 //echo "creative";
							break;
						case "ADG":
							 //echo "adgroup";
					}
				}
			}
		}
		fclose($myfile);
	}
	//print_r($cmp_act_ar);
	return array($cmp_act_ar);			// return a array
}

function search_ad_array($ad_array,$index,$value) {
	foreach ($ad_array as $array_key => $array_item) {
		if (trim($array_item[$index])==$value) {
			//echo "**####*\n";
			return $array_key; 
			break;
		}
	}
			
	return false;
}

function del_ad_array($ad_array,$index,$value) {
	foreach ($ad_array as $array_key => $array_item) {
		if (trim($array_item[$index])==$value) unset($array_key[$array_key]); 
	}
}
