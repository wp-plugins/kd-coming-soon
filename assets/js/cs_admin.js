/*
	KD Coming Soon

	Copyright (c) 2015 Kalli Dan. (email : kallidan@yahoo.com)

	KD Coming Soon is free software: you can redistribute it but NOT modify it
	under the terms of the GNU Lesser Public License as published by the Free Software Foundation,
	either version 3 of the LGPL License, or any later version.

	KD Coming Soon is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU Lesser Public License for more details.

	You should have received a copy of the GNU Lesser Public License along with KD Coming Soon.
	If not, see <http://www.gnu.org/licenses/>.
*/

/* --------------------------------------------------------------------------------------- */
//INITIALIZE...

// defaults...
var def_icl_w = 250;
var def_icl_h = 133;

jQuery(document).ready(function() {
	//date-picker handler...
	jQuery('#csSettings #cs_date').datepicker({
		autoclose: true
	});

	//social media handler...
	initializeSRTBL();
	//images preview/slideshow handler...
	initializePP();

	// page preview handler...
	jQuery(".prettyprewphoto").prettyPhoto({
		show_title: false,
		allow_resize: false,
		default_width: 800,
		default_height: 1000,
		theme: 'pp_default',
		horizontal_padding: 0,
		modal: false,
		social_tools: false
	});

	//Media Manager handler....
	var custom_file_frame;
	jQuery(document).on('click', '#gallery_man', function(event) {
		event.preventDefault();
		if (typeof(custom_file_frame)!=="undefined") {
			custom_file_frame.close();
		}
		//Create WP media frame.
		custom_file_frame = wp.media.frames.customHeader = wp.media({
			title: "Photo Gallery",
			library: {type: 'image'},
			button: {text: "Add to slider"},
			multiple: true
		});
		//Callback...
		custom_file_frame.on('select', function(){
			var ids = new Array();
			var selection = custom_file_frame.state().get('selection');
			
			var old_ids = jQuery('#csSlides #cs_slides_ids').val();
			if(!old_ids || old_ids ==""){
				jQuery('#cssrc').html("");
			}

			selection.map(function(attachment) {
				attachment = attachment.toJSON();
				ids.push(attachment.id);

				var w = parseInt(attachment.width);
				var h = parseInt(attachment.height);
				if(w > h){
					if(w > def_icl_w){
						var s = (w - def_icl_w);
						w = def_icl_w;
						h = (h - s);
						if(h < def_icl_h){ h = def_icl_h; }
					}
				}else{
					if(h > def_icl_h){
						var s = (h - def_icl_h);
						h = def_icl_h;
						w = (w - s);
						//if(w < def_icl_w){ w = def_icl_w; }
					}
				}
				var preview = '<a id="sl_'+attachment.id+'" class="outer" href="'+attachment.url+'" rel="prettyPhoto[cs_gallery001]">';
				preview +=    '<img src="'+attachment.url+'" width="'+w+'" height="'+h+'" alt="'+attachment.filename+'" title="" align="absmiddle" style="margin:4px;border:1px solid #ccc;" /></a>';
				jQuery('#cssrc').append(preview);
			});

			var new_gall = ids.join('~');
			var curr_gall = jQuery('#csSlides #cs_slides_ids').val();
			if(curr_gall != ""){
				curr_gall+= '~' + new_gall;
			}else{
				curr_gall = new_gall;
			}
			jQuery('#csSlides #cs_slides_ids').val(curr_gall);
			initializePP();
		});
		//Open the Media Manager...
		custom_file_frame.open();
	});

	//show remove slide button handler...
	jQuery(document).on('mouseenter', '.outer', function () {
		var ids = this.id;
		var offset = 14;
		var w = jQuery('#'+ids+' >img').width();
		var h = (jQuery('#'+ids+' >img').height() /2);

		var left = jQuery("#"+ids).position().left;
		var top = jQuery("#"+ids).position().top;

		var spinner = document.getElementById('dl_0');
		spinner.style.left = (left + (w -offset)) +"px";
		spinner.style.top  = (top - (h -offset)) +"px";

		jQuery('#dl_0').attr('data-id',ids).show();
	}).on('mouseleave', '.outer', function () {
		jQuery('#dl_0').hide();
	});

	//remove slide handler...
	jQuery(document).on('mousedown', '.box1', function () {
		var id = jQuery(this).attr('data-id');
		jQuery('#'+id).remove();

		var ids = new Array();
		var old_ids = jQuery('#csSlides #cs_slides_ids').val();
		if(old_ids && old_ids !=""){
			var remove_id = id.split('_');
			var old_id = old_ids.split('~');
			for(x=0;x<old_id.length;x++){
				if(old_id[x] != remove_id[1]){
					ids.push(old_id[x]);
				}
			}
			jQuery('#csSlides #cs_slides_ids').val(ids.join('~'));
		}
		if(ids.length <1){
			jQuery('#cssrc').html('Click the <strong>add slides</strong> button to select photos...');
		}
		initializePP();
	});

	//color theme handler...
	jQuery('#cs_color').ColorPicker({
		flat: false,
		livePreview: true,
		onSubmit: function(hsb, hex, rgb, el) {
			jQuery(el).val('#'+hex);
			jQuery('#colorpickerHolder').css('backgroundColor', '#' + hex);
			jQuery(el).ColorPickerHide();
		},
		onBeforeShow: function () {
			jQuery(this).ColorPickerSetColor(this.value);
		},
		onChange: function (hsb, hex, rgb, el) {
			//jQuery('#colorpickerHolder').css('backgroundColor', '#' + hex);
		}
	}).bind('keyup', function(){
		jQuery(this).ColorPickerSetColor(this.value);
	});

	//help button click handler...
	jQuery('.cshelp-inline').bind('click',function(e){
		jQuery('#contextual-help-link').trigger('click');
	});
});

