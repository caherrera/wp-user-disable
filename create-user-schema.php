<?php 
function dwul_install() {

        global $wpdb;
        $table_name = $wpdb->prefix . 'dwul_disable_user_id'; 
        if( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
            
                $charset_collate = $wpdb->get_charset_collate();

                $sql = "CREATE TABLE $table_name (
                        id int(10) NOT NULL AUTO_INCREMENT,
                        user_id bigint(20) NOT NULL UNIQUE,
                        PRIMARY KEY id (id)
                      ) $charset_collate;";

                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta($sql);
        }
    }