<?php

    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Usuario.class.php';
    $user = new Usuario();
    $data = array_merge($_POST, $_FILES);
    $add = $user->add($data);

    if ( gettype($add) === 'array' ) {
        $result = $add;
    } else {
        $result = $user->add($_POST) 
            ? array('success' => true, 'status' => 'Usuário cadastrado com sucesso')
            : array('success' => false, 'status' => 'Erro ao inserir usuário');
    }

    echo json_encode($result);