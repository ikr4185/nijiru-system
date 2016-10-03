google.load('visualization', '1', { packages : [ 'corechart' ]});
google.setOnLoadCallback(drawChart);

function drawChart(){
	// 表示するデータの設定
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'date');
	data.addColumn('number', 'NewMember');
	data.addColumn('number', 'ActiveMember');
	data.addColumn('number', 'Total');

	// データの取得
	var dates = document.getElementsByClassName("j_dates");
	var counts = document.getElementsByClassName("j_count");
	var activeMemberCounts = document.getElementsByClassName("j_activeMemberCount");
	var allMemberCounts = document.getElementsByClassName("j_allMemberCount");

	var rows = [];
	for(var i = 0; i < dates.length; i++) { // 配列の長さ分の繰り返し
		rows.push([
			dates[i].innerText,
			Number(counts[i].innerText),
			Number(activeMemberCounts[i].innerText),
			Number(allMemberCounts[i].innerText)
		]);
	}
	data.addRows(rows);

	// グラフの設定
	var option = {
		title: 'SiteMemberHistory',
		width: '100%',
		height: '100%',
		series: [
			{ type: 'line', targetAxisIndex: 0 },
			{ type: 'line', targetAxisIndex: 0 },
			{ type: 'bars', targetAxisIndex: 1 }
		],
		vAxes: [
			{ title: 'Member' },
			{ title: 'Total' }
		]
	};

	var chart = new google.visualization.ComboChart(document.getElementById('chart'));
	chart.draw(data, option);
}