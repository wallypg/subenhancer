<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	function getElementById($finder,$id) {
	    return $finder->query("//*[@id='$id']")->item(0);
	}
	
	function getElementByClass($finder,$class) {
	    return $finder->query("//div[contains(concat(' ', @class, ' '), ' $class ')]");
	}

?>