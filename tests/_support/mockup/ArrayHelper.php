<?php
namespace Joomla\Utilities;

abstract class ArrayHelper
{	
	public static function getValue($array, $key)
	{
		if (isset($array[$key])) {
			return $array[$key];
		}

		return null;
	}
}
