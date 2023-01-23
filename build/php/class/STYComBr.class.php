<?php
    error_reporting(E_ALL);
    include_once 'Util.class.php';
    include_once 'Customer.class.php';
    include_once 'CloudServer.class.php';
    include_once 'LocawebConnection.class.php';
    include_once 'constants/locaweb_ftp.php';
    include_once '../../../vendor/nicolab/php-ftp-client/src/FtpClient/FtpClient.php';
    include_once '../../../vendor/nicolab/php-ftp-client/src/FtpClient/FtpException.php';
    include_once '../../../vendor/nicolab/php-ftp-client/src/FtpClient/FtpWrapper.php';

    class STYComBr {

        public $con;

        public function __construct() { 
            $this->con = LocawebConnection::getConnection();
            $this->customer = new Customer();
            $this->CloudServer = new CloudServer();
            $this->CloudServer->connect();
            $this->customer_image_path = "./public_html/comercial/clientes/upload_cliente";
            $this->ftp = new \FtpClient\FtpClient();
            $this->FTPconnect();
        }

        //Inicia uma sessão FTP
        public function FTPconnect() {
            try {
                $this->ftp->connect(LW_FTP_HOST);
                $this->ftp->login(LW_FTP_LOGIN, LW_FTP_PASSWORD);
                $this->ftp->pasv(true);
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        public function Insert($data) {
            try {
                $query = "INSERT INTO cliente_tmp (foto, cliente, responsavel, endereco, telefone1, descricao, email, url, area_atuacao, telefone2, desde, breveDesc, ativo, sigla, password) ";
                $query .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $cliente_desde = Util::FmtDate($data['cliente_desde'], '23');
                $pathname = $data['pathname'];
                $imagem = $data['imagem_cliente'];

                if ( !is_null($imagem) ) {
                    if ( !$this->ftp->put("$this->customer_image_path/$pathname", $imagem['tmp_name'], FTP_BINARY) ) {
                        return array('error' => 'Erro ao inserir imagem do cliente na internet');
                    }
                }
                
                $stmt = $this->con->prepare($query);
                $stmt->bindValue(1, is_null($imagem) ? $data['foto'] : $pathname, PDO::PARAM_STR);
                $stmt->bindValue(2, utf8_decode($data['cliente']), PDO::PARAM_STR);
                $stmt->bindValue(3, utf8_decode($data['responsavel']), PDO::PARAM_STR);
                $stmt->bindValue(4, utf8_decode($data['endereco']), PDO::PARAM_STR);
                $stmt->bindValue(5, $data['telefone1'], PDO::PARAM_STR);
                $stmt->bindValue(6, utf8_decode($data['cliente']), PDO::PARAM_STR);
                $stmt->bindValue(7, $data['email'], PDO::PARAM_STR);
                $stmt->bindValue(8, $data['site'], PDO::PARAM_STR);
                $stmt->bindValue(9, $data['area_atuacao'], PDO::PARAM_STR);
                $stmt->bindValue(10, $data['telefone2'], PDO::PARAM_STR);
                $stmt->bindValue(11, $cliente_desde, PDO::PARAM_STR);
                $stmt->bindValue(12, utf8_decode($data['cliente']), PDO::PARAM_STR);
                $stmt->bindValue(13, $data['status'], PDO::PARAM_STR);
                $stmt->bindValue(14, $data['sigla'], PDO::PARAM_STR);
                $stmt->bindValue(15, $data['password'], PDO::PARAM_STR);
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível inserir cliente no cadastro da internet');
                }

                return array('success' => true);
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function InsertUnidadeTemp($data) {
            try {
                $query = "INSERT INTO premio_unidade_temp (codigo_cliente, unidade, numero_sorteio, status, finalizado, sigla) ";
                $query .= "VALUES (?, ?, ?, ?, ?, ?)";
                                
                $stmt = $this->con->prepare($query);
                $stmt->bindValue(1, utf8_decode($data['codigo_cliente']), PDO::PARAM_STR);
                $stmt->bindValue(2, utf8_decode($data['unidade']), PDO::PARAM_STR);
                $stmt->bindValue(3, utf8_decode(null), PDO::PARAM_STR);
                $stmt->bindValue(4, utf8_decode(0), PDO::PARAM_STR);
                $stmt->bindValue(5, utf8_decode(0), PDO::PARAM_STR);
                $stmt->bindValue(6, utf8_decode($data['sigla']), PDO::PARAM_STR);
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível inserir a Unidade!');
                }


                $this->con = NULL;
                return array('success' => true);
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function deleteUnidadeTemp($data) {
            try {
                $query = "DELETE FROM premio_unidade_temp WHERE codigo_cliente = ? AND unidade = ? ";
                               
                $stmt = $this->con->prepare($query);
                $stmt->bindValue(1, utf8_decode($data['codigo_cliente']), PDO::PARAM_STR);
                $stmt->bindValue(2, utf8_decode($data['unidade']), PDO::PARAM_STR);
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível deletar a Unidade!');
                }


                $this->con = NULL;
                return array('success' => true);
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function listarUnidadeTemp($data) {
            try {
                $sql = "SELECT * FROM premio_unidade_temp WHERE codigo_cliente = {$data['codigo_cliente']}";
                $this->con->exec("SET CHARACTER SET utf8");
                $stmt = $this->con->query($sql);                               
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível listar a(s) Unidade(s)!');
                }
                
                $result = $stmt->setFetchMode(PDO::FETCH_ASSOC); 

                $list = array();

                foreach($stmt->fetchAll() as $row) {
                    array_push($list, $row);
                }

               return $list;
               
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function updateUnidadeTemp($data) {
            try {
                
                $query = "UPDATE premio_unidade_temp SET numero_sorteio = ?, finalizado = ? WHERE codigo_cliente = ? AND unidade = ? ";
                               
                $stmt = $this->con->prepare($query);
                $stmt->bindValue(1, utf8_decode($data['numero_sorteio']), PDO::PARAM_STR);
                $stmt->bindValue(2, utf8_decode(1), PDO::PARAM_STR);
                $stmt->bindValue(3, utf8_decode($data['codigo_cliente']), PDO::PARAM_STR);
                $stmt->bindValue(4, utf8_decode($data['unidade']), PDO::PARAM_STR);
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível adicionar o número de sorteio da Unidade!');
                }

                return array('success' => true);
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function ativarUnidadeTemp($data) {
            try {
                $query = "UPDATE premio_unidade_temp SET status = ?, condomino = ?, email = ?, contato = ? WHERE codigo_cliente = ? AND unidade = ? ";
                               
                $stmt = $this->con->prepare($query);
                $stmt->bindValue(1, utf8_decode(2), PDO::PARAM_STR);
                $stmt->bindValue(2, utf8_decode($data['condomino']), PDO::PARAM_STR);
                $stmt->bindValue(3, utf8_decode($data['email']), PDO::PARAM_STR);
                $stmt->bindValue(4, utf8_decode($data['contato']), PDO::PARAM_STR);
                $stmt->bindValue(5, utf8_decode($data['codigo_cliente']), PDO::PARAM_STR);
                $stmt->bindValue(6, utf8_decode($data['unidade']), PDO::PARAM_STR);
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível ativar a Unidade!');
                }

 
                $this->con = NULL;
                return array('success' => true);
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function desativarUnidadeTemp($data) {
            try {
                $query = "UPDATE premio_unidade_temp SET status = ?, condomino = Null, email = Null, contato = Null, email_status = Null WHERE codigo_cliente = ? AND unidade = ? ";
                               
                $stmt = $this->con->prepare($query);
                $stmt->bindValue(1, utf8_decode(0), PDO::PARAM_STR);
                $stmt->bindValue(2, utf8_decode($data['codigo_cliente']), PDO::PARAM_STR);
                $stmt->bindValue(3, utf8_decode($data['unidade']), PDO::PARAM_STR);
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível ativar a Unidade!');
                }

                $this->con = NULL;
                return array('success' => true);
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function confirmarContatoUnidadeTemp($data) {
            try {
                $query = "UPDATE premio_unidade_temp SET condomino = ?, email = ?, contato=? WHERE numero_sorteio = ? ";
                               
                $stmt = $this->con->prepare($query);
                $stmt->bindValue(1, utf8_decode($data['condomino']), PDO::PARAM_STR);
                $stmt->bindValue(2, utf8_decode($data['email']), PDO::PARAM_STR);
                $stmt->bindValue(3, utf8_decode($data['contato']), PDO::PARAM_STR);
                $stmt->bindValue(4, utf8_decode($data['numero_sorteio']), PDO::PARAM_STR);
                                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível Confirmar Contato!');
                }

                $this->con = NULL;
                return array('success' => true);
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function listarSorteios() {
            try {
                $sql = "SELECT * FROM premio_unidade_sorteios";
   
                $stmt = $this->con->query($sql);                               
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível listar o(s) Sorteio(s)!');
                }
                
                $result = $stmt->setFetchMode(PDO::FETCH_ASSOC); 

                $list = array();

                foreach($stmt->fetchAll() as $row) {
                    array_push($list, $row);
                }

               return $list;
               
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function listarSorteados() {
            try {
                $sql = "SELECT * FROM premio_unidade_sorteios WHERE status = 1";
   
                $stmt = $this->con->query($sql);                               
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível listar o(s) Sorteado(s)!');
                }
                
                $result = $stmt->setFetchMode(PDO::FETCH_ASSOC); 

                $list = array();

                foreach($stmt->fetchAll() as $row) {
                    array_push($list, $row);
                }

               return $list;
               
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function listarSorteioPorConcurso($data) {
            try {
                $sql = "SELECT * FROM premio_unidade_sorteios WHERE concurso = {$data['concurso']}";
   
                $stmt = $this->con->query($sql);                               
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível listar concurso!');
                }
                
                $result = $stmt->setFetchMode(PDO::FETCH_ASSOC); 

                $list = array();

                foreach($stmt->fetchAll() as $row) {
                    array_push($list, $row);
                }

               return $list;
               
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function listarSorteioPorBilhete($data) {
            try {
                $sql = "SELECT * FROM premio_unidade_sorteios WHERE bilhete = {$data['bilhete']}";
   
                $stmt = $this->con->query($sql);                               
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível listar o bilhete!');
                }
                
                $result = $stmt->setFetchMode(PDO::FETCH_ASSOC); 

                $list = array();

                foreach($stmt->fetchAll() as $row) {
                    array_push($list, $row);
                }

               return $list;
               
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function listarSorteioPorPeriodo($data) {
            try {
                $dataInicail = Util::FmtDate($data['dataInicial'], '23');
                $dataFinal = Util::FmtDate($data['dataFinal'], '23');

                $sql = "SELECT * FROM premio_unidade_sorteios WHERE data BETWEEN '$dataInicail' AND '$dataFinal' ORDER BY data";
   
                $stmt = $this->con->query($sql);                               
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível listar o bilhete!');
                }
                
                $result = $stmt->setFetchMode(PDO::FETCH_ASSOC); 

                $list = array();

                foreach($stmt->fetchAll() as $row) {
                    array_push($list, $row);
                }

               return $list;
               
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function InsertSorteio($data) {
            try {
                $query = "INSERT INTO premio_unidade_sorteios (concurso, data, bilhete) ";
                $query .= "VALUES (?, ?, ?)";
                
                //$data_sorteio = Util::FmtDate($data['data'], '23');

                $stmt = $this->con->prepare($query);
                $stmt->bindValue(1, $data['concurso'], PDO::PARAM_STR);
                $stmt->bindValue(2, $data['data'], PDO::PARAM_STR);
                $stmt->bindValue(3, $data['bilhete'], PDO::PARAM_STR);
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível inserir o Sorteio!');
                }

                $this->con = NULL;
                return array('success' => true);
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function InsertSorteado($data) {
            try {
                $query = "INSERT INTO premio_unidade_sorteios (concurso, data, bilhete, cliente, unidade, status, ganhador, contato, email) ";
                $query .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                //$data_sorteio = Util::FmtDate($data['data'], '23');

                $stmt = $this->con->prepare($query);
                $stmt->bindValue(1, $data['concurso'], PDO::PARAM_STR);
                $stmt->bindValue(2, $data['data'], PDO::PARAM_STR);
                $stmt->bindValue(3, $data['bilhete'], PDO::PARAM_STR);
                $stmt->bindValue(4, $data['cliente'], PDO::PARAM_STR);
                $stmt->bindValue(5, $data['unidade'], PDO::PARAM_STR);
                $stmt->bindValue(6, $data['status'], PDO::PARAM_STR);
                $stmt->bindValue(7, utf8_decode($data['ganhador']), PDO::PARAM_STR);
                $stmt->bindValue(8, $data['contato'], PDO::PARAM_STR);
                $stmt->bindValue(9, $data['email'], PDO::PARAM_STR);
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível inserir o Sorteado!');
                }

                $this->con = NULL;
                return array('success' => true);
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function deleteConcurso($data) {
            try {
                $query = "DELETE FROM premio_unidade_sorteios WHERE concurso = ? ";
                               
                $stmt = $this->con->prepare($query);
                $stmt->bindValue(1, $data['concurso'], PDO::PARAM_STR);
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível deletar o concurso!');
                }

                $this->con = NULL;
                return array('success' => true);
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        
        public function listarParams() {
            try {
                $sql = "SELECT * FROM premio_unidade_parametros";
   
                $stmt = $this->con->query($sql);                               
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível listar o Parametros de configuraçôes!');
                }
                
                $result = $stmt->setFetchMode(PDO::FETCH_ASSOC); 

                $list = array();

                foreach($stmt->fetchAll() as $row) {
                    array_push($list, $row);
                }

               return $list;
               
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function filtrarGanhador($data) {
            try {
                $sql = "SELECT
                            a.id,
                            a.codigo_cliente,
                            a.unidade,
                            a.numero_sorteio,
                            a.status,
                            a.finalizado,
                            a.sigla,
                            a.condomino,  
                            a.email,
                            a.contato,
                            a.email_status,
                            a.condomino_temp,
                            a.email_temp,
                            a.contato_temp,
                            b.descricao AS cliente	
                        FROM 
                            premio_unidade_temp AS a
                        INNER JOIN 
                            cliente_tmp AS b
                        ON a.sigla = b.sigla
            WHERE
                numero_sorteio ={$data['numero_sorteio']}";
                
                $this->con->exec("SET CHARACTER SET utf8");
                $stmt = $this->con->query($sql);                               
                
                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível filtrar o ganhador!');
                }
                
                $result = $stmt->setFetchMode(PDO::FETCH_ASSOC); 

                $list = array();

                foreach($stmt->fetchAll() as $row) {
                    array_push($list, $row);
                }

               return $list;
               
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function EditLinkLoteriaFederal($data) {
            try {
                $query = "UPDATE premio_unidade_parametros SET valor = ? WHERE descricao = ?";
                               
                $stmt = $this->con->prepare($query);
                $stmt->bindValue(1, $data['link'], PDO::PARAM_STR);
                $stmt->bindValue(2, 'link-loteria-federal', PDO::PARAM_STR);

                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível editar o link!');
                }


                $this->con = NULL;
                return array('success' => true);
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        public function EditConcurso($data) {
            try {
                $query = "UPDATE premio_unidade_sorteios SET concurso = ?, data = ?, bilhete = ? WHERE id = ?";
                               
                $stmt = $this->con->prepare($query);
                $stmt->bindValue(1, $data['concurso'], PDO::PARAM_STR);
                $stmt->bindValue(2, $data['data'], PDO::PARAM_STR);
                $stmt->bindValue(3, $data['bilhete'], PDO::PARAM_STR);
                $stmt->bindValue(4, $data['id'], PDO::PARAM_STR);

                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível editar o Concurso!');
                }


                $this->con = NULL;
                return array('success' => true);
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }

        /**
         * Atualização dos dados no site do cliente
         *
         * @param array $data
         * @return array
         */
        public function Update($data) {
            try {
                $cliente_desde = Util::FmtDate($data['cliente_desde'], '23');
                $pathname = $data['pathname'];
                $imagem = $data['imagem_cliente'];

                if ( !is_null($imagem) ) {
                    if ( !$this->ftp->put("$this->customer_image_path/$pathname", $imagem['tmp_name'], FTP_BINARY) ) {
                        return array('error' => 'Erro ao alterar imagem do cliente na internet');
                    }
                }

                $query = "UPDATE cliente_tmp SET cliente = ?, responsavel = ?, endereco = ?, telefone1 = ?, descricao = ?, email = ?, ";
                $query .= "url = ?, area_atuacao = ?, telefone2 = ?, desde = ?, breveDesc = ?, ativo = ?, sigla = ?, foto = ? WHERE sigla = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bindValue(1, $data['cliente'], PDO::PARAM_STR);
                $stmt->bindValue(2, $data['cliente'], PDO::PARAM_STR);
                $stmt->bindValue(3, $data['endereco'], PDO::PARAM_STR);
                $stmt->bindValue(4, $data['telefone1'], PDO::PARAM_STR);
                $stmt->bindValue(5, $data['cliente'], PDO::PARAM_STR);
                $stmt->bindValue(6, $data['email'], PDO::PARAM_STR);
                $stmt->bindValue(7, $data['site'], PDO::PARAM_STR);
                $stmt->bindValue(8, $data['area_atuacao'], PDO::PARAM_STR);
                $stmt->bindValue(9, $data['telefone2'], PDO::PARAM_STR);
                $stmt->bindValue(10, $cliente_desde, PDO::PARAM_STR);
                $stmt->bindValue(11, $data['cliente'], PDO::PARAM_STR);
                $stmt->bindValue(12, $data['status'], PDO::PARAM_STR);
                $stmt->bindValue(13, $data['sigla'], PDO::PARAM_STR);
                $stmt->bindValue(14, $pathname, PDO::PARAM_STR);
                $stmt->bindValue(15, $data['sigla'], PDO::PARAM_STR);

                if ( !$stmt->execute() ) {
                    return array('error' => 'Não foi possível atualizar o cadastro na internet');
                }
                
                return array('success' => true);
            } catch ( Exception $e ) {
                return array('error' => $e->getMessage());
            }
        }
        /**
         * Remove a imagem do cliente
         * 
         * @param string $user_id
         * @return array
         */
        public function removeImage($user_id) {
            $pathname = $this->customer->GetPathNameByCod($user_id);
            $sigla = substr($pathname, 0, 3);

            $query = "UPDATE cliente_tmp SET foto = '' WHERE sigla = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bindValue(1, $sigla, PDO::PARAM_STR);
            $stmt->execute();
            
            return array('success' => $this->ftp->delete("$this->customer_image_path/$pathname.jpg"));
        }

        /**
         * Reseta o password do cliente para senha default
         * 
         * @param string $sigla
         * @param string $codsac
         * @return array
         */
        public function resetPassword($sigla, $codsac) {
            $new_password = md5("sty$codsac");
            $query = "UPDATE cliente_tmp SET password = ? WHERE sigla = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bindValue(1, $new_password, PDO::PARAM_STR);
            $stmt->bindValue(2, $sigla, PDO::PARAM_STR);
            $stmt->execute();
            
            return array('success' => 'Senha resetada com sucesso');
        }

        /**
         * Remove o cliente do site
         *
         * @param string $pathname
         * @return array
         */
        public function Delete($pathname) {
            //Obtém o nome da foto
            $sigla = substr($pathname, 0, 3);
            $query = "SELECT foto FROM cliente_tmp WHERE sigla = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bindValue(1, $sigla, PDO::PARAM_STR);
            $stmt->execute();
            $image_name = $stmt->fetch(PDO::FETCH_OBJ);
            $result = array();
            
            //Remove a imagem do site
            if ( $image_name ) {
                $image_name = $image_name->foto;
                if ( !$this->ftp->delete("$this->customer_image_path/$image_name") ) {
                    $result['image_site_error'] = 'Não foi possível remover a imagem do cliente no site';
                }
            }
            //Remove a imagem da 2º via
            if ( !$this->CloudServer->delete('//xampp/htdocs/app/2via/sistema/imagens/cedentes//', null, array(substr($pathname, 3).'.gif')) ) {
                $result['image_2via_error'] = 'Não foi possível remover a imagem da 2º via';
            }

            $query = "DELETE FROM cliente_tmp WHERE sigla = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bindValue(1, strtoupper($sigla), PDO::PARAM_STR);
            
            if ( !$stmt->execute() ) {
                $result['error'] = 'Não foi possível remover o cadastro na internet';
                return $result;
            }

            $result['success'] = true;
            return $result;
        }

        public function uploadContaTransitoria(array $ct) {
            $customer = $ct['customer'];
            $file = $ct['file'];

            $path = "./public_html/contatransitoria/$customer/";
            return $this->ftp->put($path.basename($file), $file, FTP_BINARY);
        }

    }