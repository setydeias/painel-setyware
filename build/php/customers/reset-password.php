<?php
    include_once '../class/STYComBr.class.php';
    $STYComBr = new STYComBr();
    $data = json_decode(file_get_contents('php://input'), true);
    $sigla = $data['sigla'];
    $codsac = str_pad($data['codsac'], 5, '0', STR_PAD_LEFT);

    $reset_password = $STYComBr->resetPassword($sigla, $codsac);
    echo json_encode($reset_password);