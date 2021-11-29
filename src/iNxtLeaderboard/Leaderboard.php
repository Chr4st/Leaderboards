<?php

namespace iNxtLeaderboard;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;

use pocketmine\{Server,Player};

use pocketmine\command\{Command,CommandSender};

use pocketmine\event\player\PlayerExperienceChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\entity\Entity;

use pocketmine\nbt\tag\StringTag;

use pocketmine\utils\Config;

use slapper\events\SlapperCreationEvent;
use onebone\economyapi\EconomyAPI;
use iNxtLeaderboard\Tasks\LevelsTask;
use iNxtLeaderboard\Tasks\KillsTask;
use iNxtLeaderboard\Tasks\CoinsTask;
use iNxtLeaderboard\Tasks\CreditsTask;

class Leaderboard extends PluginBase implements Listener {

	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder());
		$this->runTasks();
		$this->exp = new Config($this->getDataFolder() . "toplevels.yml", Config::YAML);
		$this->k = new Config($this->getDataFolder() . "topkills.yml", Config::YAML);
		$this->kills = $this->getServer()->getPluginManager()->getPlugin("iNxtXPLevels");
		$this->c = new Config($this->getDataFolder() . "topcoins.yml", Config::YAML);
		$this->coins = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		$this->cr = new Config($this->getDataFolder() . "topcredits.yml", Config::YAML);
		$this->credits = $this->getServer()->getPluginManager()->getPlugin("iNxtXPLevels");
	}
	
	public function runTasks() {
		$this->getScheduler()->scheduleRepeatingTask(new LevelsTask($this), 20 * 60);
		$this->getScheduler()->scheduleRepeatingTask(new KillsTask($this), 20 * 60);
		$this->getScheduler()->scheduleRepeatingTask(new CoinsTask($this), 20 * 60);
		$this->getScheduler()->scheduleRepeatingTask(new CreditsTask($this), 20 * 60);
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		switch($cmd->getName()) {                    
			case "top":
			if($sender instanceof Player) {
				$arg = array_shift($args);
				switch($arg) {
					
					case "addtoplevels":
						$command = "slapper create human TopLevels";
						$this->getServer()->getCommandMap()->dispatch($sender, $command); 
						break;
					
					case "addtopkills":
						$command = "slapper create human TopKills";
						$this->getServer()->getCommandMap()->dispatch($sender, $command); 
						break;
					
					case "addtopcoins":
						$command = "slapper create human TopCoins";
						$this->getServer()->getCommandMap()->dispatch($sender, $command); 
						break;

					case "addtopcredits":
						$command = "slapper create human TopCredits";
						$this->getServer()->getCommandMap()->dispatch($sender, $command); 
						break;
					
				}
				break;
			}
			return true;
		}
	}
	
	public function updateXp($player) {
		$this->exp->set($player->getName(), $player->getXpLevel());
		$this->exp->save();
		$this->onTopLevels();
    }
	
	public function updateKills($player) {
		$this->k->set($player->getName(), $this->kills->getKill($player));
		$this->k->save();
		$this->onTopKills();
    }
	
    public function updateCoins($player){
        $this->c->set($player->getName(), EconomyAPI::getInstance()->myMoney($player));
        $this->c->save();
		$this->onTopCoins();
    }

	public function updateCredits($player) {
		$this->cr->set($player->getName(), $this->credits->getCredits($player));
		$this->cr->save();
		$this->onTopCredits();
    }
	
	public function onSlapperCreate(SlapperCreationEvent $event) {
		$entity = $event->getEntity();
		$name = $entity->getNameTag();
		
		if($name == "TopLevels") {
			$entity->namedtag->setString("toplevels", "toplevels");
			$this->onTopLevels();
		}
		
		if($name == "TopKills") {
			$entity->namedtag->setString("topkills", "topkills");
			$this->onTopKills();
		}
		
		if($name == "TopCoins") {
			$entity->namedtag->setString("topcoins", "topcoins");
			$this->onTopCoins();
		}

		if($name == "TopCredits") {
			$entity->namedtag->setString("topcredits", "topcredits");
			$this->onTopCredits();
		}
		
	}
	
	public function onTopLevels() {
		$exp = $this->exp->getAll();
		arsort($exp);
		$exp = array_slice($exp, 0, 10);
		$counter = 1;
		$text = "§l§bTOP LEVELS LEADERBOARD\n";
		foreach($exp as $name => $value){
			$text .= "§e#{$counter} §7{$name} - §e{$value}\n";
			$counter++;
		}
		foreach($this->getServer()->getLevels() as $level){
			foreach($level->getEntities() as $entity){
				if($entity->namedtag->hasTag("toplevels", StringTag::class)) {
					if($entity->namedtag->getString("toplevels") == "toplevels") {
						$entity->setNameTag($text);
						$entity->getDataPropertyManager()->setFloat(Entity::DATA_BOUNDING_BOX_HEIGHT, 3);
						$entity->getDataPropertyManager()->setFloat(Entity::DATA_SCALE, 0.0);
					}
				}
			}
		}
	}
	
	public function onTopKills() {
		$kills = $this->k->getAll();
		arsort($kills);
		$kills = array_slice($kills, 0, 10);
		$counter = 1;
		$text = "§l§bTOP KILLS LEADERBOARD\n";
		foreach($kills as $name => $value){
			$text .= "§e#{$counter} §7{$name} - §e{$value}\n";
			$counter++;
		}
		foreach($this->getServer()->getLevels() as $levels){
			foreach($levels->getEntities() as $entity){
				if($entity->namedtag->hasTag("topkills", StringTag::class)) {
					if($entity->namedtag->getString("topkills") == "topkills") {
						$entity->setNameTag($text);
						$entity->getDataPropertyManager()->setFloat(Entity::DATA_BOUNDING_BOX_HEIGHT, 3);
						$entity->getDataPropertyManager()->setFloat(Entity::DATA_SCALE, 0.0);
					}
				}
			}
		}
	}
	
	public function onTopCoins() {
		$coins = $this->c->getAll();
		arsort($coins);
		$coins = array_slice($coins, 0, 10);
		$counter = 1;
		$text = "§l§bTOP COINS LEADERBOARD\n";
		foreach($coins as $name => $value){
			$text .= "§e#{$counter} §7{$name} - §e{$value}\n";
			$counter++;
		}
		foreach($this->getServer()->getLevels() as $levels){
			foreach($levels->getEntities() as $entity){
				if($entity->namedtag->hasTag("topcoins", StringTag::class)) {
					if($entity->namedtag->getString("topcoins") == "topcoins") {
						$entity->setNameTag($text);
						$entity->getDataPropertyManager()->setFloat(Entity::DATA_BOUNDING_BOX_HEIGHT, 3);
						$entity->getDataPropertyManager()->setFloat(Entity::DATA_SCALE, 0.0);
					}
				}
			}
		}
	}
	
	public function onTopCredits() {
		$credits = $this->cr->getAll();
		arsort($credits);
		$credits = array_slice($credits, 0, 10);
		$counter = 1;
		$text = "§l§bTOP CREDITS LEADERBOARD\n";
		foreach($credits as $name => $value){
			$text .= "§e#{$counter} §7{$name} - §e{$value}\n";
			$counter++;
		}
		foreach($this->getServer()->getLevels() as $levels){
			foreach($levels->getEntities() as $entity){
				if($entity->namedtag->hasTag("topcredits", StringTag::class)) {
					if($entity->namedtag->getString("topcredits") == "topcredits") {
						$entity->setNameTag($text);
						$entity->getDataPropertyManager()->setFloat(Entity::DATA_BOUNDING_BOX_HEIGHT, 3);
						$entity->getDataPropertyManager()->setFloat(Entity::DATA_SCALE, 0.0);
					}
				}
			}
		}
	}
}
