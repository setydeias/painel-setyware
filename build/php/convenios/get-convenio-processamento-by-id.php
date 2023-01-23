<?php
    error_reporting(E_ALL);
    include_once '../class/FirebirdCRUD.class.php';
    $convenio = json_decode(file_get_contents('php://input'))->convenio;

    $crud = new FirebirdCRUD();
    $data = $crud->Select(array(
        'table' => 'CEDENTES_CONVENIOS cc',
        'params' => 'cc.BANCO, cc.AGENCIA, cc.CONTA, cc.OPERACAO, cc.TIPO, cc.MANTENEDOR, cc.CONVENIO, cc.CARTEIRA, cc.VARIACAO, cc.PADRAO, cc.CHECAR_ARQUIVO_REPOSICAO',
        'where' => array('cc.CONVENIO' => $convenio )
    ));
    $convenio_info = array();
    
    if ( count($data) > 0 ) {
        for ( $i = 0 ; $i < count($data['BANCO']) ; $i++ ) {
            $convenio_info[] = array(
                'BANCO' => $data['BANCO'][$i],
                'AGENCIA' => $data['AGENCIA'][$i],
                'CONTA' => $data['CONTA'][$i],
                'OPERACAO' => $data['OPERACAO'][$i],
                'TIPO' => $data['TIPO'][$i],
                'MANTENEDOR' => $data['MANTENEDOR'][$i],
                'CONVENIO' => $data['CONVENIO'][$i],
                'CARTEIRA' => $data['CARTEIRA'][$i],
                'VARIACAO' => $data['VARIACAO'][$i],
                'PADRAO' => $data['PADRAO'][$i],
                'CHECAR_ARQUIVO_REPOSICAO' => $data['CHECAR_ARQUIVO_REPOSICAO'][$i]
            );
        }
    }

    echo json_encode($convenio_info);
