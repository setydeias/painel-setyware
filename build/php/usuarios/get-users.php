<?php

    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Usuario.class.php';
    $user = new Usuario();
    echo json_encode($user->get());