<?php
include_once('admin_add_functions.php');

function ois_has_saved()
{
	if (isset($_POST['design']))
	{
		// The user has posted data. Save these settings.
		include_once('admin_save_skin.php');
		ois_handle_new_skin();
	} // if
} // ois_has_saved()

function ois_add_new() {


	//update_option('ois-valid', 'no');

	$ois_valid = get_option('ois-valid');
	if ($ois_valid == 'yes')
	{
		// Get license key
		$key = get_option('ois-key');
		$home_url = get_option('ois-home-url');
	} // if
	else
	{
		// Need to redirect here.
		$uri = explode('?', $_SERVER['REQUEST_URI']);
		$validation_url = $uri[0] . '?page=ois-license-key';
		?>
		<script type="text/javascript">
			window.location.href = '<?php echo $validation_url ?>';
		</script>
		<?php
	}

	//$key = '12345';
	/*
		CHECK IF SAVED
		If the user has saved at this point, save data and move to new page.
	*/
	ois_has_saved();

	$design_choice = 1; // The design that is loaded for this skin.
	$all_skins = get_option('ois_skins'); // load all of the created skins
	$editing = false;
	/*
		CHECK IF EDITING A SKIN
	*/
	if (isset($_GET['id']))
		{ // If we are editing a skin.
		$skin_id = $_GET['id'];
		if (!empty($all_skins))
		{
			$this_skin = $all_skins[$skin_id];
			$editing = true;
		}
	} // if

	/*
		CHECK IF DUPLICATING A SKIN
	*/
	else if (isset($_GET['duplicate']))
		{
			// Duplicating is still creating, so we need a new skin ID.
			$skin_id = ois_generate_new_id($all_skins);

			$dup_id = $_GET['duplicate'];
			if (!empty($all_skins))
			{
				// Take all the properties of this skin
				$this_skin = $all_skins[$dup_id];
			} // if
		} // else if

	/*
		CREATING NEW SKIN
	*/
	else
	{
		$skin_id = ois_generate_new_id($all_skins);
		$this_skin = array(); // just an empty array new a new skin.
	} // else

	if ($editing)
	{
		// If we are editing a skin
		ois_editing_heading($skin_id, $this_skin['title'], $this_skin['status']);

	} // if
	else
	{
		ois_section_title('Create a New Skin', 'Here you can design an OptinSkin&trade; to place anywhere in your Wordpress website.', '');

	} // else

	if (isset($_GET['update']))
	{
		if ($_GET['update'] == 'delete')
		{
			ois_notification('Your Skin has Been Successfully Deleted', '', '');
		} // if
		// There could be other types here.
	} // if

	/*
		SKIN TITLE AND DESCRIPTION
		Load data and create "initialization" interface.
	*/
	if (!empty($this_skin))
	{
		$skin_title = stripslashes($this_skin['title']);
		$skin_desc = stripslashes($this_skin['description']);
		$design_choice = $this_skin['design'];
	} // if
	else
	{
		// Creating a new skin.
		$skin_title = '';
		$skin_desc = '';
	} // else

	// CUSTOM DESIGNS
	$custom_designs = get_option('ois_custom_designs');
	$custom_design_content = array();

	if (!empty($custom_designs))
	{
		foreach ($custom_designs as $custom_design_id)
		{
			// $custom_design_id here in an integer
			$custom_path = OIS_PATH . "customDesigns/$custom_design_id";
			$css_url = OIS_URL . "customDesigns/$custom_design_id/style.css";

			if (file_exists($custom_path))
			{
				$cust_html = file_get_contents("$custom_path/static.html");
				array_push($custom_design_content, array(
						'html' => $cust_html,
						'css' => $css_url // just the path is required
					)
				);
			} // if
		} // foreach
	} // if

	/*
		Set hidden input to skin ID
	*/
?>
	<script type="text/javascript">
		var skinID = <?php echo $skin_id ?>;
		var curDesign = <?php echo $design_choice ?>;
		var extUrl = "<?php echo OIS_EXT_URL ?>";
		var customDesigns = <?php echo json_encode($custom_design_content); ?>;
		var licenseKey = "<?php echo $key; ?>";
		var homeUrl = "<?php echo $home_url; ?>";

/* 		console.log(customDesigns); */

		var savedSettings = {};
		<?php
	// Settings
	if (!empty($this_skin['appearance']))
	{
		echo 'savedSettings = { ';
		foreach ($this_skin['appearance'] as $key => $val)
		{
			echo "'$key': '$val', ";
		}
		echo ' };';
	}
?>
	</script>
	<?php

	ois_add_init_table($skin_title, $skin_desc);

?>
	<div class="alert alert-warning" style="padding: 10px;
font-size: 13px;
font-family: 'Helvetical Neue', helvetica, sans-serif;
border: 1px solid #8e44ad;
background-color: #9b59b6;
color: #fff;
font-weight: 100;">Please note that some of the social sharing buttons will not work within this admin area. This is because you could not share this password-protected page, on Facebook, Twitter, etc. The buttons should function properly on your posts, though.</div>
	<?php
	ois_start_table('Customize Design', 'mantra/Colours.png');
	$data = array (
		'title' => 'Skin Design',
		'description' => 'Select one of our pre-made (and tested) designs using the controllers.',
		'style' => 'text-align:center !important; padding: 10px !important;',
		'alternative' => 'yes',
	);
	ois_option_label($data);
	// Load the designs carousel.
?>
	<div id="ois-control-area">
		<!-- Buttons to control the current design -->
		<div id="ois-design-num-display">
			<span id="ois-current-design">0</span>/<span id="ois-num-designs">0</span>
		</div> <!-- #ois-design-num-display -->

		 	    
	     <div style="clear:both"></div>
		<div id="ois-design-area-wrapper">
			<a href="#" id="previous-design" class="ois-change-design-button"></a>

 <a href="#" id="next-design" class="ois-change-design-button"></a>


			<div id="ois-design-area" class="ois-design"></div> <!- /design-area -->
				<div style="clear:both;"></div><!-- clear both -->
		</div><!-- design-area-wrapper -->
	</div> <!- /control-area -->

	<?php
	// I don't think we need this carosel anymore
	//ois_create_carousel($design_to_use, $skin_to_use);
	ois_option_end();

	/* This is where the preview is going go */
?>
	<!-- we need iris -->
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script><!-- for slider bars, and Iris by Automattic -->
	<script src="<?php echo OIS_URL ?>admin/addSkin/js/iris.min.js" type="text/javascript"></script><!-- the color-picker -->

	<script src="<?php echo OIS_EXT_URL ?>min/script3.min.js" type="text/javascript"></script><!-- Design controls, etc. -->

	<script type="text/javascript" src="<?php echo OIS_URL ?>admin/addSkin/js/add_skin.js"></script> <!-- Validation; changes according to selected service provider; etc. -->

<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css"><!-- for slider bars -->
	<link href="<?php echo OIS_EXT_URL ?>normalize.css" rel="stylesheet" />
	<link href="<?php echo OIS_URL ?>admin/addSkin/css/style.css" rel="stylesheet" />
	<link href="<?php echo OIS_URL ?>admin/css/glyphicons.bootstrap.min.css" rel="stylesheet" />
	<link href="<?php echo OIS_URL ?>admin/addSkin/css/style.css" rel="stylesheet" />
	<link href="<?php echo OIS_URL ?>admin/css/glyphicons.bootstrap.min.css" rel="stylesheet" />

	<!-- some necessary form elements -->
	<?php $saved = "false";  // #todo ?>
	<input type="hidden" id="hidden-design" name="design" value="1" />
	<input type="hidden" id="hidden-skin-id" name="skin-id" value="<?php echo $saved; ?>" />
	<input type="hidden" id="hidden-template" name="template" value="" />
	<input type="hidden" id="hidden-template-css" name="template-css" value="" />
	<input type="hidden" id="hidden-template-form" name="template-form" value="" />


	<?php
	$data = array(
		'title' => 'Design Options for This Skin',
		'description' => 'Certain skins allow you to customize aspects of its design.',
		'inner_style' => 'width:110px;',
	);
	ois_option_label($data);
?>
	<div id="ois-editing-area">Editing Area</div> <!- /editing-area -->
	<div style="clear:both" id="ois-non-control"></div>
	<?php

	ois_end_option_and_table();


	/*
		OPTIN SERVICE SETTINGS
	*/
	/*
		OPTIN ACCOUNT DEFAULTS
		We have stored optin information history, for user convenience.
	*/
	$optin_accounts = array (
		'feedburner-id' => get_option('ois_feedburner_id'),
		'mailchimp-form' => get_option('ois_mailchimp_form'),
		'aweber-id' => get_option('ois_aweber_id'),
		'icontact-html' => get_option('ois_icontact_html'),
		'other-html' => get_option('ois_other_html'),
		'getResponse-id' => get_option('ois_getResponse_id'),
		'getResponse-html' => get_option('ois_getResponse_html'),
		'infusionSoft-html' => get_option('ois_infusionSoft_html'),
	);
	ois_start_table('Optin Form Settings', 'mantra/Mail.png');
	ois_option_label(array('title' => 'Optin Service for this Skin', 'description' => '' ));

	// Create an array of choices for optin services
	$optin_services = array (
		'feedburner' => array (
			'ID' => 'feedburner-id',
		),
		'aweber' => array (
			'List Name (e.g. \'viperchill\')' => 'aweber-id',
		),
		'mailchimp' => array (
			'Naked Form HTML' => 'mailchimp-form',
		),
		/*
		'icontact' => array (
			'Form HTML' => 'icontact-html',
		),
*/
		'getResponse' => array (
			'Webform ID' => 'getResponse-id',
			'Form HTML' => 'getResponse-html',
		),
		'infusionSoft' => array (
			'Form HTML' => 'infusionSoft-html',
		),
		'custom' => array (
			'Form Action<br/><small>E.g. http://www.aweber.com/scripts/addlead.pl</small>' => 'custom-action',
			'Email name-value<br/><small>E.g. EMAIL</small>' => 'custom-email',
			'Name name-value (Optional)<br/><small>E.g. FNAME</small>' => 'custom-name',
		),
		'other' => array (
			'Form HTML' => 'other-html',
		),
	); // optin services

	// Display these services as input choices
	if (!empty($this_skin) && isset($this_skin['optin-service']))
	{
		$optin_choice = $this_skin['optin-service'];
	}
	else
	{
		$optin_choice = 'feedburner';
	}

	foreach ($optin_services as $name=>$data)
		{ ?>
		<span class="ois-optin-choice-holder">
			<input class="ois_optin_choice" type="radio" name="newskin_optin_choice"
						<?php
		if ($optin_choice == $name)
		{
			echo 'checked="checked"';
		} // if
		?> value="<?php echo $name; ?>" />
			<img style="padding:0 2px;margin-top:-3px;width:18px!important;" src="<?php echo OIS_URL . 'admin/images/' . strtolower($name) . '.png'; ?>" /><?php
		echo ucwords($name);
		echo '</span> ';
	} // foreach optin service ?>
		<span style="padding: 2px 12px 2px 5px;">
			<input
				class="ois_optin_choice"
				type="radio"
				name="newskin_optin_choice"
				value="none"
			<?php
	if ($optin_choice == 'none')
	{
		echo 'checked="checked"';
	} // if ?>
		/> None </span>
	<?php

	ois_option_end();
	foreach ($optin_services as $name=>$data) {
		if ($name != 'icontact') {
			$ser_title = ucwords($name);
		} else {
			$ser_title = 'iContact';
		}
		if (empty($this_skin['optin_settings']['service']) || trim($this_skin['optin_settings']['service']) != $name) {
			$inner_st = 'display:none';
		} else {
			$inner_st = '';
		}
		ois_option_label(array( 'title' => 'Optin Info for ' . $ser_title, 'description'=>'', 'class' => 'ois_optin_account ois_optin_' . $name,  'style' => $inner_st));

		foreach ($data as $nice=>$item) {
			ois_inner_label(array('title' => $nice));
			if ($name != 'other' && $name != 'mailchimp' && $nice != 'Form HTML') {
				echo '<input type="text"
				style="width:200px;"
				class="ois_textbox ois_optin_account_input"
				name="newskin_' . $item . '"
				account="' . $name . '"';
				if (!empty($this_skin['optin_settings'][str_replace('-', '_', $item)])
					&& trim($this_skin['optin_settings'][str_replace('-', '_', $item)]) != '') {
					$potential_val = trim($this_skin['optin_settings'][str_replace('-', '_', $item)]);
				} else {
					$potential_val = '';
				}
				if ($potential_val != '') {
					echo 'value="' . $potential_val . '"';
				} else {
					if (!empty($optin_accounts[$item])) {
						$potential_val = $optin_accounts[$item];
					} else {
						$potential_val = '';
					}
					if (trim($potential_val) != '') {
						echo 'value="' . $potential_val . '"';
					}
				}
				echo '/>';
			}
			else if ($nice == 'Form HTML' ||
					$name == 'other' ||  $name == 'mailchimp' || $name == 'getResponse')
				{
					if ($name == 'getResponse')
					{
						echo '<p style="padding:10px 0;text-decoration:underline;font-weight:bold;">OR</p>';
					}
					echo '<textarea type="text"
						style="width:500px; height: 200px;"
						class="ois_add_appearance ois_textbox ois_optin_account_input"
						name="newskin_' . $item . '"
						account="' . $name . '" >';
					if (!empty($this_skin['optin_settings'][str_replace('-', '_', $item)]))
					{
						$potential_val = trim($this_skin['optin_settings'][str_replace('-', '_', $item)]);
					} else
					{
						$potential_val = '';
					}
					if ($potential_val != '')
					{
						echo stripslashes($potential_val);
					} else {
						if (!empty($optin_accounts[$item]))
						{
							$potential_val = $optin_accounts[$item];
						} else
						{
							$potential_val = '';
						}
						if (trim($potential_val) != '')
						{
							echo stripslashes($potential_val);
						}
					}
					echo '</textarea>';
				}
			ois_end_option_and_table();
		}
	}
	ois_option_end();
	ois_option_label(array('title' => 'Extra Hidden Fields', 'description' => 'Optional hidden values for campaign tracking, etc.'));

	for ($i = 1; $i <= 5; $i++) {
		if (!empty($this_skin) && isset($this_skin['hidden_name_' . $i]))
		{
			$hidden_name = $this_skin['hidden_name_' . $i];
		}
		else
		{
			$hidden_name = '';
		}
		if (!empty($this_skin) && isset($this_skin['hidden_value_' . $i]))
		{
			$hidden_value = $this_skin['hidden_value_' . $i];
		}
		else
		{
			$hidden_value = '';
		}

		ois_inner_label(array('title' => 'Hidden Field ' . $i,
				'description' => 'Optional'));
?>
		<label for="ois_hidden_name_<?php echo $i; ?>">Name </label><input type="text" class="ois_textbox" id="ois_hidden_name_<?php echo $i; ?>" name="newskin_hidden_name_<?php echo $i; ?>" value="<?php echo $hidden_name ?>" />
		<label for="ois_hidden_value_<?php echo $i; ?>">Value </label><input type="text" class="ois_textbox" id="ois_hidden_value_<?php echo $i; ?>" name="newskin_hidden_value_<?php echo $i; ?>" value="<?php echo $hidden_value ?>" />
		<?php
		ois_option_end();
	}

	ois_end_option_and_table();
	ois_option_end();
	ois_option_label(array('title' => 'Redirect Option', 'description' => 'Where will users go after they have subscribed?<br/><br/>Leave blank for no redirect.'));
	ois_inner_label(array('title' => 'Full Redirect URL',
			'description' => ''));

	if (!empty($this_skin) && isset($this_skin['redirect_url']))
	{
		$redirect_url = $this_skin['redirect_url'];
	} // if
	else
	{
		$redirect_url = '';
	} // else
?>
			<input type="text" class="ois_textbox" id="ois_redirect_url" name="newskin_redirect" style="width:420px;" value="<?php echo $redirect_url ?>" />
			<select id="ois_select_page">
			<option>Select from all Pages</option>
			<?php
	$pages = get_pages();
	foreach ( $pages as $pagg ) {
		$option = '<option value="' . get_page_link( $pagg->ID ) . '">';
		$option .= $pagg->post_title;
		$option .= '</option>';
		echo $option;
	}
	?></select>

	<?php
	ois_end_option_and_table();
	ois_end_option_and_table();

	/*
		SKIN PLACEMENT OPTIONS
	*/
	ois_start_table('Placement Options', 'mantra/Designs.png');
	ois_option_label(array('title' => 'Automatic Skin Placement',
			'description' =>
			'Use these setting to specify where you want this skin to appear on your website.'));
	ois_inner_label(array('title' => 'Place my Skin'));

	if (!empty($this_skin['below_x_paragraphs']))
	{
		$below_x_paragraphs = $this_skin['below_x_paragraphs'];
	} // if
	else
	{
		$below_x_paragraphs = 2;
	} // else
	if (!empty($this_skin['scrolled_past']))
	{
		$scrolled_past = $this_skin['scrolled_past'];
	} // if
	else
	{
		$scrolled_past = '100px';
	} // else

	$positions = array (
		'post_bottom' => 'At the bottom of posts',
		'post_top' => 'At the top of posts',
		'below_first' => 'Below the first paragraph',
		'floated_second' => 'Floated right of second paragraph',
		'sidebar' => 'In a custom location, such as the sidebar using a widget, or post using a shortcode',
		'below_x_paragraphs' => 'Below <input type="text" style="width:30px; height: 22px; margin:0;" class="ois_textbox" value="' . $below_x_paragraphs . '" name="below_x_paragraphs" /> paragraphs',
		/* 		'popup' => 'Popup after user has scrolled <input type="text" style="width:75px; height: 22px; margin:0;" class="ois_textbox" value="' . $scrolled_past . '" name="scrolled_past" />' */
	);

	if (isset($this_skin['position']))
	{
		$cur_position = $this_skin['position'];
	} // if
	else
	{
		$cur_position = 'post_bottom'; // By default.
	} // else

	$i = 0;
	echo '<table>';
	foreach ($positions as $position=>$description) {
		if ($i % 2 == 0)
		{
			echo '<tr>';
		} // if

		echo '<td style="width: 260px;">';
		echo '<input type="radio" class="new_skin_post_type"
			name="post_position" value="' . $position . '"';

		if (trim($cur_position) == '')
		{
			if ($i == 0)
			{
				echo 'checked="checked"';
			} // if
		} // if
		else
		{
			if ($cur_position == $position) {
				echo 'checked="checked"';
			} // if
		} // else
		echo ' /> ';
		echo $description;
		echo '</td>';
		if ($i % 2 != 0)
		{
			echo '</tr>';
		} // if
		$i++;
	} // foreach position

	echo '</tr>';
	ois_table_end(); // ends the positions table
	echo '<p style="color: #666; padding-left: 5px; padding-top: 5px;">
				Once the skin is created, a widget with the skin will be available for sidebar use. You will also receive a shortcode to insert the skin wherever you like.
			</p>';
	ois_end_option_and_table();
	ois_option_label(array('title' => 'Post Exceptions',
			'description' =>
			'Do not place my skin on these posts'));
	ois_inner_label(array('title' => 'Post IDs<br/><small>e.g. <em>15,27,32</em>.</small>'));
	echo '<input type="text" style="width:300px;" class="ois_textbox" name="exclude_posts"';
	if (!empty($this_skin['exclude_posts']))
	{
		echo ' value="' . $this_skin['exclude_posts'] . '"';
	} // if
	echo ' /><small style="margin-left:15px;"><a href="http://optinskin.com/faq/">Need to know how to find the post ID?</a></small>';
	ois_table_end();

	ois_inner_label(array('title' => 'Category IDs<br/><small>e.g. <em>1,3,4</em></small>'));
	echo '<input type="text" class="ois_textbox" id="ois_exclude_cats" name="newskin_exclude_cats" style="width:240px;"';
	if (!empty($this_skin['exclude_categories']))
	{
		echo ' value="' . $this_skin['exclude_categories'] . '"';
	} // if
	echo ' />';
	echo '<select id="ois_select_cat">';
	echo '<option>Select from all Categories</option>';
	$cats = get_categories();

	foreach ( $cats as $cat )
	{
		$option = '<option value="' . $cat->cat_ID . '">';
		$option .= $cat->cat_name;
		$option .= '</option>';
		echo $option;
	} // foreach categoriy
?>
			</select>
			<a href="javascript:void();" id="ois_excl_cat" class="ois_secondary_button" >Add To List</a>
	<script type="text/javascript" >
		jQuery(document).ready(function ($) {
			$('#ois_excl_cat').click(function () {
				var cur_cats = $('#ois_exclude_cats').val();
				if (cur_cats != '') {
					cur_cats = cur_cats + ',' + $('#ois_select_cat').val();
				} else {
					cur_cats = $('#ois_select_cat').val();
				}
				$('#ois_exclude_cats').val(cur_cats);
			});
		});
	</script>
	<?php
	ois_option_end();
	ois_table_end();
	ois_option_end();
	ois_option_label(array('title' => 'Spaces Around the Skin',
			'description' =>
			'Add margins above, below, left and right of your skin.',
			'image' => 'spacing.png'));
	$margins = array();
	if (!empty($this_skin['margins'])) {
		$margins = $this_skin['margins'];
	} else {
		$margins = array( // default margins
			'top' => '5px',
			'right' => '0px',
			'bottom' => '5px',
			'left' => '0px',
		);
	}
	ois_inner_label(array('title' => 'Above and Below'));
	echo '<div style="margin-left:5px;">
			<p>Extra Space Above Skin:
				<input type="text" class="ois_textbox" value="' . $margins['top'] . '" style="width:70px; margin-left:15px;" name="margin_top" />
			</p>';
	echo '<p>Extra Space Below Skin:
				<input type="text" class="ois_textbox" value="' . $margins['bottom'] . '" style="width:70px; margin-left:15px;" name="margin_bottom" /></p></div>';
	ois_table_end();
	ois_inner_label(array('title' => 'Left and Right'));
	echo '<div style="margin-left:5px;"><p>Extra Space to Left of Skin:
			<input type="text" class="ois_textbox" value="' . $margins['left'] . '" style="width:70px; margin-left:15px;" name="margin_left" /></p>';
	echo '<p>Extra Space to Right of Skin:
			<input type="text" class="ois_textbox" value="' . $margins['right'] . '" style="width:70px; margin-left:15px;" name="margin_right" /></p></div>';
	ois_table_end();
	ois_inner_label(array('title' => 'Margin Type'));
	if (!empty($this_skin['margin_type']))
	{
		$margin_type = $this_skin['margin_type'];
	} // if
	else
	{
		$margin_type = 'margin';
	} // else
	echo '<p>
		<span><input type="radio" name="margin_type"';
	if (trim($margin_type) == 'margin') {
		echo ' checked="checked"';
	}
	echo ' value="margin" /> Margin</span>

		<span style="margin-left: 15px;"><input type="radio" name="margin_type"';
	if (trim($margin_type) == 'padding') {
		echo ' checked="checked"';
	}
	echo ' value="padding" /> Padding</span>
		</p>';
	ois_table_end();

	ois_option_label(array('title' => 'Special Effects', 'description' => 'Get more attention to your Optin-Form', 'image' => 'fade.png'));
	ois_inner_label(array('title' => 'Fade In'));

	echo '<p><input type="checkbox" name="special_fade"';
	if (isset($this_skin['special_fade']) && $this_skin['special_fade'] == 'yes')
	{
		echo ' checked="checked"';
	} // if
	if (isset($this_skin['fade_sec']) && trim($this_skin['fade_sec']) != '')
	{
		$fade_sec = $this_skin['fade_sec'];
	} // if
	else
	{
		$fade_sec = '3'; // default
	} // else


	echo ' value="yes" /> Enable <span style="margin-left: 10px;">Fade in after <input type="text" class="ois_textbox" name="fade_sec" style="width: 45px;" value="' . $fade_sec . '" /> seconds.</span></p>';
	echo '<p style="color: #666;">Fades into existence once the skin is visible to the user, drawing attention.</p>';
	ois_end_option_and_table();

	/*
	ois_inner_label(array('title' => 'Stick to Top'));
	echo '<p><input type="checkbox" name="special_stick"';
	if (isset($this_skin['special_stick']) && $this_skin['special_stick'] == 'yes')
	{
		echo ' checked="checked"';
	} // if
	echo ' value="yes" /> Enable </p>';
	echo '<p style="color: #666;">Stays at the top of the screen once your user scrolls past.</p>';

	ois_end_option_and_table();
*/

	ois_option_label(array('title' => 'Responsiveness',
			'description' =>
			'In the case of mobile phones, tablets, etc.'));
	echo '<p>';
	echo '<div><label><input type="radio" name="disable_mobile" value="show_large"';
	if (isset($this_skin['disable_mobile']) &&
		trim($this_skin['disable_mobile']) == 'show_large' ||
		isset($this_skin['disable_mobile']) &&
		trim($this_skin['disable_mobile']) == 'yes')
	{
		echo 'checked="checked"';
	} // if
	echo '/> Only show on large or medium-sized devices</label></div>';
	echo '<div style="margin-top:10px;"><label><input type="radio" name="disable_mobile" value="show_small"';
	if (empty($this_skin['disable_mobile']) ||
		trim($this_skin['disable_mobile']) == 'show_small')
	{
		echo 'checked="checked"';
	} // if
	echo '/> Only show small devices</label></div>';
	echo '<div style="margin-top:10px;"><label><input type="radio" name="disable_mobile" value="show_all"';
	if (empty($this_skin['disable_mobile']) ||
		trim($this_skin['disable_mobile']) == 'show_all' ||
		trim($this_skin['disable_mobile']) == '')
	{
		echo 'checked="checked"';
	} // if
	echo '/> Show on all devices</label></div>';
	echo '</p>';


	ois_end_option_and_table();
	ois_end_option_and_table(); // end positioning section.

	//echo '</table>'; // ends the positioning section.
	ois_start_table('Split-Testing', 'mantra/Clock.png');
	ois_option_label(array(
			'title' => 'Are you a perfectionist?',
			'description' => 'Find out which design or message speaks to your readers best by comparison.',
			'inner_style' => 'width:320px;' ));
	ois_inner_label(array('title' => 'Split-Test This Skin'));
	echo '<p><input type="checkbox" name="split_testing" value="yes"';
	if (isset($this_skin['split_testing']) &&
		trim($this_skin['split_testing']) == 'yes')
	{
		echo 'checked="checked"';
	} // if
	echo '/> Enable Split-Testing</p>';
	echo '<p style="color: #666;">When you enable split-testing for two skins, and you assign them to the same position, only one will appear per pageview.<br/>You can compare their performances in the \'Split-Testing\' section in the OptinSkin menu.</p>';
	ois_end_option_and_table();
	ois_end_option_and_table();

	if (isset($this_skin['aff_username'])
		&& trim($this_skin['aff_username']) != '')
	{
		$aff_username = $this_skin['aff_username'];
	} // if
	else
	{
		$aff_username = get_option('ois_aff_user');
	} // else

	if (isset($this_skin['aff_enable']))
	{
		$aff_enable = $this_skin['aff_enable'];
	} // if
	else
	{
		$aff_enable = 'no';
	} // else

	ois_start_table('Affiliate Options', 'mantra/ID.png');
	ois_option_label(array(
			'title' => 'Want to Make Money?',
			'description' => 'Use your skin to sell OptinSkin as an affiliate, and earn more money from your website.',
			'inner_style' => 'width:320px;' ));
	echo '<img style="float:right;width: 140px; margin-right:40px; padding: 15px;" src="' . OIS_URL . 'admin/images/clickbank.png" />';
	ois_inner_label(array('title' => 'Clickbank Username'));
	echo '<p><input	type="text"
					class="ois_textbox"
					name="aff_user"
					placeholder="Affliate Username"
					value="' . $aff_username . '" /></p>';
	ois_end_option_and_table();
	ois_inner_label(array('title' => 'Enable Affiliate Link for this Skin'));
	echo '<p><input	type="checkbox"
						name="aff_enable"
						value="yes"';
	if ($aff_enable == 'yes') {
		echo 'checked="checked"';
	}
	echo '/> Enable
			<p>Disabling this option will remove the link from your skin.</p></p>';
	ois_end_option_and_table();
	ois_end_option_and_table();

	ois_start_table('Finalize Your Skin', 'mantra/Upload.png');
	ois_option_label(array(
			'title' => 'Save Data',
			'description' => 'When you are finished creating your skin, hit \'Add this Skin\'.' ));
?>
					<input 	type="hidden"
							name="newskin_design_section"
							id="newskin_design_selection"
							<?php
	if (!empty($this_skin)) {
		echo 'value="' . $this_skin['design'] . '" />';
	} else {
		echo 'value="1" />';
	}
?>
					<input 	type="hidden"
							name="newskin_status"
							id="newskin_status"
							value="publish" />

				<?php  if ($skin_id != '') { ?>
					<input 	type="hidden"
							name="current_skin"
							id="newskin_current_skin"
							value="<?php echo $this_skin['id']; ?>" />
					<?php
	}
?>
					<div style="text-align:center; margin-right:300px;">
		<?php
	if (isset($this_skin['status']))
	{
		ois_super_button(array('value'=>'Update Skin'));
		/*
if ($this_skin['status'] == 'draft')
		{
			ois_super_button(array('value'=>'Publish this Skin', 'style' => '
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=\'rgb(104, 231, 127)\', endColorstr=\'#000000\');
	background: -moz-linear-gradient(top,  rgb(104, 231, 127),  rgb(48, 166, 85));
	background: -webkit-linear-gradient(top, rgb(104, 231, 127) 0px, rgb(48, 166, 85) 100%); !important; background-color: #30e77f !important; -webkit-box-shadow: rgba(255, 255, 255, 0.449219) 0px 1px 0px 0px inset !important; border: 1px solid #30a655 !important; color: #fff !important; text-shadow: transparent 0px 0px 0px, rgba(0, 0, 0, 0.449219) 0px 1px 0px !important;'));
			ois_secondary_button(array('value'=>'Update Skin', 'id'=>'ois_save_draft'));
		} // if
		else
		{

		} // else
*/
	} // if
	else // status will only be available if it has been saved before. Make sense?
		{
		ois_super_button(array('value'=>'Create this Skin', 'style' => 'filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=\'rgb(104, 231, 127)\', endColorstr=\'#000000\') !important;
	background: -moz-linear-gradient(top,  rgb(104, 231, 127),  rgb(48, 166, 85)) !important;
	background: -webkit-gradient(linear, left top, left bottom, from(rgb(104, 231, 127)), to(rgb(48, 166, 85))) !important;background-color: #30e77f !important; -webkit-box-shadow: rgba(255, 255, 255, 0.449219) 0px 1px 0px 0px inset !important; border: 1px solid #30a655 !important; color: #fff !important; text-shadow: transparent 0px 0px 0px, rgba(0, 0, 0, 0.449219) 0px 1px 0px !important;'));
		/*
ois_super_button(array(
				'value'=>'Save as a Draft',
				'id'=>'ois_save_draft',
				'style'=>'margin-left:20px;'));
*/
	} // else
	wp_nonce_field('ois_add_field', 'save_data');
	ois_end_option_and_table();
	echo '</form>';
	ois_section_end();

	/*
		LOADING GIF
	*/
?>
	<div id="ois_add_loader" style="display:none">
		<div style="margin-left:100px;margin-top:20px;margin-bottom:20px;">
		<h2 style="padding-bottom:10px;">Loading design</h2>
		</div>
	</div>
	<?php
}
?>