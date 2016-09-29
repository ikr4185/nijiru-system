var wall = new Freewall("#storage-container");

wall.reset({
	selector: '.storage-brick',
	animate: true,
	cellW: 130,
	cellH: 'auto',
	// fixSize: 1,
	onResize: function() {
		wall.fitWidth();
	}
});

var images = wall.container.find('.storage-brick');
images.find('img').load(function() {
	wall.fitWidth();
});