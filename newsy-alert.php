<?php
/*
Plugin Name: Newsy Alert
Plugin URI: https://github.com/jeromehalligan/newsy-alert-wp-plugin
Description: This is a plugin to display a breaking news alert under the theme's header
Version: 1.0
Author: Jerome Halligan
Author URI: https://github.com/jeromehalligan
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

function newsy_alert_get_web_safe_fonts() {
    return array(
        'Default (Theme Font)' => '', // An option to inherit the theme's font
        'Arial'                => 'Arial, Helvetica, sans-serif',
        'Verdana'              => 'Verdana, Geneva, sans-serif',
        'Tahoma'               => 'Tahoma, Geneva, sans-serif',
        'Trebuchet MS'         => '"Trebuchet MS", Helvetica, sans-serif',
        'Times New Roman'      => '"Times New Roman", Times, serif',
        'Georgia'              => 'Georgia, serif',
        'Garamond'             => 'Garamond, serif',
        'Courier New'          => '"Courier New", Courier, monospace',
        'Lucida Console'       => '"Lucida Console", Monaco, monospace',
    );
}

// MAIN FUNCTION TO DISPLAY ALERT

add_action( 'wp_head', 'newsy_alert' );

function newsy_alert() { 
  $textvar = get_option('newsy_alert_custom_text', 'Click here for the latest news!'); 
  $post_id = get_option('newsy_alert_post_id', '');
  $chosen_font_key = get_option('newsy_alert_font_family', '');

  // CONDITIONAL LOGIC TO CHECK IF USER READING SELECTED POST; IF SO, NO ALERT 
  if ( ! empty( $post_id ) && is_single( $post_id ) ) {
    return; // If it's the specified post, don't display the alert.
  }

  $alert_display_text = '';
  $alert_link = home_url();

  $web_safe_fonts = newsy_alert_get_web_safe_fonts();
  $alert_font_css = isset($web_safe_fonts[$chosen_font_key]) ? $web_safe_fonts[$chosen_font_key] : '';

  // If the chosen font is empty (i.e., "Default (Theme Font)"), we omit the font-family style
  $font_style_attribute = '';
  if ( ! empty( $alert_font_css ) ) {
      $font_style_attribute = 'font-family: ' . $alert_font_css . ';';
  }


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
	<div class="alert" style=" width: 100%;height: auto;background: rgba(216,7,14,1.0);font-weight: bold;color: white;padding: 1em;<?php echo esc_attr($font_style_attribute); ?>font-style: italic;line-height: 1.2em;">
    <a style="color:white" href="<?php echo esc_url($alert_link); ?>">
        Breaking: <?php echo esc_html($alert_display_text); ?> >>
    </a>
  </div>
<?php }
}


// ADMIN MENU CODE

add_action('admin_menu', 'my_admin_menu');

function my_admin_menu () {
  add_options_page('Newsy Alert', 'Newsy Alert', 'manage_options', __FILE__, 'newsy_alert_admin_page');
}

function newsy_alert_admin_page () {

  $textvar = get_option('newsy_alert_custom_text', 'Click here for the latest news!');
  $post_id_var = get_option('newsy_alert_post_id', '');
  $alert_font_family = get_option('newsy_alert_font_family');

  if (isset($_POST['change-clicked'])) {
    update_option( 'newsy_alert_custom_text', sanitize_text_field($_POST['custom_alert_text']) );
    $textvar = get_option('newsy_alert_custom_text', 'Click here for a breaking news update!');

    $new_post_id = isset($_POST['alert_post_id']) ? intval($_POST['alert_post_id']) : '';
    update_option( 'newsy_alert_post_id', $new_post_id );
    $post_id_var = get_option('newsy_alert_post_id', '');

    $new_font = isset($_POST['alert_font_family']) ? sanitize_text_field($_POST['alert_font_family']) : '';
    $allowed_fonts = newsy_alert_get_web_safe_fonts();
    if (!array_key_exists($new_font, $allowed_fonts)) { // If they somehow send a value not in our list
        $new_font = ''; // Default to empty string (theme font)
    }
    update_option( 'newsy_alert_font_family', $new_font );
    $alert_font_family = get_option('newsy_alert_font_family', ''); // Update local variable
  }

?>

<!-- SETTINGS FORM -->
<div class="wrap">
  <h1>Newsy Alert Settings</h1>
  <form action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">

    <h2>Fallback / Custom Alert Message</h2>
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row"><label for="custom_alert_text">Custom Alert Text:</label></th>
          <td>
            <textarea id="custom_alert_text" name="custom_alert_text" rows="5" cols="50" class="large-text code"><?php echo esc_textarea($textvar); ?></textarea><br>
            <p class="description">This text will be displayed if no Post ID is entered above, or if the entered Post ID is invalid. The link will default to `<?php echo esc_url(home_url()); ?>` in this case.</p>
          </td>
        </tr>
        <tr>
        <tr>
          <th scope="row"><label for="alert_post_id">Alert Post ID:</label></th>
          <td>
            <input type="number" id="alert_post_id" name="alert_post_id" value="<?php echo esc_attr($post_id_var); ?>" class="regular-text" placeholder="Enter Post ID (e.g., 123)">
            <p class="description">Enter the ID of a specific post to display its title as the alert. If left empty or an invalid ID is entered, the "Custom Alert Text" will be used. The alert will not show on the specified post's page.</p>
          </td>
        </tr>
          <th scope="row"><label for="alert_font_family">Alert Font Family:</label></th>
          <td>
            <select id="alert_font_family" name="alert_font_family">
              <?php
              $fonts = newsy_alert_get_web_safe_fonts();
              foreach ($fonts as $display_name => $css_value) {
                  echo '<option value="' . esc_attr($display_name) . '"' . selected($alert_font_family, $display_name, false) . '>' . esc_html($display_name) . '</option>';
              }
              ?>
            </select>
            <p class="description">Choose a web-safe font for the alert. "Default (Theme Font)" will attempt to use your website's primary font.</p>
          </td>
        </tr>
      </tbody>
    </table>

    <input name="change-clicked" type="hidden" value="1" />
    <?php wp_nonce_field('newsy_alert_settings_save', 'newsy_alert_nonce'); // Nonce for security ?>
    <?php submit_button('Save Alert Settings'); ?>
  </form>
</div>

<?php }
