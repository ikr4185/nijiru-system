// -----------------------------------------
// PLEASE DO NOT PROMOTE (IF CONSIDERED)!
// WAITING FOR CODEPEN TO OFFER .js 
// EXTENSION SUPPORT FOR PRIVATE PENS
// -----------------------------------------

$(function(){
	var
		position;

	$('.pep').css({
		top: '10px',
		left: '10px',
		bottom: '',
		right: ''
	});
	position = $('.pep').position();

	$('.demo.constrain-to-parent .pep').pep({
		useCSSTranslation: false,
		constrainTo: 'parent',
		droppable:   '.drop-target',
		//initiate: function(ev, obj){
		//	$('.pep').css({
		//		top: '10px',
		//		left: '10px',
		//		bottom: '',
		//		right: ''
		//	});
		//},
		start: function(ev, obj){
			$('.pep').css({
				top: position.top,
				left: position.left,
				bottom: '',
				right: ''
			});
		},
		rest: function(ev, obj){
			if(this.activeDropRegions.length){
				var dropRegion = this.activeDropRegions;
				var dropClass = dropRegion[0]["context"]["className"];
				if ( -1 != dropClass.indexOf(' lt')) {
					$('.pep').css({
						top: '10px',
						left: '10px'
					});
				}
				if( -1 != dropClass.indexOf(' rt')){
					$('.pep').css({
						top: '10px',
						left: 'auto',
						right: '10px'
					});
				}
				if( -1 != dropClass.indexOf(' lb')){
					$('.pep').css({
						top: 'auto',
						bottom: '10px',
						left: '10px'
					});
				}
				if( -1 != dropClass.indexOf(' rb')){
					$('.pep').css({
						top: 'auto',
						left: 'auto',
						bottom: '10px',
						right: '10px'
					});
				}
			}
			position = $('.pep').position();
			console.log(position);
		}
	})

});
