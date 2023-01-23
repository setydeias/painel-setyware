<?php
    error_reporting(0);
    ini_set('max_execution_time', 600);
    $data = json_decode(file_get_contents('php://input'));
    $remessas = $data->shipping;
    $write = $data->write === 'true' ? true : false; //Verifica se a conta transitória deve ser escrita
    $writeAdminPJ = false; //$data->writeAdminPJ === 'true' ? true : false; //Verifica se os dados devem ser escritos no AdminPJ
    $send_mail = $data->send_mail === 'true' ? true : false; //Verifica se o email de processamento deve ser enviado
    $attach_file = $data->attach;
    //INCLUDES
    include_once '../class/CloudServer.class.php';
    include_once '../class/DirManager.class.php';
    include_once '../class/Customer.class.php';
    include_once '../class/ShippingProcessing.class.php';
    include_once '../../../vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
    include_once '../functions.php';
    
    try {
        //Instanciando os objetos
        $dir = new DirManager();
        $CloudServer = new CloudServer();
        $customer = new Customer();
        //Diretórios
        $paths = $dir->getDirs(array('d.PROCESSAMENTO_REMESSA_GRAFICA', 'd.REMESSA_PROCESSADA_GRAFICA', 'd.REMESSA_ORIGINAL_GRAFICA'));
        $remReadDir = $paths['PROCESSAMENTO_REMESSA_GRAFICA'][0]; //Pasta de leitura das remessas
        $remProcessedDir = $paths['REMESSA_PROCESSADA_GRAFICA'][0]; //Pasta destino das remessas processadas
        $remOriginalDir = $paths['REMESSA_ORIGINAL_GRAFICA'][0]; //Pasta destino das remessas originais (sem processamento)
        //Apaga todos os arquivos dentro da pasta de leitura
        $dir->deleteFiles($remReadDir, array('txt'));
        //Apaga todos os arquivos dentro da pasta de arquivos processados
        $dir->deleteFiles($remProcessedDir, array('txt'));
        //Trazendo os arquivos de remessa do servidor para máquina local
        $CloudServer->connect();
        $CloudServer->get('./clientes/remessas/', $remReadDir, array('txt'));
        $CloudServer->get('./clientes/remessas/', $remOriginalDir, array('txt'));
        //Variáveis auxiliares
        $ct_not_found = $adminpj_error = $copyErrors = array();
        //Se houver arquivos para processar
        if ( $dir->countFiles($remReadDir, array('txt')) > 0 ) {
            $data = array();
            $files = $dir->getFiles($remReadDir, array('txt'));
            
            foreach ( $files as $file ) {
                if ( !in_array(basename($file), $remessas) ) continue;

                //Inicia o processamento do arquivo de remessa
                $shipping = new ShippingProcessing($file);
                $rem_info = $shipping->generate();
                $rem_info['already_processed'] = $shipping->registerShipping($rem_info);
                if ( $rem_info['already_processed'] ) {
                    $data[] = $rem_info;
                    continue;
                }
                $rem_info['write_ct'] = $write;
                $rem_info['write_adminpj'] = $writeAdminPJ;
                $rem_info['send_mail'] = $send_mail;
                $rem_info['attach'] = $attach_file;
                $rem_info['ADMINPJ_STATUS'] = $shipping->writeAdminPJ($rem_info);
                $rem_info['CONTA_TRANSITORIA_STATUS'] = $shipping->writeCT($rem_info); //Insere o custo da remessa na conta transitória do cliente
                $rem_info['EMAIL_STATUS'] = $shipping->send($rem_info); //Envia as informações da remessa para o email do cliente
                if ( $write && !$rem_info['CONTA_TRANSITORIA_STATUS']['status'] ) $ct_not_found[] = $rem_info['CONTA_TRANSITORIA_STATUS']['customer'];
                if ( $writeAdminPJ && !$rem_info['ADMINPJ_STATUS']['status'] ) $adminpj_error[] = $rem_info['ADMINPJ_STATUS']['customer'];

                $data[] = $rem_info;
            }
        }
        //Envia os arquivos processados para a pasta dos clientes
        if ( $dir->countFiles($remProcessedDir, array('txt')) > 0 ) {
            $files = $dir->getFiles($remProcessedDir, array('txt'));
            $path = $sigla = "";
            
            foreach ( $files as $file ) {
                $customer_sigla = substr(basename($file), 6, 3);
                $path = $customer->GetPathNameBySigla($customer_sigla);

                //Caso a cópia do arquivo não seja executada
                //Tenta criar o diretório e tenta executar a cópia novamente
                $pathToCopy = "C:\\Setydeias\\Setyware\\ADM77777\\Adm\\Clientes\\".strtoupper($path)."\\remessas";
                $filename = "$pathToCopy\\".basename($file);
                
                if ( !copy($file, $filename) ) {
                    mkdir($pathToCopy, 0777) ? copy($file, $filename) : $copyErrors[] = $file;
                }
            }
        }
        //Deleta os arquivos do servidor e fecha a conexão
        $CloudServer->delete('./clientes/remessas/', array('txt'), $remessas);
        echo json_encode(array('data' => $data, 'ct_not_found' => $ct_not_found, 'copy_errors' => $copyErrors));
    } catch (Exception $e) {
        echo json_encode(array('error' => true, 'message' => "ERRO NO PROCESSAMENTO: {$e->getMessage()}"));
    }