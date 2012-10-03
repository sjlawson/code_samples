<?php
/**
 * Locations View for drtavel.locations Component
 * 
 * @package    drtavel.locations
 * @subpackage Components
 * @link http://fatatom.com
 * @license		private
 */

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the drtavel.locations Component
 *
 * @package		drtavel.locations
 * @subpackage	Components
 */
class LocationViewLocations extends JView
{
	function display($tpl = null)
	{
		
		$model = &$this->getModel();
		$objLocations = $model->getLocations();
		$this->assignRef( 'objLocations',	$objLocations );

		parent::display($tpl);
	}
}
?>
