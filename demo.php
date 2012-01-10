<?php
require_once ("PrettyException.php");
echo new PrettyException(new CustomException("First test"));
try {
	$x = new CallingClass();
	$x->publicFunction();
}
catch (Exception $e) {
	echo new PrettyException($e);
}

class CustomException extends Exception {
	
}

class ThrowingClass {
	private function privateFunction() {
		throw new Exception("Second test");
	}
	
	public static function staticFunction($some, $variable, $names) {
		$x = new ThrowingClass();
		$x->privateFunction();
	}
}

class CallingClass {
	public function publicFunction() {
		ThrowingClass::staticFunction(1, "two", array(array(3), array(3), array(3, "<pre>", 3)));
	}
}
?>
