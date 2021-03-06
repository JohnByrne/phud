<?php
namespace Phud\Actors;
use Phud\Abilities\Ability,
	Phud\Abilities\Skill,
	Phud\Affects\Affectable,
	Phud\Inventory,
	Phud\Interactive,
	Phud\Races\Race,
	Phud\Room\Room,
	Phud\Equipped,
	Phud\Attributes,
	Phud\EasyInit,
	Phud\Identity,
	Phud\Damage,
	Phud\Debug,
	Phud\Proficiencies\Proficiency,
	Phud\Items\Corpse,
	Phud\Items\Food,
	Phud\Items\Furniture,
	Phud\Items\Equipment,
	Onit\Listener;

abstract class Actor
{
	use Affectable, Listener, Inventory, Interactive, EasyInit, Identity;

	const MAX_LEVEL = 51;
	
	const DISPOSITION_STANDING = 'standing';
	const DISPOSITION_SITTING = 'sitting';
	const DISPOSITION_SLEEPING = 'sleeping';
	
	const SEX_NEUTRAL = 1;
	const SEX_FEMALE = 2;
	const SEX_MALE = 3;
	
	protected $level = 0;
	protected $gold = 0;
	protected $silver = 0;
	protected $copper = 0;
	protected $sex = self::SEX_NEUTRAL;
	protected $disposition = self::DISPOSITION_STANDING;
	protected $race = 'critter';
	protected $race_listeners = [];
	protected $room = null;
	protected $equipped = null;
	protected $alignment = 0;
	protected $attributes = null;
	protected $max_attributes = null;
	protected $abilities = [];
	protected $target = null;
	protected $experience = 0;
	protected $experience_per_level = 0;
	protected $furniture = null;
	protected $is_alive = true;
	protected $proficiencies = [];
	
	public function __construct($properties = [])
	{
		foreach(Proficiency::getProficiencies() as $p => $class) {
			$this->proficiencies[$p] = new $class();
		}
		// set generic attribute values
		$this->attributes = new Attributes([
			'str' => 15,
			'int' => 15,
			'wis' => 15,
			'dex' => 15,
			'con' => 15,
			'cha' => 15,
			'hp' => 20,
			'mana' => 100,
			'movement' => 100,
			'ac_bash' => 100,
			'ac_slash' => 100,
			'ac_pierce' => 100,
			'ac_magic' => 100,
			'hit' => 1,
			'dam' => 1,
			'saves' => 100
		]);

		// do the EasyInit initializer
		$this->initializeProperties($properties, [
			'attributes' => function($actor, $property, $value) {
				foreach($value as $attr => $attr_value) {
					$actor->setAttribute($attr, $attr_value);
				}
			},
			'abilities' => function($actor, $property, $value) {
				foreach($value as $ability) {
					$actor->addAbility($ability);
				}
			}
		]);

		// set the max attributes based on the existing attributes
		$this->max_attributes = new Attributes([
			'str' => $this->attributes->getAttribute('str') + 4,
			'int' => $this->attributes->getAttribute('int') + 4,
			'wis' => $this->attributes->getAttribute('wis') + 4,
			'dex' => $this->attributes->getAttribute('dex') + 4,
			'con' => $this->attributes->getAttribute('con') + 4,
			'cha' => $this->attributes->getAttribute('cha') + 4,
			'hp' => $this->attributes->getAttribute('hp'),
			'mana' => $this->attributes->getAttribute('mana'),
			'movement' => $this->attributes->getAttribute('movement')
		]);

		// apply any racial modifiers
		$this->setRace(Race::lookup($this->race));

		// create equipment object
		$this->equipped = new Equipped($this);

		// initialized identity
		if($this->id) {
			self::$identities[$this->id] = $this;
		} else {
			$this->id = sha1(rand().microtime());
		}

		$this->applyListeners();
	}

	///////////////////////////////////////////////////////////////////
	// Ability functions
	///////////////////////////////////////////////////////////////////

	public function getAbilities()
	{
		return $this->abilities;
	}

	public function addAbility(Ability $ability)
	{
		$this->abilities[] = $ability->getAlias();
		if($ability instanceof Skill) {
			$listener = $ability->getListener();
			$this->on($listener[0], $listener[1]);
		}
	}

	public function removeAbility($ability)
	{
		$alias = $ability->getAlias();
		if(isset($this->ability[$alias])) {
			unset($this->ability[$alias]);
			if($ability instanceof Skill) {
				$ability->removeListener($this);
			}
		}
	}

