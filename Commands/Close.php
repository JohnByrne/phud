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
	class Close extends \Mechanics\Command
	{
	
		protected $dispositions = array(\Mechanics\Actor::DISPOSITION_STANDING);
	
		protected function __construct()
		{
			new \Mechanics\Alias('close', $this);
		}
	
		public function perform(\Mechanics\Actor $actor, $args = array())
		{
		
			if(sizeof($args) == 1)
				return \Mechanics\Server::out($actor, 'Close what?');
			
			$door = $actor->getRoom()->getDoorByInput($args[1]);
			
			if(!empty($door) && !$door->isHidden())
			{
				switch($door->getDisposition())
				{
					case \Mechanics\Door::DISPOSITION_OPEN:
						$door->setDisposition(\Mechanics\Door::DISPOSITION_CLOSED);
						$door->getParnterDoor()->setDisposition(\Mechanics\Door::DISPOSITION_CLOSED);
						return \Mechanics\Server::out($actor, 'You close ' . $door->getShort() . '.');
					case \Mechanics\Door::DISPOSITION_CLOSED:
					case \Mechanics\Door::DISPOSITION_LOCKED:
						return \Mechanics\Server::out($actor, ucfirst($door->getShort()) . ' is already closed.');
				}					
			}
			return \Mechanics\Server::out($actor, "You can't close anything like that.");
		}
	}
?>
