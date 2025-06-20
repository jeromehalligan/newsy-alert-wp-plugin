<?php
/*
Plugin Name: Newsy Alert
Plugin URI: http://itsthebulletin.com
Description: This is a plugin to display a breaking news alert under the theme's header
Version: 1.0
Author: Jerome Halligan
Author URI: https://itsthebulletin.com
License: GPLv2
*/

/* Copyright 2019 Jerome Halligan (email : jeromehalligan@gmail.com)
Newsy Alert is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Newsy Alert is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Newsy Alert. If not, see (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).
*/

// MAIN FUNCTION TO DISPLAY ALERT

add_action( 'wp_head', 'newsy_alert' );

function newsy_alert() { 
  $textvar = get_option('test_plugin_variable', ''); 
  $post_id = get_option('newsy_alert_post_id', '');

  $alert_display_text = '';
  $alert_link = home_url();

  if (!empty($post_id)) {
    $post_title = get_the_title($post_id);
    $post_permalink = get_permalink($post_id);

    if ($post_title && $post_permalink) {
      $alert_display_text = $post_title;
      $alert_link = $post_permalink;
    } else {
      $alert_display_text = $textvar;
    }
  } else {
    $alert_display_text = $textvar;
  }

if (!empty($alert_display_text)) {
  ?>
	<div class="alert" style=" width: 100%;height: auto;background: rgba(216,7,14,1.0);font-weight: bold;color: white;padding: 1em;font-family: 'Merriweather', Georgia;font-style: italic;line-height: 1.2em;">
		<a style="color:white" href="<?php echo esc_url($alert_link); ?>"><?php echo esc_html($alert_display_text); ?> >></a>
	</div>
<?php }
}

// ADMIN MENU CODE

add_action('admin_menu', 'my_admin_menu');

function my_admin_menu () {
  add_options_page('Newsy Alert', 'Newsy Alert', 'manage_options', __FILE__, 'newsy_alert_admin_page');
}

function newsy_alert_admin_page () {

  $textvar = get_option('test_plugin_variable', 'hello world');
  $post_id_var = get_option('newsy_alert_post_id', '');

  if (isset($_POST['change-clicked'])) {
    update_option( 'test_plugin_variable', sanitize_text_field($_POST['custom_alert_text']) );
    $textvar = get_option('test_plugin_variable', 'Click here for a breaking news update!');

    $new_post_id = isset($_POST['alert_post_id']) ? intval($_POST['alert_post_id']) : '';
    update_option( 'newsy_alert_post_id', $new_post_id );
    $post_id_var = get_option('newsy_alert_post_id', '');
  }

?>

<!-- SETTINGS FORM -->
<div class="wrap">
  <h1>Newsy Alert Settings</h1>
  <p>This plugin displays a breakig news alert at the top of your site. You can choose a specific post to link to by entering its ID, or set a custom alert message and link.</p>
  <form action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">

    <h2>Alert Content & Link (Preferred Method)</h2>
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row"><label for="alert_post_id">Post ID for Alert Link:</label></th>
          <td>
<input type="number" id="alert_post_id" name="alert_post_id" value="<?php echo esc_attr($post_id_var); ?>" class="regular-text" /><br>
            <p class="description">Enter the ID of the WordPress post you want the alert to link to. The alert text will automatically become that post's title.</p>
            <p class="description"><em>How to find a Post ID:</em> Edit any post in WordPress, and you'll see `post=` followed by numbers in the URL (e.g., `wp-admin/post.php?post=<strong>123</strong>&action=edit`). Those numbers are the Post ID.</p>
          </td>
        </tr>
      </tbody>
    </table>

    <h2>Fallback / Custom Alert Message</h2>
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row"><label for="custom_alert_text">Custom Alert Text:</label></th>
          <td>
            <textarea id="custom_alert_text" name="custom_alert_text" rows="5" cols="50" class="large-text code"><?php echo esc_textarea($textvar); ?></textarea><br>
            <p class="description">This text will be displayed if no Post ID is entered above, or if the entered Post ID is invalid. The link will default to `https://itsthebulletin.com/breaking/` in this case.</p>
          </td>
        </tr>
      </tbody>
    </table>

    <input name="change-clicked" type="hidden" value="1" />
    <?php submit_button('Save Alert Settings'); // WordPress's built-in submit button ?>
  </form>
</div>

<?php }