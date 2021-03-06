<?php
namespace Phud\Items;
use	Phud\Server,
	Phud\Actors\Actor;

class Drink extends Item
{
	protected $short = 'a generic drink container';
	protected $long = 'A generic drink container lays here';
	protected $amount = 0;
	protected $contents = '';
	protected $thirst = 0;
	protected $uses = 0;
	
	public function getAmount()
	{
		return $this->amount;
	}
	
	public function setAmount($amount)
	{
		$this->amount = $amount;
		$this->uses = $amount;
	}
	
	public function drink(Actor $actor)
	{
		if($this->uses === 0) {
			return false;
		}
		
		if($actor->increaseThirst($this->thirst)) {
			$this->uses--;
			return true;
		}
	}
	
	private function fill()
	{
		$this->uses = $this->amount;
	}
	
	public function getContents()
	{
		return $this->contents;
	}
	
	public function setContents($contents)
	{
		$this->contents = $contents;
		$this->fill();
	}

	public function setThirst($thirst)
	{
		$this->thirst = $thirst;
	}

	public function getThirst()
	{
		return $this->thirst;
	}
}
