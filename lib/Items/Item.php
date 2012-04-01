<?php
namespace Phud\Items;
use Phud\Affectable,
	Phud\EasyInit,
	Phud\Attributes,
	Phud\Nouns;

class Item
{
	use Affectable, EasyInit, Nouns;

	protected $short = 'a generic item';
	protected $long = 'A generic item lays here';
	protected $value = 0;
	protected $weight = 0.0;
	protected $can_own = true;
	protected $attributes = null;
	protected $level = 0;
	protected $material = 'generic';
	protected $initializing_properties = [];
	protected $repop = 100;
	
	const MATERIAL_ADAMANTITE = 'adamantite';
	const MATERIAL_ALLOY = 'alloy';
	const MATERIAL_ALUMINUM = 'aluminum';
	const MATERIAL_BRASS = 'brass';
	const MATERIAL_BRONZE = 'bronze';
	const MATERIAL_COPPER = 'copper';
	const MATERIAL_COTTON = 'cotton';
	const MATERIAL_FOOLS_GOLD = 'fools gold';
	const MATERIAL_GOLD = 'gold';
	const MATERIAL_IRON = 'iron';
	const MATERIAL_LEAD = 'lead';
	const MATERIAL_LEATHER = 'leather';
	const MATERIAL_METAL = 'metal';
	const MATERIAL_MINERAL = 'mineral';
	const MATERIAL_MITHRIL = 'mithril';
	const MATERIAL_NICKEL = 'nickel';
	const MATERIAL_OBSIDIAN = 'obsidian';
	const MATERIAL_PEWTER = 'pewter';
	const MATERIAL_PLATINUM = 'platinum';
	const MATERIAL_SILVER = 'silver';
	const MATERIAL_STAINLESS = 'stainless';
	const MATERIAL_STEEL = 'steel';
	const MATERIAL_STONE = 'stone';
	const MATERIAL_TIN = 'tin';
	const MATERIAL_TINFOIL = 'tinfoil';
	const MATERIAL_TITANIUM = 'titanium';
	const MATERIAL_WIRE = 'wire';
	const MATERIAL_WOOD = 'wood';
	
	public function __construct($properties = [])
	{
		$this->initializing_properties = $properties;
		$this->attributes = new Attributes();
		$this->initializeProperties($properties, [
			'attributes' => function($actor, $property, $value) {
				foreach($value as $attr => $attr_value) {
					$actor->getAttributes()->setAttribute($attr, $attr_value);
				}
			}
		]);
	}
	
	public function setShort($short)
	{
		$this->short = $short;
	}
	
	public function setLong($long)
	{
		$this->long = $long;
	}
	
	public function setValue($value)
	{
		$this->value = $value;
	}
	
	public function setWeight($weight)
	{
		$this->weight = $weight;
	}
	
	public function setCanOwn($can_own)
	{
		$this->can_own = $can_own;
	}
	
	public function setLevel($level)
	{
		$this->level = $level;
	}
	
	public function setMaterial($material)
	{
		$this->material = $material;
	}

	public function getInitializingProperties()
	{
		return $this->initializing_properties;
	}

	public function getRepop()
	{
		return $this->repop;
	}
	
	public function getShort()
	{
		return $this->short;
	}
	
	public function getLong()
	{
		return $this->long;
	}
	
	public function getCanOwn()
	{
		return $this->can_own;
	}
	
	public function getValue()
	{
		return $this->value;
	}
	
	public function getWeight()
	{
		return $this->weight;
	}
	
	public function getLevel()
	{
		return $this->level;
	}
	
	public function getMaterial()
	{
		return $this->material;
	}
	
	public function getAttribute($key)
	{
		return $this->attributes->getAttribute($key);
	}
	
	public function getAttributes()
	{
		return $this->attributes;
	}
	
	public function transferOwnership($from, $to)
	{
		$from->removeItem($this);
		$to->addItem($this);
	}
	
	public static function getMaterials()
	{
		return array(
					self::MATERIAL_ADAMANTITE,
					self::MATERIAL_ALLOY,
					self::MATERIAL_ALUMINUM,
					self::MATERIAL_BRASS,
					self::MATERIAL_BRONZE,
					self::MATERIAL_COPPER,
					self::MATERIAL_COTTON,
					self::MATERIAL_FOOLS_GOLD,
					self::MATERIAL_GOLD,
					self::MATERIAL_IRON,
					self::MATERIAL_LEAD,
					self::MATERIAL_LEATHER,
					self::MATERIAL_METAL,
					self::MATERIAL_MINERAL,
					self::MATERIAL_MITHRIL,
					self::MATERIAL_NICKEL,
					self::MATERIAL_OBSIDIAN,
					self::MATERIAL_PEWTER,
					self::MATERIAL_PLATINUM,
					self::MATERIAL_SILVER,
					self::MATERIAL_STAINLESS,
					self::MATERIAL_STEEL,
					self::MATERIAL_STONE,
					self::MATERIAL_TIN,
					self::MATERIAL_TINFOIL,
					self::MATERIAL_TITANIUM,
					self::MATERIAL_WIRE,
					self::MATERIAL_WOOD
				);
	}
	
	public static function findMaterial($material)
	{
		$materials = self::getMaterials();
		$key = array_search($material, $materials);
		if($key !== false)
			return $materials[$key];
		foreach($materials as $m)
			if(strpos($m, $material) === 0)
				return $m;
		return false;
	}
	
	public function getInformation()
	{
		return 
			"\n=====================\n".
			"== Base Attributes ==\n".
			"=====================\n".
			"level:            ".$this->getLevel()."\n".
			"nouns:            ".$this->getNouns()."\n".
			"short:            ".$this->getShort()."\n".
			"material:         ".$this->getMaterial()."\n".
			"value:            ".$this->getValue()."\n".
			"weight:           ".$this->getWeight()."\n".
			"ownable:          ".$this->getCanOwn()."\n".
			"long:\n".
			$this->getLong()."\n".
			"=====================\n".
			"== Affects         ==\n".
			"=====================\n".
			$this->attributes->getAttributeLabels();
	}
	
	public function __toString()
	{
		return $this->short;
	}

	protected function getDefaultNouns()
	{
		return $this->short;
	}
}
?>
