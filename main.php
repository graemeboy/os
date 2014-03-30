<?php
/*
Plugin Name: OptinSkin 3
Plugin URI: http://www.optinskin.com/
Description: OptinSkin allows you to quickly add beautiful opt-in forms and social share buttons anywhere on your blog.
Get more subscribers with unique customisation and split-testing functionality.
Version: 3
Author: ViperChill
Author URI: http://www.viperchill.com
*/


// DEFINE PATH SHORTCUTS
define( 'OIS_PATH', plugin_dir_path(__FILE__) );
define( 'OIS_URL', WP_PLUGIN_URL . "/OptinSkin/" );
define( 'OIS_EXT_URL', 'http://localhost:8888/designs/' );

// ADD PARAGRAPH FIX
add_filter('the_content', 'ois_empty_paragraph_fix', 101);

// ADD WIDGET
add_action( 'widgets_init', 'ois_load_widgets' );

// AJAX FUNCTIONS
add_action( 'wp_ajax_nopriv_ois_ajax', 'ois_submission_ajax' );
add_action( 'wp_ajax_ois_ajax', 'ois_submission_ajax' );

// SHORTCODE
add_shortcode('ois', 'ois_shortcode_skin');

// ACTIVATION HOOK AND ACTIVATION FUNCTION
register_activation_hook( __FILE__, 'ois_activation' );

// CHECK IF ADMIN OR FRONT END
if (is_admin())  {
    require OIS_PATH . 'admin/admin_main.php';
} // if
else {
    // Include the main front-end file.
    include_once OIS_PATH . 'front/front_main.php';
} // else

/**
 * ois_empty_paragraph_fix function.
 * 
 * 	Wordpress sometimes has a bug where it inserts empty paragraph
 *	tags into the content.
 *
 *	ois_empty_paragrpah_fix takes in the content of the post as a parameter,
 *	and returns the content without any (<p></p>) empty paragraph tags.

 *	Preconditions: The preg_replace function must exist, and ois_empty_paragraph_fix must
 *	be called at the latest time possible to replace all <p></p> tags.
 *	Postconditions: $content is returned without any empty paragraph tags.
 *
 * @access public
 * @param mixed $content
 * @return void
 */
function ois_empty_paragraph_fix($content)
{
    $content = force_balance_tags($content);
    return preg_replace('#<p>\s*+(<br\s*/*>)?\s*</p>#i', '', $content);
} // ois_empty_paragraph_fix (String content)


/*
    Pre: $_POST['skin_id'] must exist, and this is an ID that is contained
        in the list of all skins, found in the option, 'ois_skins'. $_POST['post_id'] should
        also be given.
    Post: A row is added to the optinskin database table, which contains
    the skin's ID, the post ID, and a 1 to show that it is a submission, not impression.

*/
function ois_submission_ajax()
{
    
    try {
        // We already checked if stats are disabled on the front-end

        // Get the Skin ID.
        if (!empty($_POST['skinId'])) 
        { // get the skin ID.
            $skin_id = $_POST['skinId'];
        } // if
        else 
        {
            $skin_id = '';
        } // else

        // Get the skin's settings.
        $all_skins = get_option('ois_skins');
        if (isset($all_skins[$skin_id]))
        {
            $this_skin = $all_skins[$skin_id];
        } // if
        else
        {
            die("No skin found");
        } // else

        // Find out if a redirect URL is set, and if so, what it is.
        if (isset($this_skin['redirect_url']) && trim($this_skin['redirect_url']) != '')
        {
            $redirect_url = $this_skin['redirect_url'];
        } // if
        else 
        {
            $redirect_url = '';
        } // else
        if (isset($_POST['postId']))
        {
            $post_id = $_POST['postId'];
        } // if
        else
        {
            $post_id = 'unknown';
        } // else

        // Since we are saving stats, set relevant data.
        $table_created = get_option('ois_table_created');
        if ($table_created == 'yes')
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'optinskin';
            $row = $wpdb->insert(
                $table_name, array(
                    'skin' => $skin_id,
                    'post' => $post_id,
                    'submission' => 1
                ) // table_name
            ); // insert
        } // if
        echo $redirect_url;
        die(0);
    } // try
    catch (Exception $ex) {
        die(0);
    } // catch
} // ois_submission_ajax

