<?php
/**
 * Plugin Name: Auto Publish and Update Posts on Video Upload (right now only video file types)
 * Plugin URI:
 * Description: Creates post on file upload to media section of admin.
 * Version:     1.0.0
 * Author:      Hossein Barzegari
 * Author URI:  https://linkedin.com/in/hossein-barzegar-996937178
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: video-post-on-upload
 */

namespace class;
require_once __DIR__ . '/class/VideoPostAttachment.php';

$VideoClass = new VideoPostAttachment();
