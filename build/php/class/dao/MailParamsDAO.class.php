<?php

    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/FirebirdCRUD.class.php';

    class MailParamsDAO {

        public function __construct() {
            $this->crud = new FirebirdCRUD();
        }

        public function get() {
            try {
                $data = $this->crud->Select(array(
                    'table' => 'EMAIL_PARAMETROS ep',
                    'params' => 'ep.SMTP_HOST, ep.EMAIL, ep.SENHA, ep.NOME, ep.PORTA'
                ));

                for ( $i = 0; $i < count($data['SMTP_HOST']); $i++ ) {
                    return array(
                        'SMTP_HOST' => $data['SMTP_HOST'][$i],
                        'PORTA' => $data['PORTA'][$i],
                        'EMAIL' => $data['EMAIL'][$i],
                        'PASSWORD' => $data['SENHA'][$i],
                        'NAME' => $data['NOME'][$i]
                    );
                }
            } catch ( Exception $e ) {
                echo $e->getMessage();
            }
        }

        public function update($data) {
            try {
                return $this->crud->Update(array(
                    'table' => 'EMAIL_PARAMETROS ep',
                    'set' => array(
                        'ep.SMTP_HOST' => $data['SMTP_HOST'], 
                        'ep.EMAIL' => $data['EMAIL'],
                        'ep.SENHA' => $data['SENHA'], 
                        'ep.NOME' => $data['NOME'], 
                        'ep.PORTA' => $data['PORTA']
                    ),
                    'where' => array('1' => '1')
                ));
            } catch ( Exception $e ) {
                echo $e->getMessage();
            }
        }

    }