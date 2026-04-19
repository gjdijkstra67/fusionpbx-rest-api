<?php
$required_params = array("domain_uuid");

function do_action($body) {
    $sql = "SELECT domain_uuid, domain_name FROM v_domains WHERE domain_uuid = :domain_uuid";
    $parameters['domain_uuid'] = $body->domain_uuid;
    $database = new database;
    $domain = $database->select($sql, $parameters, 'row');
    if(!$domain) {
        return array("error" => "domain not found", "code" => 404);
    }

    $sql = "DELETE FROM v_domains WHERE domain_uuid = :domain_uuid";
    $parameters['domain_uuid'] = $body->domain_uuid;
    $database = new database;
    if(!$database->execute($sql, $parameters)) {
        return array(
            "error" => "error deleting domain",
            "code" => 409,
            "message" => "domain may still have related records"
        );
    }

    return array(
        "success" => true,
        "domain_uuid" => $domain['domain_uuid'],
        "domain_name" => $domain['domain_name']
    );
}
