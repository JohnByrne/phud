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
	namespace Commands;
	class Put extends \Mechanics\Command
	{
	
		protected static $dispositions = array(\Mechanics\Actor::DISPOSITION_STANDING, \Mechanics\Actor::DISPOSITION_SITTING);
	
		protected function __construct()
		{
		
			\Mechanics\Command::addAlias(__CLASS__, 'put');
		}
	
		public static function perform(&$actor, $args = null)
		{
			
			$item = $actor->getInventory()->getItemByInput($args);
			
			if(!($item instanceof Item))
				return Server::out($actor, "You don't appear to have that.");
			
			array_shift($args);
			
			$target = $actor->getInventory()->getContainerByInput($args);
			if(!($target instanceof Container))
				$target = $actor->getRoom()->getInventory()->getContainerByInput($args);
			if(!($target instanceof Container))
				return Server::out($actor, "You don't have anything to put that in.");
			
			$item->transferOwnership($actor, $target);
			
			Server::out($actor, "You put " . $item->getShort() . " in " . $target->getShort() . ".");
		}
	}
?>
