<?php
$required_params = array("domain_name");

function do_action($body) {
    $sql = "SELECT domain_uuid FROM v_domains WHERE domain_name = :domain_name";
    $parameters['domain_name'] = $body->domain_name;
    $database = new database;
    if($database->select($sql, $parameters, 'column')) {
        return array("error" => "domain already exists", "code" => 409);
    }
    unset($parameters);

    $domain_uuid = uuid();
    $domain_enabled = "true";
    if(isset($body->domain_enabled)) {
        $enabled = filter_var($body->domain_enabled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if(is_null($enabled)) {
            return array("error" => "invalid domain_enabled value", "code" => 400);
        }
        $domain_enabled = $enabled ? "true" : "false";
    }

    $domain_description = "";
    if(isset($body->domain_description)) {
        $domain_description = $body->domain_description;
    }

    $domain_parent_uuid = null;
    if(isset($body->domain_parent_uuid) && $body->domain_parent_uuid) {
        $domain_parent_uuid = $body->domain_parent_uuid;
    }

    $array["domains"][] = array(
        "domain_uuid" => $domain_uuid,
        "domain_parent_uuid" => $domain_parent_uuid,
        "domain_name" => $body->domain_name,
        "domain_enabled" => $domain_enabled,
        "domain_description" => $domain_description
    );

    $_SESSION["permissions"]["domain_add"] = true;

    $database = new database;
    $database->app_name = 'rest_api';
    $database->app_uuid = '2bfe71d9-e112-4b8b-bcff-75aeb0e06302';
    if(!$database->save($array)) {
        return array("error" => "error adding domain", "code" => 500);
    }

    $sql = "SELECT * FROM v_domains WHERE domain_uuid = :domain_uuid";
    $parameters['domain_uuid'] = $domain_uuid;
    $database = new database;
    return $database->select($sql, $parameters, 'row');
}
