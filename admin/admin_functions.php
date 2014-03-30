<?php
/**
 * ois_recursive_rmdir function.
 * Based on: http://stackoverflow.com/a/15111679/126320
 * 
 * @access public
 * @param mixed $dir
 * @return void
 */
function ois_recursive_rmdir($dir)
{
	$files = new RecursiveIteratorIterator(
	    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
	    RecursiveIteratorIterator::CHILD_FIRST
	);
	
	foreach ($files as $fileinfo) {
	    $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
	    $todo($fileinfo->getRealPath());
	}
	
	rmdir($dir);
}


/**
 * ois_section_title function.
 * 
 * @access public
 * @param mixed $title
 * @param mixed $subtitle
 * @param string $helper (default: '')
 * @return void
 */
function ois_section_title($title, $subtitle, $helper = '') 
{
	?>
	<link href="<?php echo OIS_URL . 'admin/css/admin_style.css' ?>" rel="stylesheet" />
	<?php
	$all_designs = get_option('ois_designs');
	if (!$all_designs) {
		ois_update_designs_code();
		$all_designs = get_option('ois_designs');
	} // if
	
	// ENQUEUE ALL DESIGN STYLES
	if (!empty($all_designs)) {
		foreach ($all_designs as $design) {
			if ($design['custom'] == 'no'
				&& trim($design['css_url']) != '') {
				$css_url = $design['css_url'];
				echo "<link rel='stylesheet' href='" . $css_url . "' type='text/css' media='all' />";
			} // if
		} // foreach
	} // if
	
	echo '<div class="wrap" style="position:relative;">
			<div id="ois_header" style="">
			<img src="' . OIS_URL . 'front/images/optinskin.png" style="margin:0; height:60px; float:right; padding-top: 15px;" />
			<h2>' . $title . '</h2><h3>' . $subtitle . '</h3><p>' . $helper . '</p>
		</div><div style="clear:both;"></div>';
} // ois_section_title ()

/**
 * ois_section_end function.
 * 
 * @access public
 * @return void
 */
function ois_section_end() 
{
	echo '</div>';
} // ois_section_end()

/**
 * ois_option_label function.
 *
 * Creates the labels for the add skin page. 
 *
 * @access public
 * @param mixed $data
 * @return void
 */
function ois_option_label($data) 
{
	if (!empty($data['id'])) {
		$el_id = $data['id'];
	} else {
		$el_id = '';
	}
	if (!empty($data['class'])) {
		$el_class = 'class="' . $data['class'] . '"';
	} else {
		$el_class = '';
	}
	if (!empty($data['style'])) {
		$style = 'style="' . $data['style'] . '"';
	} else {
		$style = '';
	}
	if (!empty($data['inner_style'])) {
		$inner_style = $data['inner_style'];
	} else {
		$inner_style = '';
	}
	
	echo '<tr id="' . $el_id . '" ' . $el_class . ' ' . $style . '"';
	if (!empty($data['alternative']) && $data['alternative'] == 'yes') {
		echo ' class="alternate" ';
	}
	echo '>
			<td class="ois_label" style="font-family:\'Arial\';text-align:left !important;padding: 15px 5px 15 10px !important;text-shadow: #fff 0px 1px 0px !important;font-size:12px;min-width:170px;' . $inner_style . '">
				' . $data['title'] . '
				<p style="font-family: \'Arial\';">
					<small style="font-size:11px;">' . $data['description'] . '</small>
				</p>
			</td>
			<td class="ois_field">';

	if (!empty($data['image']) && trim($data['image']) != '') {
		if (!empty($data['image-right-padding']) 
			&& trim($data['image-right-padding']) != '') {
			$right_padding = $data['image-right-padding'];
		} else {
			$right_padding = '50px';
		}
		echo '<img src="' . OIS_URL . 'admin/images/' . $data['image'] . '" style="float:right;" />';
	}
}


/**
 * ois_option_end function.
 * 
 * @access public
 * @return void
 */
function ois_option_end() 
{
	echo '</td></tr>';
}


/**
 * ois_start_option_table function.
 * 
 * @access public
 * @param mixed $title
 * @param mixed $multiform
 * @param mixed $img
 * @return void
 */
function ois_start_option_table($title, $multiform, $img) 
{
	if ($multiform) {
		$multiform = 'enctype="multipart/form-data"';
	} else {
		$multiform = '';
	}
	echo '<form method="post" ' . $multiform . ' >';
	ois_start_table ($title, $img);
}

function ois_start_table($title, $img) 
{
	if (empty($img)) {
		$img = '';
	}
	echo '
		<table class="widefat ois_table" style="margin-bottom: 10px !important;">
			<thead>
				<tr class="ois_header_row">
					<th class="ois_header_title" style="font-family: \'Voces\' !important;border:none;color:#333;font-size:14px;font-weight:normal;vertical-align:middle !important;max-width:250px;text-shadow: #fff 0px 1px 0px !important;">';
			
		if (trim($img) != '') {
			echo '<img src="' . OIS_URL . 'admin/images/' . $img . '" style="height:16px;padding:0;margin:0;margin-bottom:-2px;padding-right:10px;" />';
		}
	echo $title . '</th>
					<th style="border:none;color:#333;vertical-align:middle !important;"><span style="float:right;"><a class="ois_header_min" data-closed="' . OIS_URL . 'admin/images/plus.png" data-open="' . OIS_URL . '/admin/images/minus.png" href="javascript:void();" ><img src="' . OIS_URL . '/admin/images/minus.png" style="height:25px;margin-bottom:-5px;" /></a></span></th>
				</tr>
			</thead>';
}


