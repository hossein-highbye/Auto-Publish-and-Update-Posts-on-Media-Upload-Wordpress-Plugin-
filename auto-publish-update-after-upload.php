<?php
/**
 * Plugin Name: Auto Publish and Update Posts on Video Upload (right now only video file types)
 * Plugin URI:
 * Description: Creates post on file upload to media section of admin.
 * Version:     1.0.1
 * Author:      Hossein Barzegari
 * Author URI:  https://linktr.ee/HosseinBarzegari
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: video-post-on-upload
 * Domain Path: /languages
 */

namespace class;
require_once __DIR__ . '/class/VideoPostAttachment.php';

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

$VideoClass = new VideoPostAttachment();
