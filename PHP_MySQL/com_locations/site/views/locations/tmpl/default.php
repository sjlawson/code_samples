<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php
foreach ($this->objLocations as $objLocation) {
	echo "<a href='/index.php?option=com_locations&view=location&id={$objLocation->id}'>";
	echo $objLocation->id." - ". $objLocation->location_name.
	"</a><br />"; 

}
