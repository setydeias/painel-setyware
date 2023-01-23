<?php

    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Usuario.class.php';
    $user = new Usuario();
    $user_id = json_decode(file_get_contents('php://input'));
    echo json_encode($user->getById($user_id));
    
    