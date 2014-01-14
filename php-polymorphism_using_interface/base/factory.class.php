<?php
class base_Factory {
	public static function getWriter() {
		$format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'JSON'; //Default to JSON 
		
		$class = 'writer_'.$format.'Writer';
		if(class_exists($class)) {
			return new $class();
		}
		
		//otherwise the format provided is not supported
		throw new Exception("Unsupported format",500);
	}
}