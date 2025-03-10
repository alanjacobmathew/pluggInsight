=== PluggInsight - Maintenance Status ===
Contributors: alanjacobmathew
Tags: plugin last updated, plugin maintenance status, last updated check, Maintenance Manager, PluggInsight
Tested up to: 6.7.1
Stable tag: 1.0.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

Easily access maintenance details for each plugin directly on the WordPress plugin page. Effortlessly stay updated on your installed plugins.

== Description ==
The Plugin Maintenance Status plugin enhances your WordPress plugin management experience by providing convenient access to maintenance details for each installed plugin. With this plugin, you can quickly check the maintenance status of your plugins without even leaving the plugin page. Users can easily monitor for plugins that were removed for injecting malware or any other guideline violation.

Further it helps the admins make informed decision before updating the site to the latest WordPress version. 



= Features =
- **Simple & Straightforward**: There are no settings to configure
- **Plugin Maintenance Details**: Get immediate access to maintenance information for each plugin such as *Last Updated* details, *Tested up to* and the *Latest Version* released, right from the default plugin page itself. Quick links to *Support* page, *Changelog* page and *Review* page helps you make informed decision before updating plugins.
- **Time-saving convenience**: No need to navigate away from the plugin page to check maintenance status.
- **Monitor plugins removed from repo**: Any plugins that were removed from WP repo, either by plugin author request or for guideline violation can be easily monitored.
- **Data Sourced from Official Repository**: The plugin uses the WP plugin repository API to source the data. So the data is trustworthy, as Individual plugin authors have tested and reported that their plugins are compatible with the latest WordPress versions.
- **Cache**: The plugin by default stores the data for a day, which means not every time you visit the plugin page a new API request is not being sent. So doesn't affect loading speed on general use once cached.
- **Clear cache functionality**: Give the admin to control and clear maintenance cache data of plugins with just a single click. Admins can utilize this feature from the Maintenance Status Plugin Page.
- **Colored Status Bar**: A visual identity, which has 5 different colours helps you to easily identify which plugins are not updated or tested with the latest release versions. 
- **Decide when to update WP itself**: Before updating to the latest version of WordPress, make sure all the plugins you rely on are compatible and tested with the new version. Helps you to avoid plugin conflict with new WP versions.
- **Translation-ready**: The plugin is fully translatable, allowing you to localize the plugin to your preferred language.
- **No ads and No Upsells**
- **Works on local Test sites** too. (Tested on [localwp](https://localwp.com).)
- **Works even when automatic updater is disabled**.

= Note =
- Works only for those plugins hosted on the Official WordPress Plugin Repository.
- Data is sourced from official WordPress Plugin Repository. The plugin author has not individually verified the accuracy or validity of each plugin data.
- The plugin is designed keeping mind of those plugins that provides minimal functionality, that need not be updated but tested up to the latest version.
- The plugin uses Tested Up to Data to display this status bar. 
- A plugin removed from WP repo, but still maintained from their private repo, will be only shown as plugin removed.
- The plugin checks for the latest major WP release and compare it with the major release mention in the tested up to data. The below color codes show how they are calculated


**Green**: If both has same major release version.

**Orange**: If difference is more than 1 but less that 3. 

**Red**: If the difference is more than 3.

**Blue**: If the plugin is tested upto the upcoming major release.

**Grey**: If the plugin was removed from the repository, either because of plugin author request or for guideline violation.

So if a plugin hasn't been updated for 5 years, but the author has confirmed that it works with the latest version the status bar will be shown as green.
If a plugin was removed from repo by plugin author, but actively maintains from their personal repository, the statur bar will be shown as grey.


== Installation ==
This section describes how to install the plugin and get it working.

- Search for **Plugin Maintenance Status** plugin in the plugin search box.
- Install the plugin through the WordPress plugins screen directly.
- Activate the plugin through the ‘Plugins’ screen in WordPress.

== Frequently Asked Questions ==

= Will it work with any theme? =
Yes it should. As long as the plugins installed can be found in the repository, it will display the data.

= What if the plugin is not available on the repository? =
For plugins installed from sources other than WP repo, will show a "Plugin not available in the repository" text instead of the plugin data.

= Will it include data from other sources? =
Though it is doable, the fact that most commercial plugins are distributed directly from their sites, make it difficult. But if there are other repositories other than the default WP repo, it could be implemented.

= Found a bug? =
Report it on [GitHub](https://github.com/alanjacobmathew/pluggInsight-maintenance-status/issues) 

= Want more Features? =
If you have a better idea to implement this functionality of the plugin or if you would like to add more feature. Do submit a request in the GitHub repository.  

== Screenshots ==
1. Maintenance Status Settings
2. Default Plugin Page
3. PluggInsight Settings Page

== Changelog ==
= 1.0.1 =
* Supports even when automatic updater is disabled.
* Track plugins that were removed for guideline violation.  

= 1.0.0 =
* Initial Release