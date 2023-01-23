<?php

    error_reporting(0);
    include_once 'Customer.class.php';
    include_once 'DirManager.class.php';
    include_once 'Util.class.php';
	include_once '../../../vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
	include_once '../../../vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';

    interface IContaTransitoria {
        public static function create();
        public static function update();
    }

    class ContaTransitoria implements IContaTransitoria {
        
        public static $customer_sigla = null;
        public static $customer_data;
        public static $conta_transitoria;
        public static $modelo_ct;
        public static $customer_ct;
        
        public function __construct($customer_sigla) {
            $this->customer = new Customer();
            $this->dir = new DirManager();

            self::$customer_sigla = strtolower($customer_sigla);
            self::$customer_data = $this->customer->getData(self::$customer_sigla);
            self::$conta_transitoria = $this->dir->getDirs(array('CONTA_TRANSITORIA'))['CONTA_TRANSITORIA'][0];
            self::$modelo_ct = self::$conta_transitoria."modelo.xls";
        }

        public static function create() {
            $customer_path = self::$conta_transitoria."\\".self::$customer_sigla;
            self::$customer_ct = $customer_path."\\index-".strtoupper(self::$customer_sigla).".xls";

            if ( 
                   is_null(self::$customer_sigla)
                || is_dir($customer_path)
                || !mkdir($customer_path)
                || !copy(self::$modelo_ct, self::$customer_ct) 
            ) {
                return false;
            }
            
            ContaTransitoria::update();
            return true;
        }

        public static function update() {
            try {
                $sheet = PHPExcel_IOFactory::identify(self::$customer_ct);
                $objReader = PHPExcel_IOFactory::createReader($sheet);
                $objPHPExcel = $objReader->load(self::$customer_ct);
                $objWorksheet = $objPHPExcel->getActiveSheet();
                $transf_string = 'Transf online p/'.Util::getBankName(self::$customer_data['BANCO']).' Ag: '.self::$customer_data['AGENCIA'].'  CC: '.self::$customer_data['CONTA_CORRENTE'];
                $transf_string .= self::$customer_data['BANCO'] === '104' ? " Op: ".self::$customer_data['OPERACAO'] : "";
                $AC11 = '=IF($D11="'.$transf_string.'",$H11,0)';
                $AC12 = '=IF($D12="'.$transf_string.'",$H12,0)';

                $objWorksheet
                    ->setCellValue('A2', strtoupper(self::$customer_data['NOMSAC']))
                    ->setCellValue('B11', PHPExcel_Shared_Date::FormattedPHPToExcel(date('Y'), date('m'), date('d')))
                    ->setCellValue('E2', "Código Setydeias: ".strtoupper(self::$customer_sigla)." - ".str_pad(self::$customer_data['CODSAC'], 5, '0', STR_PAD_LEFT))
                    ->setCellValue('H2', 'Cliente desde: ' . Util::FmtDate(self::$customer_data['DATA_ASSOCIACAO'], '24'))
                    ->setCellValue('K3', self::$customer_data['BANCO'])
                    ->setCellValue('AC11', $AC11)
                    ->setCellValue('AC12', $AC12);
                
                $objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, $sheet );
                $objWriter->setPreCalculateFormulas(false);
                $objWriter->save(self::$customer_ct);
            } catch ( Exception $e ) {
                echo $e->getMessage();
            }
        }

    }