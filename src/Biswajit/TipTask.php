<?php

namespace Biswajit;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\Player;
use Biswajit\Main;

Class TipTask extends Task{
    
    public function plugin(){
        $plugin = Server::getInstance()->getPluginManager()->getPlugin("BuyFlyTime");
        return $plugin;
    }
         

    public function onRun(): void{
        foreach ($this->plugin()->getServer()->getOnlinePlayers() as $player) {
        if(!$this->plugin()->player->exists($player->getName()))break;            
            
            $times = $this->plugin()->getTime($player);
            $h = $times["h"];
            $m = $times["m"];                       $s = $times["s"];
            if($player->getAllowFlight() == true && !$player->hasPermission("fly.time") && !$player->isCreative()){
                $player->sendTip("§l§c•§a Flight time:§e ".$h.":".$m.":".$s);    
                if($h == 0 && $m == 0 && $s == 0){
                    $player->setAllowFlight(false);
                    $player->setFlying(false);
                    break;
                } 
                if($m == 0 && $h > 0){
                    $m = 59;
                    $h = $h-1;
                }
                if($s == 0 && $m >0){
                    $s = 60;
                    $m = $m-1;
                }
                $s = $s-1;
                $this->plugin()->player->set($player->getName(),$h.":".$m.":".$s);
                $this->plugin()->player->save();
            }
        }
	}
}