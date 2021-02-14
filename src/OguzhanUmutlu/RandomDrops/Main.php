<?php

namespace OguzhanUmutlu\RandomDrops;

use pocketmine\item\ItemFactory;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {
  public function onEnable(): void {
    $this->saveResource("config.yml");
    $this->config = new Config($this->getDataFolder()."config.yml");
    $this->table = new Config($this->getDataFolder()."randomizedtable.yml", Config::YAML, ["tables" => []]);
    $used = $this->table->getNested("tables");
    if(count($used) === count($this->config->getNested("droppingitems"))) {
      $used = [];
      $this->table->setNested("tables", []);
    }
    $this->used = $used;
    
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }
  public function onBreak(BlockBreakEvent $e) {
    $blockid = $e->getBlock()->getId();
    $blockmeta = $e->getBlock()->getDamage();
    if($e->getPlayer()->getGamemode() != 0) {
      return;
    }
    if(!$this->table->getNested($blockid."_".$blockmeta)) {
      $cleared = [];
      foreach($this->config->getNested("droppingitems") as $x) {
        if(!in_array($x, $this->used)) {
          array_push($cleared, $x);
        }
      }
      $randval = $cleared[rand(0, count($cleared)-1)];
      $es = $this->table->getNested("tables");
      array_push($es, $blockid."_".$blockmeta);
      $this->table->setNested("tables", $es);
      $this->table->setNested($blockid."_".$blockmeta, $randval);
      $this->table->save();
      $this->table->reload();
    }
    $imp = explode("_", $this->table->getNested($blockid."_".$blockmeta));
    $item = Item::get($imp[0], $imp[1]);
    $e->setDrops([$item]);
  }
}
