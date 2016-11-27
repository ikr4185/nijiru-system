<div class="foundation_wb__wrapper">

	{*JSでのToken判別で使用する*}
	<input type="hidden" id="fwb-token" value="{$result["token"]}">

	<div class="canvas-wrap">
		<canvas width="500" height="400"></canvas>
		<ul>
			<li style="background-color:#000"></li>
			<li style="background-color:#f00"></li>
			<li style="background-color:#00f"></li>
			<li style="background-color:#fff"></li>
		</ul>
		<div class="pen-size" id="pen-size">
			<input type="button" id="pen-size_small" value="1px">
			<input type="button" id="pen-size_medium" value="5px">
			<input type="button" id="pen-size_large" value="10px">
		</div>
		<div id="button">
			<input type="button" class="buttons" id="load" value="読込"/>
			<input type="button" class="buttons" id="save" value="保存"/>
			<input type="button" class="buttons" id="clear" value="消去"/>
		</div>
	</div>

</div>
