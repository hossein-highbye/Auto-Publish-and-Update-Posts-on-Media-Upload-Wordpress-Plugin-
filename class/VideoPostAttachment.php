<?php

namespace class;

class VideoPostAttachment
{

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // require plugin file
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        add_action( 'init', array( $this, 'register_video_post_type' ) );
        add_action( 'after_switch_theme', array( $this, 'video_post_rewrite_flush' ) );
        add_action( 'edit_attachment', array( $this, 'update_video_post_on_attachment_update' ), 10, 2 );
        add_action( 'add_attachment', array( $this, 'create_video_post_from_attachment' ), 10, 1 );
        add_action( 'plugins_loaded', array( $this, 'video_posts_load_textdomain') );
    }

    /**
     * Register the custom video post type.
     */
    public function register_video_post_type() {
        $labels = array(
            'name' => _x("Video's", 'Post Type General Name', 'video-post-on-upload'),
            'singular_name' => _x('Video', 'Post Type Singular Name', 'video-post-on-upload'),
            'menu_name' => _x("Video's", 'Admin Menu Text', 'video-post-on-upload'),
            'name_admin_bar' => _x('Video', 'Add New on Toolbar', 'video-post-on-upload'),
            'parent_item_colon' => _x('Parent Post:', 'video-post-on-upload'),
            'all_items' => _x("All Video's", 'video-post-on-upload'),
            'view_item' => _x('View Video', 'video-post-on-upload'),
            'add_new_item' => _x('New Video', 'video-post-on-upload'),
            'add_new' => _x('New Video', 'video-post-on-upload'),
            'edit_item' => _x('Edit Video', 'video-post-on-upload'),
            'update_item' => _x('Update Video', 'video-post-on-upload'),
            'search_items' => _x("Search in video's", 'video-post-on-upload'),
            'not_found' => _x('Not found any video!', 'video-post-on-upload'),
            'not_found_in_trash' => _x('Video not found in trash!', 'video-post-on-upload'),
        );
        $args = array(
            'label' => _x("Video's", 'video-post-on-upload'),
            'description' => _x('Video content for your website', 'video-post-on-upload'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'thumbnail'),
            'public' => true,
            'exclude_from_search' => false,
            'taxonomies' => array('category'), // No translation needed (English is fine)
            'show_in_rest' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-video-alt2',
        );
        register_post_type('video', $args);
    }


    /**
     * Flush rewrite rules after switching themes.
     */
    public function video_post_rewrite_flush() {
        $this->register_video_post_type();
        flush_rewrite_rules();
    }

    /**
     * Create a video post from a newly uploaded video attachment.
     *
     * This function is hooked to the `add_attachment` action and creates a new
     * video post when a video file is uploaded.
     *
     * @param int $attachment_ID The ID of the newly uploaded attachment.
     */
    public function create_video_post_from_attachment( $attachment_ID ) {

        // Get attachment details
        $attachment_post = get_post( $attachment_ID );
        $type = get_post_mime_type( $attachment_ID );

        // Check if audio file and user has permission (optional)
        if ( str_starts_with($type, 'video') && current_user_can( 'edit_posts' ) ) {

            $content = "<video src='" . esc_html($attachment_post->guid) . "' width='100%' preload='none' controls></video>";

            // Create new post object for "video" custom post type
            $my_post = array(
                'post_title'    => $attachment_post->post_title,
                'post_content'  => $content,
                'post_type'   => 'video',
                'post_author'   => get_current_user_id(),
                'post_status'  => 'publish'
            );

            // Insert the "video" post
            $post_id = wp_insert_post( $my_post );

            add_post_meta( $attachment_ID, 'related_video_post', $post_id );

            // Update attachment parent (if successful)
            if ( $post_id ) {
                wp_update_post( array(
                    'ID' => $attachment_ID,
                    'post_parent' => $post_id
                ) );
            }
        }
    }

    /**
     * Load plugin text domain for translation.
     *
     * @since 1.0.0
     */
    public function video_posts_load_textdomain() {
        load_plugin_textdomain( 'video-post-on-upload', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * updates video post on attachment update
     * @param $post_id
     * @return void
     */
    public function update_video_post_on_attachment_update( $post_id ) {

        $attachment_id = $post_id;
        $attachment_object = get_post($post_id);
        $type = get_post_mime_type( $attachment_id );

        // Check if video and post type hasn't changed (prevents infinite loop)
        if (str_starts_with($type, 'video')) {

            $video_post_id = get_post_meta( $attachment_id, 'related_video_post', true );

            // Only proceed if video post exists
            if ( $video_post_id ) {

                // Update Video Post Excerpt with Description Field
                $description = $attachment_object->post_content;
                $custom_description = get_field( 'description', $attachment_id ); // Replace with your actual custom field name (if used)

                // Use the first available description
                if (!$description && $custom_description) {
                    $description = $custom_description;
                }

                if ( $description ) {
                    $description = '<p>' . $description . '</p>';
                    $content = "<video src='" . esc_html( wp_get_attachment_url( $attachment_id ) ) . "' width='100%' preload='none' controls></video>";
                    $content .= "\n" . $description; // Append description after embed code
                    wp_update_post( [
                        'ID' => $video_post_id,
                        'post_content' => $content
                    ] );

                    // Try clearing cache for immediate update (optional)
                    wp_cache_delete($video_post_id, 'post');
                }
            }
        }
    }

    /**
     * Hook the `update_video_post_on_attachment_update` function to the `edit_attachment` action.
     */
    public function uvpoau_action() {
        // Update the "video" post with the video file information when the information is updated
        add_action( 'edit_attachment', array( $this, 'update_video_post_on_attachment_update' ), 10, 1 );
    }

}