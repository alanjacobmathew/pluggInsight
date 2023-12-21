<?php
/**
 * Plugin Name: PluggInsight - Maintenance Status
 * Description: Easily access maintenance details for each plugin directly on the WordPress plugin page.
 * Version: 1.0.1
 * Author: Alan Jacob Mathew
 * Author URI: https://profiles.wordpress.org/alanjacobmathew/
 * Tested up to: 6.4.2
 * Text Domain: plugginsight-maintenance-status
 * Domain Path: /languages/
 */

register_activation_hook(__FILE__, 'plugin_maintenance_status_activate_pmswp');
register_deactivation_hook(__FILE__, 'plugin_maintenance_status_deactivate_pmswp');

function plugin_maintenance_status_activate_pmswp() {
    
}

function plugin_maintenance_status_deactivate_pmswp() {
    // Remove the column from the plugins page
    add_filter('manage_plugins_columns', 'remove_column_from_plugins_page_pmswp');

    // Remove the plugin contents
    delete_option('maintenance_status_column_contents');

    // Clear transient cache
    global $major_releases_pmswp;
    foreach ($major_releases_pmswp as $release) {
        delete_transient('plugin_maintenance_status_' . $release);
    }
    remove_submenu_page('plugins.php', 'plugginsight-maintenance-status');
    
    // Remove CSS file
   wp_dequeue_style('plugginsight-maintenance-status');
   wp_deregister_style('plugginsight-maintenance-status');

   
    
    // Remove language files
    $language_folder = plugin_dir_path(__FILE__) . 'languages/';
    if (is_dir($language_folder)) {
        $language_files = scandir($language_folder);
        foreach ($language_files as $file) {
            if (is_file($language_folder . $file)) {
                unlink($language_folder . $file);
            }
        }
        rmdir($language_folder);
    }
    
    
}


// Add a submenu page under the Plugins menu
add_action('admin_menu', 'plugin_maintenance_status_add_submenu_page_pmswp');

function plugin_maintenance_status_add_submenu_page_pmswp() {
    add_plugins_page(
        __('Maintenance Status', 'plugginsight-maintenance-status'),
        __('Maintenance Status', 'plugginsight-maintenance-status'),
        'manage_options',
        'plugginsight-maintenance-status',
        'plugginsight_maintenance_status_render_page_pmswp'
    );
}


// Render the plugin page content
function plugginsight_maintenance_status_render_page_pmswp() {
    // Check if the user has access
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1><span class="dashicons dashicons-plugins-checked"></span> ' . __('Maintenance Status Settings', 'plugginsight-maintenance-status') . '</h1>';
    echo '<p>' . __('Manage Maintenance Status Cache', 'plugginsight-maintenance-status') . '</p>';
    echo '<p><em>' . __('Disclaimer:', 'plugginsight-maintenance-status') . ' ' . __('The plugin and its developer do not independently verify the accuracy or validity of each plugin\'s data. The data displayed is obtained from the WP plugin repository, where individual plugin authors have tested and reported that their plugins are compatible with the latest WordPress versions. All the displayed logic in this plugin is based on that data.', 'plugginsight-maintenance-status') . '</em></p>';
    echo '<p><strong><em>' . __('The below process will depend on the number of plugins you have installed. More plugins installed, more time it will take to reload this cache.', 'plugginsight-maintenance-status') . '</em></strong></p>';

    // Add Clear Cache button
    echo '<form method="post">';
    echo '<input type="hidden" name="plugin_maintenance_status_clear_cache_nonce" value="' . wp_create_nonce('plugin_maintenance_status_clear_cache_nonce') . '">';
    echo '<input type="submit" name="plugin_maintenance_status_clear_cache" class="button" value="' . __('Clear Status Cache', 'plugginsight-maintenance-status') . '">';
    echo '</form>';

    // Handle cache clearing and refreshing
    if (isset($_POST['plugin_maintenance_status_clear_cache'])) {
        if (isset($_POST['plugin_maintenance_status_clear_cache_nonce']) && wp_verify_nonce($_POST['plugin_maintenance_status_clear_cache_nonce'], 'plugin_maintenance_status_clear_cache_nonce')) {

            // Clear transients cache
            global $major_releases_pmswp;
            foreach ($major_releases_pmswp as $release) {
                delete_transient('plugin_maintenance_status_' . $release);
            }

            // Refresh cache by making new API requests
            foreach ($major_releases_pmswp as $release) {
                $plugin_data = plugin_maintenance_status_get_plugin_data_pmswp($release);
                set_transient('plugin_maintenance_status_' . $release, $plugin_data, 86400);
            }

            // Display success message
            echo '<div class="notice notice-success"><p>' . __('Cache cleared and refreshed successfully.', 'plugginsight-maintenance-status') . '</p></div>';
        } else {
            // Display error message for invalid nonce
            echo '<div class="notice notice-error"><p>' . __('Invalid security token. Cache clearing and refreshing failed.', 'plugginsight-maintenance-status') . '</p></div>';
        }
    }

    echo '</div>'; // End wrap container
}