/**
 * ois_table_end function.
 * 
 * @access public
 * @return void
 */
function ois_table_end() 
{
	echo '</table>'; // Yes, really.
}


/**
 * ois_inner_label function.
 * 
 * @access public
 * @param mixed $data
 * @return void
 */
function ois_inner_label($data) 
{
	if (!empty($data['style'])) {
		$style = $data['style'];
	} else {
		$style = '';
	}
	if (!empty($data['inner_style'])) {
		$inner_style = $data['inner_style'];
	} else {
		$inner_style = '';
	}
	if (!empty($data['title'])) {
		$title = $data['title'];
	} else {
		$title = '';
	}
	echo '<table class="ois_table_inner" style="' . $style . '">
			<tr>';
			
			if (trim($title) != '') {
				echo '<th scope="row" style="min-width:100px;border:none;">
					' . $title . '
				</th>';
			}
			echo '<td class="ois_field_inner" style="' . $inner_style . '">';
}

function ois_validate_message($data) {
	
	if (!empty($data['style'])) {
		$style = $data['style'];
	} else {
		$style = '';
	}
	if (!empty($data['value'])) {
		$value = $data['value'];
	} else {
		$value = '';
	}
	
	if (!$data['show']) {
		$style = 'style="display:none; ' . $style . '"';
	} else {
		$style = 'style="' . $style . '"';
	}
	$content = '<span class="ois_valid_message_' . $value . '" ' .
		$style . ' id="' . $data['id'] . '">' . $data['text'] . '</span>';
	if (!empty($data['paragraph']) && trim($data['paragraph']) != '') {
		echo '<p>' . $content . '</p>';
	} else {
		echo $content;
	}
}

function ois_create_steps($steps) {

	$content = '<div class="ois_steps">';
	foreach ($steps as $name => $step) {
		$content .= '
		<div class="ois_step_wrap">
			<div class="ois_ui_box">
					<h3 class="ois_' . $name . '">
						<div class="ois_step_title">
							' . $step['title'] . '
						</div>
					</h3>
				<div class="ois_step_description">
					' . $step['description'] . '
				</div>
			</div>
		</div>';
	}
	$content .= '</div>

	<div style="clear:both;"></div>';

	echo trim($content);
}


/**
 * ois_notification function.
 *
 * Appends a notification to the top of the page. 
 *
 * @access public
 * @param mixed $message
 * @param mixed $style
 * @param mixed $link
 * @return void
 */
function ois_notification($message, $style, $link) {
	$content = '<div class="ois_notification" style="' . $style . '">';
	$content .= $message;

	if ($link == 'drafts') {
		$uri = explode('?', $_SERVER['REQUEST_URI']);
		$drafts_url = $uri[0] . '?page=ois-drafts';
		$content .= ' <a href="'.$drafts_url.'">View Drafts</a>';
	} else if ($link == 'trash') {
			$uri = explode('?', $_SERVER['REQUEST_URI']);
			$trash_url = $uri[0] . '?page=ois-trash';
			$content .= ' <a href="'.$trash_url.'">View Trash</a>';
		}
	$content .= '</div>';
	echo $content;
}


/**
 * ois_end_option_and_table function.
 * 
 * @access public
 * @return void
 */
function ois_end_option_and_table() {
	ois_option_end();
	ois_table_end();
}


/**
 * ois_super_button function.
 * Creates a styled button that can be used for saving major options.
 * 
 * @access public
 * @param mixed $attr
 * @return void
 */
function ois_super_button($attr) 
{
	if (!empty($attr['id'])) {
		$id = $attr['id'];
	} else {
		$id = '';
	}
	if (!empty($attr['value'])) {
		$value = $attr['value'];
	} else {
		$value = '';
	}
	if (!empty($attr['style'])) {
		$style = $attr['style'];
	} else {
		$style = '';
	}
	echo '<input	type="submit"
					class="ois_super_button"
					id="' . $id . '"
					value="' . $value . '"
					style="' . $style . '" />';
}


/**
 * ois_secondary_button function.
 * 
 * @access public
 * @param mixed $attr
 * @return void
 */
function ois_secondary_button($attr) 
{
	if (!empty($attr['id'])) {
		$id = $attr['id'];
	} else {
		$id = '';
	}
	if (!empty($attr['value'])) {
		$value = $attr['value'];
	} else {
		$value = '';
	}
	if (!empty($attr['style'])) {
		$style = $attr['style'];
	} else {
		$style = '';
	}
	echo '<input 	type="submit"
					class="ois_secondary_button"
					id="' . $id . '"
					value="' . $value . '"
					style="' . $style . '" />';
} // ois_secondary_button
?>