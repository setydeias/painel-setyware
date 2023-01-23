<?php
    $con = ibase_connect('localhost:C:\\Setydeias\\Setyware\\ADM77777\\ADM77777.GDB', 'SYSDBA', 'masterkey');
    $query = ibase_query($con, "SELECT sv.IP_SERVER, sv.FTP_LOGIN, sv.PASSWORD_SERVER FROM SERVIDOR_NUVENS_PARAMS sv");
    
    while ( $row = ibase_fetch_object($query) ) {
        $ip_server = $row->IP_SERVER;
        $login = $row->FTP_LOGIN;
        $password = $row->PASSWORD_SERVER;
    }
    
    
    ibase_close($con);
    define('FTP_HOST', $ip_server);
    define('FTP_LOGIN', $login);
    define('FTP_PASSWORD', $password);