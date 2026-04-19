<?php
$required_params = array();

function do_action($body) {
    $sql = "SELECT * FROM v_domains ORDER BY domain_name";
    $database = new database;
    return $database->select($sql, null, 'all');
}
