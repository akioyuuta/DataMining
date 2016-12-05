<?php

include "connect.php";

function check_combination($a , $b){
	$t = count($a);
	$r = 0;
	for($q = 0; $q < $t; $q++){
		for($w = 0; $w < $t; $w++){
			if($a[$q] == $b[$w]){
				$r++;
			}
			if($r == $t - 1){
				return true;
			}
		}
	}
	return false;
}

function check_exact($a , $b){
	$t = count($a);
	$r = 0;
	for($q = 0; $q < $t; $q++){
		for($w = 0; $w < $t; $w++){
			if($a[$q] == $b[$w]){
				$r++;
			}
			if($r == $t){
				return true;
			}
		}
	}
	return false;
}

function validate_combination($a, $t){
	$y = count($a);
	for($q = 0; $q < $y; $q++){
		$tmp = array();
		for($w = 0; $w < $y - 1; $w++){
			$tmp[] = $a[($q + $w) % $y];
		}
		$found = false;
		//var_dump($tmp);
		//echo "<br>";
		foreach ($t as $key => $value) {
			if(check_exact($value, $tmp)){
				$found = true;
			}
		}
		if(!$found){
			return array();
		}
	}
	return $a;
}

function apriori($min_support){
	global $con;
	$ans = array();
	// ========================== Step 1 : Calculate Min Support  ================================
	$sql = "SELECT DISTINCT(invoice_number) FROM detail_invoice";
	$res = mysqli_query($con, $sql);
	$count = mysqli_num_rows($res);

	//$min_support = 0.003;
	$count_support = $min_support * $count;

	// =================================== End of Step 1 =========================================

	// ======================== Step 2 : Prune All Item < Min Support ============================

	$sql = "SELECT stock_code, COUNT(stock_code) AS count FROM detail_invoice GROUP BY stock_code";
	$res = mysqli_query($con, $sql);

	$accept = array();

	while($row = mysqli_fetch_assoc($res)) {
		if($row['count'] >= $count_support) {
			$accept[] = array("stock_code" => $row['stock_code'], "count" => $row['count']);
		}
	}
	
	// ==================================== End of Step 2 ========================================

	// ========================== Step 3 : Create a Combination ==================================

	$n = count($accept);

	$com = array();

	for($q = 0; $q < $n; $q++){
		for($w = $q + 1; $w < $n; $w++){
			array_push($com,$accept[$q]['stock_code'].'|'.$accept[$w]['stock_code']);
		}
	}
	$result = array();

	foreach($com as $str) { 
		$data = explode("|", $str);
		$sql = "SELECT invoice_number, count(invoice_number) as count FROM detail_invoice WHERE stock_code = '".$data[0]."' OR stock_code = '".$data[1]."' GROUP BY invoice_number HAVING count = 2";
		$res = mysqli_query($con, $sql);
		$count = mysqli_num_rows($res);
		$result[] = array("item1" => $data[0], "item2" => $data[1], "count" => $count);
	}
	$accept1 = array();
	foreach($result as $value) {
		if($value['count'] >= $count_support){
			$accept1[] = $value;
		}
	}

	$items = array();
	foreach($accept1 as $value){
		$items[] = array($value['item1'],$value['item2']);
	}
	$ans['com-2'] = $items;
	// ==================================== End of Step 3 ========================================

	// ========================== Step 4 : Loop for all Combination ==============================

	$ccb = 3;
	while(count($items) > 1) {
		$n = count($items);
		$combination = array();
		for($i = 0; $i < $n; $i++){
			for($j = 0; $j< $n; $j++){
				if($j == $i){
					continue;
				}
				if(check_combination($items[$i], $items[$j])){
					$tmp = array();
					foreach ($items[$i] as $key => $value) {
						if(!in_array($value, $tmp)){
							$tmp[] = $value;
						}
					}
					foreach ($items[$j] as $key => $value) {
						if(!in_array($value, $tmp)){
							$tmp[] = $value;
						}
					}
					$combination[] = $tmp;
				}

			}
		}
		$tmp_item = array();
		foreach($combination as $value) {
			if(count(validate_combination($value, $items)) > 0){
				sort($value);
				if(!in_array($value, $tmp_item)){
					$tmp_item[] = $value;
				}
			}
		}
		$items = $tmp_item;
		$ans["com-".$ccb++] = $tmp_item;
	}

	// ==================================== End of Step 4 ========================================

	// =================================== Returned Value ========================================

	return $ans;

	// ===========================================================================================
}


function calculate_confidence($a, $b){
	global $con;
	$items = array_merge($a,$b);
	$sql = "SELECT COUNT(invoice_number) AS count FROM detail_invoice WHERE ";
	$n = count($items);
	for($q = 0; $q < $n; $q++){
		$sql .= 'stock_code = "'.$items[$q].'"';
		if($q < $n - 1){
			$sql .= ' OR ';
		}
	}
	$sql .= " GROUP BY invoice_number HAVING count = 3; ";
	$res = mysqli_query($con, $sql);
	$tmp = mysqli_num_rows($res);
	$sql = "SELECT * FROM detail_invoice WHERE ";
	$n = count($a);
	for($q = 0; $q < $n; $q++){
		$sql .= 'stock_code = "'.$a[$q].'"';
		if($q < $n - 1){
			$sql .= ' OR ';
		}
	}
	$res = mysqli_query($con, $sql);
	$count = mysqli_num_rows($res);
	return $tmp / $count * 100;
}

function calculate_support($items){
	global $con;
	$sql = "SELECT * FROM detail_invoice WHERE ";
	$n = count($items);
	for($q = 0; $q < $n; $q++){
		$sql .= 'stock_code = "'.$items[$q].'"';
		if($q < $n - 1){
			$sql .= ' OR ';
		}
	}
	$res = mysqli_query($con, $sql);
	$tmp = mysqli_num_rows($res);
	$sql = "SELECT invoice_number FROM invoice";
	$res = mysqli_query($con, $sql);
	$count = mysqli_num_rows($res);
	return $tmp / $count * 100;
}

function calculate_support_confidence($items){
	$n = count($items);
	$com = array();
	for($q = 0; $q < $n; $q++){
		for($w = 0; $w < $n - 1; $w++){
			$left = array();
			$right = array();
			for($e = 0; $e < $n; $e++){
				if($e <= $w){
					$left[] = $items[($q + $e) % $n];
				}else{
					$right[] = $items[($q + $e) % $n];
				}
			}
			$support = calculate_support(array_merge($left,$right));
			$confidence = calculate_confidence($left, $right);
			$com[] = array("left" => $left, "right" => $right, "support" => $support, "confidence" => $confidence);
		}
	}
	return $com;
}

function print_rule($left, $right){
	$str = '';
	$n = count($left);
	for($q = 0; $q < $n; $q++){
		$str .= $left[$q];
		if($q < $n - 1){
			$str .= ' ^ ';
		}
	}
	$str .= ' => ';
	$n = count($right);
	for($q = 0; $q < $n; $q++){
		$str .= $right[$q];
		if($q < $n - 1){
			$str .= ' ^ ';
		}
	}
	return $str;
}

?>