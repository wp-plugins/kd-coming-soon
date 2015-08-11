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

var ComingSoon = function () {
	var runInit = function (end_date, dir) {
		var countdown = end_date.split('/');
		jQuery('#defaultCountdown').countdown({  	/* 07/31/2015 */
			until: new Date(countdown[2], (countdown[0] -1), countdown[1]),
			padZeroes: false,
			format: 'DHMS',
			onTick: everyOne, tickInterval: 1
		});

		jQuery('.subscribe form').submit(function(e) {
			e.preventDefault();
			var postdata = jQuery('.subscribe form').serialize();
			var email = jQuery('#regForm #subscribe-email').val();
			if(isEmpty(email, 'your e-mail address')){ return false; }
			if(notEmail(email)){ return false; }

			jQuery.ajax({
				type: 'POST',
				url: dir+'/subscribe.php',
				data: postdata,
				dataType: 'json',
				success: function(json) {
					if(json.valid == 0) {
						displayFormError(json.message);
					}else {
						jQuery('.error-message').hide();
						jQuery('.success-message').hide();
						jQuery('.subscribe form').hide();
						jQuery('#subscribe-email').val("");
						jQuery('.success-message').html(json.message);
						jQuery('.success-message').fadeIn();
						setTimeout(function(){
							jQuery('.success-message').slideUp('slow', function() {
								jQuery('.success-message').hide();
								jQuery('.subscribe form').fadeIn();
							});
						},5000);
					}
				}
			});
		});

		function everyOne(periods) {
			jQuery('.days').text(periods[3]);
			jQuery('.hours').text(periods[4]);
			jQuery('.minutes').text(periods[5]);
			jQuery('.seconds').text(periods[6]);
		}
	};

	return {
		init: function (date, dir) {
			runInit(date, dir);
			jQuery('.social a').tooltip();
		}
	};
}();

function isEmpty(field) {
	if (trim(field) == "") {
		displayFormError('Please enter a valid email address.');
		return true;
	}
	return false;
}
function notEmail(field) {
	var email = trim(field);
	var at = false;
	var dot = false;
	for (var i=0; i<email.length; i++) {
		if (email.charAt(i) == "@") at = true;
		if (email.charAt(i) == "." && at) dot = true;
	}
	if (at && dot && email.length > 5){ return false; }
	displayFormError('The e-mail you entered is not a valid e-mail address.');
	return true;
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
function displayFormError(err){
	if(err){
		jQuery('.success-message').hide();
		jQuery('.error-message').hide();
		jQuery('.error-message').html(err);
		jQuery('.error-message').fadeIn();
		setTimeout(function(){
			clearFormError();
		},5000);
	}
	return false;
}
function clearFormError(){
	jQuery('.error-message').slideUp('slow', function() {
		jQuery('.error-message').hide();
	});
}