<?php
function ois_edit_skin($skin) {
	if (isset($_GET['delete'])) {
		if (check_admin_referer('trash')) 
		{
			// Now we can delete the skin!
			$all_skins = get_option('ois_skins');
			$skin_id = $_GET['delete'];
			// Remove from list of designs
			unset($all_skins[$skin_id]);
		
			// Delete content from skins directory
			update_option('ois_skins', $all_skins);
			$skin_path = OIS_PATH . "/skins/$skin_id";
			ois_recursive_rmdir($skin_path);
			
			$updated_message = '&update=delete';
			$cur_location = explode("?", $_SERVER['REQUEST_URI']);
			$new_location = 'http://' . 
				$_SERVER["HTTP_HOST"] . $cur_location[0] . '?page=addskin';
			echo '<script type="text/javascript">
					window.location = "' . $new_location . $updated_message . '";
			</script>';
		} // if
	} // if delete
	else 
	{
		if (isset($_GET['range'])) {
			$stats_range = $_GET['range'];
		} else {
			$stats_range = 20;
		}
		
		if (isset($_GET['updated']) && $_GET['updated'] == 'true') 
		{
			ois_notification('Successfully Updated Your Skin!', 'margin: 5px 0 0 0 ;', '');
		} // if
		
		else if (isset($_GET['created']) && $_GET['created'] == 'true') 
		{
				$uri = explode('?', $_SERVER['REQUEST_URI']);
				$stats_url = $uri[0] . '?page=stats';
				ois_notification('Your new skin is now live on your site.' .
					'If you enabled split-testing, you can view how it is performing <a href="' .
					 $stats_url . '">here</a>.', 'margin: 5px 0 0 0 ;', '');
		} // else if
			
		ois_section_title('Skin Performance', 
			stripslashes($skin['title']), stripslashes($skin['description']));

		$all_stats = array();
		$skin_id = $skin['id'];
		global $wpdb;
		$table_name = $wpdb->prefix . 'optinskin';
		$sql = "SELECT * FROM $table_name WHERE skin='$skin_id' ORDER BY ts DESC";
		$rows = $wpdb->get_results($sql);
		foreach ($rows as $row) {
			$submission = $row->submission;
			$timestamp = $row->ts;
			$post = $row->post;
			if ($submission == 1) 
			{
				$submission = 'yes';
				// just the convention used here; as per Wordpress accepted style.
			} else {
				$submission = 'no';
			}
			$new_stat = array (
				's' => $skin_id,
				'm' => $submission,
				't' => $timestamp,
				'p' => $post
			);
			array_push($all_stats, $new_stat);
		}
		
		$impressions = array();
		$submits = array();

		if (!empty($all_stats)) 
		{
			foreach ($all_stats as $stats) 
			{
				if (!empty($stats['m']) && $stats['m'] == 'yes') 
				{
					array_push($submits, $stats);
				} // if
				else 
				{
					array_push($impressions, $stats);
				} // else
			} // foreach
		} // id
		
		$uri = explode('?', $_SERVER['REQUEST_URI']);
		$edit_url = $uri[0] . '?page=addskin&id=' . $skin['id'];
		$dup_url = $uri[0] . '?page=addskin&duplicate=' . $skin['id'];
		$export_url = $uri[0] . '?page=oisexport&skin=' . $skin['id'];
?>

	<div>
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo $_SERVER['REQUEST_URI']; ?>" class="nav-tab nav-tab-active"><span class="glyphicon glyphicon-signal"></span> Skin Performance</a>
			<a href="<?php echo $edit_url; ?>" class="nav-tab"><span class="glyphicon glyphicon-edit"></span> Edit Skin</a>
			<a href="<?php echo $dup_url; ?>" class="nav-tab"><span class="glyphicon glyphicon-plus"></span> Duplicate Skin</a>
			<a href="<?php echo $export_url; ?>" class="nav-tab"><span class="glyphicon glyphicon-export"></span> Export Skin as HTML</a>
			<a 	class="nav-tab" href="<?php 
				echo wp_nonce_url( $_SERVER['REQUEST_URI'], 'trash'); 
			?>&delete=<?php echo $skin['id']; ?>"><span class="glyphicon glyphicon-trash"></span> Delete Skin</a>
		</h2>
	</div>

		<style>
			.ois_stats_option, .ois_stats_days_option {
				padding: 5px 9px;;
				border-radius: 4px;
				-moz-border-radius: 4px;
				-webkit-border-radius: 4px;
				-moz-box-shadow:    1px 1px 1px 1px #f1f5f9;
	 			-webkit-box-shadow: 1px 1px 1px 1px #f1f5f9;
	  			box-shadow:         1px 1px 1px 1px #f1f5f9;
	  			border: 1px solid #e3e9f0;
	  			margin: 5px;
			}
			.ois_stats_option:hover, .ois_stats_days_option:hover {
				background-color: #f7f9fb;
				-moz-box-shadow:    1px 1px 1px 1px #eee;
	 			-webkit-box-shadow: 1px 1px 1px 1px #eee;
	  			box-shadow:         1px 1px 1px 1px #eee;
			}
			div.ois_stat_options{
				padding: 15px 0 15px 5px;
			}
			.ois_plotarea {
				margin: 12px 8px 10px 8px;
				height: 255px;
			}
			.ois_vis_a_active, .ois_stats_days_a_active {
				padding: 5px 12px;
				background-color: #f0f4f8 !important;
			}
			.ois_code_snippet {
				padding: 5px;
				background-color: #fffeee;
				border: 1px dashed #fff222;
			}
			.ois_stats_table {
				margin-top: 10px !important;
			}
			.ois_vis_a:hover
			{
				text-decoration: none;
			}
		</style>
		<link href="<?php echo OIS_URL ?>admin/css/glyphicons.bootstrap.min.css" rel="stylesheet" />
			<table class="widefat" style="width:95%; margin: 10px 0;">
			<tbody>
				<tr class="alternate">
					<td>
			<h3 style="margin:5px 0;">How to embed this skin in a custom position</h3>
			<p style="line-height:20px;">
				To use this skin as a shortcode, simply put
				<span class="ois_code_snippet" id="ois_use_shortcode">[ois skin="<?php echo $skin['id']; ?>"]</span> into any of your posts.<br/>To use it on a php page, such as <em>header.php</em> or <em>footer.php</em>, use the php code
				<span class="ois_code_snippet" id="ois_do_shortcode">&lt;?php echo do_shortcode( '[ois skin="<?php echo $skin['id']; ?>"]' ); ?&gt;</span>.<br/>
				If you want to split-test using the shortcode, add other skin ID inside of split="". For example: 
				<span class="ois_code_snippet" id="ois_other_shortcode">[ois skin="<?php echo $skin['id']; ?>" split=""]</span>
				, or <span class="ois_code_snippet" id="ois_other2_shortcode">[ois skin="<?php echo $skin['id']; ?>" split="7,8"]</span>
			</p>
		</div>
		</td>
	</tr>
</tbody>
</table>
<style>
.ois_column_left {
	float:left;
	width:45%;
	padding-right: 5%;
}
.ois_column_right {
	float:right;
	width:45%;
	padding-right: 5%;
}
.ois_skin_action {
	margin: 0 5px;
}
</style>

	<?php
		// STATISTICS //
		$stats_disable = get_option('stats_disable');
		if ($stats_disable != 'yes') { ?>
		<script src="<?php echo OIS_URL ?>admin/skinStats/js/jquery.flot.min.js" language="javascript" type="text/javascript"></script>
		<script language="javascript" type="text/javascript">

		jQuery(function ($) {
			// For signups

		var signups_data = [
			{
				color: '#5793c3',
				label: "Number of Signups",
				data:
					[
				<?php
			// Data for Submits
			$submits_data = array();
			for ($i = 0; $i < $stats_range; $i++) {
				$x = 0;
				// for each day
				if (!empty($submits)) {
					foreach ($submits as $submit) {
						if ($i > 0) {
							$day_back = strtotime('-' . $i . ' days America/Chicago');
						} else {
							$day_back = strtotime('now America/Chicago');
						}
						$two_day_back =
							strtotime('-' . ($i + 1) . ' days America/Chicago');
						// go through all impressions
						if (strtotime($submit['t']) < $day_back
							&& strtotime($submit['t']) > $two_day_back) {
							// if within this day
							$x++;
						}
					}
				}
				array_unshift($submits_data, $x);
			}
			foreach ($submits_data as $i=>$quantity) {
				echo '[' . $i . ', ' . $quantity . '],';
			}
?>
					]
			}
		];
			var signups_options = {
			yaxis: {
				color: "#000",
				tickColor: "#e6eff6",
				tickDecimals: 0,
				min: 0,
			},
			xaxis: {
				color: "#000",
				tickColor: "#e6eff6",
				min: 0,
				tickSize: 30,
			    ticks: [
			    	<?php

			if ($stats_range <= 60) {
				for ($i = 0; $i < $stats_range; $i++) {
					$date = explode('-', date('Y-m-d', strtotime('-' . $i . ' days')));
					echo '[' . (($stats_range - 1) - $i) . ', \'' . $date[1] . '/' . $date[2] . '\'],';
				}
			}
?>
			    	]
		  	},
			legend: {
				show: true,
				margin: 10,
				backgroundOpacity: 0.5,
			},
			grid: {
				hoverable: true,
				clickable: true,
				aboveData: false,
				backgroundColor: null,
				color: "rgba(87, 147, 195, 0.5)",
				borderColor: "#444",
			},
			interaction: {
			},
			points: {
				show: true,
				radius: 3,
			},
			lines: {
				show: true,
				fill: true,
				fillColor: "rgba(87, 147, 195, 0.4)",
			},
		};
			var ois_signups_plot = $("#ois_signups_plot");
			$.plot( ois_signups_plot , signups_data, signups_options );

			// For impressions

			var impressions_data = [
			{
				color: '#5793c3',
				label: "Number of Impressions",
				data:
					[
				<?php
			// Data for Impressions
			$impression_data = array();
			/*
			$today = strtotime('now');
			$yesterday = strtotime('-1 day');
			$test = strtotime('2013-08-21 14:06:03');
			*/
			//echo "today: $today - $yesterday ; test: $test;";
			for ($i = 0; $i < $stats_range; $i++) {
				$x = 0;
				// for each day
				if (!empty($impressions)) {
					foreach ($impressions as $impression) {
						// go through all impressions
						if ($i > 0) {
							$day_back = strtotime('-' . $i . ' days America/Chicago');
						} else {
							$day_back = strtotime('now America/Chicago');
						}
						$two_day_back = strtotime('-' . ($i + 1) . ' days America/Chicago');
						$impression_time = strtotime($impression['t']);
						if ($impression_time < $day_back
							&& $impression_time > $two_day_back) {
							// if within this day
							$x++;
							//echo 'Yes!';
						} else {
							//echo "Compare: $impression_time < $day_back
							//&& $impression_time > $two_day_back";
						}
					}
				}
				array_unshift($impression_data, $x);
			}
			foreach ($impression_data as $i=>$quantity) {
				echo '[' . $i . ', ' . $quantity . '],';
			}
?>
					]
			}
		];
			var impressions_options = {
			yaxis: {
				tickDecimals: 0,
				min: 0,
				color: "#000",
				tickColor: "#e6eff6",
			},
			xaxis: {
				color: "#000",
				tickColor: "#e6eff6",
				min: 0,
		    	ticks: [
		    	<?php
			if ($stats_range <= 60) {
				for ($i = 0; $i < $stats_range; $i++) {
					$date = explode('-', date('Y-m-d', strtotime('-' . $i . ' days')));
					echo '[' . (($stats_range - 1) - $i) . ', \'' . $date[1] . '/' . $date[2] . '\'],';
				}
			}
?>
		    	]
		  	},
			legend: {
				show: true,
				margin: 10,
				backgroundOpacity: 0.5,
			},
			grid: {
				hoverable: true,
				clickable: true,
				aboveData: false,
			},
			interaction: {
			},
			points: {
				show: true,
				radius: 3,
			},
			lines: {
				show: true,
				fill: true,
				fillColor: "rgba(87, 147, 195, 0.4)",
			},

		};
			var ois_impressions_plot = $("#ois_impressions_plot");
			$.plot( ois_impressions_plot , impressions_data, impressions_options );

			// For Conversions

			var conversions_data = [
			{
				color: '#5793c3',
				label: "Conversion Rate",
				data:
					[
				<?php
			// Data for Conversions
			if (!empty($impression_data)) {
				foreach ($impression_data as $i=>$quantity) {
					if ($quantity > 0) {
						$rate = $submits_data[$i]/$quantity;
						echo '[' . $i . ', ' . ($rate * 100) . '],';;
					} else {
						echo '[' . $i . ', 0],';
					}
				}
			}
?>
					]
			}
		];
			var conversions_options = {
			yaxis: {
				min: 0,
				color: "#000",
				tickColor: "#e6eff6",
				tickDecimals: 2,
			},
			xaxis: {
				color: "#000",
				tickColor: "#e6eff6",
				min: 0,
		    	ticks: [
		    	<?php
			if ($stats_range <= 60) {
				for ($i = 0; $i < $stats_range; $i++) {
					$date = explode('-', date('Y-m-d', strtotime('-' . $i . ' days')));
					echo '[' . (($stats_range - 1) - $i) . ', \'' . $date[1] . '/' . $date[2] . '\'],';
				}
			}
?>
		    	]
		  	},
			legend: {
				show: true,
				margin: 10,
				backgroundOpacity: 0.5,
			},
			grid: {
				hoverable: true,
				clickable: true,
				aboveData: false,
			},
			interaction: {
			},
			points: {
				show: true,
				radius: 3,
			},
			lines: {
				show: true,
				fill: true,
				fillColor: "rgba(87, 147, 195, 0.4)",
			},
		};
			var ois_conversions_plot = $("#ois_conversions_plot");
			$.plot( ois_conversions_plot , conversions_data, conversions_options );

			function ois_stat_showTooltip(x, y, contents) {
		        $('<div id="tooltip">' + contents + '</div>').css( {
		            position: 'absolute',
		            display: 'none',
		            top: y + 5,
		            left: x + 5,
		            border: '1px solid #fdd',
		            padding: '2px',
		            'background-color': '#fee',
		            opacity: 0.80
		        }).appendTo("body").fadeIn(200);
		    }
		    var previousPoint = null;
		    $("#ois_plotarea").bind("plothover", function (event, pos, item) {
		        $("#x").text(pos.x);
		        $("#y").text(pos.y);

		        if (item) {
		            if (previousPoint != item.dataIndex) {
		                previousPoint = item.dataIndex;

		                $("#tooltip").remove();
		                var x = item.datapoint[0],
		                    y = item.datapoint[1];

		                ois_stat_showTooltip(item.pageX, item.pageY, y);
		            }
		        }
		        else {
		            $("#tooltip").remove();
		            previousPoint = null;
		        }
		    });

		    $('#ois_a_impressions').click(function() {
		    	$('#ois_chart_title').text('Impressions in the Last <?php echo $stats_range; ?> Days');
		    	$('.ois_plotarea').hide();
		    	$('#ois_impressions_plot').show();
		    });
		     $('#ois_a_signups').click(function() {
		     	$('#ois_chart_title').text('Signups in the Last <?php echo $stats_range; ?> Days');
		    	$('.ois_plotarea').hide();
		    	$('#ois_signups_plot').show();
		    });
		     $('#ois_a_conversions').click(function() {
		     	$('#ois_chart_title').text('Conversion Rates in the Last <?php echo $stats_range; ?> Days');
		    	$('.ois_plotarea').hide();
		    	$('#ois_conversions_plot').show();
		    });


		    $('.ois_plotarea').hide();
			$('#ois_signups_plot').show();

			$('.ois_vis_a').click(function () {
				$('.ois_vis_a_active').removeClass('ois_vis_a_active');
				$(this).parent().addClass('ois_vis_a_active');
			});


			$('.ois_code_snippet').click(function () {

				selectText($(this).attr('id'));

			});

			function selectText(element) {
			    var doc = document;
			    var text = doc.getElementById(element);
			    if (doc.body.createTextRange) {
			        var range = document.body.createTextRange();
			        range.moveToElementText(text);
			        range.select();
			    } else if (window.getSelection) {
			        var selection = window.getSelection();
			        var range = document.createRange();
			        range.selectNodeContents(text);
			        selection.removeAllRanges();
			        selection.addRange(range);
			    }
			}

		});
		</script>
		<table class="widefat" style="width:95%; margin-bottom:10px;">
			<thead>
				<th id="ois_chart_title">Signups in the Last <?php echo $stats_range; ?> Days</th>
			</thead>
			<tbody>
			<tr class="alternate">
				<td>
			<div id="ois_signups_plot" class="ois_plotarea">
				<div style="text-align:center;">
					<p>
						<img	style="width: 80px;"
								src="<?php
			echo OIS_URL ?>admin/images/circle_load.gif" />
					</p>
					<p>Loading a Visualization of Your Data...</p>
				</div>
			</div>
			<div id="ois_impressions_plot" class="ois_plotarea">
			</div>
			<div id="ois_conversions_plot" class="ois_plotarea">
			</div>
		</td>
			</tr>
			<tr>
				<td>
				<div class="ois_stat_options">
					<strong>Visualize Statistics: </strong>
					<span class="ois_stats_option ois_vis_a_active">
						<a href="javascript:void();" id="ois_a_signups" class="ois_vis_a">Signups</a>
					</span>
					<span class="ois_stats_option">
						<a href="javascript:void();" id="ois_a_impressions" class="ois_vis_a">Impressions</a>
					</span>
					<span class="ois_stats_option">
						<a href="javascript:void();" id="ois_a_conversions" class="ois_vis_a">Conversion Rate</a>
					</span>
					<?php
			$useable_uri = explode('&range=', $_SERVER['REQUEST_URI']);
			$days = array(20, 30, 60, 90);
			$days = array_reverse($days);
			foreach ($days as $day) 
			{
				echo '<span class="ois_stats_days_option ';
				if ($stats_range == $day) 
				{
					echo 'ois_stats_days_a_active';
				} // if
				echo '" style="float:right;margin-top:-7px;">
							<a 	href="' . $useable_uri[0] . '&range=' . $day . '"
								id="ois_a_lol" class="ois_stats_days_a ">' . $day . ' Days</a>
							</span>';
			} // foreach
?>

				</div>
				</td>
			</tr>
			</tbody>
			</table>
			<div class="wrapper" style="width:95%;">
			<?php
			// Top Posts
			$post_stats_submits = array();
			$post_stats_impressions = array();
			$all_posts = get_posts();
			
			if (!empty($all_posts)) 
			{
				foreach ($all_posts as $post) 
				{
					$post_id = $post->ID;
					$post_stats_submits[$post_id] = 0;
					$post_stats_impressions[$post_id] = 0;
					foreach ($submits as $submit) 
					{
						if ($submit['p'] == $post_id) 
						{
							$post_stats_submits[$post_id]++;
						} // if
					} // foreach
					foreach ($impressions as $impression) 
					{
						if ($impression['p'] == $post_id) 
						{
							$post_stats_impressions[$post_id]++;
						} // if
					} // foreach
				} // foreach
			} // if
			

			asort($post_stats_impressions);
			$post_stats_impressions =  array_reverse($post_stats_impressions, true);
			asort($post_stats_submits);
			$post_stats_submits = array_reverse($post_stats_submits, true);
?>
			<table class="widefat ois_stats_table">
				<thead>
					<th><span class="glyphicon glyphicon-star"></span> Top 10 Posts</th>
					<th>Signups</th>
					<th>Impressions</th>
					<th>Conversion Rate</th>
				</thead>
				<tbody>
				<?php
			$count = 0;
			$max_count = 10;
			if (!empty($post_stats_submits)) {
				foreach ($post_stats_submits as $post_num=>$stats) {
					if ($count < $max_count) {
						$this_post = get_post($post_num);
						if (strlen($this_post->post_title) > 60) {
							$title = substr($this_post->post_title, 0, 60) . '...';
						} else {
							$title = $this_post->post_title;
						}

						if ($post_stats_impressions[$post_num]) {
							$num_imp = $post_stats_impressions[$post_num];
						} else {
							$num_imp = 0;
						}
						// conversion rate
						if ($num_imp > 0) {
							$rate = round(($stats/$num_imp * 100), 1);
						} else {
							$rate = 0;
						}
						echo '<tr>';
						echo '<th scope="row"><a href="' . $this_post->guid . '">' . $title . '</a></th>';
						echo '<td>' . $stats . '</td>';
						echo '<td>' . $num_imp . '</td>';
						echo '<td>' . $rate . '%</td>';
						echo '</tr>';
						$count++;
					} else {
						break;
					}
				}
			}
?>
				</tbody>
			</table>
			</td>
			</tr>
			</tbody>
			</table>

			<?php
			$num_data = get_option('ois_cleanup_period');
			if (!$num_data) {
				$num_data = 31; // 31 is default.
				update_option('ois_cleanup_period', $num_data);
			}
?>

			</div>

			<?php
			ois_section_end();
?>
			<div style="clear:both"></div>
			<?php
		}

		function ois_start_stat_table($attr) {

?>

		<table class="widefat" style="margin-top:15px;">
			<thead>
				<tr>
					<th <?php $title_style; ?>>
						<?php echo $attr['title']; ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
					<table>
						<thead>
							<tr>
							<?php
			$i;
			foreach ($attr['subs'] as $sub) {
				if ($i == 0) {
					echo '<th ' . $attr['first_sub_style'] . ' >';
				} else {
					echo '<th>';
				}
				echo $sub . '</th>';
				$i++;
			}
?>
							</tr>
						</thead>
						<tbody>
						<?php
			foreach ($attr['data'] as $name=>$data) {
				echo '<tr>';
				echo '<th scope="row">' . $name . '</th>';

				foreach ($data as $datum) {
					echo '<td>' . $datum . '</td>';
				}
				echo '</tr>';
			}
?>
						</tbody>
					</table>
					</td>
				</tr>
			</tbody>
		</table>
	<?php
		}
	}
}
?>