	///////////////////////////////////////////////////////////////////
	// Attributes functions
	///////////////////////////////////////////////////////////////////

	public function getMaxAttribute($key)
	{
		return $this->max_attributes->getAttribute($key);
	}

	public function getUnmodifiedAttribute($key)
	{
		return $this->attributes->getAttribute($key);
	}

	public function getAttribute($key)
	{
		$n = $this->attributes->getAttribute($key);
		foreach($this->affects as $affect) {
			$n += $affect->getAttribute($key);
		}
		foreach($this->equipped->getItems() as $eq) {
			$n += $eq->getAttribute($key);
			$affs = $eq->getAffects();
			foreach($affs as $aff) {
				$n += $aff->getAttribute($key);
			}
		}
		$n += $this->race->getAttribute($key);
		$max = $this->max_attributes->getAttribute($key);
		$n = round($n);
		return $max > 0 ? min($n, $this->max_attributes->getAttribute($key)) : $n;
	}

	public function modifyAttribute($key, $amount)
	{
		$this->attributes->modifyAttribute($key, $amount);
		$this->fire('mod_'.$key, $amount);
	}

	public function setAttribute($key, $amount)
	{
		$this->fire('mod_'.$key, $amount);
		return $this->attributes->setAttribute($key, $amount);
	}

	///////////////////////////////////////////////////////////////////////////
	// Money functions
	///////////////////////////////////////////////////////////////////////////

	private function isCurrency($currency)
	{
		return $currency === 'copper' || $currency === 'silver' || $currency === 'gold';
	}

	public function getCurrency($currency)
	{
		if($this->isCurrency($currency)) {
			return $this->$currency;
		} else {
			Debug::error("\"".$currency."\" is not a valid currency type.");
		}
	}

	public function modifyCurrency($currency, $amount)
	{
		if($this->isCurrency($currency)) {
			$this->$currency += $amount;
		}
	}

	public function getWorth()
	{
		return $this->copper + ($this->silver * 100) + ($this->gold * 1000);
	}

	public function decreaseFunds($copper)
	{
		$copper = abs($copper);
		if($this->getWorth() < $copper) {
			return false; // Not enough money
		}

		$this->copper -= $copper; // Remove the money

		// ensure that copper amount stays above zero
		while($this->copper < 0) {
			if($this->silver > 0) {
				$this->silver--;
				$this->copper += 100;
				continue;
			}
			if($this->gold > 0) {
				$this->gold--;
				$this->copper += 1000;
			}
		}
	}

	///////////////////////////////////////////////////////////////////////////
	// Fighting methods
	///////////////////////////////////////////////////////////////////////////

	public function getTarget()
	{
		return $this->target;
	}

	public function setTarget(Actor $target = null)
	{
		$this->target = $target;
		if($this->target) {
			$this->on('pulse', function($event) {
				$target = $this->getTarget();
				if(empty($target) || !$this->isAlive()) {
					return $event->kill();
				}
				$e = $target->fire('attacked');
				if($e->getStatus() === 'satisfied') {
					return;
				} else if($e->getStatus() === 'killed') {
					return $event->kill();
				}
				$this->fire('attack');
			});
		}
	}

	public function reconcileTarget($args = [])
	{
		if(sizeof($args) <= 1) {
			return $this->target;
		}

		$specified_target = is_array($args) ? $this->getRoom()->getActorByInput(array_slice($args, -1)[0]) : $args;

		if(empty($this->target)) {
			if(empty($specified_target)) {
				return $this->notify("No one is there.");
			}
			if(!($specified_target instanceof self)) {
				return $this->notify("I don't think they would like that very much.");
			}
			if($this === $specified_target) {
				return $this->notify("You can't target yourself!");
			}
			$this->setTarget($specified_target);
		} else if(!empty($specified_target) && $this->target !== $specified_target) {
			return $this->notify("Whoa there sparky, don't you think one is enough?");
		}
		return $this->target;
	}

