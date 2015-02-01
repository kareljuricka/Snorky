<?php 
class Test extends \Snorky\Plugin {
    
    public function run(){
		$scope = crc32("Test");
		RR::SetScope($scope);
		$a = "Durezo!";
		$b = "Ahoj ";
		$c = $b.$c;
		RR:Add("c","pozdrav");
	}
}
