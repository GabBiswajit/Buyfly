<?php

namespace Biswajit;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerItemHeldEvent, PlayerJoinEvent, PlayerMoveEvent};
use pocketmine\utils\Config;
use Biswajit\TipTask;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use jojoe77777\FormAPI\SimpleForm;

class Main extends PluginBase implements Listener{


	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
        $this->getLogger()->info("plugin create by Let");	    
        $this->saveDefaultConfig();
        $this->saveResource("config.yml");
        @mkdir($this->getDataFolder(), 0744, true);
		$this->player = new Config($this->getDataFolder() . "player.yml", Config::YAML);
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);        
		$this->money =  $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
         $this->getScheduler()->scheduleRepeatingTask(new TipTask($this), 20);
	}

	public function onJoin(PlayerJoinEvent $ev){
	    $name = $ev->getPlayer()->getName();
        if(!$this->player->exists($name)){
            $this->player->set($name, "0:0:0");
            $this->player->save();
        }
	}
	 
    public function getTime($sender){
       $name = $sender->getName();
       $time = $this->player->get($name);
       $data = explode(":", $time);
       return ["h" => $data[0],
               "m" => $data[1],
               "s" => $data[2]
               ];
    }
	
	public function onCommand(CommandSender $sender, Command $command, String $label, array $args) : bool {
        switch($command->getName()){
            case "buyfly":
             $this->menu($sender);  
            return true;
        }
        return true;
	}
	
    public function menu($sender){
        $formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = new SimpleForm(function(Player $sender, $data){
            if($data == null)return;
            switch($data){
                case 0:
                break;
                case 1:
                    $money = $this->money->myMoney($sender);
                    $cost = $this->config->get("money");                     
                    if($money >= $cost){
                        $this->money->reduceMoney($sender, $cost);
                        $times = $this->getTime($sender);
                        $time = $times["h"] + 24;
                        $this->player->set($sender->getName(), $time.":".$times["m"].":".$times["s"]);
                        $this->player->save(); 
                        $sender->sendMessage("§a§lYou Have Successfully Purchased Fly");
                    }else{
                        $sender->sendMessage("§l§cYou Don't Have Enough Money To Buy");
                    }
                break;
                case 2:
                    $times = $this->getTime($sender);
                    if($times["h"] > 0 or $times["m"] > 0 or $times["s"] > 0){
                        $time = time();
                        if($sender->getAllowFlight() == false){
                            $sender->setAllowFlight(true);
                            $sender->sendMessage("§l§aFlight Mode Enabled");                               
                        }else{
                            $sender->setAllowFlight(false); 
 						    $sender->setFlying(false);                                                $sender->sendMessage("§l§cFlight Mode Disabled");              
                        }
                    }else{
                        $sender->sendMessage("§c§lFlight Time Has Expired, Please Use Command /buyfly To Buy Fly");   
                    }
                break;    
            }
        });
        $money = $this->config->get("money");         
        $time = $this->player->get($sender->getName());        
        $form->setTitle("§l§c♦§a Buy Fly §c♦");
        $form->setContent("§l§c•§b Flight Time Remaining:§e ".$time); 
		$form->addButton("§l§c•§b Close §c•");        
		$form->addButton("§l§c•§b Buy Fly 24 Hours §c•\n§l§c•§b Giá:§e $money §c•");
		$form->addButton("§l§c•§b Turn on Flight Mode§c •");
        $form->sendToPlayer($sender);
	 }
	 
   public function pvp(EntityDamageByEntityEvent $event){
		$entity = $event->getEntity();
        $damager = $event->getDamager();		
		if($entity instanceof Player && $damager instanceof Player && !$damager->isCreative()){
		   if($damager->getAllowFlight() === true){
						$damager->setAllowFlight(false);
						$damager->setFlying(false);
			}
		}
	}	 
}
