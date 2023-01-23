<?php

    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/dao/UsuarioDAO.class.php';

    class Usuario {

        public function __construct() {
            $this->dao = new UsuarioDAO();
        }

        public function get() {
            return $this->dao->get();
        }

        public function getById($user_id) {
            return $this->dao->getById($user_id);
        }

        public function removePhoto($user_id) {
            return $this->dao->removePhoto($user_id);
        }

        public function add($data) {
            return $this->dao->add($data);
        }

        public function edit($data) {
            return $this->dao->edit($data);
        }

        public function remove($data) {
            return $this->dao->remove($data);
        }

        public function changePassword($user_id, $old, $new) {
            return $this->dao->changePassword($user_id, $old, $new);
        }

    }