<?php
/*
Plugin Name: 	HelpBox Information
Plugin URI: 	https://fisherinc.co.uk/
Description: 	Displays a floating helpbox on your site populated with questions from the <a href="edit.php?post_type=helpbox_questions">questions</a> page.
Author: 		Fisher INC
Version: 		1.5
Author URI: 	https://fisherinc.co.uk/
License:		GPL2

Copyright 2015 Josh Fisher

helpbox.php is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

helpbox.php is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with helpbox.php. If not, see http://joshfisher.io.
*/

//ADD CUSTOM ACTION LINKS
function helpbox_action_links($actions, $plugin_file)
{
    static $plugin;

    if (!isset($plugin)) {
        $plugin = plugin_basename(__FILE__);
    }
    if ($plugin == $plugin_file) {
        $settings =  array('settings' => '<a href="edit.php?post_type=helpbox_questions&page=helpbox-settings.php">' . __('Settings', 'General') . '</a>');
        $site_link = array('support' => '<a href="http://paypal.me/mrjoshfisher" target="_blank">Donate</a>');

        $actions = array_merge($site_link, $actions);
        $actions = array_merge($settings, $actions);
    }
    return $actions;
}

add_filter('plugin_action_links', 'helpbox_action_links', 10, 5);

//ENQUEUE STYLES & SCRIPTS
function helpbox_enqueue_styles()
{
    wp_enqueue_style('helpBox-style', plugins_url('/assets/css/help-style.css', __FILE__), array(), time());
    wp_enqueue_script('helpBox-script', plugins_url('/assets/js/help-script.js', __FILE__), array('jquery'), time(), true);
}

add_action('wp_enqueue_scripts', 'helpbox_enqueue_styles');

//ENQUEUE ADMIN STYLES & SCRIPTS
function admin_style()
{
    wp_enqueue_style('helpbox-admin-styles', plugins_url('/assets/css/admin-help-style.css', __FILE__), array(), time());
    wp_enqueue_script('helpBox-script', plugins_url('/assets/js/help-script.js', __FILE__), array('jquery'), time(), true);
}
add_action('admin_enqueue_scripts', 'admin_style');

//REGISTER HELPBOX POST TYPE
function helpbox_post_type()
{
    $labels = array(
        'name'               => 'HB Questions',
        'singular_name'      => 'Question',
        'menu_name'          => 'HB Questions',
        'name_admin_bar'     => 'Question',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Question',
        'new_item'           => 'New Question',
        'edit_item'          => 'Edit Question',
        'view_item'          => 'View Question',
        'all_items'          => 'All Questions',
        'search_items'       => 'Search Questions',
        'parent_item_colon'  => 'Parent Questions:',
        'not_found'          => 'No questions found.',
        'not_found_in_trash' => 'No questions found in Trash.'
    );

    $args = array(
        'menu_icon'         	=> 'dashicons-editor-help',
        'labels'      			=> $labels,
        'description' 			=> 'All the questions are stored in this post type',
        'public' 				=> false,  // it's not public, it shouldn't have it's own permalink, and so on
        'publicly_queriable' 	=> true,   // you should be able to query it
        'show_ui' 				=> true,   // you should be able to edit it in wp-admin
        'exclude_from_search' 	=> true,   // you should exclude it from search results
        'show_in_nav_menus' 	=> false,  // you shouldn't be able to add it to menus
        'has_archive' 			=> false,  // it shouldn't have archive page
        'rewrite' 				=> false,  // it shouldn't have rewrite rules

    );
    register_post_type('helpbox_questions', $args);
}
add_action('init', 'helpbox_post_type');

//HELBOX ADMIN MENU
add_action('admin_menu', 'helpbox_menu');

function helpbox_menu()
{
    add_submenu_page(
        'edit.php?post_type=helpbox_questions',
        'HB Settings',
        'HB Settings',
        'edit_posts',
        basename(__FILE__),
        'helpbox_settingspage'
    );

    add_action('admin_init', 'helpbox_settings');
}

//HELBOX REGISTER SETTINGS
function helpbox_settings()
{
    register_setting('helpbox-sgroup', 'helpboxEnabled');
    register_setting('helpbox-sgroup', 'contactText');
    register_setting('helpbox-sgroup', 'contactLink');
    register_setting('helpbox-sgroup', 'helpboxHeaderimage');
    register_setting('helpbox-sgroup', 'helpboxHeadervideo');
    register_setting('helpbox-sgroup', 'contactShortcode');
    register_setting('helpbox-sgroup', 'helpboxColour');
    register_setting('helpbox-sgroup', 'helpTitlecolour');
}