function initializeSRTBL(){
	jQuery( "#kdcsslideopt" ).sortable({
		containment: ".smedcont"
	});
	jQuery( "#kdcsslideopt li" ).css({'cursor':'move'});
}

function initializePP(){
	jQuery("a[rel^='prettyPhoto']").prettyPhoto({
		show_title: false,
		allow_resize: false,
		default_width: 460,
		default_height: 300,
		theme: 'pp_default',
		horizontal_padding: 5,
		modal: false,
		social_tools: false
	});
}

/* --------------------------------------------------------------------------------------- */
//FORM SUBMITS...

//settings validation...
function submitCSsettings(frm){
	var name  = jQuery('#csSettings #cs_name').val();
	var email = jQuery('#csSettings #cs_email').val();
	var date = jQuery('#csSettings #cs_date').val();

	if(isEmpty(date, 'cs_date', 'date')){ return false; }
	if(isEmpty(name, 'cs_name', 'company name')){ return false; }
	if(isEmpty(email, 'cs_email', 'your e-mail address')){ return false; }
	if(notEmail(email, 'cs_email')){ return false; }
	return true;
}

//social media validation...
function submitCSsocial(frm){
	var error = 0;
	jQuery('#qord').find('input').each(function(i, obj){
		if(notUrl(obj.value, obj.id)){
			if(obj.id){ error++; }
		}
	});
	if(error >0){
		return false;
	}
	return true;
}

/* --------------------------------------------------------------------------------------- */
//MISC

function previewSite(url){
	jQuery('#csPreviewContent').attr('src', url + '?csPrew=1');
}

function timeIt(){
	setTimeout(function(){
		jQuery('.success-message').slideUp('slow', function() {
			jQuery('.success-message').hide();
		});
	},5000);
}

/* --------------------------------------------------------------------------------------- */
//FORM VALIDATION...

