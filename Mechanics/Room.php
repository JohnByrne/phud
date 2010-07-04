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
	class Room
	{
	
		static $instance = array();
		
		private $id = 0;
		private $title = '';
		private $description = '';
		private $north = 0;
		private $south = 0;
		private $east = 0;
		private $west = 0;
		private $up = 0;
		private $down = 0;
		private $door = null;
		private $inventory = null;
		private $area = '';
		private $visibility = 1;
	
		const PURGATORY_ROOM_ID = 7;
	
		public function __construct($id = null)
		{
			
		}
	
		public function loadFrom($row)
		{
			$this->id = $row->id;
			$this->title = $row->title;
			$this->description = $row->description;
			$this->north = $row->north;
			$this->south = $row->south;
			$this->east = $row->east;
			$this->west = $row->west;
			$this->up = $row->up;
			$this->down = $row->down;
			$this->area = $row->area;
			$this->visibility = $row->visibility;
		}
	
		public static function find($id)
		{
		
			if(isset(self::$instance[$id]) === true && self::$instance[$id] instanceof Room)
				return self::$instance[$id];
			
			$row = Db::getInstance()->query(
				'SELECT 
					*
				FROM 
					rooms 
				WHERE id = ?', $id)->getResult()->fetch_object();
			self::$instance[$id] = new self($id);
			self::$instance[$id]->loadFrom($row);
			self::$instance[$id]->setInventory(Inventory::find('room', $id));
			return self::$instance[$id];
		
		}
		public function getVisibility() { return $this->visibility; }
		public function getId() { return $this->id; }
		public function getTitle() { return $this->title; }
		public function getDescription() { return $this->description; }
		public function getNorth()
		{
			$door = Door::findByRoomAndDirection($this->id, 'north');
			
			if($door instanceof Door && $door->getDisposition() != Door::DISPOSITION_OPEN)
				return 0;
			return $this->north;
		}
		public function getSouth()
		{
			$door = Door::findByRoomAndDirection($this->id, 'south');
			if($door instanceof Door && $door->getDisposition() != Door::DISPOSITION_OPEN)
				return 0;	
			return $this->south;
		}
		public function getEast()
		{
			$door = Door::findByRoomAndDirection($this->id, 'east');
			if($door instanceof Door && $door->getDisposition() != Door::DISPOSITION_OPEN)
				return 0;
			return $this->east;
		}
		public function getWest()
		{
			$door = Door::findByRoomAndDirection($this->id, 'west');
			if($door instanceof Door && $door->getDisposition() != Door::DISPOSITION_OPEN)
				return 0;
			return $this->west;
		}
		public function getUp()
		{
			$door = Door::findByRoomAndDirection($this->id, 'up');
			if($door instanceof Door && $door->getDisposition() != Door::DISPOSITION_OPEN)
				return 0;
			return $this->up;
		}
		public function getDown()
		{
			$door = Door::findByRoomAndDirection($this->id, 'down');
			if($door instanceof Door && $door->getDisposition() != Door::DISPOSITION_OPEN)
				return 0;
			return $this->down;
		}
		public function getInventory() { return $this->inventory; }
		public function setInventory(Inventory $inventory) { $this->inventory = $inventory; }
		public function setArea($area) { $this->area = $area; }
		public function getArea() { return $this->area; }
		
		public function setTitle($title) { $this->title = $title; }
		public function setDescription($description) { $this->description = $description; }
		public function setNorth($north) { $this->north = $north; }
		public function setSouth($south) { $this->south = $south; }
		public function setEast($east) { $this->east = $east; }
		public function setWest($west) { $this->west = $west; }
		public function setUp($up) { $this->up = $up; }
		public function setDown($down) { $this->down = $down; }
		
		public function save()
		{
			if($this->id)
				Db::getInstance()->query('UPDATE rooms SET title = ?, description = ?, north = ?, south = ?, east = ?, west = ?, up = ?, down = ?, area = ? WHERE id = ?',
					array($this->title, $this->description, $this->north, $this->south, $this->east, $this->west, $this->up, $this->down, $this->area, $this->id));
			else
			{
				$this->id = Db::getInstance()->query('INSERT INTO rooms (title, description, north, south, east, west, up, down, area) values (?, ?, ?, ?, ?, ?, ?, ?, ?)', 
					array($this->title, $this->description, $this->north, $this->south, $this->east, $this->west, $this->up, $this->down, $this->area))->insert_id;
				self::$instance[$this->id] = $this;
			}
		}
	}

?>
