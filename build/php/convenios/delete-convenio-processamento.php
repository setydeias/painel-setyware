<?php
    error_reporting(0);
    include_once '../class/ConvenioCobranca.class.php';
    $data = json_decode(file_get_contents('php://input'), true);
    $convenio = $data['convenio'];

    $convenio_cobranca = new ConvenioCobranca();
    $data = $convenio_cobranca->remove($convenio);

    echo json_encode($data);