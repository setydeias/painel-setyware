<?php
    error_reporting(E_ALL);
    include_once '../class/FirebirdCRUD.class.php';
    
    $crud = new FirebirdCRUD();
    $data = $crud->Select(array(
        'table' => 'CEDENTES_CONVENIOS cc',
        'params' => 'cc.BANCO, cc.TIPO, cc.MANTENEDOR, cc.CONVENIO, cc.PADRAO, cc.CHECAR_ARQUIVO_REPOSICAO'
    ));
    $convenio_info = array();

    for ( $i = 0 ; $i < count($data['BANCO']) ; $i++ ) {
        $convenio_info[] = array(
            'BANCO' => $data['BANCO'][$i],
            'TIPO' => $data['TIPO'][$i],
            'MANTENEDOR' => $data['MANTENEDOR'][$i],
            'CONVENIO' => $data['CONVENIO'][$i],
            'PADRAO' => $data['PADRAO'][$i],
            'CHECAR_ARQUIVO_REPOSICAO' => $data['CHECAR_ARQUIVO_REPOSICAO'][$i]
        );
    }

    echo json_encode($convenio_info);
