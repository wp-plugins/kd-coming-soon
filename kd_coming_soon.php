<?php
/*
Plugin Name: KD Coming Soon
Plugin URI: http://kallidan.is-best.net
Description: This is simple Html content carousel. Put Your HTML CONTENT directly into a slide show.
Version: 1.0
Author: Kalli Dan.
Author URI: http://kallidan.is-best.net
License: GPL2
*/
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

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
define( "KD_CS_VERSION", 1.0 );
define( "KD_CS_OPTION_VER", "kd_cs_version" );

class CS301Redirects {
	function redirect() {
		global $wpdb;

		$site_title  = get_option('blogname');
		$userrequest = str_ireplace(get_option('home'),'',$this->get_address());
		$destination = plugins_url('/assets/templates/cs-templ_01.html',__FILE__);
		$csPrew   	 = (empty($_REQUEST['csPrew'])) ? "" : $_REQUEST['csPrew'];

		$kd_settings=get_option('kd_cs_settings');
		$cs_active	  = $kd_settings['cs_active'];
		$cs_color	  = $kd_settings['cs_color'];
		$cs_date	  	  = $kd_settings['cs_date'];
		$cs_name		  = $kd_settings['cs_name'];
		$cs_nameinfo  = $kd_settings['cs_nameinfo'];
		$cs_email	  = $kd_settings['cs_email'];
		$cs_title	  = str_replace('\\', "", $kd_settings['cs_title']);
		$cs_subtitle  = str_replace('\\', "", $kd_settings['cs_subtitle']);
		$cs_ftitle	  = str_replace('\\', "", $kd_settings['cs_ftitle']);
		$cs_fsubtitle = str_replace('\\', "", $kd_settings['cs_fsubtitle']);
		$cs_note		  = str_replace('\\', "", $kd_settings['cs_note']);

		/* don't allow people to accidentally lock themselves out of admin and/or display preview of site */ 
		if($userrequest == '/wp-login.php' || is_admin() || ($cs_active != 'on' && !$csPrew)){
			$storedrequest = "";
			$pattern = '/^' . str_replace( '/', '\/', rtrim( $storedrequest, '/' ) ) . '/';
			$destination = str_replace('*','$1',$destination);
			$output = preg_replace($pattern, $destination, $userrequest);
			if ($output !== $userrequest) {
				// pattern matched, perform redirect
				if(is_admin()){
					$do_redirect = $userrequest;
				}else{
					$do_redirect = $userrequest;
				}
			}
		}else{
			// replace default Wordpress web pages with our template...
			$bgs = '';
			$slides = get_option('kd_slides_ids');
			$sids = explode('~', $slides);
			if(isset($sids) && $slides !=""){
				foreach($sids as $slide_id){
					$query = $wpdb->prepare( "SELECT post_title, guid FROM ".$wpdb->prefix."posts WHERE ID = %d", $slide_id);
					$row = $wpdb->get_results( $query, ARRAY_A );
					if($row[0]['guid'] !=""){
						if($bgs != ""){ $bgs.= ','; }
						$bgs.= '"'.$row[0]['guid'].'"';
					}
				}
				if($bgs){
					$bgs = "jQuery('.coming-soon').backstretch([".$bgs."], {duration: 5000, fade: 750});\n";
				}
			}
			$e_data = base64_encode(serialize($cs_email));

			$kdtoret = file_get_contents($destination);
			$kdtoret = str_replace('%SCRIPT_URL%', includes_url('/js/jquery/jquery.js'), $kdtoret);
			$kdtoret = str_replace('%PLUGIN_URL%', plugins_url('/assets',__FILE__), $kdtoret);
			$kdtoret = str_replace('%CS_BKGRND%', $bgs, $kdtoret);
			$kdtoret = str_replace('%CS_COLOR%', $cs_color, $kdtoret);
			$kdtoret = str_replace('%CS_DATE%', $cs_date, $kdtoret);

			$kdtoret = str_replace('%CS_aTITLE%', $e_data, $kdtoret);
			$kdtoret = str_replace('%SITE_TITLE%', $site_title, $kdtoret);
			$kdtoret = str_replace('%CS_NAME%', $cs_name, $kdtoret);
			$kdtoret = str_replace('%CS_SUBNAME%', '<p>'.$cs_nameinfo.'</p>', $kdtoret);
			$kdtoret = str_replace('%CS_EMAIL%', $cs_email, $kdtoret);
		
			$kdtoret = str_replace('%CS_TITLE%', $cs_title, $kdtoret);
			$kdtoret = str_replace('%CS_SUBTITLE%', $cs_subtitle, $kdtoret);
			$kdtoret = str_replace('%CS_FTITLE%', $cs_ftitle, $kdtoret);
			$kdtoret = str_replace('%CS_FSUBTITLE%', $cs_fsubtitle, $kdtoret);
			if($cs_note){
				$cs_note = '<span style="color:#e75967;">*</span> <span style="font-style:italic;font-size:11px;">'.$cs_note.'</span>';
			}
			$kdtoret = str_replace('%CS_NOTE%', $cs_note, $kdtoret);

			$smedia = "";
			$cs_media = get_option('kd_cs_media');
			foreach($cs_media as $key => $media){
				foreach($media as $name => $murl){
					if($murl && $murl !=""){
						$name=stripslashes($name);
						$smedia.= '<a href="'.$murl.'" data-toggle="tooltip" data-placement="top" title="'.ucFirst($name).'" target="_new"><i class="fa fa-'.$name.'"></i></a>'."\n";
					}
				}
			}
			$kdtoret = str_replace('%CS_SOCIALMEDIA%', $smedia, $kdtoret);

			echo $kdtoret;
			exit;
		}

		//if wp-admin, display it...
		if ($do_redirect !== '' && trim($do_redirect,'/') !== trim($userrequest,'/')) {
			// check if destination needs the domain prepended
			if (strpos($do_redirect, '/') === 0){
				//$do_redirect = home_url().$do_redirect;
			}
			header ('HTTP/1.1 301 Moved Permanently');
			header ('Location: ' . $do_redirect);
			exit();
		}else {
			unset($kd_settings);
		}
	}

