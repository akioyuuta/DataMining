<?php

include "connect.php";

$sql = "SELECT DISTINCT(invoice_number) as inv FROM online";
$res = mysqli_query($con, $sql);

$lol = true;
while($row = mysqli_fetch_assoc($res)){
	$sql = "SELECT * FROM online WHERE invoice_number = '".$row['inv']."'";
	$result = mysqli_query($con, $sql);
	$date = '';

	$arr = array();
	while($data = mysqli_fetch_assoc($result)){
		if(!in_array($data['stock_code'],$arr)){
			array_push($arr, $data['stock_code']);
			$sql = "INSERT INTO detail_invoice VALUES(default, '".$data['stock_code']."', '".$data['description']."', ".$data['price'].", ".$data['qty'].", ".$data['customer_id'].",'".$row['inv']."')";
			$r = mysqli_query($con, $sql);
		}
		if($date == ''){
			$date = $data['date_time'];
		}
	}
	$sql = "INSERT INTO invoice VALUES('".$row['inv']."', '".$date."')";
	$r = mysqli_query($con, $sql);
	
}


?>