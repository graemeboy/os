<?php

function ois_custom() {

	ois_section_title('Create a Custom Design', 'This tool is for creating custom designs for your skins.', 'Enter  your HTML into the editor below, and preview your design as it instantly updates. Once you save, it will be available for you when you add new skins.');
	if (!empty($_POST) && check_admin_referer('ois_custom', 'custom_design') ) {
		// We need to save this to a file.

		// Custom Designs will hold an array of design ids
		$custom_designs = get_option('ois_custom_designs');

		if (!isset($_POST['design_id']))
		{
			// We are creating a new design id
			$design_id = 1;
			if (!empty($custom_designs))
			{
				while (in_array($design_id, $custom_designs))
				{
					$design_id++;
				} // while
				array_push($custom_designs, $design_id);
			} // if
			else
			{
				$custom_designs = array(1);
			}
			// We should now have a design id that isn't in use.
			
			update_option('ois_custom_designs', $custom_designs);
				
		} // if
		else
		{
			$design_id = $_POST['design_id'];
		} // else

		$custom_path = OIS_PATH . "customDesigns/$design_id";

		if ( !file_exists( $custom_path ) )
		{
			mkdir( $custom_path, 0777, true );
		} // if

		if (isset($_POST['design_html']))
		{
			$html_content = $_POST['design_html'];
			file_put_contents("$custom_path/static.html", stripslashes($html_content));
		} // if

		if (isset($_POST['design_css']))
		{
			$css_content = $_POST['design_css'];
			file_put_contents("$custom_path/style.css", stripslashes($css_content));
		} // if

	} // if
	else if (isset($_GET['id'])) {
			// Editing a design

			$design_id = trim($_GET['id']);
			$custom_path = OIS_PATH . "customDesigns/$design_id";

			if (file_exists($custom_path))
			{
				$this_html = file_get_contents("$custom_path/static.html");
				$this_css = file_get_contents("$custom_path/style.css");
			} // if
		} // else if
?>


	<style>
	#ois_custom_update_area {

		margin-bottom: 10px;
		min-height: 150px;


	}
	#ois_custom_editor, #ois_custom_css_editor {
		width: 100%;
		height: 210px;
		padding: 15px;
		border-radius: 3px;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		outline: none;
	}
	.ois_custom_waiting {
		text-align: center;
		margin: auto 0;
	}
	</style>

	<table class="widefat">
	<thead>
		<th>Preview of Your Design</th>
	</thead>
	<tbody>
		<tr class="alternate">
			<td>
				<style id="ois_custom_css_update_area">
					<?php
	if (isset($this_css) && trim($this_css) != '') {
		echo $this_css;
	}
?>
				</style>
				<div id="ois_custom_update_area">
					<?php
	if (isset($this_html) && trim($this_html) != '') {
		echo $this_html;
	} else {
		echo '<p style="padding-top: 20px;" class="ois_custom_waiting">Type into the Text Area Below...</p>';
	}
