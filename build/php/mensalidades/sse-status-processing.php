<?php
    header('Connection: keep-alive');
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/FirebirdCRUD.class.php';
    $crud = new FirebirdCRUD();

    $total_customers = $crud->Select(array(
        'table' => 'SACADOS s',
        'params' => 's.CODSAC',
        'where' => array( 's.STATUS' => 0 )
    ))['CODSAC'];
    
    $total_processed_customers = $crud->Select(array(
        'table' => 'TEMP_MENSALIDADE tm',
        'params' => 'tm.ID_MENSALIDADE_CUSTOMER'
    ))['ID_MENSALIDADE_CUSTOMER'];

    $getProcessed = $crud->Select(array(
        'table' => 'TEMP_MENSALIDADE tm ORDER BY tm.ID_MENSALIDADE_CUSTOMER DESC',
        'params' => 'FIRST 1 tm.CUSTOMER'
    ));

    if ( count($getProcessed) > 0 ) {
        echo "data:".json_encode(array(
            'customer'=> $getProcessed['CUSTOMER'][0],
            'total_processed' => count($total_processed_customers) - 1,
            'total_customers' => count($total_customers)
        ))."\n\n";
    }