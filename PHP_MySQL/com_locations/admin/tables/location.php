<?php
/**
 * Location table class
 * 
 * @package    Dr.Tavel locations
 * @subpackage Components
 * @link http://docs.joomla.org/Developing_a_Model-View-Controller_Component_-_Part_4
 * @license		GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Location Table class
 *
 * @package    drtavel.locations
 * @subpackage Components
 */
class TableLocation extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;

	/**
	 * @var string
	 * string, with ordering data separated by semi-colon
	 */
	var $region_title = null; // string;ordering, e.g. ('Indy North;1')
	
	/**
	 * @var string
	 */ 
	var $location_name = null; // (e.g. 'Zionsville')
	
	/**
	 * @var string
	 */ 
	var $address1 = null;
	
	var $address2 = null;
	
	/**
	 * @var string
	 */ 
	var $city = null;
	
	/**
	 * @var string
	 */ 
	var $state = null;
	
	/**
	 * @var string
	 */ 
	var $zip = null;
	
	/**
	 * @var string
	 */ 
	var $phone = null;
	
	/**
	 * @var string
	 */ 
	var $map_link = null;
	
	/**
	 * @var associative string array
	 */ 
	var $hours = null; // associative array ('sunday' => '12:00-6:00', etc... )
	
	/**
	 * @var string
	 */ 
	var $photo_main_url = null;
	
		/**
	 * @var string
	 */ 
	var $photo_thumb_url = null;
	
	/**
	 * @var string array
	 */ 
	var $photo_carousel_list = null; //(string array)
	
	/**
	 * @var string
	 */ 
	var $offerpage_url = null;
			
	
	/**
	 * Constructor
	 * com_locations 
	 * @param object Database connector object
	 */
	function TableLocation(& $db) {
		parent::__construct('#__location', 'id', $db);
	}
}