//HELBOX SETTINGS PAGE
function helpbox_settingspage()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient privileges to access this page.'));
    }

    $helpboxEnabled		= esc_attr(get_option('helpboxEnabled'));
    $helpboxcolour		= esc_attr(get_option('helpboxColour'));
    $helptitlecolour	= esc_attr(get_option('helpTitlecolour'));
    $contactuslinktext	= esc_attr(get_option('contactText'));
    $contactuslink		= esc_attr(get_option('contactLink'));
    $contactusshortcode	= esc_attr(get_option('contactShortcode'));
    $helpheaderimage	= esc_attr(get_option('helpboxHeaderimage'));
    $helpheadervideo	= esc_attr(get_option('helpboxHeadervideo'));

    if (empty($helpboxcolour)) {
        $helpboxcolour = '#000';
    }
    if (empty($helptitlecolour)) {
        $helptitlecolour = '#fff';
    } ?>

	<div class="wrap">
		<?php settings_errors(); ?>
		<div id="helpBoxTitle">
		<h2><span style="font-size: 32px;padding-right: 10px;" class="dashicons dashicons-editor-help"></span> Helpbox Settings</h2>
		<p>Displays a floating helpbox on your site populated with questions from the <a href="edit.php?post_type=helpbox_questions">questions</a> page.</p>
		<p>You must add <b>&#60;?php do_action('helpBox_head_content_tag'); ?&#62;</b> below your <b>&#60;body&#62;</b> tag for the helpbox to show, you will usually find the body tag in your header.php file.</p>
		<p>See helpbox settings below.</p>
		</div>
			<form method="post" action="options.php">
			    <?php settings_fields('helpbox-sgroup'); ?>
			    <?php do_settings_sections('helpbox-sgroup'); ?>

			    <!-- SETTINGS PANEL /-->
			    <div id="settings_panel">
				    <table class="form-table">
				        <tr valign="top">
					       <th scope="row">Enable Helpbox</th>
							<td>
								<label style="padding-right: 10px">
									<input type="checkbox" id="helpboxEnabled" name="helpboxEnabled" value="true" <?php echo ($helpboxEnabled == 'true') ? 'checked' : 'unchecked'; ?>>
								</label>


							</td>
				        </tr>
				        <tr valign="top">
					       <th scope="row">Helpbox Main Colour</th>
							<td>
								<label style="padding-right: 10px">
									<input type="text" id="helpboxColour" name="helpboxColour" value="<?php echo $helpboxcolour; ?>">
								</label>
								<p class="description">Enter hex code of colour e.g #cccccc</p>
							</td>
				        </tr>
				        <tr valign="top">
					       <th scope="row">Helpbox Question Title Colour</th>
							<td>
								<label style="padding-right: 10px">
									<input type="text" id="helpTitlecolour" name="helpTitlecolour" value="<?php echo $helptitlecolour; ?>">
								</label>
								<p class="description">Enter hex code of colour e.g #cccccc</p>
							</td>
				        </tr>
				        <tr valign="top">
					       <th scope="row">Header image url</th>
							<td>
								<label style="padding-right: 10px">
									<input style="width: 100%"  type="text" id="helpboxHeaderimage" name="helpboxHeaderimage" value="<?php echo $helpheaderimage; ?>">
								</label>
								<p class="description">Leave this box blank to <b>not</b> have a header image.</p>
							</td>
				        </tr>
				        <tr valign="top">
					       <th scope="row">Header YouTube video url</th>
							<td>
								<label style="padding-right: 10px">
									<input style="width: 100%" type="text" id="helpboxHeadervideo" name="helpboxHeadervideo" value="<?php echo $helpheadervideo; ?>">
								</label>
								<p class="description">Video url example e.g https://www.youtube.com/embed/6TdtD2Y_Y54 | Leave this box blank to <b>not</b> have a header video.</p>
							</td>
				        </tr>
				        <tr valign="top">
					       <th scope="row">Contact Us Link text</th>
							<td>
								<label style="padding-right: 10px">
									<input type="text" id="contactText" name="contactText" value="<?php echo $contactuslinktext; ?>">
								</label>
							</td>
				        </tr>
				        <tr valign="top">
					       <th scope="row">Contact Us Page</th>
							<td>
								<label style="padding-right: 10px">

									<select id="contactLink" name="contactLink" >
									<option selected="selected" disabled="disabled" value=""><?php echo esc_attr(__('Select page')); ?></option>
									    <?php

                                            $pages = get_pages();
    foreach ($pages as $page) {
        $option = '<option value="' . $page->ID . '" ';
        $option .= ($page->ID == $contactuslink) ? 'selected="selected"' : '';
        $option .= '>';
        $option .= $page->post_title;
        $option .= '</option>';
        echo $option;
    } ?>
									</select>

								</label>
								<p class="description">The contact link will show when the contact page is selected and the contact us text is filled.</p>
							</td>
				        </tr>
				        <tr valign="top">
					       <td><?php submit_button(); ?></td>
				       </tr>
				    </table>
			    </div>
			</form>
			<!-- HELPBOX PREVIEW /-->
			<div id="helpboxpreview">
				<h2>Helpbox Preview</h2>
				<p>Alter the settings and save to see preview</p>
				<p>Add / Edit questions <a href="edit.php?post_type=helpbox_questions">here</a>.</p>
				<?php

                    $args = array(
                        'post_type'=> 'helpbox_questions',
                        'order'    => 'ASC'
                    );
    $the_query = new WP_Query($args); ?>
					<div id="helpMainContainer">
						<input type="checkbox" id="sidebarcheck" />
						<div class="helpFloat">
							<label id="toggle" for="sidebarcheck">
								<div class="helpBtn">?</div>
							</label>
							<div class="helpContainer" style="background-color: <?php echo $helpboxcolour; ?>; border: solid <?php echo $helpboxcolour; ?> 1px;">

								<?php
                        // IF HAVE HEADER IMAGE SHOW IT
                        if ($helpheaderimage) {
                            echo '<img class="helpheaderimage" alt="helpboximage" src="' . $helpheaderimage . '">';
                        }
        // IF HAVE HEADER VIDEO SHOW IT
        if ($helpheadervideo) {
            echo '<iframe class="helpheadervideo" src="' . $helpheadervideo . '" frameborder="0" allowfullscreen></iframe>';
        }

        // IF HAVE QUESTIONS SHOW THEM
        if ($the_query->have_posts()) {
            echo "<ul>";
            while ($the_query->have_posts()) : $the_query->the_post(); ?>
								    <li class="hb_question">
										<h4 style="color: <?php echo $helptitlecolour; ?>;background-color: <?php echo $helpboxcolour; ?>" class="questionTitle"><?php the_title(); ?></h4>
										<p class="answer"><?php echo get_the_content(); ?></p>
									</li>
								<?php
                                endwhile;
            echo "</ul>";
        }
        // IF HAVE CONTACT LINK TEXT SHOW IT
        if ($contactuslinktext && $contactuslink) {
            echo '<h4  style="background-color: ' . $helpboxcolour . '" class="contactMe"><a href="' . get_permalink($contactuslink)  . '">' . $contactuslinktext . '</a></h4>';
        } ?>

							</div>
						</div>
					</div>
			</div>
	</div>

