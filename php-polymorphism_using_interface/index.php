<?php
/**
 * A sample of use of PHP Interface to achieve polymorphism 
 * 
 * Thanks to Steve Guidetti's articles for helping me understand this!
 * 
 */

header('Content-Type: text/plain');

include 'base/article.class.php';
include 'base/factory.class.php';
include 'writer/InterfaceWriter.php';
include 'writer/XMLWriter.class.php';
include 'writer/JSONWriter.class.php';


$article = new base_Article('Polymorphism in PHP', 'Samuel', time(), 0);

try {
	$writer = base_Factory::getWriter();
}
catch (Exception $e) {
	$writer = new writer_JSONWriter();
}

echo $article->write($writer);