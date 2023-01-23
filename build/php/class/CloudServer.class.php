<?php
    error_reporting(0);
    include_once 'CloudServerParams.class.php';
    include_once '../../../vendor/nicolab/php-ftp-client/src/FtpClient/FtpClient.php';
    include_once '../../../vendor/nicolab/php-ftp-client/src/FtpClient/FtpException.php';
    include_once '../../../vendor/nicolab/php-ftp-client/src/FtpClient/FtpWrapper.php';

    class CloudServer {

        public $ftp;

        public function __construct() {
            $this->ftp = new \FtpClient\FtpClient();
            $CloudServerParams = new CloudServerParams();
            $this->ftp_host = CloudServerParams::getHost();
            $this->ftp_login = CloudServerParams::getFTPLogin();
            $this->password = CloudServerParams::getPassword();
        }

        //Cria uma sessão FTP
        public function connect() {
            try {
                $this->ftp->connect($this->ftp_host);
                $this->ftp->login($this->ftp_login, $this->password);
                $this->ftp->pasv(true);
                
                return $this->ftp;
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        /*
        * Envia arquivos para o servidor
        */

        public function send($from, $to) {
            try {
                $status = $this->ftp->put($to, $from, FTP_BINARY) ? true : false;
                    
                return $status;
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        /*
        * Baixa os arquivos remotos para o diretório @read
        * Faz uma de cada arquivo para @original
        * @extensions são as extensões permitidas 
        */
        public function get($from, $to, array $extensions, $binary_mode = true) {
            try {
                $listFiles = $this->ftp->nlist($from, true);
                
                if ( count($listFiles) > 0 ) {
                    foreach ( $listFiles as $file ) {
                         //Se estiver dentro das extensões informadas no parâmetro @extensions
                        if ( in_array(pathinfo($file, PATHINFO_EXTENSION), $extensions) ) {
                            $fileName = basename($file); //Nome do arquivo
                            $this->ftp->get($to.$fileName, $from.$fileName, $binary_mode ? FTP_BINARY : FTP_ASCII);
                        }
                    }
                    return true;
                } 
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        /*
        * Conta a quantidade de arquivos de um determinado diretório
        * Caso exista @extensions conta apenas os arquivos com as extensões informadas na matriz
        */

        public function countDirFiles($from, array $extensions = null) {
            try {
                $listFiles = $this->ftp->count($from, 'file');
                
                if ( count($listFiles) > 0 ) return count($listFiles);
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        /*
        * Lista os arquivos em @from
        */

        public function listFiles($from, array $extensions = null) {
            try {
                $listFiles = $this->ftp->nlist($from, true);
                $files = array();
                
                if ( count($listFiles) > 0 ) {
                    for ( $i = 0 ; $i < count($listFiles) ; $i++ ) {
                        if ( !is_null($extensions) && !in_array(pathinfo($listFiles[$i], PATHINFO_EXTENSION), $extensions) ) continue;
                        $files[] = array(
                            'filename' => $listFiles[$i]
                        );
                    }
                }

                return $files;
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        /*
        * Deleta os arquivos na pasta informada em @from
        * Caso exista @extensions exclui apenas os arquivos dentro das extensões do array
        * Caso @filesToDelete seja informado, exclui apenas os arquivos inseridos no array
        */
        public function delete($from, array $extensions = null, array $filesToDelete = null) {
            try {
                $listFiles = $this->ftp->nlist($from, true);

                if ( count($listFiles) > 0 ) {
                    foreach ( $listFiles as $file ) {
                        $fileName = basename($file);
                        
                        if ( !is_null($filesToDelete) && !in_array($fileName, $filesToDelete) ) continue;
                        
                        if ( !is_null($extensions) && in_array(pathinfo($file, PATHINFO_EXTENSION), $extensions) ) {
                            $this->ftp->delete($from.$fileName);
                        }

                        if ( is_null($extensions) ) {
                            $this->ftp->delete($from.$fileName);
                        }
                    }
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        /*
        * Cria uma pasta no servidor
        */
        public function createDir($path, $recursive = false) {
            return $this->ftp->mkdir($path, $recursive);
        }
    }