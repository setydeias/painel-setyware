<?php

    include_once '../class/FirebirdCRUD.class.php';
    $crud = new FirebirdCRUD();
    $data = json_decode(file_get_contents('php://input'), true);
    $newMasterPass = $data['newPassword'];

    $dataToUpdate = array(
        'table' => 'PASSWORD_PARAM p',
        'set' => array('p.PASSWORD_MASTER' => $newMasterPass),
        'where' => array('1' => '1'),
        'messageInSuccess' => 'Senha padrão alterada com sucesso'
    );
    $update = $crud->Update($dataToUpdate);

    echo json_encode($update);