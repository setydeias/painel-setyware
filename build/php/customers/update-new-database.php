<?php

    error_reporting(0);
    
    try {
        include_once '../class/FirebirdCRUD.class.php';
        $data = json_decode(file_get_contents('php://input'), true);
        $database = $data['database'];
        $updateData = $data['data'];
        $codsac = $data['codsac'];
        $user = $updateData['CLI_SIGLA'][0].str_pad($codsac, 5, 0, STR_PAD_LEFT);
        $password = $user;
        //Conecta ao ADM77777
        $fb_con = ibase_connect("C:\\Setydeias\\Setyware\\ADM77777\\ADM77777.gdb", "SYSDBA", "masterkey");
        $stmt = ibase_query($fb_con, "SELECT cc.CONVENIO FROM CEDENTES_CONVENIOS cc WHERE cc.PADRAO = 'S'");// OR cc.ID_CEDENTES_CONVENIOS = (SELECT MAX(ID_CEDENTES_CONVENIOS) FROM CEDENTES_CONVENIOS)");
        $convenio = ibase_fetch_object($stmt)->CONVENIO; //Convênio que será padrão

        //Conecta ao banco de dados informado na variável @database
        $crud = new FirebirdCRUD(array(
            'driver' => 'firebird',
            'dbname' => 'localhost:'.$database,
            'charset' => 'WIN1252',
            'user' => 'SYSDBA',
            'password' => 'masterkey'
        ));

        //Colunas
        $columns = array(
            'c.CODCED_ID' => $codsac,
            'c.DTINI' => $updateData['DATA_ASSOCIACAO'][0],
            'c.TPDOCCED' => $updateData['TPDOCSAC'][0],
            'c.DOCCED' => $updateData['DOCSAC'][0],
            'c.NOMCED' => utf8_decode($updateData['NOMSAC'][0]),
            'c.NOMUSUCED' => utf8_decode($updateData['NOMUSUSAC'][0]),
            'c.NOMTITCED' => utf8_decode($updateData['NOMTITSAC'][0]),
            'c.CODTRACED' => "3",
            'c.SIGLACED' => $updateData['CLI_SIGLA'][0],
            'c.DTNASCCED' => $updateData['DTNASCSAC_ANO'][0].'-'.$updateData['DTNASCSAC_MES'][0].'-'.$updateData['DTNASCSAC_DIA'][0],
            'c.ENDCED' => utf8_decode($updateData['ENDSAC'][0]),
            'c.CIDCED' => $updateData['CIDSAC'][0],
            'c.UFCED' => $updateData['UFSAC'][0],
            'c.CEPCED' => $updateData['CEP'][0],
            'c.DICAEND' => substr($updateData['DICAEND'][0], 0, 40),
            'c.REPASSE' => $updateData['REPASSE'][0],
            'c.REPASSE_VARIACAO' => $codsac,
            'c.USUARIO' => $user,
            'c.SENHA' => $password
        );

        //Update CEDENTES TABLE
        $dataToUpdate = array(
            'table' => 'CEDENTES c',
            'set' => $columns,
            'where' => array('1' => '1')
        );

        $update = $crud->Update($dataToUpdate);

        if ( $update['success'] ) {            
            //Atualiza a tabela de convênios
            $allUpdate = $crud->Update(array(
                'table' => 'CEDENTES_CONVENIOS cc',
                'set' => array('cc.NOSSONUM_FIXO' => $codsac, 'cc.PADRAO' => 'N'),
                'where' => array('1' => '1'),
                'messageInSuccess' => 'Banco de dados criado com sucesso'
            ));
            /*
            * Torna o convênio selecionado como padrão nas configurações como o padrão do banco
            */
            if ( $allUpdate['success'] ) {
                $crud->Update(array(
                    'table' => 'CEDENTES_CONVENIOS cc',
                    'set' => array('cc.PADRAO' => 'S'),
                    'where' => array('cc.CODIGO_CEDENTE' => $convenio)
                ));
                //Captura o id do convênio padrão
                $id_codigo_cedente = $crud->Select(array(
                    'table' => 'CEDENTES_CONVENIOS cc',
                    'params' => 'cc.ID_CEDENTES_CONVENIOS',
                    'where' => array('cc.PADRAO' => 'S')
                ))['ID_CEDENTES_CONVENIOS'][0];
                //Torna o convênio padrão na tabela EVENTOS_CONTABEIS
                $crud->Update(array(
                    'table' => 'EVENTOS_CONTABEIS ec',
                    'set' => array('ec.CODCONV' => $id_codigo_cedente),
                    'where' => array('1' => '1')
                ));
                //Torna o convênio dos pré-lançamentos com o id do convênio padrão
                $crud->Update(array(
                    'table' => 'TIPOS_LANCAMENTOS tl',
                    'set' => array('tl.USAR_VENCIMENTO' => $id_codigo_cedente),
                    'where' => array('1' => '1')
                ));
            } else {
                $allUpdate['success'] = false;
                $allUpdate['status'] = "O banco foi gerado com falha, favor verificar";    
            }
        } else {
            $allUpdate['success'] = false;
            $allUpdate['status'] = "Houve algum problema ao atualizar o banco de dados";
        }
        
        echo json_encode($allUpdate);
    } catch ( Exception $e ) {
        http_response_code(500);
        echo $e->getMessage();
    }