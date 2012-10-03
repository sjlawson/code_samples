<?php
/**
 * Location Controller 
 * 
 * @package    drtavel.locations
 * @subpackage Components
 * @link http://fatatom.com
 * @license		private
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Locations Location Controller
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class LocationsControllerLocation extends LocationsController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add'  , 	'edit' , 'remove');
	}

	/**
	 * display the edit form
	 * @return void
	 */
	function edit()
	{
		JRequest::setVar( 'view', 'location' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar('hidemainmenu', 1);

		parent::display();
	}

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{
		$model = $this->getModel('location');
		
		if ($model->store()) {
			$msg = JText::_( 'Location Saved!' );
		} else {
			$msg = JText::_( 'Error Saving Location' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_locations';
		$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$model = $this->getModel('location');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More Locations Could not be Deleted' );
		} else {
			$msg = JText::_( 'Location(s) Deleted' );
		}

		$this->setRedirect( 'index.php?option=com_locations', $msg );
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'Operation Cancelled' );
		$this->setRedirect( 'index.php?option=com_locations', $msg );
	}
}