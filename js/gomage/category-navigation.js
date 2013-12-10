navigationOpenFilters = {};
navigation_eval_js = null;
var gan_slider_datas = new Array();

document.observe("dom:loaded", function() {   	
	ganLoadForPlain();	
});

document.onreadystatechange = ganLoadForPlain; 

function ganLoadForPlain() {
	mainNav("gan_nav_left", {"show_delay":"100","hide_delay":"100"});
	mainNav("gan_nav_top", {"show_delay":"100","hide_delay":"100"});
	mainNav("gan_nav_right", {"show_delay":"100","hide_delay":"100"});
	ganInitSliders();
}

function ganInitSliders(){
	for(var i=0;i< gan_slider_datas.length;i++){
      $(gan_slider_datas[i].code+'-value-from').innerHTML = gan_slider_datas[i].from;
      $(gan_slider_datas[i].code+'-value-to').innerHTML = gan_slider_datas[i].to;
      $(gan_slider_datas[i].code+'-value').innerHTML = gan_slider_datas[i].htmlvalue;
    }
    gan_slider_datas = new Array();
} 


function showNavigationNote(id, control){
	
	var arr = $(control).cumulativeOffset();
	$(id).style.left = arr[0] + 'px'; 
	$(id).style.top = arr[1] + 'px';
	$(id).style.display = 'block';			
}

function hideNavigationNote(){
	
	$$('.filter-note-content').each(function(e){e.style.display = 'none';});
	
}


function navigationOpenFilter(request_var){
	
	var id = 'advancednavigation-filter-content-'+request_var;
	
	if( $(id).style.display == 'none' ){
		
		$(id).style.display = 'block';
		
		if (navigation_eval_js) {
			eval(navigation_eval_js);
			ganInitSliders();
		}	
		
		navigationOpenFilters[request_var+'_is_open'] = true;
		
	}else{
		
		$(id).style.display = 'none' ;
		
		navigationOpenFilters[request_var+'_is_open'] = false;
		
	}	
}