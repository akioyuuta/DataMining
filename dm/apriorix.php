<?php

include "connect.php";

$sql = "SELECT DISTINCT(invoice_number) as inv FROM online";
$res = mysqli_query($con, $sql);
$count = mysqli_num_rows($res);

$min_support = 0.04;
$count_support = $min_support * $count;

echo "Min Support : ".$min_support."<br>";
echo "Count All : ".$count."<br>";
echo "Min Count : ".$count_support."<br>";

$table = array();

while($row = mysqli_fetch_assoc($res)){
	$table[$row['inv']] = array();
	$sql = "SELECT stock_code FROM online WHERE invoice_number = '".$row['inv']."'";
	$result = mysqli_query($con, $sql);
	$str = '';
	while($data = mysqli_fetch_array($result)){
		$str .= $data[0].'|';
	}
	$table[$row['inv']] = $str;
}

$sql = "SELECT DISTINCT(stock_code) FROM online";
$res = mysqli_query($con, $sql);

?>
<table width="100%">
	<thead>
		<tr>
			<th>#</th>
			<th>Stock Code</th>
			<th>Count</th>
			<th>Status</th>
		</tr>
	</thead>
	<?php $co = 1; ?>
	<tbody>
		
	</tbody>
</table>