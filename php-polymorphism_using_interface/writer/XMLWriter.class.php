<?php
class writer_XMLWriter implements writer_Writer {
	public function write(base_Article $obj) {
		$ret = '<article>';
		$ret .= '<title>' . $obj->title . '</title>';
		$ret .= '<author>' . $obj->author . '</author>';
		$ret .= '<date>' . $obj->date . '</date>';
		$ret .= '<category>' . $obj->category . '</category>';
		$ret .= '</article>';
		return $ret;
	}
}