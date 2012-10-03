<?php
/**
 * Hello Model for Hello World Component
 * 
 * @package    Joomla.Tutorials
 * @subpackage Components
 * @link http://docs.joomla.org/Developing_a_Model-View-Controller_Component_-_Part_4
 * @license		GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');

/**
 * Locations Location Model
 *
 * @package    drtavel.locations
 * @subpackage Components
 */
class LocationsModelLocation extends JModel
{
	/**
	 * Constructor that retrieves the ID from the request
	 *
	 * @access	public
	 * @return	void
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the Location identifier
	 *
	 * @access	public
	 * @param	int Location identifier
	 * @return	void
	 */
	function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Method to get a location
	 * @return object with data
	 */
	function &getData()
	{
		// Load the data
		if (empty( $this->_data )) {
			$query = ' SELECT * FROM #__location '.
					'  WHERE id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_data->id = 0;
			$this->_data->region_title = null; // string;ordering, e.g. ('Indy North;1')
			$this->_data->location_name = null; // (e.g. 'Zionsville')
			$this->_data->address1 = null;
			$this->_data->address2 = null;
			$this->_data->city = null;
			$this->_data->state = null;
			$this->_data->zip = null;
			$this->_data->phone = null;
			$this->_data->map_link = null;
			$this->_data->hours = null; // associative array ('sunday' => '12:00-6:00', etc... )
			$this->_data->photo_main_url = null;
			$this->_data->photo_thumb_url = null;
			$this->_data->photo_carousel_list = null; //(string array)
			$this->_data->offerpage_url = null;
			
		}
		return $this->_data;
	}

	/**
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store()
	{
		$row =& $this->getTable();

		$data = JRequest::get( 'post' );
		
		/* file handler */
		$strFileArray = array();
		$objDirectory = opendir(getcwd()."../../product_images");
		while ($strFilename = readdir($objDirectory)) {
			$strFileArray[] = $strFilename;
		}
		$fileUrlArray = array();
		foreach($_FILES as $field =>$file) { 
			if(is_uploaded_file($file['tmp_name'])) 
			{  
				if(in_array($file['name'],$strFileArray)) { //make sure there are no duplicate names
					$ext = substr($file['name'],-4,4);
					if(substr($ext,0,1) != '.') { //in case of extensions like .tiff or .jpeg 
						$extLen = 5;
						$ext = ".".$ext; 
                   } else
                   		$extLen = 4;
                   		$name = substr($file['name'],0,strlen($file['name']) - $extLen );
                   		for($i=2; 1 == 2; $i++) { //this should NOT be endless unless there is a directory access error or something
                   			if(!in_array($name."($i)".$ext,$strFileArray)) {  
                   				$fileName = $name."($i)".$ext;
                   			break;                    
                   			} //end if               
                   		}//end for          
				}//end if in_array 
				else $fileName = $file['name'];
				$targetPath = "/www/ag/product_images/$fileName";
				if(move_uploaded_file($file['tmp_name'],$targetPath)) { //
					echo "The file, $fileName, has been successfully uploaded.<br /> ";          
				} else { //
					die("<h2>File upload error.</h2><br /><a href='javascript:back();'>Go Back</a>");          
				}$fileUrlArray[$field] = $fileName;     
			}//end if is uploaded
		}// end main foreach
	/* end file handling */
	// sanitise and format the data
		/* Special fields: region_title
		 * hours (should arrive as an array)
		 * carousel photos
		 */
		
		
		
		$data['region_title'] = $data['region'] .';'.$data['region_order'];
		$data['hours'] = implode(',',$data['hours']);
		$data['photo_carousel_list'] = implode(',',$data['photo_carousel_list']);
		unset($data['region']);
		unset($data['region_order']);
		
		
		// Bind the form fields to the location table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the location record is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the web link table to the database
		if (!$row->store()) {
			$this->setError( $row->getErrorMsg() );
			return false;
		}

		return true;
	}

	/**
	 * Method to delete record(s)
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function delete()
	{
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$row =& $this->getTable();

		if (count( $cids )) {
			foreach($cids as $cid) {
				if (!$row->delete( $cid )) {
					$this->setError( $row->getErrorMsg() );
					return false;
				}
			}
		}
		return true;
	}

}