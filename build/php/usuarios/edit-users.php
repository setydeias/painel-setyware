<?php

    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Usuario.class.php';
    $user = new Usuario();
    $data = array_merge($_POST, $_FILES);
    $edit = $user->edit($data);

    if ( gettype($edit) === 'array' ) {
        $result = $edit;
    } else {
        $result = $edit
            ? array('success' => true, 'status' => 'Usuário editado com sucesso')
            : array('success' => false, 'status' => 'Erro ao editar usuário');
    }

    echo json_encode($result);