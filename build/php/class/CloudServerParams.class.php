<?php

    include_once 'dao/CloudServerDAO.class.php';

    class CloudServerParams {

        protected static $host;
        protected static $ftp_login;
        protected static $password;

        public function __construct() {
            $this->dao = new CloudServerDAO();
            $data = $this->dao->get();
            self::$host = $data['IP_SERVER'];
            self::$password = $data['PASSWORD_SERVER'];
            self::$ftp_login = $data['FTP_LOGIN'];
        }

        public function __destruct() {
            $this->dao = null;
        }

        public static function getHost() {
            return self::$host;
        }

        public static function getPassword() {
            return self::$password;
        }

        public static function getFTPLogin() {
            return self::$ftp_login;
        }

    }