	function get_address() {
		// return the full address
		return $this->get_protocol().'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}
	
	function get_protocol() {
		// Set the base protocol to http
		$protocol = 'http';
		// check for https
		if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
    		$protocol .= "s";
		}	
		return $protocol;
	}
}

/*-- SETUP -- */

// Add custom style for admin...
function kd_csadmin_style() {
	wp_enqueue_style( 'thickbox' );
	wp_enqueue_style( 'kd_cscalendar_style', plugins_url( '/assets/css/jquery-datepicker.min.css', __FILE__ ), array(),'1.8.21','all');
	wp_enqueue_style( 'kd_csprettyphoto_style', plugins_url( '/assets/css/prettyPhoto.min.css', __FILE__ ), array(),'3.1.5','all');
	wp_enqueue_style( 'kd_csswitch_style', plugins_url( '/assets/css/bootstrap-switch.min.css', __FILE__ ), array(),'1.8','all');
	wp_enqueue_style( 'kd_cscolorpicker_style', plugins_url( '/assets/css/colorpicker.min.css', __FILE__ ), array(),'2.0','all');
	wp_enqueue_style( 'kd_csadmin_style', plugins_url( '/assets/css/cs_admin.css', __FILE__ ), array(),'1.0','all');
}
add_action( 'admin_print_styles', 'kd_csadmin_style' );

// Add custom style for page...
function kd_cs_style() {
	wp_enqueue_style( 'kd_coming_soon_style', plugins_url( '/assets/css/style.css', __FILE__ ), array(),'1','all');
}
//add_action( 'wp_print_styles', 'kd_cs_style' );

// Add custom scripts...
function kd_cs_init_method() {
	wp_enqueue_script('jquery');

	if(is_admin()){
		if(isset($_REQUEST['page'])){
			if($_REQUEST['page']=="kd-coming-soon/kd_cs_settings.php"){
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_script( 'jquery-ui-sortable');
				wp_enqueue_script( 'jquery-ui-datepicker');
				wp_enqueue_script( 'kd_coming_soon_pp', plugins_url( '/assets/js/jquery.prettyPhoto.min.js', __FILE__ ));
				wp_enqueue_script( 'kd_coming_soon_switch', plugins_url( '/assets/js/bootstrap-switch.min.js', __FILE__ ));
				wp_enqueue_script( 'kd_coming_soon_color', plugins_url( '/assets/js/colorpicker.min.js', __FILE__ ));
				wp_enqueue_script( 'kd_coming_soon_adm', plugins_url( '/assets/js/cs_admin.js', __FILE__ ));
			}
		}
	}
}
add_action( 'wp_print_scripts', 'kd_cs_init_method' );