function isEmpty(field, fieldName, msg) {
	if (trim(field) == "") {
		err = 'Plese enter '+msg;
		displayFormError(fieldName, err);
		return true;
	}
	return false;
}
function notNumber(number, fieldName, msg, validate) {
	var ath = trim(number);
	ath = ath.toString();
	for (var i=0; i<ath.length; i++) {
		if (ath.charAt(i) > "9" || ath.charAt(i) < "0") {
			if(!validate){
				err = "Please only use numeric data for " + msg;
				displayFormError(fieldName, err);
			}
			return true;
		}
	}
	return false;
}
function notPhone(number, fieldName, msg, validate) {
	var ath = trim(number);
	ath = ath.toString();
	for (var i=0; i<ath.length; i++) {
		if (ath.charAt(i) > "9" || ath.charAt(i) < "0"){
			if(ath.charAt(i) != '-' && ath.charAt(i) != '+' && ath.charAt(i) != ' ') {
				if(!validate){
					err = 'Allowed characters for '+msg+' are:\nnumberic, space, +, and - <br>Example: +01 123-1234-1234';
					displayFormError(fieldName, err);
				}
				return true;
			}
		}
	}
	return false;
}
function wrongSize(field, fieldName, msg, type, val, validate){
	var ath = trim(field);
	field = stripNonNumeric(ath);
	if(type=='min' && field.length < val){
		err = 'The minimum length for '+msg+' is '+val+' characters.';
		displayFormError(fieldName, err);
		return true;
	}else if(type=='max' && field.length > val){
		err = 'The maximum length for '+msg+' is '+val+' characters.';
		displayFormError(fieldName, err);
		return true;
	}
	return false;
}
function notEmail(field, fieldName, validate) {
	var email = trim(field);
	var at = false;
	var dot = false;

	for (var i=0; i<email.length; i++) {
		if (email.charAt(i) == "@") at = true;
		if (email.charAt(i) == "." && at) dot = true;
	}
	if (at && dot && email.length > 5){ return false; }

	if(!validate){
		err = 'The e-mail you entered is not a valid e-mail address.';
		displayFormError(fieldName, err);
	}
	return true;
}
function notUrl(field, fieldName, msg, validate) {
	var url = trim(field);
   if (url == "") return false;
	if((url.substr(0,7) != 'http://' && url.substr(0,8) != 'https://') || (url.length <15 && url != 'http://')){
		if(!validate){
			err = "The url you entered is not a valid website address.<br>Please enter valid url and make sure it start on http(s)://";
			displayFormError(fieldName, err);
		}
		return true;
	}
	return false;
}
function notSelected(field, fieldName, msg) {
	if (field.selectedIndex == 0) {
		err = "Please select "+msg;
		displayFormError(fieldName, err);
		return true;
	}
	return false;
}
function trim(stringToTrim) {
	var trimmedString = "";
	if(stringToTrim){
		//left trim
		for(var i=0; i<stringToTrim.length; i++) {
			if (stringToTrim.charAt(i) != " ") break;
		}
		trimmedString = stringToTrim.substring(i);
		//right trim
		for(var i=trimmedString.length-1; i>=0; i--) {
			if (trimmedString.charAt(i) != " ") break;
		}
		trimmedString = trimmedString.substring(0, i + 1);
	}
	return trimmedString;
}
function displayFormError(fieldName, err){
	if(fieldName && err){
		jQuery('#'+fieldName).css({'background-color':'#FFFFD5'});
		jQuery("#error_"+fieldName).html(err);
		jQuery('#error_'+fieldName).show('slow', function() {
			setTimeout(function(){
				clearFormError(fieldName);
			},5000)
		});
	}
	return false;
}
function clearFormError(fieldName){
	jQuery('#'+fieldName).css({'background-color':'#ffffff'});
	jQuery('#error_'+fieldName).hide('slow', function() {
		jQuery("#error_"+fieldName).html("");
	});
}
function stripNonNumeric( str ){
	str += '';
	var rgx = /^\d|\.|-$/;
	var out = '';
	for( var i = 0; i < str.length; i++ ){
		if( rgx.test( str.charAt(i) ) ){
			if( !( ( str.charAt(i) == '.' && out.indexOf( '.' ) != -1 ) || ( str.charAt(i) == '-' && out.length != 0 ) ) ){
				out += str.charAt(i);
			}
		}
	}
	return out;
}