?>
				</div>
			</td>
		</tr>
	</tbody>
	</table>

	<form method="post">
	<table class="widefat">
	<thead>
		<th>Code Editor</th>
	</thead>
	<tbody>

	<tr>

	<td>
	<div>
	<h3>HTML Code</h3>
	<textarea id="ois_custom_editor" name="design_html"><?php
	if (isset($this_html) && trim($this_html) != '') {
		echo $this_html;
	} else {
		echo 'Add your custom HTML here...';
	}
	?></textarea>
	</div>
	<div>
	<h3>CSS Code</h3>
	<textarea id="ois_custom_css_editor" name="design_css"><?php
	if (isset($this_css) && trim($this_css) != '') {
		echo $this_css;
	} else {
		echo 'CSS goes here...';
	}
	?></textarea>
	</div>

	<script type="text/javascript">

	jQuery(document).ready(function ($) {
		$('#ois_custom_editor').focus(function () {
			if ($(this).val() == 'Add your custom HTML here...') {
				$(this).val('');
			}
		});
		$('#ois_custom_css_editor').focus(function () {
			if ($(this).val() == 'CSS goes here...') {
				$(this).val('');
			}
		});

		$('#ois_custom_editor, #ois_custom_css_editor').keydown(function (event)
		{

			if (event.keyCode == 9)
			{
				var tab = "    ";
				var t = event.target;
			    var ss = t.selectionStart;
			    var se = t.selectionEnd;
				event.preventDefault();

		        if (ss != se && t.value.slice(ss,se).indexOf("n") != -1) {
		            var pre = t.value.slice(0,ss);
		            var sel = t.value.slice(ss,se).replace(/n/g,"n"+tab);
		            var post = t.value.slice(se,t.value.length);
		            t.value = pre.concat(tab).concat(sel).concat(post);

		            t.selectionStart = ss + tab.length;
		            t.selectionEnd = se + tab.length;
				}
				else
				{
		            t.value = t.value.slice(0,ss).concat(tab).concat(t.value.slice(ss,t.value.length));
		            if (ss == se)
		            {
		                t.selectionStart = t.selectionEnd = ss + tab.length;
		            } // if
		            else
		            {
		                t.selectionStart = ss + tab.length;
		                t.selectionEnd = se + tab.length;
		            } // else
		        } // else
			} // if
		}); // keydown

		$('#ois_custom_editor').keyup(function (event) {
			$('#ois_custom_update_area').html($('#ois_custom_editor').val());
		}); // keyup
		$('#ois_custom_css_editor').keyup(function (event) {
			$('#ois_custom_css_update_area').html($('#ois_custom_css_editor').val());
		}); // keyup
	}); // document.ready
	</script>

	<?php
	if (isset($this_html) && trim($this_html) != '')
	{
		// Necessarily editing a design
?>
		<input type="hidden" name="design_id" value="<?php echo $design_id; ?>" />
			<?php
	}
?>
	<p><input type="submit" class="button-primary" value="Save Design" /></p>
	<?php
	wp_nonce_field('ois_custom', 'custom_design');
?>
	</td></tr>
	</tbody>
	</table>
	</form>


	<div class="ois_custom_title" style="font-size:15px;text-align:left;padding: 17px;">How to use the HTML/CSS Editor</div>
	<table class="widefat">
		<tr>
			<td class="ois_custom_info_sec">
				<div class="ois_custom_title">Step 1. Go to Custom Design Editor</div>
				<div>The editor provides a basic way to create your own designs, and still have OptinSkin power your stats and split-testing.</div>
			</td>

			<td>
				<img class="ois_custom_screen" src="<?php echo OptinSkin_URL . 'admin/images/custom_start.png'; ?>" />
			</td>
		</tr>
		<tr>
			<td class="ois_custom_info_sec">
				<div class="ois_custom_title">Step 2. Enter HTML Code</div>
				 <div>All you need to do is write some HTML code (or copy and paste it from your service provider) into the first box on the page. Notice how the preview updates as you do so.</div>
			</td>
			<td>
				<img class="ois_custom_screen" src="<?php echo OptinSkin_URL . 'admin/images/custom_html.png'; ?>" />
			</td>
		</tr>
		<tr>
			<td class="ois_custom_info_sec">
				<div class="ois_custom_title">Step 3. Create CSS Code</div>
				<div>You can also write some CSS in the box below, which will help you to style your design the way that you want it.</div>
			</td>
			<td>
				<img class="ois_custom_screen" src="<?php echo OptinSkin_URL . 'admin/images/custom_css.png'; ?>" />
			</td>
		</tr>
		<tr>
			<td class="ois_custom_info_sec">
				<div class="ois_custom_title">Step 4. View Final Product and Save</div>
				<div>Once you save a design, you can go over to the <em>Add Skin</em> page, and find your custom design by clicking through the slider in the <em>Customize Your Design</em> section. Once you've chosen this design, go ahead and create your skin wherever you want, and your design will appear in your posts.</div>
			</td>
			<td>
				<img class="ois_custom_screen" src="<?php echo OptinSkin_URL . 'admin/images/custom_prefinal.png'; ?>" />
			</td>
		</tr>
	</table>
	<div>


	</div>


	<style type="text/css">
		.ois_custom_screen {
			padding: 2px;
			border: 1px solid #ccc;
			max-height: 450px;
			max-width: 660px;
		}
		.ois_custom_info_sec {
			min-width:200px;
			width:34% !important;
			padding:5px 5px 15px 5px;
		}
		.ois_custom_title {
			font-size: 14px;
			font-weight: bold;
			padding: 10px 0 5px 0;
		}
	</style>
	<?php

	ois_section_end();

}

?>