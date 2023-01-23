<?php
    error_reporting(0);
    include_once('class/Util.class.php');
    include_once('class/Customer.class.php');
    include_once('class/FirebirdCRUD.class.php');
    $data = json_decode(file_get_contents('php://input'), true);
    $pathTo = $data['path']; //Destino do arquivo
    $file_content = $data['file_content']; //Caminho absoluto do arquivo
    $customer_pathname = $data['customer_pathname']; //Pathname do beneficiário
    $customer_cod = substr($customer_pathname, -3); //Código do beneficiário

    $crud = new FirebirdCRUD(array(
        'driver' => 'firebird',
        'dbname' => "localhost:D:\\Laboratorio - Setyware\\Setydeias\\Setyware\\$customer_pathname\\$customer_pathname.GDB",
        'charset' => 'WIN1252',
        'user' => 'SYSDBA',
        'password' => 'masterkey'
    ));
    
    $sql = "";
    $records_to_processing = array();
    $not_found = array();
    
    $fileArray = explode(PHP_EOL, $file_content);
    $fileArrayLength = count($fileArray) - 2;
    try {
        //Separa apenas os títulos do beneficiário selecionado
        for ( $i = 2; $i < $fileArrayLength; $i++ ) {
            $linha = $fileArray[$i];
            $segmento = substr($linha, 13, 1);

            if ( $segmento == 'P' ) {
                $nossonum = trim(substr($linha, 37, 20));
                $cod_beneficiario = substr($nossonum, 7, 3);
                
                if ( $cod_beneficiario != $customer_cod ) continue;
                $segmento_q = $fileArray[$i+1];
                $segmento_r = $fileArray[$i+2];
                $records_to_processing[] = $linha;
                $records_to_processing[] = $segmento_q;
                $records_to_processing[] = $segmento_r;
            }
        }
        //Loop nos registros retornados
        for ( $i = 0; $i < count($records_to_processing); $i++ ) {
            $linha = $fileArray[$i];
            $segmento = substr($linha, 13, 1);

            if ( $segmento == 'P' ) {
                //Segmentos
                $segmento_q = $fileArray[$i+1];
                $segmento_r = $fileArray[$i+2];
                //Dados
                $nossonum = trim(substr($linha, 37, 20));
                $convenio = substr($nossonum, 0, 7);
                $seunum = substr($linha, 62, 15);
                $dtemiss = Util::FmtDate(substr($linha, 109, 8), '4');
                $dtvcto = Util::FmtDate(substr($linha, 77, 8), '4');
                $valor = substr($linha, 85, 15) / 100;
                $agencia = substr($linha, 18, 4);
                $tipo_docsac = substr($segmento_q, 17, 1);
                $docLength = $tipo_docsac == '1' ? 11 : 14;
                $docsac = substr($segmento_q, 18, 15);
                $docsac = substr($docsac, -$docLength); //Formatação do documento do sacado
                $nomesac = substr($segmento_q, 33, 40);
                $codsac = $crud->Select(array('table' => 'SACADOS s', 'params' => 's.CODSAC', 'where' => array( 's.DOCSAC' => $docsac )));
                //Se não encontrar o sacado pelo documento
                //Insere no array de clientes não encontrados
                if ( count($codsac) < 1 ) {
                    $not_found[] = array('DOCSAC' => $docsac, 'NOMSAC' => $nomesac);
                    continue;
                }
                $codsac = $codsac['CODSAC'][0];
                $multa = substr($segmento_r, 74, 15) / 100;
                $juros = substr($linha, 126, 15) / 100;
                $tipojuros = substr($linha, 117, 1);
                $especie = substr($linha, 106, 2);
                $convenio_data = $crud->Select(array(
                    'table' => 'CEDENTES_CONVENIOS cc',
                    'params' => 'cc.ID_CEDENTES_CONVENIOS, cc.CARTEIRA, cc.VARIACAO, cc.DESCRICAO, cc.CONTA_NUMERO', 
                    'where' => array( 'cc.CODIGO_CEDENTE' => $convenio )
                ));
                //Se o convênio não for encontrado
                //Lança a exceção
                if ( count($convenio) < 1 ) {
                    throw new Exception('Convênio não encontrado');
                }
                $id_convenio = $convenio_data['ID_CEDENTES_CONVENIOS'][0];
                $banco = substr($linha, 0, 3);
                $convenio = substr($nossonum, 0, 7);
                $conta = $convenio_data['CONTA_NUMERO'][0];
                $carteira = $convenio_data['CARTEIRA'][0];
                $variacao = $convenio_data['VARIACAO'][0];
                $multatipo = substr($segmento_r, 65, 1);
                $agencia_dv = substr($linha, 22, 1);
                $conta_dv = substr($linha, 35, 1);

                $sql .= "INSERT INTO TITULOS (CODTIT, NOSSONUM, SEUNUM, DTEMISS, MESANO, DTVCTO, VLRTIT, DTREC, VLRREC, VLMULTA, VLJUROS, VLDESCONTO, TARIFA, DTCRED, VLRCRED, LOCAREC, FORMREC, CODAGE, CODSAC, MULTA, JUROS, VLDESC, PROT, MAXDIAS, CARDIAS, ESPECIE, MOEDA, LOCIMP, CODCONV, CODBAN, CODIGO_CEDENTE, CONVENIO, CONTA_CORRENTE, ACEITE, OPERACAO, CARTEIRA, CARTEIRA_COBRANCA, ID_DOCUMENTOS_ARRECADACAO, RETORNO, IMP, VARIACAO, LOTE, MULTA_TIPO_VALOR, JUROS_TIPO_VALOR, DTVCTO_TEMP, SEL, LOGWEB, SYSDATA, SYSHORA, AGENCIA_DV, CONTA_CORRENTE_DV, GERREL, BOLETO_ENVIA_EMAIL, BOLETO_ENVIA_REMESSA, DIAS_PRA_MIN_REM, FLAG, REPARAR, INSTRU_1, INSTRU_2, INSTRU_3, INSTRU_4, INSTRU_5, INSTRU_6, INSTRU_7, INSTRU_8, MSG_1, MSG_2, MSG_3, MSG_4, MSG_5, MSG_6, MSG_7, MSG_8, MSG_9, MSG_10, MSG_11, MSG_12, MSG_13, MSG_14, MSG_15, MSG_16, MSG_17, MSG_18, MSG_19, MSG_20, MSG_21, CONVENIO_COBRANCA, DATA_DESCONTO, ABATIMENTO, ABATIMENTO_CONCEDIDO, MULTA_PREV, JUROS_PREV, ANOTACOES, EXIWEB, ID_END, ID_NOME, STATUS_REG) VALUES (GEN_ID(TITULOS, 1), '$nossonum', null, '$dtemiss', null, '$dtvcto', '$valor', null, 0, 0, 0, 0, 0, null, 0, null, null, '$agencia', '$codsac', '$multa', $juros, 0, 0, '90', '0', '$especie', '9', '0', '3', '$banco', '$convenio', '$convenio', '7777', null, null, '17', null, 0, null, 'S', '11', GEN_ID(LOTE, 1), '$multatipo', '$tipojuros', null, null, '1', CAST('Now' as date), CAST('Now' as time), '$agencia_dv', '$conta_dv', null, '0', '1', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, '001CONV717', null, 0, 0, null, null, null, null, 1, 1, 1);".PHP_EOL;
            }
        }
        $sql .= "COMMIT;";
        /*
        * Cria o arquivo com as instruções SQL
        */
        $filename = $pathTo.'CONVERTIDO_'.date('Ymd').'_'.$customer_pathname.'.sql';
        $fp_handler = fopen($filename, 'w');
        fwrite($fp_handler, $sql);
        fclose($fp_handler);

        echo json_encode(array(
            'message' => "O arquivo $filename foi gerado com sucesso",
            'not_found' => $not_found
        ));
    } catch (Exception $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }