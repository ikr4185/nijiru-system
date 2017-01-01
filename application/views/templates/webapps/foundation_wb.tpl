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
				<div class="controllers__pen-alpha-div" id="pen-alpha">
					<button class="controllers__pen-alpha" data-pen-alpha="1.0">α1.0</button>
					<button class="controllers__pen-alpha" data-pen-alpha="0.5">α0.5</button>
					<button class="controllers__pen-alpha" data-pen-alpha="0.1">α0.1</button>
				</div>
				<div class="controllers__pen-size-div" id="pen-size">
					<button class="controllers__pen-size controllers__pen-size--s" data-pen-size="1">1px</button>
					<button class="controllers__pen-size controllers__pen-size--m" data-pen-size="10">10px</button>
					<button class="controllers__pen-size controllers__pen-size--l" data-pen-size="30">30px</button>
				</div>
			</div>
			<div class="controllers__load-tools" id="button">
				<label class="controllers__load-pass-label">save pass: <input type="text" class="controllers__load-pass" id="fwb-pass"></label>
				<div class="controllers__load-buttons-div">
					<button class="controllers__load-buttons" id="load">読込</button>
					<button class="controllers__load-buttons" id="save">保存</button>
					<button class="controllers__load-buttons" id="clear">消去</button>
				</div>
			</div>
		</div>
	</div>

</div>
