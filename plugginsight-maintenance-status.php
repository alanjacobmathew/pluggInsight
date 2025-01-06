<?php
/**
 * Plugin Name: PluggInsight - Maintenance Status
 * Description: Easily access maintenance details for each plugin directly on the WordPress plugin page.
 * Version: 1.0.1
 * Author: Alan Jacob Mathew
 * Author URI: https://profiles.wordpress.org/alanjacobmathew/
 * Tested up to: 6.7.1
 * Text Domain: plugginsight-maintenance-status
 * Domain Path: /languages/
 * License: GPLv3 or later
 */

if ( ! defined( 'ABSPATH' ) ) exit;
register_activation_hook(__FILE__, 'plugginsight_maintenance_status_activate_pmswp');
register_deactivation_hook(__FILE__, 'plugginsight_maintenance_status_deactivate_pmswp');

function plugginsight_maintenance_status_activate_pmswp() {
   
    add_filter('manage_plugins_columns', 'plugginsight_add_column_to_plugins_page_pmswp');
}


function plugginsight_maintenance_status_deactivate_pmswp() {
    /* Remove the column from the plugins page */
    remove_filter('manage_plugins_columns', 'plugginsight_add_column_to_plugins_page_pmswp');


    /* Clear transient cache. */
    global $plugginsight_major_releases;
    foreach ($plugginsight_major_releases as $release) {
        delete_transient('plugginsight_maintenance_status_' . $release);
    }

     remove_submenu_page('plugins.php', 'plugginsight-maintenance-status');
    
    /* Remove CSS file */
    wp_dequeue_style('plugginsight-maintenance-status');
    wp_deregister_style('plugginsight-maintenance-status');
	
	/* Remove JS */
	wp_dequeue_script('plugginsight-maintenance-status');
	wp_deregister_script('plugginsight-maintenance-status');

}

/* Add a submenu page under the Plugins menu */
add_action('admin_menu', 'plugginsight_maintenance_status_add_submenu_page_pmswp');

function plugginsight_maintenance_status_add_submenu_page_pmswp() {
    add_plugins_page(
        __('Maintenance Status', 'plugginsight-maintenance-status'),
        __('Maintenance Status', 'plugginsight-maintenance-status'),
        'manage_options',
        'plugginsight-maintenance-status',
        'plugginsight_maintenance_status_render_page_pmswp'
    );
}


/* Render the plugin page content */
function plugginsight_maintenance_status_render_page_pmswp() {
    /* Check if the user has access */
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1><span class="dashicons dashicons-plugins-checked"></span> ' . esc_html( __('Maintenance Status Settings', 'plugginsight-maintenance-status') ) . '</h1>';
    echo '<p>' . esc_html( __('Manage Maintenance Status Cache', 'plugginsight-maintenance-status') ) . '</p>';
    echo '<p><em>' . esc_html( __('Disclaimer:', 'plugginsight-maintenance-status') ). ' ' . esc_html( __('The plugin and its developer do not independently verify the accuracy or validity of each plugin\'s data. The data displayed is obtained from the WP plugin repository, where individual plugin authors have tested and reported that their plugins are compatible with the latest WordPress versions. All the displayed logic in this plugin is based on that data.', 'plugginsight-maintenance-status') ). '</em></p>';
    echo '<p><strong><em>' . esc_html( __('The below process will depend on the number of plugins you have installed. More plugins installed, more time it will take to reload this cache.', 'plugginsight-maintenance-status') ). '</em></strong></p>';

    /* Add Clear Cache button */
    echo '<form method="post">';
	echo '<input type="hidden" name="plugginsight_maintenance_status_clear_cache_nonce" value="' . esc_attr(wp_create_nonce('plugginsight_maintenance_status_clear_cache_nonce')) . '">';

    echo '<input type="submit" name="plugginsight_maintenance_status_clear_cache" class="button" value="' . esc_html( __('Clear Status Cache', 'plugginsight-maintenance-status') ). '">';
    echo '</form>';

	
    /* Handle cache clearing and refreshing */
if (isset($_POST['plugginsight_maintenance_status_clear_cache'])) {
    /* Unsure if the nonce is unslashed and sanitized */
    $nonce = isset($_POST['plugginsight_maintenance_status_clear_cache_nonce']) 
        ? sanitize_text_field(wp_unslash($_POST['plugginsight_maintenance_status_clear_cache_nonce'])) 
        : '';

    if ($nonce && wp_verify_nonce($nonce, 'plugginsight_maintenance_status_clear_cache_nonce')) {

    /* Clear transients cache */
    global $plugginsight_major_releases; 
    foreach ($plugginsight_major_releases as $release) {
        delete_transient('plugginsight_maintenance_status_' . $release);
    }

    /* Refresh cache by making new API requests */
    foreach ($plugginsight_major_releases as $release) {
        $plugginsight_plugin_data = plugginsight_maintenance_status_get_plugin_data_pmswp($release); 
        set_transient('plugginsight_maintenance_status_' . $release, $plugginsight_plugin_data, 86400);
    }

    /* Display success message */
    echo '<div class="notice notice-success"><p>' . esc_html(__('Cache cleared and refreshed successfully.', 'plugginsight-maintenance-status')) . '</p></div>';
} else {
    /* Display error message for invalid nonce */
    echo '<div class="notice notice-error"><p>' . esc_html(__('Invalid security token. Cache clearing and refreshing failed.', 'plugginsight-maintenance-status')) . '</p></div>';
}

}

echo '</div>'; // End wrap container

}





