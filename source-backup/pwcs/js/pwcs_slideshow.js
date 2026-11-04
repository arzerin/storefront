// $Id: pwcs-slideshow.js,v 1.0 2011/03/23 rasel kabir Shuvro (shuvro.mist@gmail.com) Exp $

$(document).ready(function() {


//  <div id="primaryCallout">
//    <span id="calloutLinks">
//      <a title="Featured Deal 1" href="#" rel="1">1</a>
//      <a title="Featured Deal 2" href="#" rel="2">2</a>
//      <a title="Featured Deal 3" href="#" rel="3">3</a>
//    </span>
//    <div><a href="#" target="_self"><img src="image1.jpg" alt="Title of the item" /></a></div>
//    <div><a href="#" target="_self"><img src="image2.jpg" alt="Title 2" /></a></div>
//    <div><a href="#" target="_self"><img src="image3.jpg" alt="Title 3" /></a></div>
//  </div>


	// Cycle Features Callouts
	//Show the paging and activate first
	$("#calloutLinks a:first").addClass("active");
	$("#primaryCallout div:first").css('display', 'block');

	//Paging  and Slider Function
	rotate = function(){
		var triggerID = $active.attr("rel") - 1; //Get current slide

		$("#calloutLinks a").removeClass('active'); //Remove all active class
		$active.addClass('active'); //Add active class (the $active is declared in the rotateSwitch function)
		$("#primaryCallout div").css('display', 'none');
		$("#primaryCallout div:eq("+triggerID+")").css('display', 'block');
	}; 
	
	//Rotation  and Timing Event
	rotateSwitch = function(){
		playPrimary = setInterval(function(){ //Set timer
			$active = $('#calloutLinks a.active').next(); //Move to the next paging
			if ( $active.length === 0) { //If end...
				$active = $('#calloutLinks a:first'); //go back to first
			}
			rotate(); //Trigger the paging and slider function
		}, 5000); //Timer speed
	};
	
	rotateSwitch(); //Run function on launch
	
	//On Hover
	$("#primaryCallout div").hover(function() {
		clearInterval(playPrimary); //Stop the rotation
	}, function() {
		rotateSwitch(); //Resume rotation timer
	});	
	
	//On Click
	$("#calloutLinks a").click(function() {
		$active = $(this); //Activate the clicked paging
		//Reset Timer
		clearInterval(playPrimary); //Stop the rotation
		rotate(); //Trigger rotation immediately
		rotateSwitch(); // Resume rotation timer
		return false; //Prevent browser jump to link anchor
	});


});