	public function attack($attack_name = '', $verb = '')
	{
		$victim = $this->getTarget();
		if(!$victim) {
			return;
		}

		$victim->fire('hit', $this);

		if(!$attack_name) {
			$attack_name = 'Reg';
		}

		$attacking_weapon = $this->getEquipped()->getEquipmentByPosition(Equipment::POSITION_WIELD);

		if($attacking_weapon['equipped']) {
			if(!$verb) {
				$verb = $attacking_weapon['equipped']->getVerb();
			}
			$dam_type = $attacking_weapon['equipped']->getDamageType();
		} else {
			if(!$verb) {
				$verb = $this->getRace()->getUnarmedVerb();
			}
			$dam_type = Damage::TYPE_BASH;
		}

		// ATTACKING
		$hit_roll = $this->getAttribute('hit');
		$dam_roll = $this->getAttribute('dam');
		$hit_roll += ($this->getAttribute('dex') / Attributes::MAX_STAT) * 4;

		// DEFENDING
		$def_roll = ($victim->getAttribute('dex') / Attributes::MAX_STAT) * 4;

		// Size modifier
		$def_roll += 5 - $victim->getRace()->getSize();

		$ac = 0;
		if($dam_type === Damage::TYPE_BASH)
			$ac = $victim->getAttribute('ac_bash');
		else if($dam_type === Damage::TYPE_PIERCE)
			$ac = $victim->getAttribute('ac_pierce');
		else if($dam_type === Damage::TYPE_SLASH)
			$ac = $victim->getAttribute('ac_slash');
		else if($dam_type === Damage::TYPE_MAGIC)
			$ac = $victim->getAttribute('ac_magic');

		$ac = $ac / 100;	

		$roll['attack'] = rand(0, $hit_roll);
		$roll['defense'] = rand(0, $def_roll) - $ac;

		if($dam_roll < 5)
			$descriptor = 'clumsy';
		elseif($dam_roll < 10)
			$descriptor = 'amateur';
		elseif($dam_roll < 15)
			$descriptor = 'competent';
		else
			$descriptor = 'skillful';

		$actors = $this->getRoom()->getActors();
		foreach($actors as $a) {
			$a->notify(($a === $this ? '('.$attack_name.') Your' : ucfirst($this)."'s").' '.$descriptor.' '.$verb.' '.($dam_roll > 0 ? 'hits ' : 'misses ').($victim === $a ? 'you' : $victim) . '.');
		}

		// Lost the hit roll -- miss
		if($roll['attack'] <= $roll['defense']) {
			$dam_roll = 0;
		} else {
			//(Primary Stat / 2) + (Weapon Skill * 4) + (Weapon Mastery * 3) + (ATR Enchantments) * 1.stance modifier
			//((Dexterity*2) + (Total Armor Defense*(Armor Skill * .03)) + (Shield Armor * (shield skill * .03)) + ((Primary Weapon Skill + Secondary Weapon Skill)/2)) * (1. Stance Modification)

			$modifier = 1;
			$this->fire('damage modifier', $victim, $modifier, $dam_roll, $attacking_weapon);
			$victim->fire('defense modifier', $this, $modifier, $dam_roll, $attacking_weapon);
			$dam_roll *= $modifier;
			$dam_roll = _range(0, 200, $dam_roll);
			$victim->modifyAttribute('hp', -($dam_roll));
		}

		if($victim->getAttribute('hp') < 1) {
			$victim->setTarget(null);
			$this->setTarget(null);

			$this->notify('You have KILLED '.$victim.'.');

			$gold = round($victim->getCurrency('gold') / 2);
			$silver = round($victim->getCurrency('silver') / 2);
			$copper = round($victim->getCurrency('copper') / 2);

			$victim->modifyCurrency('gold', -$gold);
			$victim->modifyCurrency('silver', -$silver);
			$victim->modifyCurrency('copper', -$copper);

			$this->gold += $gold;
			$this->silver += $silver;
			$this->copper += $copper;

			$this->getRoom()->announce([
				['actor' => $victim, 'message' => ''],
				['actor' => '*', "You hear ".$victim."'s death cry."]
			]);
			if(chance() < 0.25) {
				$s = $victim->getDisplaySex();
				$parts = $victim->getRace()->getParts();
				$custom_message = [
					['brains' => ucfirst($victim)."'s brains splash all over you!"],
					['guts' => ucfirst($victim).' spills '.$s.' guts all over the floor.'],
					['heart' => ucfirst($victim)."'s heart is torn from ".$s." chest."]
				];
				$k = array_rand($parts);
				if(isset($custom_message[$parts[$k]])) {
					$message = $custom_message[$parts[$k]];
				} else {
					$message = ucfirst($victim)."'s ".$parts[$k].' is sliced from '.$s.' body.';
				}
				$this->getRoom()->announce([
					['actor' => '*', 'message' => $message]
				]);
				$this->getRoom()->addItem(new Food([
					'short' => 'the '.$parts[$k].' of '.$victim,
					'long' => 'The '.$parts[$k].' of '.$victim.' is here.',
					'nourishment' => 5
				]));
			}
			
			$this->notify("\r\n".$this->prompt());
		}
	}
	