function kd_cs_install() {
	global $wpdb;

	$kd_media = array(
		0 => array('facebook'	=> ""),
		1 => array('twitter'		=> ""),
		2 => array('linkedin'	=> ""),
		3 => array('google-plus'=> ""),
		4 => array('flickr'		=> ""),
		5 => array('foursquare'	=> ""),
		6 => array('tumblr'		=> ""),
		7 => array('dribbble'	=> ""),
		8 => array('skype'		=> ""),
		9 => array('youtube'		=> "")
	);
	$nextMonth = time() + (30 * 24 * 60 * 60);
	$kd_settings['cs_date']		  = date('m/d/Y', $nextMonth);
	$kd_settings['cs_active']	  = "";
	$kd_settings['cs_color']	  = "#e75967";
	$kd_settings['cs_name']		  = get_option('blogname');
	$kd_settings['cs_email']	  = get_option('admin_email');
	$kd_settings['cs_nameinfo']  = "Tel: <span>your_phone_number</span> | Skype: <span>my_skype_id</span>";
	$kd_settings['cs_title']	  = "We're Coming Soon";
	$kd_settings['cs_subtitle']  = "We are working very hard on the new version of our site. It will bring a lot of new features. Stay tuned!";
	$kd_settings['cs_ftitle']	  = "Subscribe to our newsletter";
	$kd_settings['cs_fsubtitle'] = "Sign up now to our newsletter and you'll be one of the first to know when the site is ready:";
	$kd_settings['cs_note']		  = "We won't use your email for spam, just to notify you of our launch.";

	add_option("kd_cs_settings", $kd_settings);
	add_option('kd_slides_ids', "");
	add_option('kd_cs_media', $kd_media);
	add_option( KD_CS_OPTION_VER, KD_CS_VERSION, '', 'no');
}
register_activation_hook(__FILE__,'kd_cs_install');

function kd_comingsoon_upgrade() {
	// Check db version and update it if required...
	if ( get_option( KD_CS_OPTION_VER, NULL ) !== KD_CS_VERSION) {
		kd_cs_install();
	}
}
add_action('plugins_loaded', 'kd_comingsoon_upgrade');

function kd_cs_uninstall() {
	global $wpdb;

	delete_option( KD_CS_OPTION_VER );
	delete_option( "kd_cs_settings" );
	delete_option( "kd_slides_ids" );
	delete_option( "kd_cs_media" );
}
register_uninstall_hook( __FILE__, 'kd_cs_uninstall' );

function kd_cs_add_menu() {
	$file = dirname( __FILE__ ) . '/kd_cs_settings.php';
	$icon = plugins_url('/assets/img/icl.png',__FILE__);
	$list_page1 = add_menu_page('KD Coming Soon', 'KD Coming Soon', 2, $file, '', $icon);
	$list_page2 = add_submenu_page($file, 'Settings', 'Settings', 'manage_options', $file, 'kd_cs_menu_op');
	
	if ( class_exists( "WP_Screen" ) ) {
		add_action( 'load-' . $list_page1, 'kd_cs_help' );
	} else if ( function_exists( "add_contextual_help" ) ) {
		add_contextual_help( $list_page1, kd_cs_get_help_text() );
	}
}

