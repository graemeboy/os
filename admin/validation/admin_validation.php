<?php

add_action( 'wp_ajax_ois_update_license', 'ois_update_license' );

/*
	Please do not edit this page, unless you really know what you're doing.
	For example, if you try to override wthe license-key information that is used,
	the plugin will probably break.
	The license key is required to be sent to our server,
	in order to receive the latest designs and functions.
*/
function ois_license_key()
{
	ois_section_title('License Key', 'Here you can update your license key settings', '');
	
	$license_key = get_option('ois-key');
	$home_url = home_url();
	if ($home_url == '')
	{
		$home_url = 'Undefined';
	}
	
?>

<style type="text/css">
.ois-validation-alert
{
	display: none;
	border-style: solid;
	border-width: 1px;
	padding: 15px;
	margin-bottom: 20px;
	border-radius: 4px;
}
#validation-error
{
	background-color: #f2dede;
	border-color: #ebccd1;
	color: #a94442;
}
#validation-success
{
	background-color: #dff0d8;
	border-color: #d6e9c6;
	color: #3c763d;
}
</style>

<div id="validation-error" class="ois-validation-alert">Error</div>
<div id="validation-success" class="ois-validation-alert">Success</div>

<h4>Please enter your license key below. The plugin requires this to access the designs when you create a new skin.</h4>
<form method="post" id="ois-validation-form">
	<input type="hidden" name="homeUrl" value="<?php echo $home_url; ?>"
	<div>
		<input type="text" id="ois-license-in" class="ois_textbox" style="width:230px;" placeholder="Type your license key here" name="licenseKey" value="<?php echo $license_key ?>">
	</div>
	<div>
		<input type="submit" class="ois_super_button" value="Save Settings">
	</div>
</form>
<script type="text/javascript">
	jQuery(document).ready(function ($)
	{
		var data;
		var apiUrl = "<?php echo OIS_EXT_URL ?>check_license.php";
		var homeUrl = "<?php echo get_home_url(); ?>"
		var license = "";
		
		$('#ois-validation-form').submit(function (e)
		{
			e.preventDefault();
			license = $("#ois-license-in").val();
			
			// We will be expecting a new alert, so hide any current.
			$('.ois-validation-alert').hide();
			
			data = $('#ois-validation-form').serialize();
			console.log(data);
			jQuery.post(apiUrl, data, function (data)
			{
				// This is going to check to see if we have a working license.
				if (data == 1)
				{
					// Good.
					var data = {
						action: 'ois_update_license',
						license: license,
						homeUrl: "<?php echo $home_url ?>",
					};
					// ajaxurl is already defined.
					$.post(ajaxurl, data, function(response) {
						if (response == 0)
						{
							$('#validation-error').text(
								"Your license key was validated, but there " +
								"seems to be a problem with updating the option on Wordpress. " +
								"Please try again, and if the problem persists, " +
								"contact OptinSkin support.");
								
							$('#validation-error').show();
						} // if 
						else
						{
							$('#validation-success').text(response);
							$('#validation-success').show();
						} // else
						//alert('Got this from the server: ' + response);
					});
					
					
				}
				else
				{
					// Error.
					$('#validation-error').text(data);
					$('#validation-error').show();
				} // else
			}); // done

		}); // submit
	}); // document. ready
</script>
<?php
	//}
}

// Update the license once successfully posted via ajax.
function ois_update_license ()
{
	if (isset($_POST['license']))
	{
		$license = $_POST['license'];
		if (isset($_POST['homeUrl']))
		{
			$home_url = $_POST['homeUrl'];
		} // if
		else
		{
			// But generally, there should be. This is in rare case.
			$home_url = 'Undefined';
		} // else no home url
		update_option('ois-valid', 'yes');
		update_option('ois-home-url', $home_url);
		update_option('ois-key', $license);
		echo "Your license key was validated, and saved successfully. Thank you for using OptinSkin.";
	} // if
	else
	{
		// There was an error updating.
		echo 0;
	} // else
	
	die();
}
?>