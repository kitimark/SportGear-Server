<?php
// required headers
require_once('../../headers/default.php');

// load Models
require('../../models/login_model.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // REQUIRED user and pwd;
    $json_data = json_decode(file_get_contents('php://input'),true);
    $user = $json_data['user'];
    $pwd = $json_data['pwd'];
    if(empty($user) || empty($pwd)){
        $msg = array('message'=>'require user , pwd');
        echo json_encode($msg);
        http_response_code(200);
    }else{
        $model = new login();
        if($model->userCount($user) > 0){
            if($model->loginVerify($user,$pwd)){
                //dummy result;
                $msg = array('message'=>'LOGIN!');
                echo json_encode($msg);
                http_response_code(200);
                
            }else{
                $msg = array('message'=>'Password Incorrect !');
                echo json_encode($msg);
                http_response_code(200);
            }
        }else{
            $msg = array('message'=>'User not found!');
            echo json_encode($msg);
            http_response_code(200);
        }
    }
}else{
    http_response_code(405);
}