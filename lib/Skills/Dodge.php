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
	namespace Skills;
    use \Mechanics\Ability\Ability;
    use \Mechanics\Ability\Skill;
    use \Mechanics\Actor;
    use \Mechanics\Server;
    use \Mechanics\Race;

	class Dodge extends Skill
	{
		protected function __construct()
		{
			self::addAlias('dodge', $this);
		}
	
		public function perform(Actor $actor, $args = null)
		{
			$roll = Server::chance();
			switch($actor->getSize())
			{
				case Race::SIZE_TINY:
					$chance += 5;
					break;
				case Race::SIZE_SMALL:
					$chance += 1;
					break;
				case Race::SIZE_LARGE:
					$chance -= 5;
					break;
			}
			
			$roll += $this->getNormalAttributeModifier($actor->getDex());
			
			if($roll < $this->percent)
			{
				Server::out($actor, $args->getAlias(true) . ' dodges your attack!');
				Server::out($args, 'You dodge ' . $actor->getAlias() . "'s attack!");
				return true;
			}
			return false;
		}
	
	}

?>
