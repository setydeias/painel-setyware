<?php
    error_reporting(E_ALL);
    include_once 'constants/locaweb_ftp_com.php';
    include_once '../../../vendor/nicolab/php-ftp-client/src/FtpClient/FtpClient.php';
    include_once '../../../vendor/nicolab/php-ftp-client/src/FtpClient/FtpException.php';
    include_once '../../../vendor/nicolab/php-ftp-client/src/FtpClient/FtpWrapper.php';

    class STYCom {

        public $con;

        public function __construct() {
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

        public function uploadContaTransitoria(array $ct) {
            $customer = $ct['customer'];
            $file = $ct['file'];

            $path = "./web/contatransitoria/$customer/";
            return $this->ftp->put($path.basename($file), $file, FTP_BINARY);
        }

    }