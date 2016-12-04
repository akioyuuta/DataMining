<?php 

include "connect.php";

$sql = "SELECT DISTINCT(invoice_number) FROM online";
$res = mysqli_query($con, $sql);
$count = mysqli_num_rows($res);

$min_support = 0.04;
$count_support = $min_support * $count;

echo "Min Support : ".$min_support."<br>";
echo "Count All : ".$count."<br>";
echo "Min Count : ".$count_support."<br>";

$sql = "SELECT COUNT(stock_code) as count, stock_code FROM online 
		WHERE stock_code <> 'POST' AND
			  stock_code <> 'PADS' AND
			  stock_code <> 'C2' AND
			  stock_code <> 'CRUK' AND
			  stock_code <> 'D' AND
			  stock_code <> 'M' AND
			  stock_code <> 'BANK CHARGES' AND
			  stock_code <> 'DOT' 
		GROUP BY stock_code";
$res = mysqli_query($con, $sql);

$arr = array();

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
	<?php echo $co = 1; $prune = 0; $acc = 0; ?>
	<tbody>
		<?php while($row = mysqli_fetch_assoc($res)) { ?>
			<?php

			$status = 0;
			if($row['count'] >= $count_support){
				$status = 1;
				$acc++;
				$arr[] = array("stock_code" => $row['stock_code'], "count" => $row['count']);
			} else { 
				$prune++;
			}

			?>
			<tr bgcolor="<?php echo $status == 1 ? 'green':'red'; ?>">
				<td><?php echo $co++; ?></td>
				<td><?php echo $row['stock_code']; ?></td>
				<td><?php echo $row['count']; ?></td>
				<td>
					<?php if($status == 1) { ?>
						Accepted
					<?php } else if($status == 0) { ?>
						Prune
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
		<?php foreach($arr as $value) { ?>
			<tr>
				<td><?php echo $co++; ?></td>
				<td><?php echo $value['stock_code']; ?></td>
				<td><?php echo $value['count']; ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php

$n = count($arr);

$com = array();

for($q = 0; $q < $n; $q++){
	for($w = $q + 1; $w < $n; $w++){
		array_push($com,$arr[$q]['stock_code'].'|'.$arr[$w]['stock_code']);
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
</table>