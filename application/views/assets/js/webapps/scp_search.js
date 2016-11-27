// DOMを全て読み込んだあとに実行される
$(function () {

	var number = null,
		input = $("#scp_search"),
		display = $("#number-display");

	// 初期化
	function clear_scp() {
		input.val("");
		display.text("");
		number = input.val();
		display.slideUp(100);

	}
	clear_scp();

	function insert_scp(num) {
		number = input.val();
		input.val(number + num);
		display.text( "SCP-" + number + num );
		display.slideDown(100);
	}
	
	$('#button_1').click(function () {
		insert_scp("1");
	});
	$('#button_2').click(function () {
		insert_scp("2")
	});
	$('#button_3').click(function () {
		insert_scp("3")
	});
	$('#button_4').click(function () {
		insert_scp("4")
	});
	$('#button_5').click(function () {
		insert_scp("5")
	});
	$('#button_6').click(function () {
		insert_scp("6")
	});
	$('#button_7').click(function () {
		insert_scp("7")
	});
	$('#button_8').click(function () {
		insert_scp("8")
	});
	$('#button_9').click(function () {
		insert_scp("9")
	});
	$('#button_0').click(function () {
		insert_scp("0")
	});

	$('#button_go').click(function () {
		$('#form').submit();
	});
	$('#button_clear').click(function () {
		clear_scp();
	});
	
});