/* Add a link to the plugin page in the plugin.php page */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'plugginsight_maintenance_status_add_plugin_page_link_pmswp');

function plugginsight_maintenance_status_add_plugin_page_link_pmswp($links) {
    $plugin_page_link = admin_url('plugins.php?page=plugginsight-maintenance-status');
    $new_link = '<a href="' . $plugin_page_link . '">' . esc_html( __('Settings', 'plugginsight-maintenance-status') ). '</a>';
    array_push($links, $new_link);
    return $links;
}




add_filter('manage_plugins_columns', 'plugginsight_add_column_to_plugins_page_pmswp');
function plugginsight_add_column_to_plugins_page_pmswp($columns) {
    
    $columns['maintenance_status_column'] = sprintf(
        esc_html(__('Maintenance Status', 'plugginsight-maintenance-status')) . ' <a href="%s" class="plugginsight_clear-cache-link" title="%s"><span class="dashicons dashicons-plugins-checked"></span></a>',
        esc_url(admin_url('admin.php?page=plugginsight-maintenance-status')),
        esc_html(__('Update Status Cache', 'plugginsight-maintenance-status'))
    );

    return $columns;
}



$plugginsight_major_releases = array(
    '5.0', '5.1', '5.2', '5.3', '5.4', '5.5', '5.6', '5.7', '5.8', '5.9', '6.0', '6.1', '6.2' , '6.3','6.4','6.5','6.6','6.7'
);
$plugginsight_upcoming_major_release = '6.8';

function plugginsight_get_latest_major_wp_release_pmswp() {
    global $plugginsight_major_releases;
    return end($plugginsight_major_releases);
}

function plugginsight_generate_status_bar_pmswp($tested_up_to) {
    global $plugginsight_major_releases, $plugginsight_upcoming_major_release;

    $plugginsight_tested_version_parts = explode('.', $tested_up_to);
    $plugginsight_tested_major_release = $plugginsight_tested_version_parts[0] . '.' . $plugginsight_tested_version_parts[1];
    
    if ($tested_up_to === $plugginsight_upcoming_major_release) {
        return '<div class="plugginsight_status-bar skyblue"></div>';
    }

    $plugginsight_latest_major_release = plugginsight_get_latest_major_wp_release_pmswp();
    $plugginsight_latest_major_release_index = array_search($plugginsight_latest_major_release, $plugginsight_major_releases);
    $plugginsight_tested_major_release_index = array_search($plugginsight_tested_major_release, $plugginsight_major_releases);

    $plugginsight_difference = $plugginsight_latest_major_release_index - $plugginsight_tested_major_release_index;

    $plugginsight_status_bar_color = '';

    if ($plugginsight_difference < 1) {
        $plugginsight_status_bar_color = 'green';
    } elseif ($plugginsight_difference >= 1 && $plugginsight_difference < 3) {
        $plugginsight_status_bar_color = 'orange';
    } else {
        $plugginsight_status_bar_color = 'red';
    }

    return '<div class="plugginsight_status-bar ' . esc_html($plugginsight_status_bar_color) . '"></div>';
}



