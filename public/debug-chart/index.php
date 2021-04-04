<?php
	include_once ("src/fusioncharts.php");
	include("../includes/app.php");
?>
<html>

   <head>
	<title>IWP DEBUG CHART</title>
	<script src="https://static.fusioncharts.com/code/latest/fusioncharts.js"></script>
   </head>
   <body>
<?php
	
	if ($_GET['type'] == 'backup' && !empty($_GET['historyID'])) {
		new IWP_Debug_Chart('Time Taken', ' Sec', $_GET['historyID'], 'Backup Times', 'chart-1');
	}
?>
	<div  id="chart-1"><!-- Fusion Charts will render here--></div>
	<div  id="chart-2"><!-- Fusion Charts will render here--></div>
	<div  id="chart-3"><!-- Fusion Charts will render here--></div>
	<div  id="chart-4"><!-- Fusion Charts will render here--></div>
   </body>
</html>


<?php

Class IWP_Debug_Chart{
	private $chart_meta;
	private $dataset;

	public function __construct($caption, $numbersuffix, $historyID, $graph_name, $chart_id){
		$this->init_chart_meta($caption, $numbersuffix);
		$this->read_logs($historyID);
		$this->plot_graph($graph_name, $chart_id);
	}

	private function plot_graph($graph_name, $chart_id){
		$encoded_data = $this->struct_data();
		$pieChart = new FusionCharts("line", $graph_name , "100%", 300, $chart_id, "json", $encoded_data);
		$pieChart->render();
	}

	private function struct_data(){
		$this->chart_meta['data'] = $this->dataset;
		return json_encode($this->chart_meta);
	}

	private function read_logs($historyID) {
		$where = array(
			'query' => "parentHistoryID = ':historyID'",
			'params' => array(
				':historyID'	=> $historyID,
				)
			);
		$resultArray = DB::getArray("?:history", "historyID,microtimeStarted,microtimeEnded", $where);

		if(empty($resultArray)){
			return false;
		}

		foreach ($resultArray as $key => $value) {
			$this->dataset[] = array(
					"label" => $value['historyID'],
					"value" => $value['microtimeEnded']-$value['microtimeStarted'],
					"color" => "008ee4",
					"stepSkipped" => false,
					"appliedSmartLabel" => true
				);
		}

		// fclose($file);
	}

	private function init_chart_meta($caption, $numbersuffix){
		$this->chart_meta = array(
			"chart" => array(
				"caption" =>$caption,
				"numbersuffix" => $numbersuffix,
				"bgcolor" => "FFFFFF",
				"showalternatehgridcolor" => "0",
				"plotbordercolor" => "008ee4",
				"plotborderthickness" => "3",
				"showvalues" => "0",
				"divlinecolor" => "CCCCCC",
				"showcanvasborder" => "0",
				"tooltipbgcolor" => "00396d",
				"tooltipcolor" => "FFFFFF",
				"tooltipbordercolor" => "00396d",
				"numdivlines" => "20",
				"yaxisvaluespadding" => "20",
				"anchorbgcolor" => "008ee4",
				"anchorborderthickness" => "0",
				"showshadow" => "0",
				"showLabels" =>"0",
				"anchorradius" => "2",
				"chartrightmargin" => "25",
				"canvasborderalpha" => "0",
				"showborder" => "1",
			)
		);
	}
}