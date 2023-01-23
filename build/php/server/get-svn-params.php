<?php

    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/CloudServerParams.class.php';
    $CloudServerParams = new CloudServerParams();
    echo json_encode(array(
        'HOST' => CloudServerParams::getHost(),
        'USER' => CloudServerParams::getFTPLogin(),
        'PASSWORD' => CloudServerParams::getPassword()
    ));