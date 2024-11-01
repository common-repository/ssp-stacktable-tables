<?php
/*
Plugin Name: SSP Stacktable Tables
Plugin URI: http://wordpress.org/plugins/ssp-stacktable-tables
Description: Reformat tables for mobile devices using the jQuery Stacktable plugin from John Polacek.
Author: John Polacek & Stephen Sherrard
Version: 1.0.0
Author URI: https://stephensherrardplugins.com
License: GPL2
Text Domain: ssp-stacktable-tables
Domain Path: /languages
*/

function ssp_stacktable_add_scripts() {
	global $post;
	$options = get_option('ssp_stacktable_options');
	$pages = isset($options['ssp_stacktable_page_ids']) ? (array)$options['ssp_stacktable_page_ids'] : array('none');
	if(in_array('all', $pages) || (is_a( $post, 'WP_Post' ) && ( in_array($post->ID, $pages) || has_shortcode( $post->post_content, 'stacktable') ) ) ) {
		wp_enqueue_script( 'stacktable', plugins_url( 'stacktable.js' , __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'ssp-stacktable', plugins_url( 'ssp-stacktable.js' , __FILE__ ), array( 'jquery', 'stacktable' ) );
		if(isset($options['ssp_stacktable_disable_css']) && 1 == $options['ssp_stacktable_disable_css']) {
			return;
        } else {
			wp_enqueue_style( 'stacktable-css', plugins_url( 'stacktable.css', __FILE__ ) );
        }
    }
}
add_action('wp_enqueue_scripts', 'ssp_stacktable_add_scripts');
	
function ssp_stactable_admin_scripts($hook) {
    if ( 'toplevel_page_ssp_stacktable_options' != $hook ) {
        return;
    }
	// WooCommerce registers an older version of select2, so we have to change our handle to use our own version.
	// Until WooCommerce stops pre-registering common scripts (especially outdated versions), we have to give ours a unique name
	wp_enqueue_style('ssp-select2', plugins_url('select2.min.css', __FILE__ ), array(), '4.0.4');
	wp_enqueue_script('ssp-select2', plugins_url('select2.min.js', __FILE__ ), array('jquery'), '4.0.4');
	wp_enqueue_script( 'ssp-stacktable-admin', plugins_url( 'ssp-stacktable-admin.js' , __FILE__ ), array( 'jquery', 'ssp-select2' ) );
}
add_action( 'admin_enqueue_scripts', 'ssp_stactable_admin_scripts' );
	
	
function ssp_stacktable_register_options() {
	add_settings_section('ssp_stacktable_options', __('Settings', 'ssp-stacktable-tables'), 'ssp_stacktable_main_description', 'ssp_stacktable_options');
	add_settings_field('ssp_stacktable_page_ids', __('Enable on pages: ', 'ssp-stacktable-tables'), 'ssp_stacktable_pages_select', 'ssp_stacktable_options', 'ssp_stacktable_options');
	add_settings_field('ssp_stacktable_disable_css', __('Disable CSS?', 'ssp-stacktable-tables'), 'ssp_stacktable_disable_css_checkbox', 'ssp_stacktable_options', 'ssp_stacktable_options');
	register_setting( 'ssp_stacktable_options', 'ssp_stacktable_options', 'ssp_stacktable_validate_options' );
}
add_action('admin_init', 'ssp_stacktable_register_options');

function ssp_stacktable_admin_menu() {
	add_menu_page(__('SSP Stacktable', 'ssp-stacktable-tables'), __('SSP Stacktable', 'ssp-stacktable-tables'), 'manage_options', 'ssp_stacktable_options', 'ssp_stacktable_page');
}
add_action('admin_menu', 'ssp_stacktable_admin_menu');

function ssp_stacktable_validate_options($inputs) {
    foreach ($inputs as $id => $values) {
        if('ssp_stacktable_page_ids' === $id) {
            foreach ($values as $key => $value) {
                if(!in_array($value, array('all', 'none'))) {
                    $value = absint($value);
                    $values[$key] = $value;
                }
            }
            $inputs[$id] = $values;
        } elseif ('ssp_stacktable_disable_css' === $id) {
            if( 1 != $values ) {
                $values = false;
            }
	        $inputs[$id] = $values;
        }
    }
    return $inputs;
}
	
function ssp_stacktable_main_description() {
	echo '<p> ' . __('Settings for SSP Stacktable Tables', 'ssp-stacktable-tables') . '</p>';
}

function ssp_stacktable_pages_select() {
    $options = get_option('ssp_stacktable_options');
    $selected = isset($options['ssp_stacktable_page_ids']) ? (array)$options['ssp_stacktable_page_ids'] : array('none');
    $ids = get_all_page_ids();
    $values = array( 'none' => __('None', 'ssp-stacktable-tables' ), 'all' => __('ALL Pages and Posts', 'ssp-stacktable-tables'));
    foreach ($ids as $id) {
        $values[$id] = get_the_title($id);
    }
    ?>
    <select id="ssp_stacktable_page_ids" name="ssp_stacktable_options[ssp_stacktable_page_ids][]" multiple style="width:60%">
        <?php
        foreach ($values as $key => $title) {
	        echo '<option value="'.stripslashes(esc_attr($key)).'"';
	        if (in_array($key, $selected)) {
		        echo ' selected="selected"';
	        }
	        echo '>'.stripslashes(esc_html($title)).'</option>';
        }
        ?>
    </select>
    <a id="reset-pages" href="#" class="button-secondary"><?php _e('Reset to None', 'ssp-stacktable-tables'); ?></a>
    <br/>
    <em>
        <?php _e('Select one or more pages where you want the script to be enqueued. Stacktable formatting will apply to ALL tables on the selected pages.', 'ssp-stacktable-tables'); ?><br/>
        <?php _e('Select the ALL option to globally load the script on ALL pages of your site. Select the None option to only load the script on pages that have a shortcode (see below).', 'ssp-stacktable-tables'); ?><br/>
        <?php _e('If you use the shortcode on any pages, you do NOT need to select those pages above.', 'ssp-stacktable-tables'); ?><br/>
        <?php _e('Use the above if you want to use the default stacktable view without having to the shortcode on the selected pages.', 'ssp-stacktable-tables'); ?>
    </em>
    <?php
}

function ssp_stacktable_disable_css_checkbox() {
	$options = get_option('ssp_stacktable_options');
	if(isset($options['ssp_stacktable_disable_css']) && true == $options['ssp_stacktable_disable_css']) {
		$checked = 'checked="checked"';
	} else {
		$checked = '';
	}
	?>
    <input id="ssp_stacktable_disable_css" name="ssp_stacktable_options[ssp_stacktable_disable_css]" type="checkbox" value="1" <?php echo $checked; ?> />
    <em><?php _e('Check this to disable the plugin stacktable.css stylesheet. Copy that style sheet CSS to your theme and modify and add to as desired to customize. <br/><strong>NOTE: If you do not copy the necessary CSS to your theme, checking this option will effectively disable the stacktable script since the mobile breakpoint is defined in the CSS and the mobile class will never get applied.</strong>', 'ssp-stacktable-tables'); ?></em>
	<?php
}

function ssp_stacktable_page() {
	if (!current_user_can('manage_options'))  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'ssp-stacktable-tables' ) );
	}
	
	?>
	<div class="wrap ssp_stacktable">
		<div id="icon-themes" class="icon32"></div>
		<h2><?php _e('SSP Stacktable Tables', 'ssp-stacktable-tables'); ?></h2>
		<?php settings_errors(); ?>
		<form action="options.php" method="post">
			<?php
				
				settings_fields('ssp_stacktable_options');
				do_settings_sections('ssp_stacktable_options');
				
				submit_button();
			?>
		</form>
		<?php
			do_action('ssp_stacktable_settings_after_submit_button');
		?>
	</div>
	<?php
    include('shortcodes.php');
}

