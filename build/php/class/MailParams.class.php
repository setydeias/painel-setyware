<?php

    include_once 'dao/MailParamsDAO.class.php';

    class MailParams {

        protected static $mail;
        protected static $password;
        protected static $smtp_host;
        protected static $port;
        protected static $name;

        public function __construct() {
            $this->dao = new MailParamsDAO();
            $data = $this->dao->get();
            self::$mail = $data['EMAIL'];
            self::$password = $data['PASSWORD'];
            self::$smtp_host = $data['SMTP_HOST'];
            self::$name = $data['NAME'];
            self::$port = $data['PORTA'];
        }

        public static function get() {
            return array(
                'EMAIL' => self::$mail,
                'SMTP_HOST' => self::$smtp_host,
                'NAME' => self::$name,
                'PORT' => self::$port
            );
        }

        public function update($data) {
            return $this->dao->update($data);
        }

        public static function _mail() {
            return self::$mail;
        }

        public function _password() {
            return self::$password;
        }

        public function _smtp_host() {
            return self::$smtp_host;
        }

        public function _port() {
            return self::$port;
        }

        public function _name() {
            return self::$name;
        }
    }