<?php
    header('Connection: keep-alive');
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/FirebirdCRUD.class.php';
    $crud = new FirebirdCRUD();

    $getProcessed = $crud->Select(array(
        'table' => 'TEMP_MENSALIDADE tm ORDER BY tm.ID_MENSALIDADE_CUSTOMER DESC',
        'params' => 'FIRST 1 tm.CUSTOMER'
    ));

    if ( count($getProcessed) > 0 ) {
        echo "data:".json_encode(array(
            'customer'=> $getProcessed['CUSTOMER'][0]
        ))."\n\n";
    }