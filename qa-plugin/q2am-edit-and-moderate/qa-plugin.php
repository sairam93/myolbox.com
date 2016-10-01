<?php

/*
*   Q2AM Edit and Moderate
*   
*   register plugin
*   
*   @author         Q2A Market
*   @category       Plugin
*   @Version        1.00
*   @URL            http://www.q2amarket.com
*   
*   @Q2A Version    1.6+
*
*   Any modification can stop plugin working
*/

/*
	Plugin Name: Q2AM Edit and Moderate
	Plugin URI: http://store.q2amarket.com/q2a-free-plugins/edit-and-moderate
	Plugin Description: Allows to edit content before approve in moderation queue
	Plugin Version: 1.0
	Plugin Date: 2013-09-25
	Plugin Author: Q2A Market
	Plugin Author URI: http://www.q2amarket.com/
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.6
	Plugin Update Check URI: http://q2amarket.com/meta/update/plugins/q2am-edit-and-moderate/qa-plugin.php
*/


	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}

	qa_register_plugin_overrides('q2am-edit-and-moderate.php');
	

/*
	Omit PHP closing tag to help avoid accidental output
*/