function ssp_stacktable_activate() {
	$options = get_option('ssp_stacktable_options');
	$defaults = array(
        'ssp_stacktable_page_ids' => array('none'),
        'ssp_stacktable_disable_css' => false,
    );
	$updated = false;
	foreach ($defaults as $key => $value) {
	    if(!isset($options[$key])) {
	        $updated = true;
	        $options[$key] = $value;
        }
    }
    if($updated) {
	    update_option('ssp_stacktable_options', $options);
    }
}
register_activation_hook( __FILE__, 'ssp_stacktable_activate' );

/**
 * New shortcode functionality with version 1.0.0
 */
function ssp_stacktable_init() {
    add_shortcode('stacktable','ssp_process_stacktable_shortcode');
}
add_action('init','ssp_stacktable_init');

function ssp_process_stacktable_shortcode($atts=array()) {
    // normalize attribute keys, lowercase
    $atts = array_change_key_case( (array) $atts, CASE_LOWER );

    // override default attributes with user attributes
    $stacktable_atts = shortcode_atts(
        array(
            'type' => 'stacktable',
            'class' => '',
            'headindex' => 0,
            'showheader' => 'yes'
        ), $atts, 'stacktable'
    );
    $type = strtolower(sanitize_text_field( $stacktable_atts['type']));
    if(!in_array($type, array('stacktable','cardtable','stackcolumns'))) {
        $type = 'stacktable'; // default
    }
    $class = sanitize_text_field( $stacktable_atts['class']);
    $index = absint($stacktable_atts['headindex']);
    $showheader = 'no' === strtolower(sanitize_text_field($stacktable_atts['showheader'])) ? 'no' : 'yes';
    // put parameters in hidden div for access by javascript
    return '<div id="ssp-stacktable-params" style="display:none;" data-type="'.esc_attr($type).'" data-class="'.esc_attr($class).'" data-headindex="'.esc_attr($index).'" data-showheader="'.esc_attr($showheader).'"></div>';
}