<?php
    
    include_once '../class/Retorno.class.php';
    include_once '../class/AbstractFileGenerator.class.php';
    
    try {
        $data = json_decode(file_get_contents('php://input'))->data;
        $customer = $data->customer;
        $convenio = $data->convenio;
        $data_de = $data->dataDe;
        $data_ate = $data->dataAte;
        $group_params = array( 'customer' => $customer, 'convenio' => $convenio, 'data_arquivo' => array('de' => $data_de, 'ate' => $data_ate ));
        $retornos = new Retorno($group_params);
        //Obtém os títulos pagos
        $paid_billets = $retornos->getPaid();
        //Gera os arquivos de retorno
        if ( count($paid_billets) > 0 ) {
            $group_params['records'] = $paid_billets;
            $fileGenerator = new AbstractFileGenerator($group_params);
            $data = $fileGenerator->create();
            echo json_encode( array('success' => true, 'data' => $data) );
            return;
        }
        
        echo json_encode( array('success' => false, 'message' => 'Nenhum pagamento encontrado com estes parâmetros') );
    } catch ( Exception $e ) {
        echo json_encode( array('success' => false, 'message' => $e->getMessage()) );
    }