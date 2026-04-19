<?php
$required_params = array("domain_uuid");

function do_action($body) {
    $sql = "SELECT * FROM v_domains WHERE domain_uuid = :domain_uuid";
    $parameters['domain_uuid'] = $body->domain_uuid;
    $database = new database;
    $existing_domain = $database->select($sql, $parameters, 'row');
    if(!$existing_domain) {
        return array("error" => "domain not found", "code" => 404);
    }

    $domain_name = $existing_domain['domain_name'];
    if(isset($body->domain_name) && $body->domain_name) {
        $sql = "SELECT domain_uuid FROM v_domains WHERE domain_name = :domain_name AND domain_uuid <> :domain_uuid";
        $parameters = array(
            'domain_name' => $body->domain_name,
            'domain_uuid' => $body->domain_uuid
        );
        $database = new database;
        if($database->select($sql, $parameters, 'column')) {
            return array("error" => "domain name already exists", "code" => 409);
        }
        $domain_name = $body->domain_name;
    }

    $domain_enabled = $existing_domain['domain_enabled'];
    if(isset($body->domain_enabled)) {
        $enabled = filter_var($body->domain_enabled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if(is_null($enabled)) {
            return array("error" => "invalid domain_enabled value", "code" => 400);
        }
        $domain_enabled = $enabled ? "true" : "false";
    }

    $domain_description = $existing_domain['domain_description'];
    if(isset($body->domain_description)) {
        $domain_description = $body->domain_description;
    }

    $domain_parent_uuid = $existing_domain['domain_parent_uuid'];
    if(property_exists($body, 'domain_parent_uuid')) {
        if($body->domain_parent_uuid) {
            $domain_parent_uuid = $body->domain_parent_uuid;
        } else {
            $domain_parent_uuid = null;
        }
    }

    $sql = "UPDATE v_domains
            SET domain_parent_uuid = :domain_parent_uuid,
                domain_name = :domain_name,
                domain_enabled = :domain_enabled,
                domain_description = :domain_description,
                update_date = NOW(),
                update_user = :update_user
            WHERE domain_uuid = :domain_uuid";

    $update_user = 'rest_api';
    if(isset($_SESSION['username']) && $_SESSION['username']) {
        $update_user = $_SESSION['username'];
    }

    $parameters = array(
        'domain_uuid' => $body->domain_uuid,
        'domain_parent_uuid' => $domain_parent_uuid,
        'domain_name' => $domain_name,
        'domain_enabled' => $domain_enabled,
        'domain_description' => $domain_description,
        'update_user' => $update_user
    );

    $database = new database;
    if(!$database->execute($sql, $parameters)) {
        return array("error" => "error updating domain", "code" => 500);
    }

    $sql = "SELECT * FROM v_domains WHERE domain_uuid = :domain_uuid";
    $parameters = array('domain_uuid' => $body->domain_uuid);
    $database = new database;
    return $database->select($sql, $parameters, 'row');
}
