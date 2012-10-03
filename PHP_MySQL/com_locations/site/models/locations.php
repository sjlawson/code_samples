<?php
/**
 * Locations Model
 * 
 * @package    drtavel.locations
 * @subpackage Components
 * @link http://fatatom.com
 * @license		private
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

/**
 * Locations Model
 *
 * @package    drtavel.locations
 * @subpackage Components
 */

class LocationModelLocations extends JModel
{
	/**
	 * Gets the locations list
	 * @return multi database object array
	 */

	
	function getLocations() {
		$db =& JFactory::getDBO();

		$query = "SELECT * FROM #__location";
		$db->setQuery( $query );
		$objLocations = $db->loadObjectList();
		
		return $objLocations;
	}
	
}