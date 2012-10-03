<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
JHTML::stylesheet('locStyles.css','components/com_locations/css/',array('media'=>'all'));

//var_dump($this->params);
$columns = $this->params->get('columns', 2);
$thumbWidth = $this->params->get('thumbWidth', 64);
?>

<div class='regionList'>
<a href='/index.php?option=com_locations&view=locations'>Central</a>
<?php 

foreach($this->objRegions as $objRegion){
	echo " | <a href='/index.php?option=com_locations&view=locations&rid={$objRegion->id}'>
	{$objRegion->region_title}</a>";

}

?>

</div>
<div class='findLocationHeader' >Find a Location</div>
<div class='findLocSubHeader'>Choose the Indiana eye care location nearest you.</div>
<?php
$regionTitle = null;

foreach ($this->objLocations as $objLocation) {

		if($regionTitle != $objLocation->region_title) {
			if($regionTitle === null) {
				echo "<div class='regionTitle'>{$objLocation->region_title}</div>
				<table class='locationsList'><tr>";
			} else {
				echo "</tr></table><br /><div class='regionTitle'>{$objLocation->region_title}</div>
				<table class='locationsList'><tr>";
			}
			$regionTitle = $objLocation->region_title;
			$columnCount = 1;
			
		}
		if($columnCount >= $columns) {
			echo "</tr><tr>";
			$columnCount = 1;
		}
		
		echo "<th valign='top'>";
		echo "<a href='/index.php?option=com_locations&view=location&id={$objLocation->id}'>";
		echo "<img width='$thumbWidth' src='{$objLocation->photo_thumb_url}' /></a></th>";
		echo "<td valign='top'><a class='locNameLink' href='/index.php?option=com_locations&view=location&id={$objLocation->id}'>";
		echo $objLocation->location_name."</a><br />"; 
		echo $objLocation->address1."<br />";
		if(!empty($objLocation->address2))
			echo $objLocation->address2."<br />";
		echo $objLocation->city.", ".$objLocation->state." ".$objLocation->zip."<br />";
		echo $objLocation->phone."<br />";
		echo "<a href='/index.php?option=com_locations&view=location&id={$objLocation->id}'>Hours|Map|Schedule Appt.</a>";
		
		echo "</td>";
		$columnCount++;
		
	}//end foreach
	echo "</tr></table>";
	
	

