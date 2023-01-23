<?php

    //Módulos
    include_once '../class/FirebirdCRUD.class.php';
    include_once '../class/Customer.class.php';

    //Parâmetros
    $data = json_decode(file_get_contents('php://input'), true);
    $codsac = $data['codsac'];
    $crud = new FirebirdCRUD();

    //Retorna os dados do CEDENTE de acordo com o código do sacado
    $dataToSelect = array(
        'table' => 'SACADOS s',
        'params' => 's.DATA_ASSOCIACAO, s.TPDOCSAC, s.DOCSAC, s.NOMSAC, s.NOMUSUSAC, s.NOMTITSAC, s.CLI_SIGLA, s.DTNASCSAC_DIA, s.DTNASCSAC_MES, s.DTNASCSAC_ANO,
        s.ENDSAC, s.CIDSAC, s.UFSAC, s.CEP, s.DICAEND, s.REPASSE',
        'where' => array(
            's.CODSAc' => $codsac
        )
    );

    $result = $crud->Select($dataToSelect);

    echo json_encode($result);