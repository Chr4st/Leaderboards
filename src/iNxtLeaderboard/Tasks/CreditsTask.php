<?php

namespace iNxtLeaderboard\Tasks;

use pocketmine\{Server,Player};

use pocketmine\scheduler\Task;

use iNxtLeaderboard\Leaderboard;

class CreditsTask extends Task {

	private $main;

	public function __construct(Leaderboard $main) {
		$this->main = $main;
	}

	public function onRun($tick) {
		foreach($this->main->getServer()->getOnlinePlayers() as $player) {
			$this->main->updateCredits($player);
		}
	}
}