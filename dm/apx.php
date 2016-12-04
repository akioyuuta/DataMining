<?php 

include "connect.php";

$sql = "SELECT invoice_number FROM invoice";
$res = mysqli_query($con, $sql);
$count = mysqli_num_rows($res);

$min_support = 0.003;
$count_support = $min_support * $count;

echo "Min Support : ".$min_support."<br>";
echo "Count All : ".$count."<br>";
echo "Min Count : ".$count_support."<br>";

$sql = "SELECT stock_code, COUNT(stock_code) AS count FROM detail_invoice GROUP BY stock_code";
$res = mysqli_query($con, $sql);

$accept = array();

?>
<table width="100%" border="1">
	<thead>
		<tr>
			<th>#</th>
			<th>Stock Code</th>
			<th>Count</th>
			<th>Status</th>
		</tr>
	</thead>
	<?php $co = 1; $prune = 0; $acc = 0; ?>
	<tbody>
		<?php while($row = mysqli_fetch_assoc($res)) { ?>
			<tr bgcolor="<?php echo $row['count'] >= $count_support ? 'green':'red'; ?>">
				<td><?php echo $co++; ?></td>
				<td><?php echo $row['stock_code']; ?></td>
				<td><?php echo $row['count']; ?></td>
				<td>
					<?php if($row['count'] >= $count_support) { ?>
						<?php 

						 $acc ++; 
						 $accept[] = array("stock_code" => $row['stock_code'], "count" => $row['count']);

						?>
						Accepted
					<?php } else {  $prune++; ?>
						Pruned
					<?php } ?>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php

echo "Accepted : ".$acc."<br>";
echo "Pruned : ".$prune."<br>";

?>
<h2>Accepted</h2>
<table width="100%" border="1">
	<thead>
		<tr>
			<th>#</th>
			<th>Stock Code</th>
			<th>Count</th>
		</tr>
	</thead>
	<?php $co = 1; ?>
	<tbody>
		<?php foreach($accept as $value) { ?>
			<tr>
				<td><?php echo $co++; ?></td>
				<td><?php echo $value['stock_code']; ?></td>
				<td><?php echo $value['count']; ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php

$n = count($accept);

$com = array();

for($q = 0; $q < $n; $q++){
	for($w = $q + 1; $w < $n; $w++){
		array_push($com,$accept[$q]['stock_code'].'|'.$accept[$w]['stock_code']);
	}
}

?>
<h2>Combination</h2>
<ul>
	<?php foreach($com as $str) { ?>
		<li><?php echo $str; ?></li>
	<?php } ?>
</ul>
<?php

$result = array();

foreach($com as $str) { 
	$data = explode("|", $str);
	$sql = "SELECT invoice_number, count(invoice_number) as count FROM detail_invoice WHERE stock_code = '".$data[0]."' OR stock_code = '".$data[1]."' GROUP BY invoice_number HAVING count = 2";
	$res = mysqli_query($con, $sql);
	$count = mysqli_num_rows($res);
	$result[] = array("item1" => $data[0], "item2" => $data[1], "count" => $count);
	$acc = 0;
	$prune = 0;
	$accept1 = array();
}

?>
<table width="100%" border="1">
	<thead>
		<tr>
			<th>#</th>
			<th>Item #1</th>
			<th>Item #2</th>
			<th>Count</th>
			<th>Status</th>
		</tr>
	</thead>
	<?php $co = 1; ?>
	<tbody>
		<?php foreach($result as $value) { ?>
			<tr bgcolor="<?php echo $value['count'] >= $count_support ? 'green':'red'; ?>">
				<td><?php echo $co++; ?></td>
				<td><?php echo $value['item1']; ?></td>
				<td><?php echo $value['item2']; ?></td>
				<td><?php echo $value['count']; ?></td>
				<td>
				<?php if($value['count'] >= $count_support){ ?>
					<?php 

					$acc++;
					$accept1[] = $value;

					?>
					Accepted
				<?php } else { ?>
					<?php $prune++; ?>
					Pruned
				<?php } ?>	
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php 

echo "Accepted : ".$acc."<br>";
echo "Pruned : ".$prune."<br>";

?>
<h2>Accepted</h2>
<table width="100%" border="1">
	<thead>
		<tr>
			<th>#</th>
			<th>Item #1</th>
			<th>Item #2</th>
			<th>Count</th>
		</tr>
	</thead>
	<?php $co = 1; ?>
	<tbody>
		<?php foreach($accept1 as $value) { ?>
			<tr bgcolor="<?php echo $value['count'] >= $count_support ? 'green':'red'; ?>">
				<td><?php echo $co++; ?></td>
				<td><?php echo $value['item1']; ?></td>
				<td><?php echo $value['item2']; ?></td>
				<td><?php echo $value['count']; ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php

$items = array();

foreach($accept1 as $value){
	$items[] = array($value['item1'],$value['item2']);
}

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

// Loop
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
//var_dump($combination);

$tmp_item = array();

//$ty = 2;

//var_dump($combination[$ty]);
//echo "<br>";

//var_dump(validate_combination($combination[$ty],$items));

?>
<h2>Combination</h2>
<table width="100%" border="1">
	<thead>
		<tr>
			<th>#</th>
			<th>Item #1</th>
			<th>Item #2</th>
			<th>Item #3</th>
		</tr>
	</thead>
	<?php $co = 1; ?>
	<tbody>
		<?php foreach($combination as $value) { ?>
			<tr>
				<td><?php echo $co++; ?></td>
				<?php foreach($value as $data){ ?>
					<td><?php echo $data; ?></td>
				<?php } ?>
			</tr>
			<?php 

			if(count(validate_combination($value, $items)) > 0){
				sort($value);
				if(!in_array($value, $tmp_item)){
					$tmp_item[] = $value;
				}
			}

			?>
		<?php } ?>
	</tbody>
</table>
<hr>
<h2>Accepted</h2>
<table width="100%" border="1">
	<thead>
		<tr>
			<th>#</th>
			<th>Item #1</th>
			<th>Item #2</th>
			<th>Item #3</th>
		</tr>
	</thead>
	<?php $co = 1; ?>
	<tbody>
		<?php foreach($tmp_item as $value) { ?>
			<tr>
				<td><?php echo $co++; ?></td>
				<?php foreach($value as $data){ ?>
					<td><?php echo $data; ?></td>
				<?php } ?>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php 

$items = $tmp_item;

var_dump($items);
}
?>