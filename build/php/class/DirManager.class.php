<?php

    include_once 'dao/DirManagerDAO.class.php';
    include_once 'Util.class.php';

    class DirManager {

        public $dao;

        public function __construct() {
            $this->dao = new DirManagerDAO();
        }

        /*
        * Retorna os diretórios informados no array em @dirs var
        */

        public function getDirs(array $dirs) {
            return $this->dao->getDirs($dirs);
        }

        /*
        * Conta a quantidade de arquivos em um diretório
        */

        public function countFiles($dir, $extensions = null) {
            try {
                $listFiles = scandir($dir);
                $qtde = 0;

                if ( !is_null($extensions) ) {
                    foreach ( $listFiles as $file ) if ( file_exists($dir.$file) && in_array(pathinfo($file, PATHINFO_EXTENSION), $extensions) ) $qtde++;
                } else {
                    $qtde = count($listFiles);
                }

                return $qtde;
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        /*
        * Retorna os arquivos encontrados em @dir
        */

        public function getFiles($dir, $extensions = null) {
            try {
                $listFiles = scandir($dir);
                $filesToReturn = array();
                
                if ( !is_null($extensions) ) {
                    foreach ( $listFiles as $file ) if ( file_exists($dir.$file) && in_array(pathinfo($file, PATHINFO_EXTENSION), $extensions) ) $filesToReturn[] = $dir.$file;
                } else {
                    foreach ( $listFiles as $file ) if ( file_exists($dir.$file) ) $filesToReturn[] = $dir.$file;
                }

                return $filesToReturn;
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        /*
        * Exclui os arquivos dentro de uma pasta
        * Se o parâmetro @extensions for informado, apenas os arquivos daquela extensão serão excluídos dentro de @dir
        * Se não for informado, exclui todos os arquivos
        */
        
        public function deleteFiles($dir, $extensions = null) {
            try {
                $listFiles = scandir($dir);
                
                if ( !is_null($extensions) ) {
                    foreach ( $listFiles as $file ) if ( file_exists($dir.$file) && in_array(pathinfo($file, PATHINFO_EXTENSION), $extensions) ) unlink( $dir.$file );
                } else {
                    foreach ( $listFiles as $file ) if ( file_exists($dir.$file) ) unlink( $dir.$file );
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        public function copyFiles($file, $to) {
            try {
                if ( copy($file, $to) ) {
                    return true;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }  

        public function createConfigIni($data) {
            $path = $data['path'];
            $name = str_replace(' ', '_', trim(strtoupper(Util::RemoverAcentos($data['name']))));
            $sigla = $data['sigla'];
            $codigo_sacado = $data['codigo_sacado'];

            $config_ini = "[EMPRESAS]".PHP_EOL;
            $config_ini .= "$sigla=$name".PHP_EOL.PHP_EOL;
            $config_ini .= "[$name]".PHP_EOL;
            $config_ini .= "NOME=$name".PHP_EOL;
            $config_ini .= "BASE=$path$sigla$codigo_sacado\\$sigla$codigo_sacado.gdb".PHP_EOL;
            $config_ini .= "SIGLA=$sigla".PHP_EOL;
            $config_ini .= "CODIGO=$codigo_sacado".PHP_EOL.PHP_EOL;
            $config_ini .= "NOME_USADO_PES=3".PHP_EOL;
            $config_ini .= "NOME_USADO_COB=3".PHP_EOL.PHP_EOL;
            $config_ini .= "LERRET=0";

            $filename = "$path\\config_$sigla.ini";
            $fp = fopen($filename, 'w+');
            fwrite($fp, $config_ini);
            fclose($fp);
        }

        /*
        * Copia uma o conteúdo de uma pasta recursivamente
        */ 

        public function copyr($source, $dest, $permissions = 0755) {
            // Check for symlinks
            if (is_link($source)) {
                return symlink(readlink($source), $dest);
            }

            // Simple copy for a file
            if (is_file($source)) {
                return copy($source, $dest);
            }

            // Make destination directory
            if (!is_dir($dest)) {
                mkdir($dest, $permissions);
            }

            // Loop through the folder
            $dir = dir($source);
            while (false !== $entry = $dir->read()) {
                // Skip pointers
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                // Deep copy directories
                $this->copyr("$source/$entry", "$dest/$entry", $permissions);
            }

            // Clean up
            $dir->close();
            return true;
        }
    }