// Add a link to the plugin page in the plugin.php page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'plugin_maintenance_status_add_plugin_page_link_pmswp');

function plugin_maintenance_status_add_plugin_page_link_pmswp($links) {
    $plugin_page_link = admin_url('plugins.php?page=plugginsight-maintenance-status');
    $new_link = '<a href="' . $plugin_page_link . '">' . __('Settings', 'plugginsight-maintenance-status') . '</a>';
    array_push($links, $new_link);
    return $links;
}




add_filter('manage_plugins_columns', 'add_column_to_plugins_page_pmswp');
function add_column_to_plugins_page_pmswp($columns) {
    $new_columns = array();
    $position = 0;
    foreach ($columns as $key => $value) {
        if ($key === 'maintenance_status_column') {
            unset($columns[$key]);
        } else {
            $new_columns[$key] = $value;
            if ($position === 3) {
                $new_columns['maintenance_status_column'] = sprintf(
                    __('Maintenance Status', 'plugginsight-maintenance-status') . ' <a href="%s" class="clear-cache-link" title="%s"><span class="dashicons dashicons-plugins-checked"></span></a>',
                    esc_url(admin_url('admin.php?page=plugginsight-maintenance-status')),
                    __('Update Status Cache', 'plugginsight-maintenance-status')
                );
            }
            $position++;
        }
    }
    return $new_columns;
}



$major_releases_pmswp = array(
    '5.0', '5.1', '5.2', '5.3', '5.4', '5.5', '5.6', '5.7', '5.8', '5.9', '6.0', '6.1', '6.2','6.3','6.4'
);
$upcoming_major_release = '6.5';

function get_latest_major_wp_release_pmswp() {
    global $major_releases_pmswp;
    return end($major_releases_pmswp);
}
function generate_status_bar_pmswp($tested_up_to) {
    global $major_releases_pmswp,$upcoming_major_release;

    $tested_version_parts = explode('.', $tested_up_to);
    $tested_major_release = $tested_version_parts[0] . '.' . $tested_version_parts[1];
	
	if ($tested_up_to === $upcoming_major_release) {
        return '<div class="status-bar skyblue"></div>';
    }

    $latest_major_release = get_latest_major_wp_release_pmswp();
    $latest_major_release_index = array_search($latest_major_release, $major_releases_pmswp);
    $tested_major_release_index = array_search($tested_major_release, $major_releases_pmswp);

    $difference = $latest_major_release_index - $tested_major_release_index;

    $status_bar_color = '';

    if ($difference < 1) {
        $status_bar_color = 'green';
    } elseif ($difference >= 1 && $difference < 3) {
        $status_bar_color = 'orange';
    } else {
        $status_bar_color = 'red';
    }

    return '<div class="status-bar ' . $status_bar_color . '"></div>';
}


add_action('admin_enqueue_scripts', 'plugin_maintenance_status_enqueue_scripts_pmswp');

function plugin_maintenance_status_enqueue_scripts_pmswp() {
    // Enqueue CSS file
    wp_enqueue_style('plugginsight-maintenance-status', plugins_url('plugginsight-maintenance-status.css', __FILE__), array(), filemtime(plugin_dir_path(__FILE__) . 'plugginsight-maintenance-status.css'));

    // Enqueue JS file
    wp_enqueue_script('plugginsight-maintenance-status', plugins_url('plugginsight-maintenance-status.js', __FILE__), array('jquery'), filemtime(plugin_dir_path(__FILE__) . 'plugginsight-maintenance-status.js'));
}


