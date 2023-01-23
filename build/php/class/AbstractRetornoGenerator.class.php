<?php
    
    error_reporting(0);
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/DirManager.class.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Util.class.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/strategy_class/StrategyRetornoBB.class.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/strategy_class/StrategyRetornoCEF.class.php';

    class AbstractRetornoGenerator {

        public function __construct($data) {
            $this->data = $data;
            $this->strategy = null;
            $this->dir = new DirManager();
            $this->path = $this->dir->getDirs(array('RETORNOS_PROCESSADOS'))['RETORNOS_PROCESSADOS'][0];
            $this->dir->deleteFiles($this->path, array('ret', 'RET', 'srq', 'SRQ')); //Remove os arquivos que estão na pasta
            $this->groupedByDateAndBank = array();
            
            foreach ( $this->data['records'] as $record ) {
                $this->groupedByDateAndBank[$record['DATA_ARQUIVO']][$record['BANCO']][$record['CONVENIO']][] = $record;
            }
        }

        public function create() {
            $data = array();
            
            try {
                foreach ( $this->groupedByDateAndBank as $data_arquivo => $banco_data ) {
                    foreach ( $banco_data as $banco => $convenio_data ) {
                        foreach ( $convenio_data as $convenio => $records ) {
                            switch ( $banco ) {
                                case '001':
                                    $this->strategy = new StrategyRetornoBB($data_arquivo, $convenio, $records);
                                    break;
                                case '104':
                                    $this->strategy = new StrategyRetornoCEF($data_arquivo, $convenio, $records);
                                    break;
                                default:
                                    throw new Exception("[$banco] - Banco não permitido para a operação");
                            }

                            $content = $this->strategy->create();
                            $data_arquivo = Util::FmtDate($data_arquivo, '8');
                            $filename = "{$this->path}IEDCBR_{$data_arquivo}_{$this->data['customer']}_$banco"."_".Util::alignToRight($convenio, 7)."_000000.RET";
                            $data_arquivo = Util::FmtDate($data_arquivo, '14'); //Retorna a data do arquivo para o formato original
                            $fh = fopen($filename, 'w+');
                            $data[ $fh ? 'CREATED_FILES' : 'FAILED_FILES' ][] = $filename;
                            fwrite($fh, $content);
                            fclose($fh);
                        }
                    }
                }
                return $data;
            } catch ( Exception $e ) {
                $data['exceptions'][] = $e->getMessage();
                return $data;
            }
        }

    }