/*
    Provides a shortcode for the user, in the form of: [ois skin="1" split="2,3,4"]
    Pre: The attributes skin and split must contain IDs of existing skins.
    Post: The skin specified by the skin attribute is output, if nothing is passed to split,
    otherwise, a random skin from the union of skin and split values is output.
*/
function ois_shortcode_skin($attr) {
    $to_return = '';
    $skin_id = $attr['skin'];

    // Check for split testing
    if (isset($attr['split']))
    {
        $split_ids = $attr['split'];
        $split_ids = explode(',', $split_ids);
        array_push($split_ids, $skin_id);
        // Choose a random skin from this list.
        $skin_id = trim($split_ids[array_rand($split_ids)]);
    } // if

    $to_return .= ois_make_skin($skin_id);
    return $to_return;
} // ois_shortcode_skin

/*
    Installs the database. Only to be called on registration.
*/
function ois_activation() {
    ois_install_database();
    update_option('ois_table_created', 'yes');
}

/*
    Pre: Plugin must have permission to create a table.
    Post: A database table is installed, which contains the columns:
        skin (integers of skin IDs),
        ts (auto timestamp),
        post (integers of post IDs),
        submission (1 or 0).
*/
function ois_install_database() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'optinskin';

    // create the table.
    // this table is specifically for storing impressions and submissions data.

    $sql = "CREATE TABLE $table_name (
        skin int(4),
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        post int(4),
        submission int(2));";
    //echo $sql;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

// WIDGETS - LOAD AND WIDGET CLASS

/**
 * ois_load_widgets function.
 * 
 * @access public
 * @return void
 */
function ois_load_widgets() {
    register_widget( 'OptinSkin_Widget' );
} // ois_load_widgets()


/**
 * OptinSkin_Widget class.
 * 
 * @extends WP_Widget
 */
