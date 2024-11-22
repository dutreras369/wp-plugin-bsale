<?php

function enqueue_custom_admin_styles() {
    wp_enqueue_style( 'bsale-admin-style', plugin_dir_url( __FILE__ ) . '../assets/css/admin-style.css', array(), '1.0.0' );
}

add_action( 'admin_enqueue_scripts', 'enqueue_custom_admin_styles' );
