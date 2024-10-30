<?php


class Class_Wp_Ilf_Activator
{
    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        global $wpdb;

        update_option('internal_link_flow_version', TAILF_VERSION_INT);
        $table_name = $wpdb->prefix . 'internal_link_flow';
        $wpdb->query('DROP TABLE IF EXISTS ' . $table_name);
        $sql ="CREATE TABLE $table_name (
                  id int(11) NOT NULL AUTO_INCREMENT,
                  user varchar(255) DEFAULT NULL,
                  name varchar(255) DEFAULT NULL,
                  node_count smallint(6) DEFAULT NULL,
                  nodes text,
                  edges text,
                  viewport text,
                  created_at datetime DEFAULT NULL,
                  updated_at datetime DEFAULT NULL,
                  PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                ";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        return dbDelta($sql);
    }
}