<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;
delete_option('mct_options');
delete_option('mct_page_id');
