<?php
    error_reporting(0);
    session_start();
    include_once 'class/FirebirdCRUD.class.php';

    try {
        $data = json_decode(file_get_contents('php://input'));
        $user = $data->user;
        $password = $data->password;
        $crud = new FirebirdCRUD();

        $data = $crud->Select(array(
            'table' => 'USUARIOS u',
            'params' => 'u.ID_USUARIOS, u.NOME, u.USUARIO, u.FOTO, u.SEXO',
            'where' => 'u.USUARIO = \''.$user.'\' AND u.SENHA = \''.$password.'\''
        ));

        if ( !count($data) ) {
            $info = array(
                'success' => false,
                'message' => 'Usuário ou senha incorretos, tente novamente'
            );
            session_destroy();
        } else {
            //Se estiver tudo OK
            //Cria as variáveis de sessão
            $_SESSION['id'] = $data['ID_USUARIOS'][0];
            $_SESSION['login'] = $data['USUARIO'][0];
            $_SESSION['nome']  = $data['NOME'][0];
            $_SESSION['foto']  = $data['FOTO'][0];
            $_SESSION['sexo']  = $data['SEXO'][0];

            $info = array(
                'success' => true
            );
        }

        echo json_encode($info);
    } catch ( Exception $e ) {
        echo $e->getMessage();
    }