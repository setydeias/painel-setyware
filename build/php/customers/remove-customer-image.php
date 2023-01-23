<?php
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/CloudServer.class.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/STYComBr.class.php';

    $CloudServer = new CloudServer();
    $sty = new STYComBr();
    $user_id = json_decode(file_get_contents('php://input'));
    
    $remove_sty = $sty->removeImage($user_id);

    if ( !$remove_sty['success'] ) {
        return http_response_code(400);
    }

    $CloudServer->connect();
    $image_name = str_pad($user_id, 5, '0', STR_PAD_LEFT).'.gif';
    $remove_svn = $CloudServer->delete('//xampp/htdocs/app/2via/sistema/imagens/cedentes//', null, array($image_name));

    if ( !$remove_svn ) {
        return http_response_code(400);
    }

    return http_response_code(200);