<?php
namespace Phud\Commands\Arguments;
use Phud\Room\Direction as rDirection;

class Direction extends Argument
{
	public function parse($arg)
	{
		foreach(rDirection::getDirections() as $dir) {
			if(strpos($dir, $arg) === 0) {
				return $dir;
			}
		}
		$this->fail("Not a valid direction: ".$arg);
	}
}
