<?php

include "function.php";

$min_support = $_REQUEST['support'];

$data = apriori($min_support);

session_start();
$_SESSION['last_session'] = serialize($data);

$products = array();
foreach ($data as $com) {
	foreach ($com as $key => $value) {
		foreach ($value as $v) {
			if(!in_array($v, $products)){
				$products[] = $v;
			}
		}
	}
}

$sql = "SELECT DISTINCT(description) as description,stock_code as code FROM detail_invoice WHERE ";
$co = 0;
$end = count($products);
foreach ($products as $key => $value) {
	$sql .= "stock_code = '".$value."'";
	if(++$co < $end){
		$sql .= ' OR ';
	}
}

$res = mysqli_query($con, $sql);
$product = array();
while($row = mysqli_fetch_assoc($res)){
	$product[$row['code']] = $row['description'];
}

?>
<?php foreach($data as $key => $value) { ?>
<?php 

$tmp = explode("-", $key);
$com = $tmp[1];

?>
<h2 class="page-header"><?php echo $com?> Combination</h2>
<table class="table">
	<thead>
		<tr>
			<th>#</th>
			<?php for($q = 1; $q <= $com;$q++) { ?>
				<th>Item #<?php echo $q; ?></th>
				<th>Desc #<?php echo $q; ?></th>
			<?php } ?>
			<th>Action</th>
		</tr>
	</thead>
	<?php $co = 1; ?>
	<tbody>
		<?php foreach($data['com-'.$com] as $value) { ?>
			<tr>
				<td><?php echo $co++; ?></td>
				<?php foreach($value as $p) { ?>
					<td><?php echo $p; ?></td>
					<td><?php echo $product[$p]; ?></td>
				<?php } ?>
				<td>
					<a href='detail_apriori.php?a=<?php echo json_encode($value);?>' class="btn btn-warning btn-sm"><i class="fa fa-search"></i></a>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php } ?>
