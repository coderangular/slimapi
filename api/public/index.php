<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

# Headers
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Origin, Content-Type, accept');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-XSRF-TOKEN');

# Vendor
require '../vendor/autoload.php';

# Configurations
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
$config['db']['host']   = 'localhost';
$config['db']['user']   = 'root';
$config['db']['pass']   = '';
$config['db']['dbname'] = 'angular_ue';
$app = new \Slim\App(['settings' => $config]);

# Dependencies
require 'dependency.php';

$app->get('/hello',function(Request $request, Response $response, $args){
    return "Hello";
});

# Save data to database
$app->post('/user', function(Request $request, Response $response, $args){
    $input = $request->getParsedBody();
    $hash_pwd = MD5($input['password']);
    $sql = "INSERT INTO tbl_users (username,password,firstname,lastname) VALUES (:username,:password,:firstname,:lastname)";
	$sth = $this->db->prepare($sql);
	$sth->bindParam("username", $input['username']);
	$sth->bindParam("password", $hash_pwd);
	$sth->bindParam("firstname", $input['firstname']);
	$sth->bindParam("lastname", $input['lastname']);
	$sth->execute();
	$input['id'] = $this->db->lastInsertId();
	return $this->response->withJson($input);
});

$app->post('/login', function (Request $request, Response $response, array $args) {
    $input = $request->getParsedBody();
    $hash_pwd = MD5($input['password']);
    $sth = $this->db->prepare("SELECT * FROM tbl_users WHERE username=:username AND password=:password");
    $sth->bindParam("username",$input['username'] );
    $sth->bindParam("password", $hash_pwd );
    $sth->execute();
    $todos = $sth->fetchObject();
    return $this->response->withJson($todos);
});

# Save data to database
$app->post('/facility', function(Request $request, Response $response, $args){
	$input = $request->getParsedBody();
    $sql = "INSERT INTO tbl_facility (name,description) VALUES (:name,:description)";
	$sth = $this->db->prepare($sql);
	$sth->bindParam("name", $input['name']);
	$sth->bindParam("description", $input['description']);
	$sth->execute();
	$input['id'] = $this->db->lastInsertId();
	return $this->response->withJson($input);
});
$app->post('/service_type', function(Request $request, Response $response, $args){
	$input = $request->getParsedBody();
    $sql = "INSERT INTO tbl_service_type (name,description) VALUES (:name,:description)";
	$sth = $this->db->prepare($sql);
	$sth->bindParam("name", $input['name']);
	$sth->bindParam("description", $input['description']);
	$sth->execute();
	$input['id'] = $this->db->lastInsertId();
	return $this->response->withJson($input);
});
$app->post('/service', function(Request $request, Response $response, $args){
	$input = $request->getParsedBody();
    $sql = "INSERT INTO tbl_service (name,facility_id,service_type_id) VALUES (:name,:facility_id,:service_type_id)";
	$sth = $this->db->prepare($sql);
	$sth->bindParam("name", $input['name']);
	$sth->bindParam("facility_id", $input['facility_id']);
	$sth->bindParam("service_type_id", $input['service_type_id']);
	$sth->execute();
	$input['id'] = $this->db->lastInsertId();
	return $this->response->withJson($input);
});

$app->get('/services', function (Request $request, Response $response, array $args) {
    $input = $request->getParsedBody();
    $sth = $this->db->prepare(
        "SELECT tbl_service.id as id,tbl_service.name as name,tbl_service_type.name as sname, tbl_facility.name as fname FROM 
        tbl_service, tbl_service_type, tbl_facility WHERE 
        tbl_service.facility_id = tbl_facility.id AND 
        tbl_service.service_type_id=tbl_service_type.id"
    );
    $sth->execute();
    $services = $sth->fetchObject();
    return $this->response->withJson($services);
});




$app->run();