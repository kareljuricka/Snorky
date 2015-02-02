<?php 
class Pltest extends \Snorky\Plugin {
    
    public function run(){
		$scope = crc32("Test");
		RR::SetScope($scope);
		$a = "Durezo!";
		$b = "Ahoj ";
		$c = $b.$c;
		RR::Add("c");
	}
        
    public function metoda($a,$b){
        $c = 25;
        RR::Add("c", "help");
    }
}
