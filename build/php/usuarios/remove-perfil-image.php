<?php

    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Usuario.class.php';
    $user = new Usuario();
    $user_id = json_decode(file_get_contents('php://input'));
    $user->removePhoto($user_id) ? http_response_code(200) : http_response_code(400);