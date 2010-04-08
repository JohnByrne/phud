<?php

	/**
	 *
	 * Phud - a PHP implementation of the popular multi-user dungeon game paradigm.
     * Copyright (C) 2009 Dan Munro
	 * 
     * This program is free software; you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation; either version 2 of the License, or
     * (at your option) any later version.
	 * 
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
	 * 
     * You should have received a copy of the GNU General Public License along
     * with this program; if not, write to the Free Software Foundation, Inc.,
     * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
	 *
	 * Contact Dan Munro at dan@danmunro.com
	 * @author Dan Munro
	 * @package Phud
	 *
	 */

	abstract class Actor
	{
	
		protected $id;
		protected $alias;
		protected $password = '';
		protected $description;
		protected $hp;
		protected $max_hp;
		protected $mana;
		protected $max_mana;
		protected $movement;
		protected $max_movement;
		protected $level;
		protected $gold;
		protected $silver;
		protected $copper;
		protected $str;
		protected $int;
		protected $wis;
		protected $dex;
		protected $con;
		protected $trigger;
		protected $fighting;
		protected $sex;
		protected $disposition; // sitting, sleeping, standing
		protected $experience;
		protected $exp_per_level;
		protected $concentration;
		protected $delay = 0;
		protected $fightable = true;
		
		protected $race = null;
		public $_class = null;
		protected $room = null;
		protected $inventory = null;
		protected $equipment = null;
		public $skill_set = null;
		
		static $instances;
		
		public function __construct($room_id)
		{
		
			Debug::addDebugLine("Adding actor " . $this->getAlias(true) . " to observer list.");
			ActorObserver::instance()->add($this);
			$this->equipment = new Equipment();
			$this->room = Room::find($room_id);
			$this->inventory = Inventory::find($this->getTable(), $this->id);
		}
		
		public function save()
		{
			Debug::addDebugLine("Saving actor " . $this->getAlias(true));
			Db::getInstance()->query('UPDATE ' . $this->getTable() . ' SET 
											alias = ?,
											hp = ?,
											max_hp = ?,
											mana = ?,
											max_mana = ?,
											movement = ?,
											max_movement = ?,
											level = ?,
											copper = ?,
											silver = ?,
											gold = ?,
											pass = ?,
											str = ?,
											`int` = ?,
											wis = ?,
											dex = ?,
											con = ?,
											race = ?,
											experience = ?,
											exp_per_level = ?,
											fk_room_id = ? WHERE id = ?', array(
											$this->getAlias(),
											$this->hp,
											$this->max_hp,
											$this->mana,
											$this->max_mana,
											$this->movement,
											$this->max_movement,
											$this->level,
											$this->copper,
											$this->silver,
											$this->gold,
											$this->password,
											$this->str,
											$this->int,
											$this->wis,
											$this->dex,
											$this->con,
											$this->getRaceStr(),
											$this->experience,
											$this->exp_per_level,
											$this->getRoom()->getId(),
											$this->id));
		}
		
		public function getStr() { return $this->str; }
		public function getInt() { return $this->int; }
		public function getWis() { return $this->wis; }
		public function getDex() { return $this->dex; }
		public function getCon() { return $this->con; }
		
		public function getId() { return $this->id; }
		public function getAlias($upper = null)
		{
		
			if($upper === null)
				if($this instanceof User)
					return ucfirst($this->alias);
				else
					return $this->alias;
			
			if($upper)
				return ucfirst($this->alias);
			else
				return $this->alias;
			
			
			//if($this instanceof User || ($this instanceof Mob && $this->unique === true))
			//	return ucfirst($this->alias);
			//
			//return $upper === true ? ucfirst($this->alias) : strtolower($this->alias);
		}
		public function getRaceStr() { return $this->race->getRaceStr(); }
		public function getClassStr() { return $this->_class->getClassStr(); }
		public function getLevel() { return $this->level; }
		
		public function getHp() { return $this->hp; }
		public function getMaxHp() { return $this->max_hp; }
		public function getMana() { return $this->mana; }
		public function getMaxMana() { return $this->max_mana; }
		public function getMovement() { return $this->movement; }
		public function getMaxMovement() { return $this->max_movement; }
		public function getInventory() { return $this->inventory; }
		public function getEquipment() { return $this->equipment; }
		public function getRoomId() { return $this->room->getId(); }
		public function getRoom() { return $this->room; }
		public function getCopper() { return $this->copper; }
		public function getSilver() { return $this->silver; }
		public function addSilver($silver) { $this->silver += $silver; }
		public function getGold() { return $this->gold; }
		public function setRoom($room)
		{
			if($room instanceof Room)
				$this->room = $room;
		}
		public function getRace() { return $this->race; }
		public function getFighters() { return $this->fighting; }
		public function getExperience() { return $this->experience; }
		public function getExpPerLevel() { return $this->exp_per_level; }
		public function getConcentration() { return $this->concentration; }
		public function getFighter($fighter_alias)
		{
			foreach($this->fighting as $index => $fighter)
				if($fighter->getAlias() == $fighter_alias)
					return $fighter;
		}
		public function getTarget()
		{
			$fighters = $this->getFighters();
			if($fighters === null)
				return null;
			
			$fighter = array_shift($fighters);
			while(!($fighter instanceof Actor))
			{
				if(sizeof($fighters) == 0)
					return null;
				
				$fighter = array_shift($fighters);
			}
			return $fighter;
		}
		public function getHpPercent()
		{
			return ($this->hp / $this->max_hp) * 100;
		}
		public function getStatus()
		{
		
			$statuses = array
			(
				'100' => 'is in excellent condition',
				'99' => 'has some scratches',
				'50' => 'has quite a few wounds',
				'10' => 'has some big nasty wounds and scratches'
			);
			
			$hp_percent = $this->getHpPercent();
			
			foreach($statuses as $index => $status)
			{
				if($hp_percent <= $index)
				{
					$descriptor = $status;
				}
			}
			return $descriptor;
		
		}
		public function increaseCopper($amount) { $this->copper += $amount; }
		public function decreaseCopper($amount) { $this->copper -= $amount; }
		public function increaseSilver($amount) { $this->silver += $amount; }
		public function decreaseSilver($amount) { $this->silver -= $amount; }
		public function increaseGold($amount) { $this->gold += $amount; }
		public function decreaseGold($amount) { $this->gold -= $amount; }
		public function isAlive()
		{
			if($this->max_hp == 0)
				return true; // Creation
			return $this->hp > 0;
		}
		public function incrementDelay($delay)
		{
			$this->delay += $delay;
		}
		public function decrementDelay()
		{
			if($this->delay > 0)
				$this->delay--;
		}
		public function getDelay() { return $this->delay; }
		public function getFightable() { return $this->fightable; }
		public function setHp($hp, $killer = null) { $this->hp = $hp; $this->checkAlive($killer); }
		public function setMaxHp($max_hp) { $this->max_hp = $max_hp; }
		public function setMana($mana) { $this->mana = $mana; }
		public function setMaxMana($max_mana) { $this->max_mana = $max_mana; }
		public function setMovement($movement) { $this->movement = $movement; }
		public function setMaxMovement($max_movement) { $this->max_movement = $max_movement; }
		public function setLevel($level)
		{
			while($this->level < $level)
				$this->levelUp(false);
		}
		public function addFighter(Actor &$fighting)
		{
			if(!$fighting->getFightable())
				return Server::out($this, "You can't fight them!");
			
			Debug::addDebugLine("User " . $this->getAlias(true) . " adding fighter " . $fighting->getAlias(true) . ".");
			Server::out($this, 'You scream and attack!');
			$this->fighting[] = $fighting;
		}
		public function setExpPerLevel($exp) { $this->exp_per_level = $exp; }
		public function clearFighters() { $this->fighting = null; }
		public function setExperience($experience)
		{
			$this->experience = $experience;
			if($this->experience <= 0)
				$this->levelUp();
		}
		public function awardExperience($experience)
		{
			$this->experience -= $experience;
			if($this->experience <= 0)
				$this->levelUp();
		}
		public function clearFighter($fighter_alias)
		{
			foreach($this->fighting as $index => $fighter)
				if($fighter->getAlias() == $fighter_alias)
					unset($this->fighting[$index]);
		}
		
		public function setAlias($alias) { $this->alias = $alias; }
		
		public function setStr($str)
		{
			if($str > $this->race->getMaxStr())
				throw new Actor_Exception();
			
			$this->str = $str;
		}
		public function setInt($int)
		{
			if($int > $this->race->getMaxInt())
				throw new Actor_Exception();
			
			$this->int = $int;
		}
		public function setWis($wis)
		{
			if($wis > $this->race->getMaxWis())
				throw new Actor_Exception();
			
			$this->wis = $wis;
		}
		public function setDex($dex)
		{
			if($dex > $this->race->getMaxDex())
				throw new Actor_Exception();
			
			$this->dex = $dex;
		}
		public function setCon($con)
		{
			if($con > $this->race->getMaxCon())
				throw new Actor_Exception();
			
			$this->con = $con;
		}
		public function setRace($race)
		{
			$race = Race::getInstance($race);
			if($race instanceof Race)
			{
				$this->race = $race;
				$this->race->applyRacialAttributeModifiers($this);
			}
		
		}
		public function decreaseFunds($value)
		{
			$copper = $this->copper;
			$silver = $this->silver;
			$gold = $this->gold;
			
			if($copper > $value)
				return $this->copper -= $value;
			else
			{
				$value -= $copper;
				$copper = 0;
			}
			$value = $value / 100;
			if($silver > $value)
			{
				$silver -= $value;
				$value = 0;
			}			
			else
			{
				$value -= $silver;
				$silver = 0;
			}
			$value = $value / 100;
			if($gold > $value)
			{
				$gold -= $value;
				$value = 0;
			}
			else
			{
				$value -= $gold;
				$gold = 0;
			}
			
			if($value > 0)
				return false;
			
			$this->copper = $copper;
			$this->silver = $silver;
			$this->gold = $gold;
			
			return true;
		}
		public function decreaseConcentration() { $this->concentration--; if($this->concentration < 0) $this->concentration = 0; }
		public function increaseConcentration() { $this->concentration++; if($this->concentration > 10) $this->concentration = 10; }

		public function attack(Actor &$actor)
		{
		
			Debug::addDebugLine("Battle round: " . $this->getAlias() . " attacking " . $actor->getAlias() . ". ", false);
			$attacking_weapon = $this->equipment->getEquipmentByPosition(Equipment::WEAPON);
			
			if($attacking_weapon === null)
				$verb = $this->getRace()->getUnarmedVerb();
			else
				$verb = $attacking_weapon->getVerb();
		
			// Attack - hit or miss?
			if($this->str <= $actor->getDex())
				$attack = 1;
			if($this->str > $actor->getDex())
				$attack = $this->str - $actor->getDex();
			
			$die = 5;
			$roll = rand(0, $die);
			
			if($roll >= $attack)
				$attack = 0;
			
			// Verb
			if($attack < 5)
				$descriptor = 'clumsy';
			else
				$descriptor = 'WICKED';
			
			$actors = ActorObserver::instance()->getActorsInRoom($this->room->getId());
			
			foreach($actors as $actor_sub)
				Server::out($actor_sub, ($actor_sub->getAlias() == $this->getAlias() ? 'Your' : $this->getAlias(true) . "'s") . ' ' . $descriptor . ' ' . $verb . ' ' . ($attack > 0 ? 'hits ' : 'misses ') . ($actor->getAlias() == $actor_sub->getAlias() ? 'you' : $actor->getAlias()) . '.');
			
			$actor->setHp($actor->getHp() - $attack, $this);
			
			if($actor->getHp() > 0)
			{
				$actor_target = $actor->getTarget();
				if(!($actor_target instanceof Actor))
					$actor->addFighter($this);
			}
			Debug::addDebugLine(' Round done computing.');
		}
		
		public function checkAlive($killer = null)
		{
		
			if(!$this->isAlive())
			{
			
				$this->clearFighters();
				$killer->clearFighters();
			
				if($killer instanceof Actor && $this->getAlias() != $killer->getAlias())
				{
					Debug::addDebugLine($killer->getAlias(true) . ' killed ' . $this->getAlias() . ".");
					Server::out($killer, 'You have KILLED ' . $this->getAlias() . '.');
					Server::out($killer, "You get " . $killer->applyExperienceFrom($this) . " experience for your kill.");
				}
				
				if($this instanceof User)
					$nouns = $this->getAlias();
				elseif($this instanceof Mob)
					$nouns = $this->getNoun();
				
				$corpse = new Container(0,
										'A corpse of ' . $this->getAlias() . ' lies here.',
										'a corpse of ' . $this->getAlias(),
										'corpse ' . $this->getAlias(),
										0,
										100,
										0,
										'corpse',
										$this->inventory,
										false);
				
				$this->room->getInventory()->add($corpse);
				
				if($this instanceof User)
				{
					$this->inventory = new Inventory('users', $this->id);
					$this->inventory->save();
				}
				
				$this->setHp(1);
				Debug::addDebugLine($this->getAlias(true) . ' died.');
				Server::out($this, 'You have been KILLED!');
				if($this instanceof Mob)
					$this->handleRespawn();
				
				$target = $this->getTarget();
			}
		
		}
		
		public function applyExperienceFrom(Actor $actor)
		{
			Debug::addDebugLine("Applying experience from " . $actor->getAlias() . ' to ' . $this->getAlias() . '.');
			$experience = $actor->getKillExperience();
			$level_diff = $this->level - $actor->getLevel();
			
			if($level_diff > 5)
				$experience *= 1.3;
			else if($level_diff > 3)
				$experience *= 1.2;
			else if($level_diff > 0)
				$experience *= 1.1;
			else if($level_diff < 0)
				$experience *= 0.75;
			else if($level_diff < -3)
				$experience *= 0.25;
			else
				$experience *= 0.1;
			
			$experience = (int) $experience;
			
			$this->experience += $experience;
			
			$diff = (int) ($this->experience / $this->exp_per_level);
			if($diff > $this->level)
				$this->levelUp();
			
			return $experience;
		}
		
		public function lookDescribe()
		{
		
			if($this->sex === 'm')
				$sex = 'him';
			else if($this->sex === 'f')
				$sex = 'her';
			
			if(!isset($sex))
				$sex = 'it';
			
			return 'You see nothing special about ' . $sex . '.' . "\r\n" . 
					$this->getAlias(true) . ' the ' . strtolower($this->race->getRaceStr()) . ' ' . $this->getStatus() . '.';
		
		}
		
		private function levelUp($display = true)
		{
			Debug::addDebugLine($this->getAlias(true) . ' levels up.');
			$hp_gain = ceil($this->con * 0.5);
			$movement_gain = ceil(($this->con * 0.6) + ($this->dex * 0.9) / 1.5);
			$mana_gain = ceil(($this->wis + $this->int / 2) * 0.8);
			
			$this->max_hp += (int) $hp_gain;
			$this->max_mana += (int) $mana_gain;
			$this->max_movement += (int) $movement_gain;
			
			$this->level = (int) ($this->experience / $this->exp_per_level);
			
			if($display)
			{
				Server::out($this, 'You LEVELED UP!');
				Server::out($this, 'Congratulations, you are now level ' . $this->level . '!');
			}
		}
		
		public function getKillExperience()
		{
			return 300;
		}
		abstract public function getTable();
		public function getNoun()
		{
			return $this->alias;
		}
	}
	
	class Actor_Exception extends Exception
	{
		const MAX_ATTRIBUTE = 0;
	}

?>