<?php
}

//ADJUST HELPBOX QUESTION ORDER
function helpbox_post_type_admin_order($wp_query)
{
    if (is_admin()) {

    // Get the post type from the query
        $post_type = $wp_query->query['post_type'];

        if ($post_type == 'helpbox_questions') {
            $wp_query->set('orderby', 'title');
            $wp_query->set('order', 'DESC');
        }
    }
}
add_filter('pre_get_posts', 'helpbox_post_type_admin_order');


//HELPBOX HEAD CONTENT
function helpBox_head_content()
{
    $helpboxEnabled		= esc_attr(get_option('helpboxEnabled'));
    $helpboxcolour		= esc_attr(get_option('helpboxColour'));
    $helptitlecolour	= esc_attr(get_option('helpTitlecolour'));
    $contactuslinktext	= esc_attr(get_option('contactText'));
    $contactuslink		= esc_attr(get_option('contactLink'));
    $helpheaderimage	= esc_attr(get_option('helpboxHeaderimage'));
    $helpheadervideo	= esc_attr(get_option('helpboxHeadervideo'));

    if ($helpboxEnabled == 'true') {
        $args = array(
            'post_type'=> 'helpbox_questions',
            'order'    => 'ASC',
            'posts_per_page' => 8,

        );
        $the_query = new WP_Query($args); ?>
		<div id="helpMainContainer">
			<input type="checkbox" id="sidebarcheck" />
			<div class="helpFloat">
				<label id="toggle" for="sidebarcheck" >
					<div class="helpBtn" style="background-color: <?php echo $helpboxcolour; ?>; border: solid <?php echo $helpboxcolour; ?> 1px;">?</div>
				</label>
				<div class="helpContainer" style="background-color: <?php echo $helpboxcolour; ?>; border: solid <?php echo $helpboxcolour; ?> 1px;">
					<?php


                        // IF HAVE HEADER IMAGE SHOW IT
                        if ($helpheaderimage) {
                            echo '<img class="helpheaderimage" alt="helpboximage" src="' . $helpheaderimage . '">';
                        }
        // IF HAVE HEADER VIDEO SHOW IT
        if ($helpheadervideo) {
            echo '<iframe class="helpheadervideo" src="' . $helpheadervideo . '" frameborder="0" allowfullscreen></iframe>';
        }

        // IF HAVE QUESTIONS SHOW THEM
        if ($the_query->have_posts()) {
            echo "<ul>";
            while ($the_query->have_posts()) : $the_query->the_post(); ?>
								    <li class="hb_question">
										<h4 style="color: <?php echo $helptitlecolour; ?>;background-color: <?php echo $helpboxcolour; ?>" class="questionTitle"><?php the_title(); ?></h4>
										<p class="answer"><?php echo get_the_content(); ?></p>
									</li>
								<?php
                                endwhile;
            echo "</ul>";
        }
        // IF HAVE CONTACT LINK TEXT SHOW IT
        if ($contactuslinktext && $contactuslink) {
            echo '<h4  style="background-color: ' . $helpboxcolour . '" class="contactMe"><a href="' . get_permalink($contactuslink)  . '">' . $contactuslinktext . '</a></h4>';
        } ?>

				</div>
			</div>
		</div>

    <?php
    }
}

add_action('helpBox_head_content_tag', 'helpBox_head_content');
