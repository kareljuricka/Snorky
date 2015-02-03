<?php
$cycles = 80000;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
echo " measurement started<br>";
$old =0.33456456363201 ;
ob_start();
 $str = file_get_contents('cache/template._cache.php');
 
 $time =0;
 //echo "<XMP>$str</XMP>";
for($i = 0; $i < $cycles; $i++){
$time_start = microtime(true);

/******************* script part ***********************/

eval('?>'.$str.'<?php;');


/***************** end script part *********************/


// Display Script End time
$time_end = microtime(true);
$time += ($time_end - $time_start)*1000;
}
ob_get_clean();

//dividing with 60 will give the execution time in minutes other wise seconds
//$execution_time = ($time_end - $time_start)*1000;
$execution_time = $time /$cycles;
//execution time of the script
echo '<b>Total Execution Time:</b> '.$time.' mili secs';
echo  "<br><b>Mean Execution Time:</b> $execution_time mili secs";
$diff = $execution_time - $old;
echo  "<br><b>Time difference:</b> $diff mili secs";