<?php
/**
 * Location Model
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
 * Location Model
 *
 * @package    drtavel.locations
 * @subpackage Components
 */
class LocationModelLocation extends JModel
{
	/**
	 * Gets the location
	 * @return multi database object 
	 */
	function getLocation($id = null)
	{
		if(!$id)
			return null;
			
		$db =& JFactory::getDBO();

		$query = "SELECT * FROM #__location WHERE `id` = $id";
		$db->setQuery( $query );
		$objLocation = $db->loadObject();
		
		return $objLocation;
	}
	
	
}