add_action('admin_enqueue_scripts', 'plugginsight_maintenance_status_enqueue_scripts_pmswp');
function plugginsight_maintenance_status_enqueue_scripts_pmswp() {
    /* Register CSS file */
    wp_register_style(
        'plugginsight-maintenance-status', 
        plugins_url('plugginsight-maintenance-status.css', __FILE__), 
        array(), 
        filemtime(plugin_dir_path(__FILE__) . 'plugginsight-maintenance-status.css')
    );
    wp_enqueue_style('plugginsight-maintenance-status');

    /* Register JS file */
    wp_register_script(
        'plugginsight-maintenance-status', 
        plugins_url('plugginsight-maintenance-status.js', __FILE__), 
        array('jquery'), 
        filemtime(plugin_dir_path(__FILE__) . 'plugginsight-maintenance-status.js'), 
        true // Load in footer
    );
    wp_enqueue_script('plugginsight-maintenance-status');
}




function plugginsight_maintenance_status_format_last_updated_pmswp($last_updated) {
    $now = time();
    $lastupdated = sanitize_text_field($last_updated); // Sanitize the input
	$updated_timestamp = strtotime($lastupdated); // Convert to timestamp
	if ($updated_timestamp === false) {
    return esc_html('Data Unavailable');
	}
    $diff = $now - $updated_timestamp;
    $years = floor($diff / (365 * 24 * 60 * 60));
    $months = floor($diff / (30 * 24 * 60 * 60));
    $days = floor($diff / (24 * 60 * 60));

    if ($years > 0) {
        return esc_html($years . ' year' . ($years > 1 ? 's' :  '') . ' ago');
    } elseif ($months > 0) {
    
        return esc_html( $months . ' month' . ($months > 1 ? 's' : '') . ' ago');
    } else if ($days > 0) {
        return esc_html($days . ' day' . ($days > 1 ? 's' : '') . ' ago');
    } else {
        return esc_html('Today');
    }
}
add_action('manage_plugins_custom_column', 'plugginsight_populate_maintenance_status_column_pmswp', 10, 3);

function plugginsight_populate_maintenance_status_column_pmswp($column, $plugin_file, $plugin_data) {
    if ($column === 'maintenance_status_column') {
        $plugin_slug = basename(dirname($plugin_file));
        $plugin_data = plugginsight_maintenance_status_get_plugin_data_pmswp($plugin_slug); 
    
        if ($plugin_data !== null) {
            if (isset($plugin_data->removed) && $plugin_data->removed === true) {
				// When plugin is removed from repo, either because of author request, or for guideline violation.
				echo '<strong>' . esc_html(__('Plugin Removed from WP', 'plugginsight-maintenance-status')) . '</strong><br>';
				echo '<strong>' . esc_html(__('Date:', 'plugginsight-maintenance-status')) . '</strong> ' . esc_html($plugin_data->closed_date) . '<br>';
				echo '<strong>' . esc_html(__('Reason:', 'plugginsight-maintenance-status')) . '</strong> ' . esc_html($plugin_data->reason_text) . '<br>';
				$status_color = 'plugginsight_status-bar removed';
				echo '<div class="' . esc_attr($status_color) . '"></div>';
			} else {
                // Display available plugin information
                echo '<strong>' . esc_html(__('Latest Version:', 'plugginsight-maintenance-status')) . '</strong> ' .esc_html($plugin_data->version) . '<br>';
				echo '<strong>' . esc_html(__('Last Updated:', 'plugginsight-maintenance-status')) . '</strong> ' .esc_html(plugginsight_maintenance_status_format_last_updated_pmswp($plugin_data->last_updated)) . '<br>';
				echo '<strong>' . esc_html(__('Tested Up to:', 'plugginsight-maintenance-status')) . '</strong> ' .esc_html($plugin_data->tested) . '<br>';
				echo wp_kses_post(plugginsight_generate_status_bar_pmswp($plugin_data->tested));
			}
        } else {
            echo esc_html(__('Plugin not available in the repository', 'plugginsight-maintenance-status'));
        }
    }
}





