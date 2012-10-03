<?php
/**
 * Locations View for Drtavel locations component
 * 
 * @package    drtavel.locations
 * @subpackage Components
 * @link http://fatatom.com
 * @license		private
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * Locations View
 *
 * @package    drtavel.locations
 * @subpackage Components
 */
class LocationsViewLocations extends JView
{	 // locationViewlocations ??
	/**
	 * Locations view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		JToolBarHelper::title(   JText::_( 'Dr. Tavel Location Manager' ), 'generic.png' );
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();

		// Get data from the model
		$items		= & $this->get( 'Data');

		$this->assignRef('items',		$items);

		parent::display($tpl);
	}
}