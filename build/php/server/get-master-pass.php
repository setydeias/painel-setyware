<?php

    include_once '../class/FirebirdCRUD.class.php';
    $crud = new FirebirdCRUD();

    $select = array('table' => 'PASSWORD_PARAM p', 'params' => 'p.PASSWORD_MASTER');
    $data = $crud->Select($select);
    
    echo json_encode(array('password' => $data["PASSWORD_MASTER"][0]));