<?php


/**
 * ois_general_settings function.
 * Sets up the options page for OptinSkin Settings.
 * 
 * @access public
 * @return void
 */
function ois_general_settings() {

	if ( !empty($_POST) ) {
		if (!empty($_POST['stats_submissions_disable'])) {
			if ( !check_admin_referer('ois_general_field', 'save_data')) {
				echo 'Sorry, your nonce did not verify.';
				exit;
			} // if 
			else {
				if (!empty($_POST['stats_impressions_disable'])) {
					$stats_impressions_disable = $_POST['stats_impressions_disable'];
				} // if 
				else {
					$stats_impressions_disable = '';
				} // else
				if (!empty($_POST['stats_submissions_disable'])) {
					$stats_submissions_disable = $_POST['stats_submissions_disable'];
				} // if 
				else {
					$stats_submissions_disable = '';
				} // else
				update_option('stats_impressions_disable', $stats_impressions_disable);
				update_option('stats_submissions_disable', $stats_submissions_disable);
				ois_notification('Your General Settings Have Been Updated!', '', '');
			} // else
		} // if
		else if (!empty($_POST['ois_reset'])) {
			if ($_POST['ois_reset'] == 'designs') {
				if (!check_admin_referer('ois_reset_designs', 'reset')) {
					echo 'Sorry, your nonce did not verify.';
					exit;
				} else {
					ois_update_designs_code();
					ois_notification('Your Designs Have Been Reset to Default', '', '');
				}
			} else if ($_POST['ois_reset'] == 'stats') {
				if (!check_admin_referer('ois_reset_stats', 'reset')) {
					echo 'Sorry, your nonce did not verify.';
					exit;
				} // if 
				else {
					// Now we need to reset all of the stats.
					global $wpdb;
					$table_name = $wpdb->prefix . 'optinskin';
					$wpdb->query("TRUNCATE TABLE $table_name");
					ois_notification('Your Stats Have Been Successfully Cleared', '', '');
				} // else
			} // else if
		} // else if
	} // if


	ois_section_title('General Settings', 'Here you can update your general settings', '');
	ois_start_option_table('Configure Stats', 'no', '');
	$stats_submissions_disable = get_option('stats_submissions_disable');
	$stats_impressions_disable = get_option('stats_impressions_disable');
?>
	<tr>
		<th scope="row" style="width:280px;">
			Disable Impression Statistics <br/>
		</th>
		<td>
			<p>
				<input	type="checkbox"
						name="stats_impressions_disable"
						value="yes"
				<?php if ($stats_impressions_disable == 'yes') { echo 'checked="checked"'; } ?> /> Disable
			</p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			Disable Submission Statistics <br/>
		</th>
		<td>
			<p>
				<input	type="checkbox"
						name="stats_submissions_disable"
						value="yes"
				<?php if ($stats_submissions_disable == 'yes') { echo 'checked="checked"'; } ?> /> Disable
			</p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			Save Options
		</th>
		<td>
			<?php wp_nonce_field('ois_general_field', 'save_data'); ?>
			<input type="submit" class="ois_super_button" value="Save Options" />
		</td>
	</tr>
	</table>
	</form>
	
	<?php
	ois_start_option_table('Reset Stats', 'no', '');
	?>
	<tr>
		<th scope="row" style="width:280px;">
			Reset Stats <br/>
		</th>
		<td>
			<p>
				<input type="hidden" name="ois_reset" value="stats" />
				<?php wp_nonce_field('ois_reset_stats', 'reset'); ?>
				<input	type="submit"
				 		class="ois_super_button"
						value="Clear All Stats" />
			</p>
		</td>
	</tr>
	</table>
	</form>
	
	<?php
	ois_start_option_table('Reset Designs', 'no', '');
	?>
	<tr>
		<th scope="row" style="width:280px;">
			Reset Designs <br/>
		</th>
		<td>
			<p>
				<input type="hidden" name="ois_reset" value="designs" />
				<?php wp_nonce_field('ois_reset_designs', 'reset'); ?>
				<input	type="submit"
				 		class="ois_super_button"
						value="Reset to Default Designs" />
			</p>
			<p>Note: This will overwrite any custom changes you have made, or designs you have added.</p>
		</td>
	</tr>
	</table>
	</form>
<?php
	ois_section_end();	
} // ois_general_settings()
?>