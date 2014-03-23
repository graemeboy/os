<?php

function ois_editing_heading($skin_id, $skin_name, $status)
{
	// Title
	$subtitle = "You are Currently Editing <em>$skin_name</em></em>";
	ois_section_title('Edit Skin', $subtitle);
	
	
	$uri = explode('?', $_SERVER['REQUEST_URI']);
	$dup_url = $uri[0] . '?page=addskin&duplicate=' . $skin_id;
?>
<div>
	<h2 class="nav-tab-wrapper">
	<?php 
	if ($status == 'publish')
	{
		// If the skin has been published, then the user can check performance.
		$performance_url = $uri[0] . '?page=ois-' . $skin_id;
		?>
		<a href="<?php echo $performance_url; ?>" class="nav-tab"><span class="glyphicon glyphicon-signal"></span> Skin Performance</a>
		<?php
	} // if
	?>
		<a href="<?php echo $_SERVER['REQUEST_URI']; ?>" class="nav-tab-active nav-tab"><span class="glyphicon glyphicon-edit"></span> Edit Skin</a>
		<a href="<?php echo $dup_url; ?>" class="nav-tab"><span class="glyphicon glyphicon-plus"></span> Duplicate Skin</a>
	</h2>
</div>	
	<?php
} // ois_add_heading (boolean)

function ois_generate_new_id($all_skins)
{
	// To create a new skin id, we cannot just count and add 1, because skins can be removed.
	$skin_id = 1;
	while (isset($all_skins[$skin_id]))
	{
		$skin_id++;
	} // while
	
	return $skin_id;
} // ois_generate_new_id (array)

function ois_add_init_table($skin_title, $skin_desc)
{
	ois_start_option_table('Initialize Your Skin', true, 'mantra/Comments.png');

	$data = array(
		'title' => 'Skin Name',
		'description' => 'The title used to identity this skin.',
		'alternative' => 'yes',
	);
	ois_option_label($data); ?>

	<input type="text" class="ois_textbox" id="ois_skin_name" name="newskin_name" placeholder="New Skin Name" value="<?php echo $skin_title; ?>" />
	<?php
	$random_messages = array( 'Great name!', 'That will do!', 'Excellent!', 'A splendid name!');
	$message = $random_messages[array_rand($random_messages)];
	ois_validate_message( array(
			'text' => $message,
			'value' => 'approve',
			'show' => false,
			'id' => 'ois_name_approve'));
	ois_validate_message( array(
			'text' => 'Please name your skin',
			'value' => 'disapprove',
			'show' => false,
			'id' => 'ois_name_disapprove'));
	ois_option_end();

	$data = array(
		'title' => 'Skin Purpose',
		'description' => 'Briefly describe your outcome for this skin.',
	);
	ois_option_label($data);
?>
	<input type="text" class="ois_textbox" id="new_skin_description" name="newskin_description" placeholder="The reason I am creating this skin is" value="<?php echo $skin_desc; ?>" /><br/>
	<?php
	ois_validate_message( array(
			'text' => 'Awesome. Having a description for your skin will keep you focused on its aim.',
			'value' => 'approve',
			'show' => false,
			'id' => 'ois_description_approve',
			'paragraph' => true));
	ois_option_end();
	ois_table_end();
} // ois_add_init_table()
?>