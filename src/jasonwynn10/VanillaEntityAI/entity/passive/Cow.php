<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\passive;

use jasonwynn10\VanillaEntityAI\entity\AnimalBase;
use jasonwynn10\VanillaEntityAI\entity\Interactable;
use jasonwynn10\VanillaEntityAI\entity\passiveaggressive\Player;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\item\Bucket;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class Cow extends AnimalBase implements Interactable {
	public const NETWORK_ID = self::COW;
	public $width = 1.5;
	public $height = 1.2;

	public function initEntity() : void {
		$this->setMaxHealth(10);
		parent::initEntity();
	}

	/**
	 * @param int $tickDiff
	 *
	 * @return bool
	 */
	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff); // TODO: Change the autogenerated stub
	}

	public function onUpdate(int $currentTick) : bool {
		if($this->closed){
			return false;
		}
		$tickDiff = $currentTick - $this->lastUpdate;
		if($this->attackTime > 0) {
			$this->move($this->motion->x * $tickDiff, $this->motion->y, $this->motion->z * $tickDiff);
			$this->motion->y -= 0.2 * $tickDiff;
			$this->updateMovement();
			return parent::onUpdate($currentTick);
		}
		return parent::onUpdate($currentTick);
	}

	/**
	 * @return Item[]
	 */
	public function getDrops() : array {
		$drops = parent::getDrops();
		if(!$this->isBaby()) {
			if($this->isOnFire()) {
				$drops[] = ItemFactory::get(Item::COOKED_BEEF, 0, mt_rand(1, 3));
			}else{
				$drops[] = ItemFactory::get(Item::RAW_BEEF, 0, mt_rand(1, 3));
			}
			$drops[] = ItemFactory::get(Item::LEATHER, 0, mt_rand(0, 2));
			return $drops;
		}else {
			return $drops;
		}
	}

	/**
	 * @return int
	 */
	public function getXpDropAmount() : int {
		$exp = parent::getXpDropAmount();
		if(!$this->isBaby()) {
			$exp += mt_rand(1, 3);
			return $exp;
		}
		return $exp;
	}

	/**
	 * @return string
	 */
	public function getName() : string {
		return "Cow";
	}

	/**
	 * @param Entity $entity
	 */
	public function onCollideWithEntity(Entity $entity) : void {
		// TODO: Implement onCollideWithEntity() method.
	}

	public function onPlayerLook(Player $player) : void {
		$hand = $player->getInventory()->getItemInHand();
		if($hand instanceof Bucket and $hand->getDamage() === 0) { // check for empty bucket
			$this->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "Milk");
		}
	}

	public function onPlayerInteract(Player $player) : void {
		$hand = $player->getInventory()->getItemInHand();
		if($hand instanceof Bucket and $hand->getDamage() === 0) { // check for empty bucket
			$item = ItemFactory::get(Item::BUCKET, 1);
			if($player->isSurvival()){
				if($hand->getCount() === 0){
					$player->getInventory()->setItemInHand($item);
				}else{
					$player->getInventory()->setItemInHand($hand);
					$player->getInventory()->addItem($item);
				}
			}else{
				$player->getInventory()->addItem($item);
			}
			$this->level->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_MILK, 0, EntityIds::PLAYER, $this->isBaby());
		}
	}
}