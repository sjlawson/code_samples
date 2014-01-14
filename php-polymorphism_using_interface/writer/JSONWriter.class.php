<?php
class writer_JSONWriter implements writer_Writer {
	public function write(base_Article $obj) {
		$array = array('article'=>$obj);
		return json_encode($array);
	}
}