class OptinSkin_Widget extends WP_Widget 
{
	
	
    /**
     * OptinSkin_Widget function.
     * 
     * @access public
     * @return void
     */
    function OptinSkin_Widget() 
    {
        $homeurl = get_option('home');
        $widget_ops = array( 'classname' => 'OptinSkin', 'description' => __('Load your skins in your sidebar!', 'OptinSkin') );
        $control_ops = array( 'id_base' => 'optinskin-widget' );
        $this->WP_Widget( 'optinskin-widget', __('OptinSkin', 'OptinSkin'), $widget_ops, $control_ops );
    } // OptinSkin_Widget ()
    
    
    /**
     * widget function.
     * 
     * Creates the skin and displays it in the widget area.
     *
     * @access public
     * @param mixed $args
     * @param mixed $instance
     * @return void
     */
    function widget( $args, $instance ) 
    {
        extract( $args );
        //$title = apply_filters('widget_title', $instance['title'] );
        echo $before_widget;
        if ( $title ) {
            echo $before_title . $title . $after_title;
        }
        
        // Primary skin
        $skin_id = $instance['skin'];
        
        // Split-testing
        $split_testing = $instance['split-test'];
        if ($split_testing == 'yes') {
            $skin_b_id = $instance['skin-b'];
            
            // Alternate randomly.
            if (rand(0,1))
            {
                $skin_id = $skin_b_id;
            } // if
        }// if
        
        echo ois_make_skin($skin_id);

        echo '<div style="clear:both;"></div>';
        echo $after_widget;
    } // widget()
    
    
    /**
     * update function.
     *
     * Updates the settings of the OptinSkin widget once form is subitted. 
     *
     * @access public
     * @param mixed $new_instance
     * @param mixed $old_instance
     * @return void
     */
    function update( $new_instance, $old_instance ) 
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['skin'] = strip_tags( $new_instance['skin'] );
        $instance['split-test'] = strip_tags( $new_instance['split-test'] );
        $instance['skin-b'] = strip_tags( $new_instance['skin-b'] );
        return $instance;
    } // update()

	
    /**
     * form function.
     *
     * Form for controlling the options of the OptinSkin widget. 
     *
     * @access public
     * @param mixed $instance
     * @return void
     */
    function form( $instance ) 
    {
        $defaults = array( 'title' => __('OptinSkin', 'OptinSkin'));
        $instance = wp_parse_args( (array) $instance, $defaults );
        $instance_key = uniqid();
?>

    <style type="text/css">
        .ois_admin_widget_title {
                font-size:15px;
                padding: 0 0 7px 0px;
            }
            .ois_admin_widget {
                font-family: 'Asap';
                max-width: 250px;
            }
            .ois_widget_selection {
                min-width: 200px;
            }
            .ois_admin_widget p {
                max-width: 250px;
            }
    </style>
    <div class="ois_admin_widget">
        <h3 style="padding-top: 0;margin-top:10px;">Basic Settings</h3>

        <p></p>

        <div class="ois_admin_widget_title">
            Skin to Display:
        </div><select class="ois_widget_selection" name="<?php echo $this->get_field_name( 'skin' ); ?>">
            <?php
                    $skins = get_option('ois_skins');
                    foreach ($skins as $id=>$skin) {
                        echo '<option value="' . $id . '"';
                        if (isset($instance['skin']) && $instance['skin'] == $id) {
                            echo ' selected="selected" ';
                        } // if
                        echo '>' . $skin['title'] . '</option>';
                    } // foreach
            ?>
        </select>
        <p>

        <p></p>
        <hr>
        <p>

        <h3>Split-Testing <span style="font-weight:normal;">(Optional)</span></h3>

        <p><input class="ois_widget_split" id="<?php echo $instance_key; ?>_split" type="checkbox" name="<?php echo $this->get_field_name( 'split-test' ); ?>" <?php if ($instance['split-test'] == 'yes') {
                    echo ' checked="checked" ';
                }?> value="yes"> <span style="font-size:13px;">I want to split-test this widget</span></p>

        <div id="<?php echo $instance_key; ?>_selection" style="padding: 2px 0 8px 0;">
            <div class="ois_admin_widget_title">
                Alternate Skin:
            </div><select class="ois_widget_selection" name="<?php echo $this->get_field_name( 'skin-b' ); ?>">
                <?php
                        foreach ($skins as $id=>$skin) {
                            echo '<option value="' . $id . '"';
                            if (isset($instance['skin-b']) && $instance['skin-b'] == $id) {
                                echo ' selected="selected" ';
                            } // if
                            echo '>' . $skin['title'] . '</option>';
                        }
                ?>
            </select>
        </div>

        <p style="border: 1px solid #e0e0e0; padding: 7px;&lt;?php
        ?&gt;" id="<?php echo $instance_key; ?>_info">If split-testing is enabled, the widget will either show the first or second skin, based on a random algorithm.</p>
    </div><?php
        } // form (instance)
    } // OptinSkin_Widget()


    /**
     * ois_add_impression function.
     * Adds an impression statistic to the OptinSkin statistics database.
     * 
     * @access public
     * @param mixed $skin_id
     * @return void
     */
    function ois_add_impression($skin_id)
    {
        global $wp_query;
        $post_id = $wp_query->post->ID;
        $table_created = get_option('ois_table_created');
        if ($table_created == 'yes')
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'optinskin';
            $row = $wpdb->insert(
                $table_name, array(
                    'skin' => $skin_id,
                    'post' => $post_id,
                    'submission' => 0
                ) // table_name
            ); // insert
        } // if
    } // ois_add_impression ()
    
    /**
     * ois_make_skin function.
     * 
     * Returns the content for a given skin.
     *
     * Preconditions: $skin_id must be an existing skin; the path to "...skins/$skin_id" must exist.
     *
     * @access public
     * @param mixed $skin_id
     * @return void
     */
    function ois_make_skin($skin_id)
    {
    	// Do we need to add an impression to the statistics database?
    	$stats_impressions_disable = get_option('stats_impressions_disable');
    	if ($stats_impressions_disable != 'yes') 
    	{
        	// Add as an impression
        	ois_add_impression($skin_id);
        } // if
        // The two CSS files required, and the one JS file required, are already enqueued.
        $skin_path = OIS_PATH . "skins/$skin_id";
        $skin_path = OIS_PATH . "/Skins/$skin_id";
        $html_file = "$skin_path/static.html";
        if (file_exists($html_file))
        {
            return file_get_contents($html_file);
        } // if
        else
        {
            return "<!-- OptinSkin file was not found in the directory. -->";
        } // else
    } // ois_make_skin ()
    
    // EOF
?>