<?php
/**
 * Location View for drtavel.locations Component
 * 
 * @package    drtavel.locations
 * @subpackage Components
 * @link http://fatatom.com
 * @license		private
 */

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Locations Component
 *
 * @package		drtavel.locations
 * @subpackage	Components
 */
class LocationViewLocation extends JView
{
	function display($tpl = null)
	{
		$intLocationId = JRequest::getVar('id');  //alt to $_REQUEST['id'];
		if(!$intLocationId) {
			$dbo =& JFactory::getDBO();
			$dbo->setQuery('SELECT `id` FROM `#__location` LIMIT 1');
			$intLocationId = $dbo->loadResult();
		}
		
		$model = &$this->getModel();
		$objLocation = $model->getLocation($intLocationId);
		$this->assignRef( 'objLocation',	$objLocation );

		parent::display($tpl);
	}
}
?>
