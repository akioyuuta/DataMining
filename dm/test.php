<?php

include "function.php";

$res = calculate_support_confidence(array("22469","22470","85123A"));

echo json_encode($res);

echo "<br>";

//$support = calculate_support(array_merge($left,$right));
//$confidence = calculate_confidence(array("22469"), array("22470","85123A"));



?>