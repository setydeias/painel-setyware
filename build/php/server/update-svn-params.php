<?php

    include_once '../class/FirebirdCRUD.class.php';

    try {
        $crud = new FirebirdCRUD();
        $data = json_decode(file_get_contents('php://input'))->data;
        $svn_ip = $data->svn_ip;
        $svn_login_ftp = $data->svn_login_ftp;
        $svn_password = $data->svn_password;
        $svn_new_password = $data->svn_new_password;
        
        if ( $svn_ip === '' ) {
            echo json_encode(array('error' => 'IP do Servidor é obrigatório'));
            return http_response_code(400);
        }

        if ( $svn_login_ftp === '' ) {
            echo json_encode(array('error' => 'Login FTP é obrigatório'));
            return http_response_code(400);
        }

        //Verifica se os campos senha foram preenchidos
        if ( ($svn_password !== '' && $svn_new_password === '') || ($svn_password === '' && $svn_new_password !== '') ) {
            echo json_encode(array('error' => 'Ao informar uma senha, a outra é obrigatória'));
            return http_response_code(400);
        }

        $set = array('snp.IP_SERVER' => $svn_ip, 'snp.FTP_LOGIN' => $svn_login_ftp );

        if ( $svn_password !== '' ) {
            $current_password = $crud->Select(array(
                'table' => 'SERVIDOR_NUVENS_PARAMS snp',
                'params' => 'snp.PASSWORD_SERVER'
            ))['PASSWORD_SERVER'][0];

            if ( $svn_password !== $current_password ) {
                echo json_encode(array('error' => 'A Senha Atual está incorreta'));
                return http_response_code(400);
            } else {
                $set = array_merge($set, array('snp.PASSWORD_SERVER' => $svn_new_password));
            }
        }

        $update = $crud->Update(array(
            'table' => 'SERVIDOR_NUVENS_PARAMS snp',
            'set' => $set,
            'where' => array('1' => '1')
        ));

        if ( !$update['success'] ) {
            echo json_encode(array('error' => 'Não foi possível atualizar os parâmetros, tente novamente'));
            return http_response_code(400);
        }
    } catch ( Exception $e ) {
        echo json_encode(array('error' => $e->getMessage()));
        return http_response_code(500);
    }