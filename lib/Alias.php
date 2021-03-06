<?php
namespace Phud;

trait Alias
{
	protected static $aliases = [];
	
	public static function addAlias($alias, $lookup, $priority = 10)
	{
		self::$aliases[$alias] = ['alias' => $alias, 'lookup' => $lookup, 'priority' => $priority];
	}

	public static function record($alias)
	{
		// Direct match
		if(isset(self::$aliases[$alias])) {
			return self::$aliases[$alias];
		}
		
		$possibilities = array_filter(
			self::$aliases,
			function($lookup) use ($alias) {
				return strpos($lookup['alias'], $alias) === 0;
			}
		);
		
		// Return the highest priority match
		if($possibilities) {
			usort($possibilities, function($a, $b) {
				return $a['priority'] < $b['priority'];
			});
			return $possibilities[0];
		}
	}
	
	public static function lookup($alias)
	{
		return self::record($alias)['lookup'];
	}

	public static function create($alias)
	{
		$result = self::record($alias);
		if($result && $lookup = $result['lookup']) {
			return new $lookup();
		}
	}

	public static function getAliases()
	{
		return self::$aliases;
	}
}
