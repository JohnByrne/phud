<?php
namespace Phud\Abilities;
use Phud\Actors\Actor;

class Cure_Critical extends Spell
{
	protected $alias = 'cure critical';
	protected $proficiency = 'healing';
	protected $required_proficiency = 45;
	protected $normal_modifier = ['wis'];

	protected function success(Actor $actor)
	{
		$prof_rand = rand(9, 11);
		$amount = round(rand(10, ($proficiency / $prof_rand) + 8));
		$target->modifyAttribute('hp', $amount);
		$target->notify("You feel better!");
	}
}
