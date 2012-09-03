<?php
/*
See yak-for-wordpress.php for information and license terms
*/

if (!function_exists('yak_db_column_exists')) {
    function yak_db_column_exists($table, $column, $data_type = null) {
        global $wpdb;
        
        $schema = DB_NAME;
        
        $sql = "select count(*) as count
                from information_schema.columns
                where table_schema = '$schema'
                and table_name = '$table'
                and column_name = '$column'";
        if ($data_type != null) {
            $sql .= " data_type = '$data_type'";
        }
        
        $row = $wpdb->query($sql);
        return ($row->count == 1);
    }
}
?>