<?php
namespace Phud\Commands;
use Phud\Actors\Actor,
	Phud\Room;

class Recall extends Command
{
	protected $alias = 'recall';

	protected $dispositions = [Actor::DISPOSITION_STANDING];

	public function perform(Actor $actor, $args = [])
	{
		$actor->setRoom(Room::find(1));
	}
}
?>