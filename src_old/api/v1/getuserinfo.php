<?php
// required headers
require_once('../../headers/default.php');

// load Models
require('../../models/user_model.php');

if($_SERVER['REQUEST_METHOD'] == 'GET'){
    http_response_code(200);
}else{
    http_response_code(405);
}