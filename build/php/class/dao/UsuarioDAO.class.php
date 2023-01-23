<?php
    error_reporting(0);
    session_start();
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/FirebirdCRUD.class.php';

    class UsuarioDAO {

        public function __construct() {
            $this->con = new FirebirdCRUD();
        }

        public function wrap_data($data) {
            $context = array();

            if ( count($data) > 0 ) {
                foreach ( $data as $key => $value ) {
                    foreach ( $value as $index => $info ) {
                        $context[$index][$key] = $info;
                    }
                }
            }
            
            return $context;
        }

        public function get() {
            $data = $this->con->Select(array(
                'table' => 'USUARIOS u',
                'params' => 'u.ID_USUARIOS, u.NOME, u.USUARIO, u.SEXO, u.FOTO'
            ));

            return $this->wrap_data($data);
        }

        public function getById($user_id) {
            $data = $this->con->Select(array(
                'table' => 'USUARIOS u',
                'params' => 'u.ID_USUARIOS, u.NOME, u.USUARIO, u.SEXO, u.FOTO',
                'where' => array('u.ID_USUARIOS' => $user_id)
            ));

            return $this->wrap_data($data);
        }

        public function removePhoto($user_id) {
            $data = $this->con->Select(array(
                'table' => 'USUARIOS u',
                'params' => 'u.FOTO',
                'where' => array('u.ID_USUARIOS' => $user_id)
            ));

            if ( count($data) > 0 ) {
                $filename = $_SERVER['DOCUMENT_ROOT'].'/painel/build/images/perfil/'.$data['FOTO'][0];
                $data = $this->con->Update(array(
                    'table' => 'USUARIOS u',
                    'set' => array('u.FOTO' => null),
                    'where' => array('u.ID_USUARIOS' => $user_id)
                ));

                if ( $data['success'] ) {
                    unlink($filename);

                    $select = $this->con->Select(array(
                        'table' => 'USUARIOS u',
                        'params' => 'u.USUARIO',
                        'where' => "u.ID_USUARIOS = '$user_id'"
                    )); 

                    if ( $select['USUARIO'][0] === $_SESSION['login'] ) {
                        $_SESSION['foto'] = null;
                    }
                }

                return $data['success'];
            } else {
                return true;
            }
        }

        public function add($data) {

            $select = $this->con->Select(array(
                'table' => 'USUARIOS u',
                'params' => 'u.USUARIO',
                'where' => array('u.USUARIO' => $data['usuario'])
            ));

            if ( count($select) ) {
                return array('success' => false, 'status' => 'Usuário já existente');
            }
            
            $columns = array( 'NOME' => utf8_decode($data['nome']), 'USUARIO' => $data['usuario'], 'SEXO' => $data['sexo'], 'SENHA' => $data['password'] );

            if ( isset($data['avatar']) ) {
                $image_extension = PATHINFO($data['avatar']['name'], PATHINFO_EXTENSION);
                $avatar_name = uniqid('').'.'.$image_extension;
                $filename = $_SERVER['DOCUMENT_ROOT']."/painel/build/images/perfil/$avatar_name";
                $columns = array_merge($columns, array('FOTO' => $avatar_name));
            }

            $result = $this->con->Insert(array( 'table' => 'USUARIOS', 'columns' => $columns ));
            
            if ( $result['success'] && isset($data['avatar']) ) {
                if ( !move_uploaded_file($data['avatar']['tmp_name'], $filename) ) {
                    return array('success' => true, 'status' => 'Usuário inserido com sucesso, porém o upload da imagem não foi possível');
                }
            }

            return $result['success'];
        }

        public function edit($data) {
            /*
            * Verifica se o usuário informado já existe
            */
            $select = $this->con->Select(array(
                'table' => 'USUARIOS u',
                'params' => 'u.USUARIO',
                'where' => "u.USUARIO = '".$data['usuario']."' AND u.ID_USUARIOS <> '".$data['id_usuario']."'"
            ));

            if ( count($select) ) {
                return array('success' => false, 'status' => 'Usuário já existente');
            }
            
            /*
            * Verifica se a senha do usuário é correspondente
            */
            $senha_usuario = $this->con->Select(array(
                'table' => 'USUARIOS u',
                'params' => 'u.SENHA',
                'where' => "u.ID_USUARIOS = '".$data['id_usuario']."'"
            ))['SENHA'][0];

            if ( $senha_usuario !== $data['password'] ) {
                return array('success' => false, 'status' => 'Senha não confere');
            }
            
            $columns = array( 'NOME' => utf8_decode($data['nome']), 'USUARIO' => $data['usuario'], 'SEXO' => $data['sexo'], 'SENHA' => $data['password'] );

            if ( isset($data['avatar']) ) {
                $image_extension = PATHINFO($data['avatar']['name'], PATHINFO_EXTENSION);
                $avatar_name = uniqid('').'.'.$image_extension;
                $filename = $_SERVER['DOCUMENT_ROOT']."/painel/build/images/perfil/$avatar_name";
                $columns = array_merge($columns, array('FOTO' => $avatar_name));
            }

            $result = $this->con->Update(array( 'table' => 'USUARIOS', 'set' => $columns, 'where' => array('ID_USUARIOS' => $data['id_usuario']) ));
            
            if ( $result['success'] && isset($data['avatar']) ) {
                if ( !move_uploaded_file($data['avatar']['tmp_name'], $filename) ) {
                    return array('success' => true, 'status' => 'Usuário inserido com sucesso, porém o upload da imagem não foi possível');
                }
                
                $select = $this->con->Select(array(
                    'table' => 'USUARIOS u',
                    'params' => 'u.USUARIO',
                    'where' => "u.ID_USUARIOS = '".$data['id_usuario']."'"
                )); 

                if ( $select['USUARIO'][0] === $_SESSION['login'] ) {
                    $_SESSION['foto'] = $avatar_name;
                }
            }

            return $result['success'];
        }

        public function remove($user_id) {
            $select = $this->con->Select(array(
                'table' => 'USUARIOS u',
                'params' => 'u.FOTO',
                'where' => array('u.ID_USUARIOS' => $user_id)
            ));

            if ( count($select) === 0 ) {
                return array('success' => false, 'status' => 'Usuário não encontrado');
            }
            
            $remove = $this->con->Delete(array(
                'table' => 'USUARIOS u',
                'columns' => array('u.ID_USUARIOS' => $user_id)
            ));

            if ( $remove['success'] && !is_null($select['FOTO'][0]) ) {
                $filename = $_SERVER['DOCUMENT_ROOT'].'/painel/build/images/perfil/'.$select['FOTO'][0];
                if ( !unlink($filename) ) {
                    return array('success' => true, 'status' => 'Usuário removido mas a foto não foi encontrada');
                }
            }

            return $remove['success'];
        }

        public function changePassword($user_id, $old, $new) {
            $password = $this->con->Select(array(
                'table' => 'USUARIOS u', 'params' => 'u.SENHA', 'where' => array('u.ID_USUARIOS' => $user_id)
            ));

            $password = $password['SENHA'][0];
            
            if ( $password !== $old ) {
                return array('success' => false, 'status' => 'A senha atual está incorreta');
            }

            $update = $this->con->Update(array(
                'table' => 'USUARIOS u',
                'set' => array('u.SENHA' => $new),
                'where' => array('u.ID_USUARIOS' => $user_id)
            ));

            if ( !$update['success'] ) {
                return array('success' => false, 'status' => 'Erro ao trocar a senha, tente novamente');
            }

            return array('success' => true, 'status' => 'Senha alterada com sucesso');
        }

    }