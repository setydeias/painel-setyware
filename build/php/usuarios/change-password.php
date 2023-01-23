<?php

    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Usuario.class.php';
    $data = json_decode(file_get_contents('php://input'));
    $current_password = $data->password;
    $new_password = $data->new_password;
    $sure_new_password = $data->sure_password;
    $user_id = $data->user_id;

    if ( $new_password !== $sure_new_password ) {
        echo json_encode(array('success' => false, 'status' => 'As novas senhas devem ser correspondentes'));
        return;
    }

    $user = new Usuario();
    $changed = $user->changePassword($user_id, $current_password, $new_password);
    echo json_encode($changed);