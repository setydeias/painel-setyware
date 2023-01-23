<?php
    error_reporting(0);
    include_once 'Util.class.php';
    include_once 'LocawebConnection.class.php';

    class Validador {

        public $con;

        public function __construct() {
            $this->con = LocawebConnection::getConnection();
        }

        //Insere um cliente
        public function Insert(array $data) {
            try {
                $codigo_cedente = $data['codigo_cedente'];
                $nome_cedente = strtoupper(Util::RemoverAcentos($data['nome_cedente']));
                $sigla = $data['sigla'];
                $site = $data['site'];
                $status = array();

                $stmt = $this->con->prepare("INSERT INTO validador_clientes (cod_repasse, nome_cliente, sigla_cliente, site) VALUES ('$codigo_cedente', '$nome_cedente', '$sigla', '$site')");

                if ( $stmt->execute() ) {
                    $status['success'] = true;
                    $status['message'] = "Cliente inserido com sucesso";
                } else {
                    $status['success'] = false;
                    $status['message'] = "Cliente não foi inserido no validador, informe ao suporte técnico";
                }

                return $status;
            } catch (Exception $e) {
                $status = array(
                    'success' => false,
                    'message' => "Cliente foi cadastrado no banco mas não foi inserido no validador, informe ao suporte técnico: ".$e->getMessage()
                    );
                
                return $status;
            }
        }

        //Atualizar informações do cliente
        public function Update(array $data) {
            try {
                $codigo_cedente = $data['codigo_cedente'];
                $nome_cedente = strtoupper(Util::RemoverAcentos($data['nome_cedente']));
                $sigla = $data['sigla'];
                $site = $data['site'];
                $status = array();

                $stmt = $this->con->prepare("UPDATE validador_clientes SET nome_cliente = '$nome_cedente', sigla_cliente = '$sigla', site = '$site' WHERE cod_repasse = '$codigo_cedente'");

                if ( $stmt->execute() ) {
                    $status['success'] = true;
                    $status['message'] = "Os dados do cliente foram atualizados no validador com sucesso";
                } else {
                    $status['success'] = false;
                    $status['message'] = "Os dados não foram atualizados no validador, informe ao suporte técnico";
                }

                return $status;
            } catch (Exception $e) {
                $status = array(
                    'success' => false,
                    'message' => "Os dados do cliente não foram atualizados no validador, informe ao suporte técnico: ".$e->getMessage()
                    );
                
                return $status;
            }
        }

        //Exclui um cliente
        public function Delete($codced) {
            try {
                $status = array();

                $stmt = $this->con->prepare("DELETE FROM validador_clientes WHERE cod_repasse = '$codced'");

                if ( $stmt->execute() ) {
                    $status['success'] = true;
                } else {
                    $status['success'] = false;
                    $status['message'] = "Não foi possível remover o cliente do validador";
                }

                return $status;
            } catch (Exception $e) {
                $status = array(
                    'success' => false,
                    'message' => "Não foi possível remover o cliente do validador: ".$e->getMessage()
                    );
                
                return $status;
            }
        }
    }
