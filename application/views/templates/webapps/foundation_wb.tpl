<div class="foundation_wb__wrapper">

	{*JSでのToken判別で使用する*}
	<input type="hidden" id="fwb-token" value="{$result["token"]}">

	<div class="canvas-wrap" id="canvas-wrap">
		<canvas class="canvas" width="500" height="400" id="canvas"></canvas>

		<div class="controllers">
			<div class="controllers__pen-tools">
				<ul class="controllers__pen-color-ul">
					<li class="controllers__pen-color controllers__pen-color--black" data-pen-color="#000"></li>
					<li class="controllers__pen-color controllers__pen-color--red" data-pen-color="#f00"></li>
					<li class="controllers__pen-color controllers__pen-color--blue" data-pen-color="#00f"></li>
					<li class="controllers__pen-color controllers__pen-color--white" data-pen-color="#fff"></li>
				</ul>
				<div class="controllers__pen-size-div" id="pen-size">
					<button class="controllers__pen-size controllers__pen-size--s" id="pen-size_small">1px</button>
					<button class="controllers__pen-size controllers__pen-size--m" id="pen-size_medium">5px</button>
					<button class="controllers__pen-size controllers__pen-size--l" id="pen-size_large">10px</button>
				</div>
			</div>
			<div class="controllers__load-tools" id="button">
				<label>save pass: <input type="text" class="controllers__load-pass" id="fwb-pass"></label>
				<br>
				<button class="controllers__load-buttons" id="load">読込</button>
				<button class="controllers__load-buttons" id="save">保存</button>
				<button class="controllers__load-buttons" id="clear">消去</button>
			</div>
		</div>
	</div>

</div>