function plugin_maintenance_status_format_last_updated_pmswp($last_updated) {
    $now = time();
    $updated_timestamp = strtotime($last_updated);
    $diff = $now - $updated_timestamp;
    $years = floor($diff / (365 * 24 * 60 * 60));
    $months = floor($diff / (30 * 24 * 60 * 60));
    $days = floor($diff / (24 * 60 * 60));

    if ($years > 0) {
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    } elseif ($months > 0) {
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } elseif ($days > 0) {
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return 'Today';
    }
}
add_action('manage_plugins_custom_column', 'populate_maintenance_status_column_pmswp', 10, 3);

function populate_maintenance_status_column_pmswp($column, $plugin_file, $plugin_data) {
    if ($column === 'maintenance_status_column') {
        $plugin_slug = basename(dirname($plugin_file));
        $plugin_data = plugin_maintenance_status_get_plugin_data_pmswp($plugin_slug); // Updated function name
		
        if ($plugin_data !== null) {
            // Display additional information from the plugin repository API
            echo '<strong>' . __('Latest Version:', 'plugginsight-maintenance-status') . '</strong> ' . (isset($plugin_data->version) ? $plugin_data->version : __('Unknown', 'plugginsight-maintenance-status')) . '<br>';
            echo '<strong>' . __('Last Updated:', 'plugginsight-maintenance-status') . '</strong> ' . plugin_maintenance_status_format_last_updated_pmswp(isset($plugin_data->last_updated) ? $plugin_data->last_updated : '') . '<br>'; // Updated function name
            echo '<strong>' . __('Tested Up to:', 'plugginsight-maintenance-status') . '</strong> ' . (isset($plugin_data->tested) ? $plugin_data->tested : __('Unknown', 'plugginsight-maintenance-status')) . '<br>';
            echo generate_status_bar_pmswp(isset($plugin_data->tested) ? $plugin_data->tested : ''); // Updated function name
			$review_link = 'https://wordpress.org/plugins/' . $plugin_data->slug . '/#reviews';
			$changelog_link = 'https://wordpress.org/plugins/' . $plugin_data->slug . '/#developers';
			echo '<strong><a href="' . $review_link . '"target="_blank">' . __('Reviews', 'plugginsight-maintenance-status') . '</a> | <a href="https://wordpress.org/support/plugin/' . $plugin_slug . '/" target="_blank">' . __('Support', 'plugginsight-maintenance-status') . '</a> | <a href="' . $changelog_link . '"target="_blank">' . __('Changelog', 'plugginsight-maintenance-status') . '</a></strong>';
        } else {
            echo __('Plugin not available in the repository', 'plugginsight-maintenance-status');
        }
    }
}





function plugin_maintenance_status_get_plugin_data_pmswp($plugin_name) {
    $cached_data = get_transient('plugin_maintenance_status_' . $plugin_name);
    if ($cached_data !== false) {
        return $cached_data;
    }

    $plugin_slug = sanitize_title($plugin_name);

    if (empty($plugin_slug)) {
        $plugin_slug = $plugin_name;
    }

    $api_url = esc_url_raw(sprintf('https://api.wordpress.org/plugins/info/1.0/%s.json', $plugin_slug));
    $response = wp_remote_get($api_url);

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $data = json_decode(wp_remote_retrieve_body($response));
        set_transient('plugin_maintenance_status_' . $plugin_name, $data, 86400);
        return $data;
    }

    return null;
}


add_action('plugins_loaded', 'plugginsight_maintenance_status_load_textdomain');


function plugginsight_maintenance_status_load_textdomain() {
    load_plugin_textdomain('plugginsight-maintenance-status', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}



add_action('admin_head', 'customize_column_width_and_alignment_pmswp');
function customize_column_width_and_alignment_pmswp() {
    echo '<style>
        .column-maintenance_status_column { width: 18%; }
        .column-maintenance_status_column strong { display: inline-block; min-width: 70px; }
    </style>';
}
