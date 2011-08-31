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
	namespace Mechanics;
	abstract class Fighter extends Actor
	{
	
		const MAX_ATTRIBUTE = 25;
		
		protected $experience = 0;
		protected $experience_per_level = 0;
		protected $concentration = 0;
		protected $delay = 0;
		protected $attributes = null;
		protected $battle = null;
		protected $target = null;
		protected $fighting = array();
	
		public function __construct()
		{
			$this->attributes = new Attributes();
			parent::__construct();
		}
		public function getAttributes()
		{
			return $this->attributes;
		}
		public function getHp()
		{
			return $this->attributes->getHp();
		}
		public function getMaxHp()
		{
			$hp = $this->attributes->getMaxHp();
			foreach($this->affects as $a)
				$hp += $a->getAttributes()->getMaxHp();
			return $hp;
		}
		public function getMana()
		{
			return $this->attributes->getMana();
		}
		public function getMaxMana()
		{
			$mana = $this->attributes->getMaxMana();
			foreach($this->affects as $a)
				$mana += $a->getAttributes()->getMaxMana();
			return $mana;
		}
		public function getMovement()
		{
			return $this->attributes->getMovement();
		}
		public function getMaxMovement()
		{
			$movement = $this->attributes->getMaxMovement();
			foreach($this->affects as $a)
				$movement += $a->getAttributes()->getMaxMovement();
			return $movement;
		}
		public function setStr($str)
		{
			$this->attributes->setStr($str);
		}
		public function setInt($int)
		{
			$this->attributes->setInt($int);
		}
		public function setWis($wis)
		{
			$this->attributes->setWis($wis);
		}
		public function setDex($dex)
		{
			$this->attributes->setDex($dex);
		}
		public function setCon($con)
		{
			$this->attributes->setCon($con);
		}
		public function getStr()
		{
			$str = $this->attributes->getStr();
			foreach($this->affects as $a)
				$str += $a->getAttributes()->getStr();
			return $str;
		}
		public function getInt()
		{
			$int = $this->attributes->getInt();
			foreach($this->affects as $a)
				$int += $a->getAttributes()->getInt();
			return $int;
		}
		public function getWis()
		{
			$wis = $this->attributes->getWis();
			foreach($this->affects as $a)
				$wis += $a->getAttributes()->getWis();
			return $wis;
		}
		public function getDex()
		{
			$dex = $this->attributes->getDex();
			foreach($this->affects as $a)
				$dex += $a->getAttributes()->getDex();
			return $dex;
		}
		public function getCon()
		{
			$con = $this->attributes->getCon();
			foreach($this->affects as $a)
				$con += $a->getAttributes()->getCon();
			return $con;
		}
		
		public function getBaseStr()
		{
			return $this->attributes->getStr();
		}
		public function getBaseInt()
		{
			return $this->attributes->getInt();
		}
		public function getBaseWis()
		{
			return $this->attributes->getWis();
		}
		public function getBaseDex()
		{
			return $this->attributes->getDex();
		}
		public function getBaseCon()
		{
			return $this->attributes->getCon();
		}
		
		public function getMaxStr()
		{
			return $this->attributes->getMaxStr();
		}
		public function getMaxInt()
		{
			return $this->attributes->getMaxInt();
		}
		public function getMaxWis()
		{
			return $this->attributes->getMaxWis();
		}
		public function getMaxDex()
		{
			return $this->attributes->getMaxDex();
		}
		public function getMaxCon()
		{
			return $this->attributes->getMaxCon();
		}
		
		public function tick()
		{
			$this->attributes->setHp($this->attributes->getHp() + floor(rand($this->attributes->getMaxHp() * 0.05, $this->attributes->getMaxHp() * 0.1)));
			if($this->attributes->getHp() > $this->attributes->getMaxHp())
				$this->attributes->setHp($this->attributes->getMaxHp());
			$this->attributes->setMana($this->attributes->getMana() + floor(rand($this->attributes->getMaxMana() * 0.05, $this->attributes->getMaxMana() * 0.1)));
			if($this->attributes->getMana() > $this->attributes->getMaxMana())
				$this->attributes->setMana($this->attributes->getMaxMana());
			$this->attributes->setMovement($this->attributes->getMovement() + floor(rand($this->attributes->getMaxMovement() * 0.05, $this->attributes->getMaxMovement() * 0.1)));
			if($this->attributes->getMovement() > $this->attributes->getMaxMovement())
				$this->attributes->setMovement($this->attributes->getMaxMovement());
			parent::tick();
		}
		
		public function setRace($race)
		{
			parent::setRace($race);
			$this->attributes->setMaxStr($this->getRace()->getAttributes()->getMaxStr());
			$this->attributes->setStr($this->getRace()->getAttributes()->getStr());
			$this->attributes->setMaxInt($this->getRace()->getAttributes()->getMaxInt());
			$this->attributes->setInt($this->getRace()->getAttributes()->getInt());
			$this->attributes->setMaxWis($this->getRace()->getAttributes()->getMaxWis());
			$this->attributes->setWis($this->getRace()->getAttributes()->getWis());
			$this->attributes->setMaxDex($this->getRace()->getAttributes()->getMaxDex());
			$this->attributes->setDex($this->getRace()->getAttributes()->getDex());
			$this->attributes->setMaxCon($this->getRace()->getAttributes()->getMaxCon());
			$this->attributes->setCon($this->getRace()->getAttributes()->getCon());
		}
		
		public function getConcentration()
		{
			return $this->concentration;
		}
		
		public function getTarget()
		{
			return $this->target;
		}
		
		public function setTarget(Actor $target = null)
		{
			$this->target = $target;
		}
		
		public function getHpPercent()
		{
			return ($this->attributes->getHp() / $this->attributes->getMaxHp()) * 100;
		}
		public function getStatus()
		{
			
			$statuses = array
			(
				'100' => 'is in excellent condition',
				'99' => 'has a few scratches',
				'75' => 'has some small wounds and bruises',
				'50' => 'has quite a few wounds',
				'30' => 'has some big nasty wounds and scratches',
				'15' => 'looks pretty hurt',
				'0' => 'is in awful condition'
			);
			
			$hp_percent = $this->getHpPercent();
			$descriptor = '';
			foreach($statuses as $index => $status)
				if($hp_percent <= $index)
					$descriptor = $status;
			
			return $descriptor;
		
		}
		public function lookDescribe()
		{
		
			if($this->sex === 'm')
				$sex = 'him';
			else if($this->sex === 'f')
				$sex = 'her';
			
			if(!isset($sex))
				$sex = 'it';
			
			if(!$this->long)
				$this->long = 'You see nothing special about ' . $sex . '.';
			
			return  $this->long . "\r\n" . 
					$this->getAlias(true) . ' ' . $this->getStatus() . '.';
		
		}
		public function setHit($hit)
		{
			$this->attributes->setHit($hit);
		}
		public function setDam($dam)
		{
			$this->attributes->setDam($dam);
		}
		public function setAcSlash($ac_slash)
		{
			$this->attributes->setAcSlash($ac_slash);
		}
		public function setAcBash($ac_bash)
		{
			$this->attributes->setAcBash($ac_bash);
		}
		public function setAcPierce($ac_pierce)
		{
			$this->attributes->setAcPierce($ac_pierce);
		}
		public function setAcMagic($ac_magic)
		{
			$this->attributes->setAcMagic($ac_magic);
		}
		public function getAcSlash()
		{
			$ac = $this->attributes->getAcSlash();
			foreach($this->affects as $a)
				$ac += $a->getAttributes()->getAcSlash();
			return $ac;
		}
		public function getAcBash()
		{
			$ac = $this->attributes->getAcBash();
			foreach($this->affects as $a)
				$ac += $a->getAttributes()->getAcBash();
			return $ac;
		}
		public function getAcPierce()
		{
			$ac = $this->attributes->getAcPierce();
			foreach($this->affects as $a)
				$ac += $a->getAttributes()->getAcPierce();
			return $ac;
		}
		public function getAcMagic()
		{
			$ac = $this->attributes->getAcMagic();
			foreach($this->affects as $a)
				$ac += $a->getAttributes()->getAcMagic();
			return $ac;
		}
		public function isAlive()
		{
			if($this->attributes->getMaxHp() == 0)
				return true; // Creation
			return $this->attributes->getHp() > 0;
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
		public function getDelay()
		{
			return $this->delay;
		}
		public function setHp($hp)
		{
			$this->attributes->setHp($hp);
		}
		public function setMaxHp($max_hp)
		{
			$this->attributes->setMaxHp($max_hp);
		}
		public function setMana($mana)
		{
			$this->attributes->setMana($mana);
		}
		public function setMaxMana($max_mana)
		{
			$this->attributes->setMaxMana($max_mana);
		}
		public function setMovement($movement)
		{
			$this->attributes->setMovement($movement);
		}
		public function setMaxMovement($max_movement)
		{
			$this->attributes->setMaxMovement($max_movement);
		}
		public function increaseHitDam($hit = 0, $dam = 0)
		{
			$this->attributes->setHit($this->attributes->getHit() + $hit);
			$this->attributes->setDam($this->attributes->getDam() + $dam);
		}
		public function decreaseHitDam($hit = 0, $dam = 0)
		{
			$this->attributes->setHit($this->attributes->getHit() - $hit);
			$this->attributes->setDam($this->attributes->getDam() - $dam);
		}
		public function getHit()
		{
			$hit = $this->attributes->getHit();
			foreach($this->affects as $a)
				$hit += $a->getAttributes()->getHit();
			return $hit;
		}
		public function getDam()
		{
			$dam = $this->attributes->getDam();
			foreach($this->affects as $a)
				$dam += $a->getAttributes()->getDam();
			return $dam;
		}
		
		public function decreaseConcentration()
		{
			$this->concentration--;
			if($this->concentration < 0)
				$this->concentration = 0;
		}
		
		public function increaseConcentration()
		{
			$this->concentration++;
			if($this->concentration > 10)
				$this->concentration = 10;
		}
		
		public function attack($attack_name = '', $verb = '')
		{
		
			$victim = $this->getTarget();
			if(!$victim)
				return;
		
			Debug::addDebugLine("Battle round: " . $this->getAlias() . " attacking " . $victim->getAlias() . ". ", false);
			
			//$hand_l = null;//$this->getEquipped()->getEquipmentByPosition(Equipped::POSITION_WIELD_L);
			$attacking_weapon = $this->getEquipped()->getEquipmentByPosition(\Items\Equipment::POSITION_WIELD);
			
			if($attacking_weapon)
			{
				if(!$verb)
					$verb = $attacking_weapon->getVerb();
				$dam_type = $attacking_weapon->getDamageType();
			}
			else
			{
				if(!$verb)
					$verb = $this->getRace()->getUnarmedVerb();
				$dam_type = Damage::TYPE_BASH;
			}
		
			// ATTACKING
			$hit_roll = $this->getHit();
			$dam_roll = $this->getDam();
			
			$hit_roll += ($this->getDex() / self::MAX_ATTRIBUTE) * 4;
			
			// DEFENDING
			$def_roll = ($victim->getDex() / self::MAX_ATTRIBUTE) * 4;
			
			// Size modifier
			$def_roll += 5 - $victim->getRace()->getSize();
			
			if($dam_type == Damage::TYPE_BASH)
				$ac = $victim->getAcBash();
			elseif($dam_type == Damage::TYPE_PIERCE)
				$ac = $victim->getAcPierce();
			elseif($dam_type == Damage::TYPE_SLASH)
				$ac = $victim->getAcSlash();
			else
				$ac = $victim->getAcMagic();
			
			$ac = $ac / 100;	
			
			$roll['attack'] = rand(0, $hit_roll);
			$roll['defense'] = rand(0, $def_roll) - $ac;
			
			// Lost the hit roll -- miss
			if($roll['attack'] <= $roll['defense'])
				$dam_roll = 0;
			
			if($dam_roll < 5)
				$descriptor = 'clumsy';
			elseif($dam_roll < 10)
				$descriptor = 'amateur';
			elseif($dam_roll < 15)
				$descriptor = 'competent';
			else
				$descriptor = 'skillful';
			
			//(Primary Stat / 2) + (Weapon Skill * 4) + (Weapon Mastery * 3) + (ATR Enchantments) * 1.stance modifier
			//((Dexterity*2) + (Total Armor Defense*(Armor Skill * .03)) + (Shield Armor * (shield skill * .03)) + ((Primary Weapon Skill + Secondary Weapon Skill)/2)) * (1. Stance Modification)
			
			if(!$attack_name)
				$attack_name = 'Reg';
			
			$this->damage($victim, $dam_roll);
			$this->announce($victim, $attack_name, $dam_roll, $descriptor, $verb);
			
			$victim->checkAlive($this);
			Debug::addDebugLine(' Round done computing.');
		}
		
		private function announce($actor, $attack_name, $dam_roll, $descriptor, $verb)
		{
			$actors = $this->getRoom()->getActors();
			foreach($actors as $actor_sub)
						Server::out($actor_sub, ($actor_sub->getAlias() == $this->getAlias() ? 'Your' : $this->getAlias(true) . "'s") . ' ' . $descriptor . ' ' . $verb . ' ' . ($dam_roll > 0 ? 'hits ' : 'misses ') . ($actor->getAlias() == $actor_sub->getAlias() ? 'you' : $actor->getAlias()) . '.');
		}
		
		public function damage(Fighter $target, $damage, $type = Damage::TYPE_HIT)
		{
		
			// Don't do anything if dead
			// Don't hit yerself
			// Check for safe rooms, imms, non mobs & non players, etc
			if(!$target->isAlive() || !$this->isAlive() || $this === $target || $target->isSafe())
				return false;
			
			// Check for any skill to defend against a hit, such as parry, dodge, shield block, etc
			if($type === Damage::TYPE_HIT)
			{
				$skills = $target->getAbilitySet()->getAbilitiesByHook(Ability::HOOK_HIT_DEFEND);
				foreach($skills as $skill)
					if($skill->perform($this))
						return false;
			}
			
			$target->setHp($target->getHp() - $damage);
			return true;
			
		}
		
		public function reconcileTarget($args = array())
		{
			$actor_target = $this->getTarget();
			if(!$args)
				return $actor_target;
			
			if(is_array($args))
				$specified_target = $this->getRoom()->getActorByInput($args);
			else if($args instanceof Fighter)
				$specified_target = $args;
				
			if($specified_target === $this)
			{
				Server::out($this, "You can't target yourself!");
				return;
			}
			if(!$actor_target)
			{
				if(!$specified_target)
				{
					Server::out($this, "No one is there.");
					return false;
				}
				$this->initiateBattle($specified_target);
				return $specified_target;
			}
			else if(!($actor_target instanceof Fighter))
			{
				Server::out($this, "I don't think they would like that very much.");
				return false;
			}
			else if($actor_target && !$specified_target)
				return $actor_target;
			else if($actor_target === $specified_target)
				return $actor_target;
			Server::out($this, "Whoa there sparky, don't you think one is enough?");
			return false;
		}
		
		public function checkAlive($killer = null)
		{
		
			if(!$this->isAlive())
			{
			
				$this->setTarget(null);
				$killer->setTarget(null);
			
				if($this->getAlias() != $killer->getAlias())
				{
					Debug::addDebugLine($killer->getAlias(true) . ' killed ' . $this->getAlias() . ".");
					Server::out($killer, 'You have KILLED ' . $this->getAlias() . '.');
					$killer->applyExperienceFrom($this);
				}
				
				if($this instanceof \Living\User)
					$nouns = $this->getAlias();
				elseif($this instanceof \Living\Mob)
					$nouns = $this->getNouns();
				
				$gold = intval($this->gold * 0.75);
				$killer->addGold($gold);
				$this->gold -= $gold;
				
				$silver = intval($this->silver * 0.75);
				$killer->addSilver($silver);
				$this->silver -= $silver;
				
				$copper = intval($this->copper * 0.75);
				$killer->addCopper($copper);
				$this->copper -= $copper;
				
				$this->gold = intval($this->gold/3);
				$this->silver = intval($this->silver/3);
				$this->copper = intval($this->copper/3);
				
				$corpse = new \Items\Corpse();
				$corpse->setLong('A corpse of ' . $this->getAlias() . ' lies here.');
				$corpse->setShort('a corpse of ' . $this->getAlias());
				$corpse->setNouns('corpse '.$nouns);
				$corpse->setWeight(100);
				$corpse->getInventory()->transferItemsFrom($this->inventory);
				
				$corpse->setGold($this->gold * 2);
				$corpse->setSilver($this->silver * 2);
				$corpse->setCopper($this->copper * 2);
				
				$this->afterDeath($killer);
				$this->getRoom()->getInventory()->add($corpse);
				
				if($this instanceof \Living\User)
				{
					$this->inventory = new Inventory();
					$this->inventory->save();
				}
				
				if($killer instanceof \Living\User)
					\Mechanics\Server::out($killer, "\n".$killer->prompt(), false);
				
				$this->handleDeath();
				return false;
			}
			return true;
		}
		
		protected function afterDeath($killer)
		{
			$r = -1;//ceil(rand(0, 3));
			if($r < 0)
			{
				return $this->getRoom()->announce($this, "You hear ".$this->getAlias()."'s death cry.");
			}
			else
			{
				$parts = array(
					'brains' => "'s brains splash all over you!",
					'guts' => ' spills '.$this->getDisplaySex().' guts all over the floor.',
					'heart' => "'s heart is torn from ".$this->getDisplaySex(). " chest."
				);
				$r = round(rand(0, sizeof($parts)-1));
				if($r == 1)
				{
					$this->getRoom()->getInventory()->add(new \Items\Food(0, 'The brains of '.$this->getAlias().' is here.', 'the brains of '.$this->getAlias(), 'brains', 0, 1, 1));
					\Mechanics\Server::out($killer, $this->getAlias(true).$parts['brains']);
				}
				else if($r == 2)
				{
					$this->getRoom()->getInventory()->add(new \Items\Food(0, 'The entrails of '.$this->getAlias().' is here.', 'the entrails of '.$this->getAlias(), 'entrails', 0, 1, 1));
					\Mechanics\Server::out($killer, $this->getAlias(true).$parts['guts']);
				}
				else if($r == 3)
				{
					$this->getRoom()->getInventory()->add(new \Items\Food(0, 'The heart of '.$this->getAlias().' is here.', 'the heart of '.$this->getAlias(), 'heart', 0, 1, 1));
					\Mechanics\Server::out($killer, $this->getAlias(true).$parts['heart']);
				}
			}
		}
		
		protected function handleDeath($move_soul = true)
		{
			//if($move_soul)
			//	$this->setRoom($);
			$this->setHp(1);
			Debug::addDebugLine($this->getAlias(true) . ' died.');
			Server::out($this, 'You have been KILLED!');
		}
		
		private function initiateBattle(Actor $actor)
		{
			if($actor === $this)
				return false;
			$this->setTarget($actor);
			if($actor->getBattle())
				return $actor->getBattle()->addActor($this);
			$this->setBattle(new Battle($this));
		}
		
		public function setBattle(Battle $battle)
		{
			$this->battle = $battle;
		}
		
		public function getBattle()
		{
			return $this->battle;
		}
		
		public function applyExperienceFrom(Actor $victim)
		{
			if(!$this->experience_per_level) // Mobs have 0 exp per level
				return 0;
			
			Debug::addDebugLine("Applying experience from " . $victim->getAlias() . ' to ' . $this->getAlias() . '.');
			
			if($this->experience < $this->experience_per_level)
			{
				$experience = $victim->getKillExperience($this);
				$this->experience += $experience;
				Server::out($this, "You get " . $experience . " experience for your kill.");
			}
		}
		
		public function getKillExperience(Fighter $killer)
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
		
		protected function levelUp($display = true)
		{
			Debug::addDebugLine($this->getAlias(true) . ' levels up.');
			$hp_gain = ceil($this->getCon() * 0.5);
			$movement_gain = ceil(($this->getCon() * 0.6) + ($this->getCon() * 0.9) / 1.5);
			$mana_gain = ceil(($this->getWis() + $this->getInt() / 2) * 0.8);
			
			$this->attributes->setMaxHp($this->attributes->getMaxHp() + (int) $hp_gain);
			$this->attributes->setMaxMana($this->attributes->getMaxMana() + (int) $mana_gain);
			$this->attributes->getMaxMovement($this->attributes->getMaxMovement() + (int) $movement_gain);
			
			$this->level = (int) ($this->experience / $this->getExperiencePerLevel());
			
			if($display)
			{
				Server::out($this, 'You LEVELED UP!');
				Server::out($this, 'Congratulations, you are now level ' . $this->level . '!');
			}
		}
		
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
		
		public function getExperience()
		{
			return $this->experience;
		}
		
		public function getExperiencePerLevel()
		{
			return $this->experience_per_level; 
		}
		
		public function setExperiencePerLevel($xp = 0)
		{
			if($xp < 1)
				$xp = $this->getExperiencePerLevelFromCP();
			$this->experience_per_level = $xp;
		}
		
		public function getExperiencePerLevelFromCP()
		{
			$cp = $this->getCreationPoints();
		
			if($cp < 30)
				return 1000;
		
			$base_mod = 100;
			if($cp < 99)
				return $cp * $base_mod;
			
			$upper_mod = 200;
			return (100 * $base_mod) + ($cp - 100 * $upper_mod);
		}
		
		public function getCreationPoints()
		{
			return $this->getAbilitySet()->getCreationPoints() + $this->getRace()->getCreationPoints();
		}
	}
?>
