<?php
namespace Phud\Commands;
use Phud\Actors\Actor,
	Phud\Abilities\Ability,
	Phud\Abilities\Spell as aSpell;

class Cast extends Command
{
	protected $alias = ['cast', 11];
	protected $dispositions = [Actor::DISPOSITION_STANDING];
	
	public function perform(Actor $actor, $args = [])
	{
		$s = sizeof($args);
		if($s === 2) {
			$spell = Ability::lookup(implode(' ', array_slice($args, 1)));
		} else if($s > 2) {
			$spell = Ability::lookup(implode(' ', array_slice($args, 1, $s-2)));
		}

		// Check if the spell exists
		if(!($spell instanceof aSpell)) {
			return $actor->notify("That spell does not exist in this realm.");
		}

		// Does the caster actually know the spell?
		if(!in_array($spell->getAlias(), $actor->getAbilities())) {
			return $actor->notify("You do not know that spell.");
		}

		$actor->fire('casting', $spell);

		$spell->perform($actor, $args);
	}
}
