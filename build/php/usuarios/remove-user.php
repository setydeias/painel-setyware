<?php
    session_start();
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Usuario.class.php';
    $user = new Usuario();
    $qtde_users = count($user->get());
    
    if ( $qtde_users === 1 ) {
        echo json_encode(array('success' => false, 'status' => 'Não é permitida a exclusão de todos os usuários do sistema'));
        return;
    }

    $user_id = json_decode(file_get_contents('php://input'));
    $usuario_sessao = $_SESSION['login'];
    $usuario = $user->getById($user_id)[0]['USUARIO'];
    $remove = $user->remove($user_id);

    if ( gettype($remove) === 'array' ) {
        $result = $remove;
    } else {
        $result = $remove
            ? array('success' => true, 'status' => 'Usuário removido com sucesso')
            : array('success' => false, 'status' => 'Erro ao remover usuário');
    }

    if ( $result['success'] && $usuario_sessao === $usuario ) {
        session_destroy();
        $result['redirect'] = true;
    }

    echo json_encode($result);