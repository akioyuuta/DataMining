<?php 

include "function.php";

$arr = json_decode($_GET['a']);

$data = array();
$category = array();
$start = true;
$name = array();
foreach ($arr as $key => $value) {
	$sql = "SELECT SUM(qty), DATE_FORMAT(date_time,'%y-%m'), description FROM online WHERE stock_code = '".$value."' GROUP BY DATE_FORMAT(date_time,'%y-%m')";
	$res = mysqli_query($con, $sql);
	$tmp = array();
	while($row = mysqli_fetch_array($res)){
		$tmp[] = $row[0];
		if($start){
			$category[] = $row[1];
			$name[] = $row[2];
		}
	}
	$data[] = $tmp;
	if($start){
		$start = false;
	}
}

$cat_str = '[';
foreach ($category as $key => $value) {
	$cat_str .= '"'.$value.'",';
}
$cat_str = rtrim($cat_str, ",");
$cat_str .= ']';

$n = count($data);
$dat = '[{';
for($q = 0; $q < $n;$q++){
	$t = $q+1;
	$dat .= "name: '".$name[$q]."', data: [";
	foreach ($data[$q] as $key => $value) {
		$dat .= $value.',';
	}
	$dat = rtrim($dat, ",");
	$dat .= "]";
	if($q != $n - 1){
		$dat .= '},{';
	}
}

$dat .= '}]';

//echo $dat;

?>
<?php include "header.php"; ?>
	
	<?php include "nav.php"; ?>

<script type="text/javascript">
$(function () {
    $('#container').highcharts({
        chart: {
            type: 'line'
        },
        title: {
            text: 'Monthly Transaction'
        },
        subtitle: {
            text: ''
        },
        xAxis: {
            categories: <?php echo $cat_str; ?>
        },
        yAxis: {
            title: {
                text: 'Temperature (°C)'
            }
        },
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: true
                },
                enableMouseTracking: false
            }
        },
        series: <?php echo $dat;?>
    });
});
</script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<div class="container">
	<div class="row">
		<div class="col-sm-12">
			<h1 class="page-header"><i class="fa fa-search"></i> Detail Apriori</h1>
			<ol class="breadcrumb">
				<li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
				<li><a href="apriori_page.php"><i class="fa fa-bar-chart"></i> Apriori Analysis</a></li>
				<li class="active"><i class="fa fa-search"></i> Detail Apriori</li>
			</ol>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading">Association Rule</div>
				<div class="panel-body">
					<?php 

					$result = calculate_support_confidence($arr);

					?>
					<table class="table table-hover">
						<thead>
							<tr>
								<th>#</th>
								<th>Rule</th>
								<th>Support</th>
								<th>Confidence</th>
							</tr>
						</thead>
						<?php $co = 1; ?>
						<tbody>
							<?php foreach($result as $value) { ?>
								<tr>
									<td><?php echo $co++; ?></td>
									<td><b><?php echo print_rule($value['left'], $value['right']); ?></b></td>
									<td><?php echo $value['support'].' %'; ?></td>
									<td><?php echo $value['confidence'].' %'; ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include "footer.php"; ?>