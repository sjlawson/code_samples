<?php
/**
 * Location View for Drtavel locationsComponent
 * 
 * @package    Joomla.Tutorials
 * @subpackage Components
 * @link http://docs.joomla.org/Developing_a_Model-View-Controller_Component_-_Part_4
 * @license		GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * Location View
 *
 * @package    drtavel.locations
 * @subpackage Components
 */
class LocationsViewLocation extends JView
{
	/**
	 * display method of location view
	 * @return void
	 **/
	function display($tpl = null)
	{
		//get the location
		$location		=& $this->get('Data');
		$isNew		= ($location->id < 1);

		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Location' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		$this->assignRef('isNew', $isNew);
		$this->assignRef('location', $location);

		parent::display($tpl);
	}
}