function plugginsight_maintenance_status_get_plugin_data_pmswp($plugin_name) {
    $cached_data = get_transient('plugginsight_maintenance_status_' . $plugin_name);
    if ($cached_data !== false) {
        return $cached_data;
    }

    $plugin_slug = sanitize_title($plugin_name);

    if (empty($plugin_slug)) {
        $plugin_slug = $plugin_name;
    }

    $api_url = esc_url_raw(sprintf('https://api.wordpress.org/plugins/info/1.0/%s.json', $plugin_slug));
    $response = wp_remote_get($api_url);

    if (!is_wp_error($response)) {
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code === 200) {
        // When Plugin found, return data
        $data = json_decode(wp_remote_retrieve_body($response));
		// New object to store only the necessary data, saves cache storage.
        $required_data = new stdClass();
		// Extract only the required fields and provide default values if missing
        $required_data->name = isset($data->name) ? $data->name : __('No name specified', 'plugginsight-maintenance-status');
        $required_data->slug = isset($data->slug) ? $data->slug : __('No slug specified', 'plugginsight-maintenance-status');
        $required_data->version = isset($data->version) ? $data->version : __('No version specified', 'plugginsight-maintenance-status');
        $required_data->last_updated = isset($data->last_updated) ? $data->last_updated : __('No last updated date specified', 'plugginsight-maintenance-status');
        $required_data->tested = isset($data->tested) ? $data->tested : __('No tested version specified', 'plugginsight-maintenance-status');
		// Set transient for caching the required data
        set_transient('plugginsight_maintenance_status_' . $plugin_name, $required_data, 86400);
        return $required_data;
        } elseif ($response_code === 404) {
            // If Plugin removed or closed. Could be by author request or for guideline violation.
            $data = json_decode(wp_remote_retrieve_body($response));
            if (isset($data->error) && $data->error === 'closed') {
                
                $data->removed = true;
                $data->reason_text = isset($data->reason_text) ? $data->reason_text : __('No reason specified', 'plugginsight-maintenance-status');
                return $data;
            }
        }
    }

    return null;
}


add_action('plugins_loaded', 'plugginsight_maintenance_status_load_textdomain');


function plugginsight_maintenance_status_load_textdomain() {
    load_plugin_textdomain('plugginsight-maintenance-status', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}



/* Uninstall hook to delete language files, only when the plugin is deleted */
register_uninstall_hook(__FILE__, 'plugginsight_maintenance_status_uninstall_pmswp');

function plugginsight_maintenance_status_uninstall_pmswp() {
    global $plugginsight_wp_filesystem;

    if (empty($plugginsight_wp_filesystem)) {
        WP_Filesystem();
    }

    /*Check if filesystem was initialized properly */
    if (!is_object($plugginsight_wp_filesystem)) {
        return;
    }

    $plugginsight_language_folder = plugin_dir_path(__FILE__) . 'languages/';
    if ($plugginsight_wp_filesystem->is_dir($plugginsight_language_folder)) {
        $plugginsight_language_files = $plugginsight_wp_filesystem->dirlist($plugginsight_language_folder);
        foreach ($plugginsight_language_files as $file) {
            if ($file['type'] !== 'dir') {
                wp_delete_file($plugginsight_language_folder . $file['name']);
            }
        }
        /* Remove the folder */
        $plugginsight_wp_filesystem->rmdir($plugginsight_language_folder);
    }
}

