<?php

    include_once '../class/MailParams.class.php';

    try {
        $MailParams = new MailParams();
        $data = json_decode(file_get_contents('php://input'))->data;
        $mail_sty_name = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $data->mail_sty_name);
        $mail_host_smtp = $data->mail_host_smtp;
        $mail_email = $data->mail_email;
        $mail_port = $data->mail_port;
        $mail_password = $data->mail_password;
        $mail_new_password = $data->mail_new_password;
        
        if ( $mail_sty_name === '' ) {
            echo json_encode(array('error' => 'Nome da empresa é obrigatório'));
            return http_response_code(400);
        }

        if ( $mail_host_smtp === '' ) {
            echo json_encode(array('error' => 'Host SMTP é obrigatório'));
            return http_response_code(400);
        }

        if ( $mail_port === '' ) {
            echo json_encode(array('error' => 'Porta é obrigatório'));
            return http_response_code(400);
        }

        if ( $mail_email === '' ) {
            echo json_encode(array('error' => 'Email é obrigatório'));
            return http_response_code(400);
        }

        //Verifica se os campos senha foram preenchidos
        if ( ($mail_password !== '' && $mail_new_password === '') || ($mail_password === '' && $mail_new_password !== '') ) {
            echo json_encode(array('error' => 'Ao informar uma senha, a outra é obrigatória'));
            return http_response_code(400);
        }

        $set = array('SMTP_HOST' => $mail_host_smtp, 'PORTA' => $mail_port, 'EMAIL' => $mail_email, 'NOME' => $mail_sty_name, 'SENHA' => $mail_password );

        if ( $mail_password !== '' ) {
            $current_password = $MailParams::_password();

            if ( $mail_password !== $current_password ) {
                echo json_encode(array('error' => 'A Senha Atual está incorreta'));
                return http_response_code(400);
            } else {
                $set = array_merge($set, array('SENHA' => $mail_new_password));
            }
        }

        $update = $MailParams->update($set);

        if ( !$update['success'] ) {
            echo json_encode(array('error' => 'Não foi possível atualizar os parâmetros, tente novamente'));
            return http_response_code(400);
        }

        echo json_encode(array('success' => true, 'message' => 'Parâmetros de email atualizados com sucesso'));
    } catch ( Exception $e ) {
        echo json_encode(array('error' => $e->getMessage()));
        return http_response_code(500);
    }