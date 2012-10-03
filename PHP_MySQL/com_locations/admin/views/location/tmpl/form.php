<?php defined('_JEXEC') or die('Restricted access'); 
/* data model:
 * 	$this->_data = new stdClass();
			$this->_data->id = 0;
			$this->_data->region_title = null; // string;ordering, e.g. ('Indy North;1')
			$this->_data->location_name = null; // (e.g. 'Zionsville')
			$this->_data->address = null;
			$this->_data->city = null;
			$this->_data->state = null;
			$this->_data->zip = null;
			$this->_data->phone = null;
			$this->_data->map_link = null;
			$this->_data->hours = null; // string array Monday = index 0, semicol separate rows ('12:00-6:00'; etc... )
			$this->_data->photo_main_url = null;
			$this->_data->photo_carousel_list = null; //(string csv array)
			$this->_data->offerpage_url = null;
			
 */
if(!$this->isNew) {
$region_data = explode(';',$this->location->region_title);
$data = $this->location;
$carusel_photos = explode(',', $this->location->photo_carousel_list); //photo_carousel_list
$aHours = explode(',',$this->location->hours); //monday is index 0
}


?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="region">
					<?php echo JText::_( 'Region Title' ); ?>:
				</label>
			</td>
			<td>
				<input class="text" type="text" name="region" id="region" size="25" maxlength="250" 
				value="<?php if(!$this->isNew) echo $region_data[0]; ?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="region_order">
					<?php echo JText::_( 'Region Order' ); ?>:
				</label>
			</td>
			<td>
				<input class="text" type="text" name="region_order" id="region_order" size="3" maxlength="3" 
				value="<?php if(!$this->isNew) echo $region_data[1]; ?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="location_name">
					<?php echo JText::_( 'Location Name' ); ?>:
				</label>
			</td>
			<td>
				<input class="text" type="text" name="location_name" id="location_name" size="25" maxlength="250" 
				value="<?php if(!$this->isNew) echo $data->location_name; ?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="address1">
					<?php echo JText::_( 'Street Address' ); ?>:
				</label> 
			</td>
			<td>
				<input class="text" type="text" name="address1" id="address1" size="25" maxlength="250" 
				value="<?php if(!$this->isNew) echo $data->address1; ?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="address2">
					<?php echo JText::_( 'Street Address 2nd line' ); ?>:
				</label>
			</td>
			<td>
				<input class="text" type="text" name="address1" id="address2" size="25" maxlength="250" 
				value="<?php if(!$this->isNew) echo $data->address2; ?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="city">
					<?php echo JText::_( 'City & State' ); ?>:
				</label>
			</td>
			<td>
				<input class="text" type="text" name="city" id="city" size="45" maxlength="250" 
				value="<?php if(!$this->isNew) echo $data->city; ?>" />
				,&nbsp;
				<input class="text" type="text" name="state" id="state" size="3" maxlength="2" 
				value="<?php if(!$this->isNew) echo $data->state; ?>" />
				
			</td>
		</tr>
			<tr>
			<td width="100" align="right" class="key">
				<label for="zip">
					<?php echo JText::_( 'Zip Code' ); ?>:
				</label>
			</td>
			<td>
				<input class="text" type="text" name="zip" id="zip" size="12" maxlength="10" 
				value="<?php if(!$this->isNew) echo $data->zip; ?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="phone">
					<?php echo JText::_( 'Phone' ); ?>:
				</label>
			</td>
			<td>
				<input class="text" type="text" name="phone" id="phone" size="12" maxlength="15" 
				value="<?php if(!$this->isNew) echo $data->zip; ?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="map_link">
					<?php echo JText::_( 'Map Link URL' ); ?>:
				</label>
			</td>
			<td>
				<input class="text" type="text" name="map_link" id="map_link" size="45" maxlength="250" 
				value="<?php if(!$this->isNew) echo $data->map_link; ?>" />
			</td>
		</tr>
			<tr>
			<td width="100" align="right" class="key">
				<label for="offerpage_url">
					<?php echo JText::_( 'Offer Page URL' ); ?>:
				</label>
			</td>
			<td>
				<input class="text" type="text" name="offerpage_url" id="offerpage_url" size="45" maxlength="250" 
				value="<?php if(!$this->isNew) echo $data->offerpage_url; ?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="photo_main_url">
					<?php echo JText::_( 'URL of main photo' ); ?>:
				</label>
			</td>
			<td>
				<input class="text" type="text" name="photo_main_url" id="photo_main_url" size="45" maxlength="250" 
				value="<?php if(!$this->isNew) echo $data->photo_main_url; ?>" />
			</td>
		</tr>
		
		<tr>
			<td width="100" align="right" class="key">
				<label for="photo_thumb_url">
					<?php echo JText::_( 'Thumbnail for locations list' ); ?>:
				</label>
			</td>
			<td>
				<input class="text" type="text" name="photo_thumb_url" id="photo_thumb_url" size="45" maxlength="250" 
				value="<?php if(!$this->isNew) echo $data->photo_thumb_url; ?>" />
			</td>
		</tr>
		
		<tr>
			<td width="100" align="right" class="key">
				<label for="photo_carousel_list">
					<?php echo JText::_( 'Additional photos (for carusel)' ); ?>:
				</label>
			</td>
			<td>
			<?php 
			if(!$this->isNew)
				for($i=0; $i < 5; $i++) { ?>
				<input class="text" type="text" name="photo_carousel_list[]" id="photo_carousel_list" size="45" maxlength="250" 
				value="<?php echo isset($carusel_photos[$i]) ? $carusel_photos[$i] : ''; ?>" /><br />
				<?php } 
			else { 
				for($i=0; $i < 5; $i++) { ?>
				<input class="text" type="text" name="photo_carousel_list[]" id="photo_carousel_list" 
						size="45" maxlength="250"/><br />
			<?php }//end for
			 }//end else?>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="hours">
					<?php echo JText::_( 'Hours of Operation' ); ?>:
				</label>
			</td>
			<td>
			<table>
			<?php 
			$aWDays = array(0 => 'Monday',
				1 => 'Tuesday',
				2 => 'Wednesday',
				3 => 'Thursday',
				4 => 'Friday',
				5 => 'Saturday',
				6 => 'Sunday');
			
			for($i=0; $i < 7; $i++) { ?>
				<tr><td>
				<?php echo $aWDays[$i]; ?> </td><td>
				<input class="text" type="text" name="hours[]" id="hours" size="25" maxlength="50" 
				value="<?php if(!$this->isNew) echo $aHours[$i]; ?>" /></td></tr>
				<?php }  ?>
				
			</table>	
			</td>
		</tr>
		
		
	</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_locations " />
<input type="hidden" name="id" value="<?php echo $this->location->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="location" />
</form>
