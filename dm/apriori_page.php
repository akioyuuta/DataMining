<?php

include "connect.php";

session_start();
$data = array();
if(isset($_SESSION['last_session'])){
	$data = unserialize($_SESSION['last_session']);
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
}

?>
<?php include "header.php"; ?>

	<?php include "nav.php"; ?>

<div class="container">
	<div class="row">
		<div class="col-sm-12">
			<h1 class="page-header"><i class="fa fa-bar-chart"></i> Apriori Analysis</h1>
			<ol class="breadcrumb">
				<li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
				<li class="active"><i class="fa fa-bar-chart"></i> Apriori Analysis</li>
			</ol>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading">Parameters</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-sm-4">
							<div class="form-group">
								<label for="decimal-range">Min.Support : &nbsp&nbsp&nbsp</label>
								<input id="ex8" data-slider-id='ex1Slider' type="text" data-slider-min="0" data-slider-max="1" data-slider-step="0.001" data-slider-value="0.003"/>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								<input type="text" id="val" class="form-control input-sm">
							</div>
						</div>
						<div class="col-sm-2">
							<button type="button" class="btn btn-default btn-block btn-sm" id="btn">
								<i class="fa fa-check"></i> Generate
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading">Analysis</div>
				<div class="panel-body" id="content">
					<?php if(isset($_SESSION['last_session'])){ ?>
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
					<?php } else { ?>
						<div class="text-center">
							<img src="img/01-progress.gif" alt="" width="200px" id="loading">
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){
		var mySlider = $("#ex8").bootstrapSlider();
		$("#loading").hide();
		$("#val").val(mySlider.bootstrapSlider('getValue'));
		$("#ex8").change(function(){
			$("#val").val(mySlider.bootstrapSlider('getValue'));
		});
		$("#val").keyup(function(){
			var value = $("#val").val();
			mySlider.bootstrapSlider('setValue', value);
		});
		$("#btn").click(function(){
			$("#loading").show();
			var support = $("#ex8").val();
			$("#content").load("ajax_load_apriori.php",{"support":support});
		});
	});
</script>

<?php include "footer.php"; ?>