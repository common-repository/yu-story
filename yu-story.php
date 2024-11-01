<?php
/*
Plugin Name: YU STORY
Description: Yu Story is a WordPress plugin that lets you create and share interactive stories with ease, enhancing your site with engaging visual narratives.
Version: 1.0.0
Author: Yusuf Biberoğlu
Author URI: https://www.yusufbiberoglu.com
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: yu-story
Domain Path: /languages
*/

if ( !defined( 'WPINC' ) ) {
    die;
}

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;

// Register Custom Post Type
function ystory_create_post_type() {
    register_post_type('story',
        array(
            'labels' => array('name' => __('Stories'), 'singular_name' => __('Story')),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor', 'thumbnail'),
            'menu_icon' => 'dashicons-slides',
        )
    );
}
add_action('init', 'ystory_create_post_type');

// Shortcode to display stories
function ystory_shortcode() {
    $args = array(
        'post_type' => 'story',
        'posts_per_page' => 10
    );
    $overlay_output = '<div id="story-overlay" style="display: none;"></div>';
    $stories = new WP_Query($args);


    $thumbnails_output = '<div id="story-thumbnails">';
    $index = 0;
    while ($stories->have_posts()) {
        $stories->the_post();
        $thumbnails_output .= '<div class="story-thumbnail" data-index="' . $index . '">';
        $thumbnails_output .= '<img src="' . esc_url(get_the_post_thumbnail_url()) . '" alt="' . esc_attr(get_the_title()) . '">';
        $thumbnails_output .= '<div class="story-thumbnail-title">' . get_the_title() . '</div>';
        $thumbnails_output .= '</div>';
        $index++;
    }
    $thumbnails_output .= '</div>';
    $container_output = '<div id="story-container" style="display: none;">';
    $container_output .= '<div id="close-story">&#10005;</div>';

    $index = 0;
    $stories->rewind_posts();
    while ($stories->have_posts()) {
        $stories->the_post();
        $story_link = get_post_meta(get_the_ID(), '_story_link', true);

        $container_output .= '<div class="story" data-index="' . $index . '" style="display: none;"';
        if (!empty($story_link)) {
            $container_output .= ' data-story-link="' . esc_url($story_link) . '"';
        }
        $container_output .= '>';
        $container_output .= '<img src="' . get_the_post_thumbnail_url() . '" alt="' . get_the_title() . '">';
        $container_output .= '<div class="story-text">' . wp_kses_post(get_the_content()) . '</div>';

        $container_output .= '</div>'; // story bitişi
        $index++;
    }
    $container_output .= '</div>';



    return $overlay_output . $thumbnails_output . $container_output;

}
add_shortcode('yu_story', 'ystory_shortcode');

// Meta box for story link
function ystory_add_link_meta_box() {
    add_meta_box(
        'ystory_story_link_meta_box',
        'Story Link',
        'ystory_link_meta_box_callback',
        'story'
    );
}

function ystory_link_meta_box_callback($post) {
    wp_nonce_field('save_story_link', 'ystory_story_link_meta_box');

    $story_link = get_post_meta($post->ID, '_story_link', true);

    echo '<label for="story_link">Story Link</label>';
    echo '<input type="text" id="story_link" name="story_link" value="'. esc_attr($story_link) .'" size="25" />';

}

add_action('add_meta_boxes', 'ystory_add_link_meta_box');

// Save the story link meta box data
function ystory_save_link($post_id) {
     if (!isset($_POST['ystory_story_link_meta_box']) || 
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ystory_story_link_meta_box'])), 'ystory_save_link')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['story_link'])) {
        $story_link = sanitize_text_field($_POST['story_link']);
        update_post_meta($post_id, '_story_link', $story_link);
    }
}

add_action('save_post', 'ystory_save_link');

// Enqueue plugin styles and scripts
function ystory_enqueue_scripts() {
    wp_enqueue_style('ystory_style', plugins_url('css/style.css', __FILE__), array(), '1.0');
    wp_enqueue_script('ystory_script', plugins_url('js/story.js', __FILE__), array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'ystory_enqueue_scripts');

// Load plugin textdomain for translations
function ystory_load_textdomain() {
    load_plugin_textdomain('yu-story', false, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'ystory_load_textdomain');
