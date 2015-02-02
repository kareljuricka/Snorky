<?php 
class Pltest extends \Snorky\Plugin {
    
    public function run(){
		$scope = crc32("Pltest");
		RR::SetScope($scope);
		$a = "Durezo!";
		$b = "Ahoj ";
		$c = $b.$c;
		RR::Add($c,"c");
	}
        
    public function metoda($a,$b){
        $scope = crc32("Pltest");        
	RR::SetScope($scope);
        $c = "Barel Juricka";
        RR::Add($c, "help");
    }
}
