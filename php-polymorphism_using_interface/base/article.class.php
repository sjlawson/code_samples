<?php
 
class base_Article {
	public $title;
	public $author;
	public $date;
	public $category;
	
	public function __construct($title, $author, $date, $category=0) {
		$this->title = $title;
		$this->author = $author;
		$this->date = $date;
		$this->category = $category;
	}
	
	public function write(writer_Writer $writer) {
		return $writer->write($this);
	}
}