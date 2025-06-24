<?php
	function getClasses($className)
	{
		// Map opbouwen
		$baseDir = dirname(__DIR__);
		$relativePath = "../classes/";
		$className = str_replace('\\','/',$className);
		
		$fullPathInterface = "{$baseDir}/{$relativePath}{$className}.inter.php";
		$fullPathAbstract = "{$baseDir}/{$relativePath}{$className}.abstract.php";
		$fullPathClass = "{$baseDir}/{$relativePath}{$className}.class.php";
		
		// Testen en gebruiken
		if(file_exists($fullPathInterface))
		{
			include $fullPathInterface;
		} else if(file_exists($fullPathAbstract))
		{
			include $fullPathAbstract;
		} else if(file_exists($fullPathClass))
		{
			include $fullPathClass;
		} else {
			return false;
		}
		
		// Het is gelukt
		return true;
	}
	
	spl_autoload_register('getClasses');
?>