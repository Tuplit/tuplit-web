rnd=Math.random();
var field_focus = '';

/*
 * Function  : updateTips
 * Purpose   : Display error message or tips
 * Arguments : t - text to display, elmError - id of DOM element to dispaly message 
 */
function updateTips(t,elmError,element) {
	
	var container = elmError + '_container';
	if ( $('#errElementId').val()) {
	 	elmError = $('#errElementId').val();
	}
	$("#" + elmError).html(t);
	$("#" + container).show();
	if(field_focus == '')
		field_focus = element;
	$("#errorFlag").val(1);
	
	//return false;
}
/*
 * Function :
 * Purpose  : Hide error container element
 * elmarray : dom element array
 *
 */
function hideDomElement(elmArray) {
	$.each(elmArray, function() {
		container = this + '_container';
		$('#' + container).hide();
	 });
}

/*
*	Function : checkBlank
*	Purpose	: To check the element is null or not
*/
function checkBlank(element,text,elmError) {
	if($.trim($(element).val()) == "") {
		updateTips(" * " + text + " is required.", elmError, element);
		return false;
	} else {	
		if($(field_focus).attr('id') == $(element).attr('id')){
			field_focus = '';
		}
		return true;
	}
}
function checkRegexp(element,regexp,text,elmError) {
	if ( !( regexp.test( element.val() ) ) ) {
		updateTips(text, elmError,element);
		return false;
	} else {
		return true;
	}
}

// Function to check lenght of the character..
function checkLengthof(element,text,min,max,elmError) 
{	
	if (checkBlank(element,text,elmError)) 
	{ //check for blank
		var tips = 0;
		var length = element.val().length;
		
		if ((min != 0 && max != 0) &&  ( length > max || length < min ) )
			tips = "* Length of " + text + " must be between "+min+" to "+max+".";
		else if((min !=0 && max == 0) && (length < min))
				tips =  "* Length of " + text + " must be minimum "+min+" letters.";
		else if((min == 0 && max != 0) && (length > max))
				tips = "* Length of " + text + " must be maximum "+max+" letters."
		
		if (tips != 0) {
			element.addClass('errorclass');
			updateTips(tips,elmError,element);
			return false;
		}
		else
		{
			if($(field_focus).attr('id') == $(element).attr('id'))
				field_focus = '';
			return true;
		}
	}
}
// End

// Begin : Function  to check max lenght of 30
function checkMaxLength(element,text,max,elmError)
{
	if(element.val().length > max )
	{
		element.addClass('errorclass');
		updateTips("* Length of " +text+" must be maximum "+max+" letters",elmError,element);
		return false;
	}
	else 
		return true;
}
// End



//  Begin: Function to compare 2 elements
function compareElements(element1, element2, text,elmError){
	if($.trim($(element2).val()) != $.trim($(element1).val()) ) {
		element1.addClass('errorclass');
		$("#errorFlag").val(1); 
		updateTips(text, elmError,element1);
		return false;
	} else {
		return true;
	}
}
// End  

function checkandSetFieldFocus()
{
	if(field_focus != '')
		$(field_focus).focus();
}

//Begin: Field focus 
function fieldfocus(getField){
	if(getField!='' && document.getElementById(getField)){
		document.getElementById(getField).focus();
	}
}
//End : Field focus

function clear_app(id){
	var frm = document.getElementById(id);
	for (var i = 0; i < frm.elements.length; i++) {
		if(frm.elements[i].type=='text') {
			 frm.elements[i].value = '';
		}
	   if (frm.elements[i].name.indexOf('[]') > 0)
 	 	  frm.elements[i].checked = false;
	 }
	if (frm.titlecheckbox)
		frm.titlecheckbox.checked = false;
}

function Show(idname){
	if(document.getElementById(idname).style.display == 'none')
	{
		document.getElementById(idname).style.display = 'block';
		$("#device_type_msg_container").hide();
		$("#status_msg_container").hide();
		$("#device_version_msg_container").hide();
		$("#device_build_msg_container").hide();
		$("#device_type").val('0');
		$("#status").val('0');
		return true;
	}
	//document.getElementById(idname).style.display = 'none';
}

function Cancel(idname){
	document.getElementById(idname).style.display = 'none';
}

function validateAppStatus(id)
{
	var device_version	= 	$("#device_type"+id),
	status				=	$("#status"+id),
	version				=	$("#device_version"+id),
	build				=	$("#device_build"+id),
	field_focus			=	'',
	allContainerArray 	=	new Array('device_type'+id+'_msg','status'+id+'_msg','device_version'+id+'_msg','device_build'+id+'_msg','displayMsg');
	allFields 			=	$([]).add(device_version).add(status).add(version).add(build);
	allFields.removeClass('ui-state-error'); //Remove error class if any
	hideDomElement(allContainerArray); //Hide all error message container
	$("#errorFlag").val(0);
	
	if(device_version.length)
		checkBlank(device_version,"Device", "device_type"+id+"_msg");
		checkBlank(status,"App type", "status"+id+"_msg");
		checkBlank(version,"Version", "device_version"+id+"_msg");
		checkBlank(build,"Build", "device_build"+id+"_msg");
	
	if ( $("#errorFlag").val() == 1) {
		checkandSetFieldFocus();
		return false;  
	}
	else {	
		return true;
	}
}
