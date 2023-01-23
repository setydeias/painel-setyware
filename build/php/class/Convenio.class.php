<?php

    error_reporting(0);
    include_once 'FirebirdCRUD.class.php';

    class Convenio {
        public $connection;
        public $allowed_banks;

        public function __construct() { 
            $this->connection = new FirebirdCRUD();
            $this->allowed_banks = array('001', '104', '237', '341');
            $this->allowed_convenio_types = array('1', '2');
        }

        public function __destruct() { }

        /*
        * Cadastra o convênio
        */
        public function add($data) {
            $banco = $data['banco'];
            $agencia = $data['agencia'];
            $conta = $data['conta'];
            $operacao = $data['op'];
            $convenio = $data['convenio'];
            $carteira = $data['carteira'];
            $variacao = $data['variacao'];
            $tipo_convenio = $data['tipo_convenio'];
            $customer = $data['customer'];
            $padrao = $data['padrao'];
            $checar_arquivo_reposicao = $data['checkFile'];

            if ( !in_array($banco, $this->allowed_banks) ) {
                return array('success' => false, 'error' => 'Banco não permitido');
            }

            if ( strlen($convenio) < 6 || strlen($convenio) > 7 ) {
                return array('success' => false, 'error' => 'Número do convênio inválido');
            }
            
            if ( !in_array($tipo_convenio, $this->allowed_convenio_types) ) {
                return array('success' => false, 'error' => 'Tipo de convênio inválido');
            }

            if ( $tipo_convenio === '2' && $customer === '' ) {
                return array('success' => false, 'error' => 'Convênio próprio requer ao menos um cliente selecionado');
            }

            //Verifica se o convênio já existe
            $select_convenio = $this->connection->Select(array(
                'table' => 'CEDENTES_CONVENIOS cc',
                'params' => 'cc.BANCO, cc.CONVENIO',
                'where' => array('cc.CONVENIO' => $convenio, 'cc.BANCO' => $banco)
            ));

            if ( count($select_convenio) !== 0 ) {
                http_response_code(400);
                return array('success' => false, 'error' => 'Convênio já existe na base de dados');
            }

            $columns = array(
                'BANCO' => $banco,
                'AGENCIA' => $agencia,
                'CONTA' => $conta,
                'OPERACAO' => $operacao,
                'TIPO' => $tipo_convenio,
                'MANTENEDOR' => $tipo_convenio === '1' ? 'STY' : $customer,
                'CONVENIO' => $convenio,
                'CARTEIRA' => $carteira,
                'VARIACAO' => $variacao,
                'PADRAO' => $padrao ? 'S' : 'N',
                'CHECAR_ARQUIVO_REPOSICAO' => $checar_arquivo_reposicao ? 'S' : 'N'
            );

            $toInsert = array(
                'table' => 'CEDENTES_CONVENIOS',
                'columns' => $columns
            );
            
            if ( $this->connection->Insert($toInsert)['success'] ) {
                return $padrao && !$this->makePattern($convenio)
                    ? array('success' => false, 'error' => 'Não foi possível tornar o convênio padrão')
                    : array('success' => true);
            } else {
                return array('success' => false, 'error' => 'Erro ao cadastrar convênio');
            }
        }

        /*
        * Edita o convênio informado
        */
        public function edit($data) {
            $banco = $data['banco'];
            $agencia = $data['agencia'];
            $conta = $data['conta'];
            $operacao = $data['op'];
            $convenio = $data['convenio'];
            $carteira = $data['carteira'];
            $variacao = $data['variacao'];
            $tipo_convenio = $data['tipo_convenio'];
            $convenio_original = $data['convenio_original'];
            $customer = $data['customer'];
            $padrao = $data['padrao'];
            $checar_arquivo_reposicao = $data['checkFile'];

            if ( !in_array($banco, $this->allowed_banks) ) {
                return array('success' => false, 'error' => 'Banco não permitido');
            }

            if ( strlen($convenio) < 6 || strlen($convenio) > 7 ) {
                return array('success' => false, 'error' => 'Número do convênio inválido');
            }
            
            if ( !in_array($tipo_convenio, $this->allowed_convenio_types) ) {
                return array('success' => false, 'error' => 'Tipo de convênio inválido');
            }

            //Verifica se o convênio já existe
            $select_convenio = $this->connection->Select(array(
                'table' => 'CEDENTES_CONVENIOS cc',
                'params' => 'cc.BANCO, cc.CONVENIO',
                'where' => array('cc.CONVENIO' => $convenio, 'cc.BANCO' => $banco)
            ));

            if ( count($select_convenio) !== 0 ) {
                http_response_code(400);
                return array('success' => false, 'error' => 'Convênio já existe na base de dados');
            }

            if ( $tipo_convenio === '2' && $customer === '' ) {
                return array('success' => false, 'error' => 'Convênio próprio requer ao menos um cliente selecionado');
            }

            $columns = array(
                'BANCO' => $banco,
                'AGENCIA' => $agencia,
                'CONTA' => $conta,
                'OPERACAO' => $operacao,
                'TIPO' => $tipo_convenio,
                'MANTENEDOR' => $tipo_convenio === '1' ? 'STY' : $customer,
                'CONVENIO' => $convenio,
                'CARTEIRA' => $carteira,
                'VARIACAO' => $variacao,
                'PADRAO' => $padrao ? 'S' : 'N',
                'CHECAR_ARQUIVO_REPOSICAO' => $checar_arquivo_reposicao ? 'S' : 'N'
            );
            
            $toUpdate = array(
                'table' => 'CEDENTES_CONVENIOS cc',
                'set' => $columns,
                'where' => array('cc.CONVENIO' => $convenio_original),
            );
            
            if ( $this->connection->Update($toUpdate)['success'] ) {
                return $padrao && !$this->makePattern($convenio)
                    ? array('success' => false, 'error' => 'Não foi possível tornar o convênio padrão')
                    : array('success' => true);
            } else {
                return array('success' => false, 'error' => 'Erro ao atualizar convênio');
            }
        }

        /*
        * Retorna o convênio selecionado
        */
        public function getByConv($convenio) {
            $data = $this->connection->Select(array(
                'table' => 'CEDENTES_CONVENIOS cc',
                'params' => 'cc.ID_CEDENTES_CONVENIOS, cc.BANCO, cc.AGENCIA, cc.CONTA, cc.TIPO, cc.MANTENEDOR, cc.CONVENIO, cc.CARTEIRA, cc.VARIACAO, cc.PADRAO',
                'where' => "cc.CONVENIO = '$convenio'"
            ));

            $convenio = array();
            
            if ( count($data) > 0 ) {
                foreach ( $data as $key => $value ) {
                    foreach ( $value as $index => $info ) {
                        $convenio[$index][$key] = $info;
                    }
                }
            }

            return $convenio[0];
        }

        /*
        * Retorna os convênios cadastrados
        */
        public function get($sty = null) {
            $get = array(
                'table' => 'CEDENTES_CONVENIOS cc',
                'params' => 'cc.ID_CEDENTES_CONVENIOS, cc.BANCO, cc.AGENCIA, cc.CONTA, cc.TIPO, cc.MANTENEDOR, cc.CONVENIO, cc.CARTEIRA, cc.VARIACAO, cc.PADRAO'
            );

            if ( !is_null($sty) ) {
                $get = array_merge($get, array('where' => array('TIPO' => 1)));
            }

            $data = $this->connection->Select($get);
            $convenios = array();
            
            for ( $i = 0; $i < count($data['ID_CEDENTES_CONVENIOS']); $i++ ) {
                $convenios[] = array(
                    'ID_CEDENTES_CONVENIOS' => $data['ID_CEDENTES_CONVENIOS'][$i],
                    'BANCO' => $data['BANCO'][$i],
                    'AGENCIA' => $data['AGENCIA'][$i],
                    'CONTA' => $data['CONTA'][$i],
                    'TIPO' => $data['TIPO'][$i],
                    'MANTENEDOR' => $data['MANTENEDOR'][$i],
                    'CONVENIO' => $data['CONVENIO'][$i],
                    'CARTEIRA' => $data['CARTEIRA'][$i],
                    'VARIACAO' => $data['VARIACAO'][$i],
                    'PADRAO' => $data['PADRAO'][$i],
                );
            }
            
            return $convenios;
        }

        /*
        * Retorna o convênio padrão
        */
        public function getConvenioPadrao() {
            $get = array(
                'table' => 'CEDENTES_CONVENIOS cc',
                'params' => 'cc.ID_CEDENTES_CONVENIOS, cc.BANCO, cc.AGENCIA, cc.CONTA, cc.TIPO, cc.MANTENEDOR, cc.CONVENIO, cc.CARTEIRA, cc.VARIACAO, cc.PADRAO',
                'where' => array('cc.PADRAO' => 'S')
            );

            $data = $this->connection->Select($get);
            $convenio = array();

            if ( count($data) > 0 ) {
                $convenio[] = array(
                    'ID_CEDENTES_CONVENIOS' => $data['ID_CEDENTES_CONVENIOS'][0],
                    'BANCO' => $data['BANCO'][0],
                    'TIPO' => $data['TIPO'][0],
                    'MANTENEDOR' => $data['MANTENEDOR'][0],
                    'CONVENIO' => $data['CONVENIO'][0],
                    'PADRAO' => $data['PADRAO'][0]
                );
            }

            return $convenio;
        }

        /*
        * Verifica se o convênio informado existe
        */
        public function exists($convenio) {
            $checkConvenioExists = array(
                'table' => 'CEDENTES_CONVENIOS cc',
                'params' => 'cc.CONVENIO',
                'where' => array('cc.CONVENIO' => $convenio)
            );

            return count($this->connection->Select($checkConvenioExists)) > 0;
        }

        /*
        * Torna o convênio informado como padrão
        */
        public function makePattern($convenio) {
            //Torna todos os convênios não padrão
            $makePattern = array(
                'table' => 'CEDENTES_CONVENIOS cc',
                'set' => array('cc.PADRAO' => 'N'),
                'where' => array('1' => '1')
            );

            $updateAll = $this->connection->Update($makePattern);

            if ( $updateAll['success'] ) {
                //Deixa apenas o convênio informado como padrão
                $makeConvenioPattern = array(
                    'table' => 'CEDENTES_CONVENIOS cc',
                    'set' => array('cc.PADRAO' => 'S'),
                    'where' => array('cc.CONVENIO' => $convenio)
                );

                $updateConvenio = $this->connection->Update($makeConvenioPattern);
                return $updateConvenio['success'];
            } else {
                return false;
            }
        }

        /*
        * Deletar convênio
        */
        public function delete($convenio) {
            return $this->connection->Delete(array( 
                'table' => 'CEDENTES_CONVENIOS cc', 
                'columns' => array('cc.CONVENIO' => $convenio)
            ))['success'];
        }

        /*
        * Torna o convênio disponível para verificação do arquivo de reposição
        */
        public function checkFileReplacement($convenio, $check) {
            return $this->connection->Update(array(
                'table' => 'CEDENTES_CONVENIOS cc',
                'set' => array('cc.CHECAR_ARQUIVO_REPOSICAO' => $check),
                'where' => array('cc.CONVENIO' => $convenio)
            ))['success'];
        }

        public function getConvenioFileReplacement() {
            $data = $this->connection->Select(array(
                'table' => 'CEDENTES_CONVENIOS cc',
                'params' => 'cc.BANCO, cc.CONVENIO, cc.MANTENEDOR',
                'where' => array('cc.CHECAR_ARQUIVO_REPOSICAO' => 'S')
            ));

            $convenios = array();

            if ( count($data) > 0 ) {
                for ( $i = 0; $i < count($data['CONVENIO']); $i++ ) {
                    $convenios[] = array(
                        'BANCO' => $data['BANCO'][$i],
                        'CONVENIO' => $data['CONVENIO'][$i],
                        'MANTENEDOR' => $data['MANTENEDOR'][$i]
                    );
                }
            }
            
            return $convenios;
        }
    }