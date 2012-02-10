<?php
namespace Mechanics;
class Area
{
	protected $fp = null;
	protected $last_room = null;

	public function __construct($area)
	{
		$this->fp = fopen($area, 'r');
		while($line = $this->readLine()) {
			switch($line) {
				case strpos($line, 'room') === 0:
					$this->loadRoom(substr($line, strpos($line, ' ')+1));
					break 1;
				case strpos($line, 'item') === 0:
					$this->loadItems();
					break 1;
			}
		}
	}

	protected function loadRoom($id)
	{
		$getdir = function($d) {
			$directions = ['north', 'south', 'east', 'west', 'up', 'down'];
			foreach($directions as $dir) {
				if(strpos($dir, $d) === 0) {
					return $dir;
				}
			}
		};
		$p = [];
		$p['id'] = $id;
		$p['title'] = $this->readLine();
		$p['description'] = $this->readBlock();
		$p['area'] = $this->readLine();
		$line = $this->readLine();
		$break = false;
		while($line) {
			if(substr($line, -1) === '~') {
				$line = substr($line, 0, -1);
				$break = true;
			}
			list($dir, $id) = explode(' ', $line);
			$p[$getdir($dir)] = $id;
			if($break) {
				break;
			}
			$line = $this->readLine();
		}
		$this->last_room = new Room($p);
	}

	protected function loadItems()
	{
		$p = [];
		$class = $this->readLine();
		$p['nouns'] = $this->readLine();
		$p['short'] = $this->readLine();
		$p['long'] = $this->readBlock();
		$p['contents'] = $this->readLine();
		$uses = $this->readLine();
		if(substr($uses, -1) === '~') {
			$p['uses'] = substr($uses, 0, -1);
		} else {
			$p['uses'] = $uses;
			$this->loadItems();
		}
		$full_class = 'Items\\'.$class;
		$this->last_room->addItem(new $full_class($p));
	}

	private function readLine()
	{
		$input = fgets($this->fp);
		$line = trim($input);
		if(strpos($line, '#') === 0 || (strlen($line) === 0 && $input !== false)) {
			return $this->readLine();
		}
		return $line;
	}

	private function readBlock()
	{
		$line = '';
		$block = '';
		$break = false;
		while($line = $this->readLine()) {
			if(substr($line, -1) === '~') {
				$line = substr($line, 0, -1);
				$break = true;
			}
			$block .= $line;
			if($break) {
				break;
			}
		}
		return $block;
	}
}
?>