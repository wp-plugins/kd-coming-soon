<?php
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

function isEmail($email) {
    return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", $email));
}

if($_POST) {
	include_once("emailer/class.phpmailer.php");

	$emailSiteName		= $_POST['ctitle'] . ' - website';
	$emailToName		= $_POST['ctitle'];
	$emailTo = unserialize(base64_decode($_POST['cetitle']));
	$subscriber_email = addslashes(trim($_POST['email']));

	$array = array();
	$array['valid'] = 0;
	if(!isEmail($subscriber_email)) {
		$array['message'] = 'Please enter a valid email address.';
	}else {
		$subject = 'New Subscriber!';
		$body = "You have a new subscriber at ".$_POST['ctitle']."!\n\nEmail: " . $subscriber_email;

		$mail = new PHPMailer();
		$mail->IsMail();
		$mail->IsHTML(true);

		$mail->From     = $subscriber_email;
		$mail->FromName = $emailSiteName;
		//$mail->AddReplyTo($from_email, $from);
		$mail->AddAddress($emailTo, $emailToName);
		$mail->Subject  = $subject;
		$mail->AltBody  = 'To view this message, please use an HTML compatible email viewer!';
		$mail->WordWrap = 70;
		$mail->MsgHTML($body);

		$CharSet = 'utf-8';
		$Priority = 3;
		if(!$mail->Send()) {
			$array['message'] = 'Sorry we could not send your subscription!<br>Please try again...';//.$mail->ErrorInfo;
		}else{
			$array['valid'] = 1;
			$array['message'] = 'Thanks for your subscription!';
		}

		$mail->ClearAddresses();
		$mail->ClearAllRecipients();
		$mail->ClearAttachments();
    }
	 echo json_encode($array);
}
?>