	public function death()
	{
		$this->notify('You have been KILLED!');
		$corpse = new Corpse([
			'short' => 'the corpse of '.$this,
			'long' => 'The corpse of '.$this.' lies here.',
			'weight' => 100
		]);
		foreach($this->items as $i) {
			$this->removeItem($i);
			$corpse->addItem($i);
		}
		$this->getRoom()->addItem($corpse);
		$this->is_alive = false;
		$this->target->applyExperienceFrom($this);
		$this->fire('died');
	}

	public function tick()
	{
		$amount = rand(0.05, 0.1);
		$modifier = 1;
		$this->fire('tick', $amount, $modifier);
		$amount *= $modifier;
		foreach(['hp', 'mana', 'movement'] as $att) {
			$this->modifyAttribute($att, round($amount * $this->getAttribute($att)));
		}
	}

	public function getProficiencies()
	{
		return $this->proficiencies;
	}

	public function getProficiencyScore($proficiency)
	{
		if(!isset($this->proficiencies[$proficiency])) {
			Debug::error("proficiency not defined: ".$proficiency);
			return -1;
		}
		return $this->proficiencies[$proficiency]->getScore();
	}

	public function improveProficiency($proficiency)
	{
		if(!isset($this->proficiencies[$proficiency])) {
			Debug::error("proficiency not defined: ".$proficiency);
			$this->proficiencies[$proficiency] = 15;
		}
		$this->proficiencies[$proficiency]++;
	}

	public function getFurniture()
	{
		return $this->furniture;
	}

	public function setFurniture(Furniture $furniture = null)
	{
		if($this->furniture) {
			$this->furniture->removeActor($this);
		}
		$this->furniture = $furniture;
	}

	public function getAlignment()
	{
		return $this->alignment;
	}

	public function modifyAlignment($alignment)
	{
		$this->alignment += $alignment;
	}

	public function getDisposition()
	{
		return $this->disposition;
	}

	public function setDisposition($disposition)
	{
		$this->disposition = $disposition;
	}

	public function getLong()
	{
		return $this->long ? $this->long : 'You see nothing special about '.$this->getDisplaySex([self::SEX_MALE => 'him', self::SEX_FEMALE => 'her', self::SEX_NEUTRAL => 'it']).'.';
	}

	public function getEquipped()
	{
		return $this->equipped;
	}

	public function getSex()
	{
		return $this->sex;
	}

	public function getDisplaySex($set = [])
	{
		if(empty($set)) {
			$set = [self::SEX_MALE=>'his', self::SEX_FEMALE=>'her', self::SEX_NEUTRAL=>'its'];
		}
		if(isset($set[$this->sex])) {
			return $set[$this->sex];
		}
		return 'its';
	}

	public function setSex($sex)
	{
		if($sex === self::SEX_FEMALE || $sex === self::SEX_MALE || $sex === self::SEX_NEUTRAL) {
			$this->sex = $sex;
		}
	}

	public function setRoom(Room $room)
	{
		if($this->room) {
			$this->room->actorRemove($this);
		}
		$room->actorAdd($this);
		$this->room = $room;
	}

	public function getRoom()
	{
		return $this->room;
	}

	public function getRace()
	{
		return $this->race;
	}

	public function setRace(Race $race)
	{
		if(isset($this->race) && is_object($this->race)) {
			// Undo all previous racial listeners/abilities/stats/proficiencies
			foreach($this->race_listeners as $listener) {
				$this->unlisten($listener[0], $listener[1]);
			}
			foreach($this->race->getProficiencies() as $proficiency => $amount) {
				$this->proficiencies[$proficiency]->modifyScore(-$amount);
			}
			foreach($this->race->getAbilities() as $ability_alias) {
				$ability = Ability::lookup($ability_alias);
				$this->removeAbility($ability);
			}
		}

		// Assign all racial listeners/abilities/stats/proficiencies
		$this->race = $race;
		$this->race_listeners = $race->getListeners();
		foreach($this->race_listeners as $listener) {
			$this->on($listener[0], $listener[1]);
		}
		$profs = $race->getProficiencies();
		foreach($profs as $name => $value) {
			$this->proficiencies[$name]->modifyScore($value);
		}
		foreach($race->getAbilities() as $ability_alias) {
			$ability = Ability::lookup($ability_alias);
			$this->addAbility($ability);
		}
	}

