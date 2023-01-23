<?php
    include_once '../class/DirManager.class.php';
    error_reporting(E_ALL);
    $data = json_decode(file_get_contents('php://input'));
    $remessas = implode("', '", $data);
    $backup_files = isset($data->backup) ? $data->backup : false;
    $host = "179.188.38.39:E:/ServidorWeb/banco-setrem/REM.FDB";
    $connect = ibase_connect($host, 'SYSDBA', 'masterkey');
    $query = "SELECT r.NOME_REMESSA, r.ARQUIVO FROM REMESSAS_GRAFICA r WHERE r.NOME_REMESSA IN ('$remessas') AND r.EXPORTADO = 'N'";
    $stmt = ibase_query($connect, $query);
    $rem_name = array();
    $dir = new DirManager();
    $path = $dir->getDirs(array('PASTA_BACKUP_REMESSA_BANCO'))['PASTA_BACKUP_REMESSA_BANCO'][0];
    $dir->deleteFiles($path, array('REM')); //Deleta os arquivos da pasta de backup

    while ( $row = ibase_fetch_object($stmt) ) {
        //Captura as informações do BLOB
        $blob_file = $row->ARQUIVO;
        $blob_info = ibase_blob_info($blob_file);
        $blob_handler = ibase_blob_open($blob_file);
        $blob_data = ibase_blob_get($blob_handler, $blob_info[0]);
        
        /**
         * Verifica se o arquivo está com o nome repetido
         * Se estiver altera o nome do arquivo com um id único
         */
        if ( !in_array($row->NOME_REMESSA, $rem_name) ) {
            $rem_name[] = $row->NOME_REMESSA;
        } else {
            $row->NOME_REMESSA = "$row->NOME_REMESSA-".uniqid();
        }

        //Escreve local para eventual backup
        $fopen_local = !file_exists("$path$row->NOME_REMESSA.txt")
            ? fopen("$path$row->NOME_REMESSA.txt", 'w+')    
            : fopen("$path$row->NOME_REMESSA-".uniqid().".txt", 'w+');
        
        if ( $backup_files && !$fopen_local ) {
            http_response_code(500);
            echo json_encode(array('error' => true, 'message' => 'Erro ao efetuar backup dos arquivos'));
            return false;
        }
        fwrite($fopen_local, $blob_data);
        fclose($fopen_local);
        //Escreve no servidor
        $fopen = fopen("ftp://administrator:ACNF7499@179.188.38.39/clientes/remessas/$row->NOME_REMESSA.txt", 'w');
        if ( !$fopen ) {
            $fopen = fopen("ftp://administrator:ACNF7499@179.188.38.39/clientes/remessas/$row->NOME_REMESSA-".uniqid().".txt", 'w');
        }
        fwrite($fopen, $blob_data);
        fclose($fopen);
    }
    
    $query = "UPDATE REMESSAS_GRAFICA r SET r.EXPORTADO = 'S', r.DATA_EXPORTACAO = CAST('NOW' as timestamp) WHERE r.NOME_REMESSA IN ('$remessas') AND r.EXPORTADO = 'N'";
    ibase_query($connect, $query);