<?php

/*
Plugin Name:  Simple Post Content Generator (RnzDev)
Plugin URI:   https://github.com/renzramos/wordpress-openai-simple-post-generator
Description:  Fast & easy plugin to create post content in WordPress using OpenAI API
Version:      1.0
Author:       Renz R. Ramos
Author URI:   https://www.renzramos.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  rnzdev-spcg
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


require_once('includes/admin-setting.php');
require_once('includes/rest-api.php');


