<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Pretty
 *
 * @author godfred7
 */
class PrettyExceptions {
	
	private static $defaultColours = 
		array(
			"#3366CC", // blue
			"#DC3912", // red
			"#FF9900", // orange
			"#109618", // green
			"#990099", // purple
			"#0099C6", // teal
			"#DD4477", // pink
			"black",   // black
			"#505050", // grey
		);
	
	/* @var $exception Exception */
	protected $exception = null;
	protected $options = null;
	protected $colours = null;
	protected $knownClasses = null;
	
	public function __construct(Exception $e, $options = array()) {
		$this->exception = $e;
		$this->options = $options;
		$this->colours = self::$defaultColours;
		$this->knownClasses = array();
	}
	
	public function renderException(Exception $exception)
	{
		$returns = array();
		$returns[] = 
			"<h1>" . get_class($exception) . ": " .$exception->getMessage() 
			. "</h1>";
		$returns[] = 
			"<p>" . $exception->getFile() . "(" . $exception->getLine() 
			. ")</p>";
		$returns[] = "<ul>";
		foreach ($exception->getTrace() as $traceLine) {
			$returns[] = "<li>";
			foreach ($this->renderTraceLine($traceLine) as $htmlLine) {
				$returns[] = $htmlLine;
			}
			$returns[] = "</li>";
		}
		$returns[] = "</ul>";
		
		return $returns;
	}
	
	private function renderTraceLine($traceLine) {
		$returns = array();
		
		$traceLine = array_merge(
			array(
				"class" => null,
				"type" => null,
				"function" => null,
				"line" => null,
				"file" => null,
				"args" => null
			),
			$traceLine
		);
		
		foreach (
			$this->renderCall(
				$traceLine["file"],
				$traceLine["line"],
				$traceLine["args"], 
				$traceLine["function"], 
				$traceLine["type"], 
				$traceLine["class"]
			) 
			as $callLine
		) {
			$returns[] = $callLine;
		}
		
		return $returns;
	}
	
	private function renderCall(
		$file, 
		$line, 
		$args, 
		$function, 
		$type = null, 
		$class = null
	) {
		$returns = array();
		$returns[] = "<dl>";
		$returns[] = 
			"<dt>" . ($class != null ? $this->renderClassName($class) : $class) . $type . $function . "(" 
			. implode(", ", $this->renderArguments($args, $function, $class)) 
			. ")</dt>";
		$returns[] = "<dd>" . $file . ":" . $line . "</dd>";
		$returns[] = "</dl>";
		return $returns;
	}
	
	private function renderClassName($class) {
		return 
			"<span style=\"background: " . $this->getClassColour($class) 
			. "; color: white; padding: 0.1em;\">" . $class . "</span>";
	}
	
	private function renderArguments($args, $function, $class = null) {
		$returns = array();
		if ($args == null) {
			return $returns;
		}
		if ($class == null && !function_exists($function)) {
			foreach ($args as $val) {
				$returns[] = $this->stringify($val);
			}
			return $returns;
		}
		
		$reflection = (
			$class == null 
				? new ReflectionFunction($function) 
				: new ReflectionMethod($class, $function)
		);
		/* @var $reflection ReflectionFunction */
		$params = $reflection->getParameters();
		
		if (count($params) == 0) {
			foreach ($args as $val) {
				$returns[] = $this->stringify($val);
			}
			return $returns;
		}
		
		foreach ($args as $index => $val) {
			$returns[] = 
				"<span style=\"color: grey;\">" . $params[$index]->getName() 
				. " </span>" . $this->stringify($val);
		}
		return $returns;
	}
	
	private function stringify($variable) {
		if (is_null($variable)) {
			return "NULL";
		} else if (is_array($variable)) {
			$array = array();
			foreach ($variable as $val) {
				$array[] = $this->stringify($val);
			}
			return "[" . implode(", ", $array) . "]";
		} else if (empty($variable)) {
			return "\"\"";
		} else if (is_object($variable)) {
			return get_class($variable);
		} else {
			return "\"" . htmlspecialchars($variable, ENT_QUOTES) . "\"";
		}
	}
	
	private function getClassColour($className) {
		if (!in_array($className, $this->knownClasses)) {
			$this->knownClasses[] = $className;
		}
		return 
			(
				$this->colours[array_search($className, $this->knownClasses) 
				% count($this->colours)]
			);
	}


	public function __toString() {
		return implode("\n", $this->renderException($this->exception));
	}
	
}
