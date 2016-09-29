google.load('visualization', '1', { packages : [ 'corechart' ]});
google.setOnLoadCallback(drawChart);

function drawChart(){
	// 表示するデータの設定
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'date');
	data.addColumn('number', 'count');
	data.addColumn('number', 'avg');
	data.addColumn('number', 'med');

	// データの取得
	var dates = document.getElementsByClassName("j_dates");
	var counts = document.getElementsByClassName("j_count");
	var avgs = document.getElementsByClassName("j_avgs");
	var meds = document.getElementsByClassName("j_meds");

	var rows = [];
	for(var i = 0; i < dates.length; i++) { // 配列の長さ分の繰り返し
		rows.push([
			dates[i].innerText,
			Number(counts[i].innerText),
			Number(avgs[i].innerText),
			Number(meds[i].innerText)
		]);
	}
	data.addRows(rows);

	// グラフの設定
	var option = {
		title: 'voteHistory',
		width: '100%',
		height: '100%',
		series: [
			{ type: 'bars', targetAxisIndex: 0 },
			{ type: 'line', targetAxisIndex: 1 },
			{ type: 'line', targetAxisIndex: 1 }
		],
		vAxes: [
			{ title: 'count' },
			{ title: 'average/median' }
		]
	};

	var chart = new google.visualization.ComboChart(document.getElementById('chart'));
	chart.draw(data, option);
}