	public function levelUp()
	{
		Debug::log($this.' levels up.');
		$this->level++;
		$this->trains++;
		$this->practices += ceil($this->getWis() / 5);

		$this->notify("You LEVELED UP!\r\nCongratulations, you are now level ".$this->level."!");
	}

	public function getLevel()
	{
		return $this->level;
	}

	public function getStatus()
	{
		$statuses = [
			 '100' => 'is in excellent condition',
			 '99' => 'has a few scratches',
			 '75' => 'has some small wounds and bruises',
			 '50' => 'has quite a few wounds',
			 '30' => 'has some big nasty wounds and scratches',
			 '15' => 'looks pretty hurt',
			 '0' => 'is in awful condition'
		];

		$hp_percent = ($this->getAttribute('hp') / $this->getMaxAttribute('hp')) * 100;
		$descriptor = '';
		foreach($statuses as $index => $status)
			if($hp_percent <= $index)
				$descriptor = $status;

		return $descriptor;

	}
	
	public function respawn()
	{
		$this->is_alive = true;
	}

	public function isAlive()
	{
		return $this->is_alive;
	}

	public function addExperience($experience)
	{
		$this->experience += $experience;
	}

	public function applyExperienceFrom(Actor $victim)
	{
		Debug::log("applying experience from ".$victim." to ".$this.".");
		$experience = $victim->getKillExperience($this);
		$this->notify("You get ".$experience." experience for your kill.");
		if($this->experience < $this->experience_per_level) {
			$this->experience += $experience;
		}
	}

	protected function getKillExperience(Actor $killer)
	{
		$level_diff = $this->level - $killer->getLevel();

		switch($level_diff)
		{
			case -8:
				$base_exp = 2;
				break;
			case -7:
				$base_exp = 7;
				break;
			case -6:
				$base_exp = 13;
				break;
			case -5:
				$base_exp = 20;
				break;
			case -4:
				$base_exp = 26;
				break;
			case -3: 
				$base_exp = 40;
				break;
			case -2:
				$base_exp = 60;
				break;
			case -1:
				$base_exp = 80;
				break;
			case 0:
				$base_exp = 100;
				break;
			case 1:
				$base_exp = 140;
				break;
			case 2:
				$base_exp = 180;
				break;
			case 3:
				$base_exp = 220;
				break;
			case 4:
				$base_exp = 280;
				break;
			case 5:
				$base_exp = 320;
				break;
			default:
				$base_exp = 0;
				break;
		}
		
		if($level_diff > 5)
			$base_exp += 30 * $level_diff;
		
		$align_diff = abs($this->alignment - $killer->getAlignment()) / 2000;
		if($align_diff > 0.5)
		{
			$mod = rand(15, 35) / 100;
			$base_exp = $base_exp * (1 + ($align_diff - $mod));
		}
		
		$base_exp = rand($base_exp * 0.8, $base_exp * 1.2);
		return intval($base_exp);
	}
	
	public function getExperience()
	{
		return $this->experience;
	}
	
	public function getExperiencePerLevel()
	{
		return $this->experience_per_level; 
	}

	public function consume($item)
	{
		if($this->removeItem($item) !== false) {
			foreach($item->getAffects() as $aff) {
				$aff->apply($this);
			}
		}
	}

	public function applyListeners()
	{
		// all actors get one attack per round to start
		$this->on('attack', function($event, $fighter) {
			$fighter->attack('Reg');
		});

		// return fire if attacked
		$this->on('hit', function($event, $victim, $attacker) {
			if(!$victim->getTarget()) {
				$victim->setTarget($attacker);
			}
		});

		$this->on('mod_hp', function($event, $actor) {
			if($actor->isAlive() && $actor->getAttribute('hp') < 1) {
				$actor->death();
			}
		});

		foreach($this->proficiencies as $p) {
			foreach($p->getImprovementListeners() as $l) {
				$this->on($l[0], $l[1]);
			}
		}
	}
	
	public function __toString()
	{
		return $this->alias;
	}

	abstract public function notify($message);
}