function kd_cs_help() {
	$screen = get_current_screen();
	$screen->add_help_tab( array ( 'id' => 'kd_cs-setting-help',
	                               'title' => __( 'Settings', 'kd_cs-help' ),
	                               'content' => kd_cs_get_setting_help_text() ));
	$screen->add_help_tab( array ( 'id' => 'kd_cs-slides-help',
	                               'title' => __( 'Slider Content', 'kd_cs-help' ),
	                               'content' => kd_cs_get_slides_help_text() ));
	$screen->add_help_tab( array ( 'id' => 'kd_cs-social-help',
	                               'title' => __( 'Social Media', 'kd_cs-help' ),
	                               'content' => kd_cs_get_social_help_text() ));
}
function kd_cs_get_setting_help_text() {
	$help_text = '<p><strong>' . __( 'Settings:', 'kd_cs-help' ) . '</strong></p>';
	$help_text .= '<p>' . __( 'Select the date <span style="color:#21759b;font-weight:bold;">KD Coming Soon</span> should be active and fill in all required fields.<br/>
						When done, hit the <strong>Save settings</strong> button.
						<p>
						<table>
							<tr><td colspan="2">Fields description:</td></tr>
							<tr>
								<td style="width:200px;vertical-align:top;font-weight:bold;">Date Active</td>
								<td style="vertical-align:top;">-</td>
								<td>This is the date the <span style="color:#21759b;font-weight:bold;">KD Coming Soon</span> page will be shown.<br>
								When this date is reached your orginal Wordpress site will be displayed.</td>
							</tr>
							<tr>
								<td style="vertical-align:top;font-weight:bold;">Email Address</td>
								<td style="vertical-align:top;">-</td>
								<td>This is the email address to which <span style="color:#21759b;font-weight:bold;">KD Coming Soon</span> will send all new subscriptions.<br>
								The email address is encrypted and not shown or displayed anywhere on the site unless you explicitly enter it into some settings fields.</td>
							</tr>
						</table>
						</p>', 'kd_cs-help' ) . '</p>';
	return $help_text;
}
function kd_cs_get_slides_help_text() {
	$help_text = '<p><strong>' . __( 'Slider Content:', 'kd_cs-help' ) . '</strong></p>';
	$help_text .= '<p>' . __( 'To add a photo to the slider click the <strong>Add Slide</strong> button.
						The Media Manager will popup where you can upload and/or select photos to add.<br/>
						To view full version of a photo click any image.<br/>
						To remove photo hover the mouse over a image and click the <img src="'.plugins_url('/assets/img/delete.png',__FILE__).'" style="padding: 0px 0px 3px 0px;" align="absmiddle"> icon.<br/>
						When done, hit the <strong>Save now</strong> button.', 'kd_cs-help' ) . '</p>';
	return $help_text;
}
function kd_cs_get_social_help_text() {
	$help_text = '<p><strong>' . __( 'Social Media:', 'kd_cs-help' ) . '</strong></p>';
	$help_text .= '<p>' . __( 'Fill in the fields you want with the url to your social media portal.<br/>
						You can re-order the media links by click an social icon and drag it up or down to the location you want.<br/>
						When done, hit the <strong>Save now</strong> button.', 'kd_cs-help' ) . '</p>';
	return $help_text;
}

$redirect_plugin = new CS301Redirects();

if(isset($redirect_plugin)) {
	// add the redirect action, high priority
	add_action('init', array($redirect_plugin,'redirect'), 1);
	// create the menu
	add_action('admin_menu', 'kd_cs_add_menu');
}

/*-- ADMIN -- */
function kd_cs_menu_op() {
	global $wpdb;

	add_thickbox();
	$upload_dir = wp_upload_dir();
	$slide_url  = $upload_dir['baseurl'];
	$slide_path = $upload_dir['basedir'];
	
	$def_icl_w = 250;
	$def_icl_h = 133;
	?>
	<script type='text/javascript'>
		def_icl_w = <?php echo $def_icl_w;?>;
		def_icl_h = <?php echo $def_icl_h;?>;
	</script>

	<div id="my-content-id" style="display:none;">
		<p style="padding:40px 0 0 16px;"><img id="ticker_content" src="#"></p>
	</div>
	<a id="thickWin_view" href="#TB_inline?width=350&height=270&inlineId=my-content-id" class="thickbox"></a>

	<div id="cspreview">
		<iframe id="csPreviewContent" border="0" marginheight="0" frameborder="0" scrolling="no" seamless />
			<div style="text-align:center;padding-top:50;font-weight:bolder;font-size:18px;">Your browser does not support previews.</div>
		</iframe>
	</div>

	<div class="wrap">
		<h2 class="cstitle"><img src="<?php echo plugins_url('/assets/img/icon.png',__FILE__);?>" alt="" align="absmiddle">KD Coming Soon
		<span style="color:#444;font-size:18px"></span></h2>
	</div>

	<?php
	if( isset($_POST['kdupdcs']) || isset($_POST['kdsetsub']) || isset($_POST['kdupdcsslides'])){
		if( isset($_POST['kdupdcs'])){
			$msg = 'Social links';
		}elseif( isset($_POST['kdsetsub'])){
			$msg = 'Settings';
		}else{
			$msg = 'Slides';
		} ?>
		<div class="updated success-message" style="padding: 20px;">
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<?php echo '<img src="'.plugins_url('/assets/img/dialog_info.gif',__FILE__).'" alt="" width="32" height="32" align="top">';?>
					</td>
					<td style="padding:0 0 0 20px;">
						<strong><?php echo $msg; ?> saved.</strong>
					</td>
				</tr>
			</table>
		</div>
		<script type='text/javascript'>
			jQuery(document).ready(function() { timeIt('updated'); });
		</script>
	<?php } ?>

	<!-- SETTINGS -->
	<div class="wrap">
		<h3>Settings:</h3>
		<h5>Fill out all required fields.</h5>
		<?php 
		if(isset($_POST['kdsetsub'])){
			$kd_settings['cs_date']		  = $_POST['cs_date'];
			$kd_settings['cs_active']	  = $_POST['cs_active'];
			$kd_settings['cs_color']	  = $_POST['cs_color'];
			$kd_settings['cs_name']		  = $_POST['cs_name'];
			$kd_settings['cs_nameinfo']  = $_POST['cs_nameinfo'];
			$kd_settings['cs_email']	  = $_POST['cs_email'];
			$kd_settings['cs_title']	  = str_replace("'", '\\\'', $_POST['cs_title']);
			$kd_settings['cs_subtitle']  = str_replace("'", '\\\'', $_POST['cs_subtitle']);
			$kd_settings['cs_ftitle']	  = str_replace("'", '\\\'', $_POST['cs_ftitle']);
			$kd_settings['cs_fsubtitle'] = str_replace("'", '\\\'', $_POST['cs_fsubtitle']);
			$kd_settings['cs_note'] 	  = str_replace("'", '\\\'', $_POST['cs_note']);
			update_option('kd_cs_settings', $kd_settings);
		}

		$kd_settings=get_option('kd_cs_settings');
		$cs_date	  	  = $kd_settings['cs_date'];
		$cs_active	  = $kd_settings['cs_active'];
		$cs_color	  = $kd_settings['cs_color'];
		$cs_name		  = $kd_settings['cs_name'];
		$cs_nameinfo  = $kd_settings['cs_nameinfo'];
		$cs_email	  = $kd_settings['cs_email'];
		$cs_title	  = str_replace('\\', "", $kd_settings['cs_title']);
		$cs_subtitle  = str_replace('\\', "", $kd_settings['cs_subtitle']);
		$cs_ftitle	  = str_replace('\\', "", $kd_settings['cs_ftitle']);
		$cs_fsubtitle = str_replace('\\', "", $kd_settings['cs_fsubtitle']);
		$cs_note		  = str_replace('\\', "", $kd_settings['cs_note']);
		if($cs_active){ $checked = ' checked'; }else{ $checked=""; }
		?>

		<form id="csSettings" name="csSettings" method="post">
			<table class="csadmin">
				<tr>
					<td style="width:150px;" >Coming Soon Active <span class="cserror cs-sw">*</span></td>
					<td>
						<div class="make-switch switch-mini" data-on="primary" data-off="default">
							<input type="checkbox" id="cs_active" name="cs_active"<?php echo $checked;?>>
						</div>
					 </td>
				</tr>
				<tr>
					<td>Date Active <span class="cserror cs-sw">*</span></td>
					<td>
						<input type="text" id="cs_date" name="cs_date" style="width:100px;cursor:pointer;" value="<?php echo $cs_date; ?>" class="datepicker" placeholder="mm/dd/yyyy" onFocus="clearFormError('cs_date');" />
						<img  src="<?php echo plugins_url('/assets/img/calendar.gif',__FILE__);?>" width="20" height="20" alt="" title="" align="absmiddle" />
						<span class="cshelp-inline"></span>
						<div id="error_cs_date" class="cserror"></div>
					</td>
				</tr>
				<tr>
					<td>Theme Color</td> 
					<td>
						<input type='text' id="cs_color" name="cs_color" style="width:100px;cursor:pointer;" value="<?php echo $cs_color; ?>" placeholder="#" onFocus="clearFormError('cs_color');" />
						<div id="colorpickerHolder" style="display:inline-block;width:20px;height:20px;background-color:<?php echo $cs_color; ?>;margin:-5px 0;"></div>
						<div id="error_cs_color" class="cserror"></div>
					</td>
				</tr>
				<tr>
					<td>Site Name <span class="cserror cs-sw">*</span></td>
					<td>
						<input type='text' id="cs_name" name="cs_name" style="width:250px;" value="<?php echo $cs_name; ?>" placeholder="Your site name" onFocus="clearFormError('cs_name');" />
						<div id="error_cs_name" class="cserror"></div>
					</td>
				</tr>
				<tr>
					<td>Email Address <span class="cserror cs-sw">*</span></td>
					<td>
						<input type='text' id="cs_email" name="cs_email" style="width:250px;" value="<?php echo $cs_email; ?>" placeholder="Your email address" onFocus="clearFormError('cs_email');" />
						<span class="cshelp-inline"></span>
						<div id="error_cs_email" class="cserror"></div>
					</td>
				</tr>
				<tr>
					<td>Header Info</td>
					<td>
						<input type='text' id="cs_nameinfo" name="cs_nameinfo" style="width:100%;" value="<?php echo $cs_nameinfo; ?>" placeholder="Your phone number" onFocus="clearFormError('cs_nameinfo');" />
						<div id="error_cs_nameinfo" class="cserror"></div>
					</td>
				</tr>
				<tr>
					<td>Title</td>
					<td>
						<input type='text' id="cs_title" name="cs_title" style="width:100%;" value="<?php echo $cs_title; ?>" placeholder="" onFocus="clearFormError('cs_title');" />
						<div id="error_cs_title" class="cserror"></div>
					</td>
				</tr>
				<tr>
					<td>Sub Title</td>
					<td>
						<input type='text' id="cs_subtitle" name="cs_subtitle" style="width:100%;" value="<?php echo $cs_subtitle; ?>" placeholder="" onFocus="clearFormError('cs_subtitle');" />
						<div id="error_cs_subtitle" class="cserror"></div>
					</td>
				</tr>
				<tr>
					<td>Footer Title</td>
					<td>
						<input type='text' id="cs_ftitle" name="cs_ftitle" style="width:100%;" value="<?php echo $cs_ftitle; ?>" placeholder="" onFocus="clearFormError('cs_ftitle');" />
						<div id="error_cs_ftitle" class="cserror"></div>
					</td>
				</tr>
				<tr>
					<td>Footer Sub-title</td>
					<td>
						<input type='text' id="cs_fsubtitle" name="cs_fsubtitle" style="width:100%;" value="<?php echo $cs_fsubtitle; ?>" placeholder="" onFocus="clearFormError('cs_fsubtitle');" />
						<div id="error_cs_fsubtitle" class="cserror"></div>
					</td>
				</tr>
				<tr>
					<td>Footer Notification</td>
					<td>
						<input type='text' id="cs_note" name="cs_note" style="width:100%;" value="<?php echo $cs_note; ?>" placeholder="" onFocus="clearFormError('cs_note');" />
						<div id="error_cs_note" class="cserror"></div>
					</td>
				</tr>
				<tr>
					<td style="padding:20px 0px 0px 0px;">
						<a href="#cspreview" onClick="javascript:previewSite('<?php echo stripslashes(get_site_url());?>','');" class="button-primary btn-success prettyprewphoto">preview</a>
					</td>
					<td style="text-align:right;padding:20px 0px 0px 0px;">
						<input type="button" class="button-secondary" onClick="javascript:this.form.reset();" value="reset" /> &nbsp;
						<input type="submit" name="kdsetsub" class="button-primary" onClick="return submitCSsettings(this.form);" value="save settings" />
					</td>
				</tr>
			</table>
		</form>
	</div>

	<!-- SLIDES -->
	<div class="wrap" style="padding-top:30px;">
		<h3>Slider Content:</h3>
		<h5>Click photo to view full size.</h5>
		<?php
		if(isset($_POST['kdupdcsslides'])){
			update_option('kd_slides_ids', $_POST['cs_slides_ids']);
		}
		$slide=get_option('kd_slides_ids');
		$ids = explode('~', $slide);
		$SLIDES = array();
		if($ids){
			foreach($ids as $slide_id){
				$query = $wpdb->prepare( "SELECT post_title, guid FROM ".$wpdb->prefix."posts WHERE ID = %d", $slide_id);
				$row = $wpdb->get_results( $query, ARRAY_A );
				if($row[0]['guid'] && $row[0]['guid'] !=""){
					$SLIDES[$slide_id] = $row[0]['guid'];
				}
			}
		}
		?>
		<form id="csSlides" name="csSlides" class="csadmin" method="post">
			<input type="hidden" id="cs_slides_ids" name="cs_slides_ids" value="<?php echo $slide;?>">
			<div id="cssrc">
				<?php
				if(count($SLIDES) >0){
					foreach($SLIDES as $id => $src){
						if(!$src || $src ==""){ continue; }
						$w = $def_icl_w;
						$h = $def_icl_h;
						$pos = strrpos($src, '/');
						$img = substr($src, $pos);     
						if ($pos !== false) {
							$size = getimagesize($slide_path.$img);
							if($size[0] > $size[1]){
								if($size[0] > $def_icl_w){
									$s = ($size[0] - $def_icl_w);
									$w = $def_icl_w;
									$h = ($size[1] - $s);
									if($h < $def_icl_h){ $h = $def_icl_h; }
								}
							}else{
								if($size[1] > $def_icl_h){
									$s = ($size[1] - $def_icl_h);
									$h = $def_icl_h;
									$w = ($size[0] - $s);
									//if($w < 250){ $w = 250; }
								}
							}
						}
						echo '
					<a id="sl_'.$id.'" class="outer" href="'.$src.'" rel="prettyPhoto[cs_gallery001]">
						<img src="'.$src.'" width="'.$w.'" height="'.$h.'" alt="" title="" align="absmiddle" style="margin:3px;border:1px solid #ccc;" />
					</a>';
					}
				}else{
					echo 'Click the <strong>add slides</strong> button to select photos...';
				}
				?>
			</div>
			<div style="float:left;padding:20px 0px 0px 0px;">
				<a href="#cspreview" onClick="javascript:previewSite('<?php echo stripslashes(get_site_url());?>','');" class="button-primary btn-success prettyprewphoto">preview</a>
			</div>
			<div style="text-align:right;padding:20px 0px 0px 0px;">
				<a href="javascript:;" id="gallery_man" name="gallery_man" class="button-secondary">add slides</a> &nbsp;
				<input type="submit" name="kdupdcsslides" class="button-primary" value="save now" />
			</div>
		</form>
	</div>
	<div id="dl_0" class="box1">
		<img  src="<?php echo plugins_url('/assets/img/delete.png',__FILE__);?>" alt="Remove" title="" />
	</div>
	
	<!-- SOCIAL MEDIA -->
	<div class="wrap" style="padding-top:30px;">
		<h3>Social Media:</h3>
		<h5>Drag up / down to reorder.</h5>
		<?php
		if(isset($_POST['kdupdcs'])){
			update_option('kd_cs_media', $_POST['csslidecont']);
		}
		$cs_media=get_option('kd_cs_media');
		?>
		<div class="smedcont" style="width:70%;">
			<form class="csadmin" id="qord" name="qord" method="post">
				<ul id="kdcsslideopt">
					<?php
					foreach($cs_media as $key => $media){
						foreach($media as $name => $murl){
							$name=stripslashes($name);
							$simg = plugins_url('/assets/img/media/'.$name.'.png',__FILE__);
							?>
							<li>
								<img src="<?php echo $simg;?>" width="32" height="32" alt="<?php echo ucfirst($name);?>" title="<?php echo ucfirst($name);?>" align="absmiddle" style="margin:3px;">
								<input type="text" id="<?php echo $name;?>" name="csslidecont[][<?php echo $name;?>]" value="<?php echo $murl;?>" style="width:70%;" placeholder="http://" onFocus="clearFormError('<?php echo $name;?>');" />
								<div id="error_<?php echo $name;?>" class="cserror"></div>
							</li>
							<?php
						}
					}
					?>
				</ul>
				<div style="float:left;padding:20px 0px 0px 0px;">
					<a href="#cspreview" onClick="javascript:previewSite('<?php echo stripslashes(get_site_url());?>','');" class="button-primary btn-success prettyprewphoto">preview</a>
				</div>
				<div style="text-align:right;padding:20px 0px 0px 0px;">
					<input type="button" class="button-secondary" onClick="javascript:this.form.reset();" value="reset" /> &nbsp;
					<input type="submit" name="kdupdcs" class="button-primary" onClick="return submitCSsocial(this.form);" value="save now" />
				</div>
			</form>
		</div>
	</div>
	<?php
}

// this is here for php4 compatibility
if(!function_exists('str_ireplace')){
  function str_ireplace($search,$replace,$subject){
    $token = chr(1);
    $haystack = strtolower($subject);
    $needle = strtolower($search);
    while (($pos=strpos($haystack,$needle))!==FALSE){
      $subject = substr_replace($subject,$token,$pos,strlen($search));
      $haystack = substr_replace($haystack,$token,$pos,strlen($search));
    }
    $subject = str_replace($token,$replace,$subject);
    return $subject;
  }
}
?>