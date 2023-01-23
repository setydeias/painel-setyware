<?php
    
    error_reporting(0);
    date_default_timezone_set('America/Sao_Paulo');
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Util.class.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/interfaces/IFileStrategy.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/interfaces/IRetornoStrategy.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Convenio.class.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Customer.class.php';

    class StrategyRetornoCEF implements IFileStrategy, IRetornoStrategy {

        public static $lote_counter = 0;

        public function __construct($data_arquivo, $convenio, $records) {
            $this->strategy = null;
            $this->data_arquivo = Util::FmtDate($data_arquivo, '5');
            $this->records = $records;
            $convenios = new Convenio();
            $customer = new Customer();
            
            $this->params = $convenios->getByConv($convenio);

            if ( is_null($this->params) ) {
                throw new Exception((int) $convenio);
            }
            
            $mantenedor = $this->params['MANTENEDOR'];
            if ( $mantenedor === 'STY' ) {
                $this->customer = array(
                    'tipo_documento' => Setydeias::$tipo_documento,
                    'documento' => Setydeias::$cnpj,
                    'razao_social' => Setydeias::$razao_social
                );
            } else {
                $params = $customer->getData($mantenedor);
                $this->customer = array(
                    'tipo_documento' => $params['TPDOCSAC'],
                    'documento' => $params['DOCSAC'],
                    'razao_social' => strtoupper($params['NOMSAC'])
                );
            }
        }

        public function GenerateFileHeader() {
            $header = "104".Util::alignToRight('', 5).Util::alignToLeft('', 9).$this->customer['tipo_documento'].Util::alignToRight($this->customer['documento'], 14);
            $header .= Util::alignToRight('', 20).Util::alignToRight($this->params['AGENCIA'], 6).Util::alignToLeft($this->params['CONVENIO'], 6);
            $header .= Util::alignToRight('', 8).Util::alignToLeft(substr($this->customer['razao_social'], 0, 30), 30);
            $header .= Util::alignToLeft('CAIXA ECONOMICA FEDERAL', 30).Util::alignToLeft('', 10)."1{$this->data_arquivo}".date('His')."00000010100000".Util::alignToLeft('', 69).PHP_EOL;

            return $header;
        }

        public function GenerateLoteHeader() {
            $header = "10400011T01  060 ".$this->customer['tipo_documento'].Util::alignToRight($this->customer['documento'], 15);
            $header .= Util::alignToLeft($this->params['CONVENIO'], 6).Util::alignToRight('', 14).Util::alignToRight($this->params['AGENCIA'], 6);
            $header .= Util::alignToLeft($this->params['CONVENIO'], 6).Util::alignToRight('', 8).Util::alignToLeft(substr($this->customer['razao_social'], 0, 30), 30);
            $header .= Util::alignToLeft('', 80)."00000000".date('dmY').Util::alignToRight('', 8).Util::alignToLeft('', 33).PHP_EOL;

            return $header;
        }

        public function GenerateLoteTrailer() {
            $trailer = "10400015".Util::alignToLeft('', 9).Util::alignToRight(count($this->data[$this->params['CONVENIO']]), 6);
            $trailer .= Util::alignToRight('', 100).Util::alignToLeft('', 117).PHP_EOL;

            return $trailer;
        }

        public function GenerateFileTrailer() {
            $trailer = "10499999".Util::alignToLeft('', 9).Util::alignToRight('1', 6);
            $trailer .= Util::alignToRight(count($this->records), 6)."000000";
            $trailer .= Util::alignToLeft('', 205).PHP_EOL;
            
            return $trailer;
        }

        public function getDetails($data) {
            return "{$this->getSegmentoT($data)}{$this->getSegmentoU($data)}";
        }

        public function getSegmentoT($data) {
            $segmento = "10400013".Util::alignToRight(++self::$lote_counter, 5)."T ".$data['COD_MOVIMENTO'].Util::alignToRight($data['AGENCIA'], 5);
            $segmento .= $data['AGENCIA_DV'].Util::alignToLeft($this->params['CONVENIO'], 6)."0000000  ".( (int) $this->params['CONVENIO'] > 60000 ? '9' : '0' )."14";
            $segmento .= Util::alignToLeft(substr($data['NOSSO_NUMERO'], 0, 15), 15).'01'.Util::alignToLeft(substr($data['NOSSO_NUMERO'], 6), 11).Util::alignToLeft('', 4);
            $segmento .= Util::FmtDate($data['DATA_VCTO'], '5').Util::alignToRight(number_format($data['VALOR'], 2, '', ''), 15).$data['BANCO_RECEBEDOR'];
            $segmento .= Util::alignToRight($data['AGENCIA_RECEBEDORA'], 5).$data['AGENCIA_RECEBEDORA_DV'].Util::alignToLeft(substr($data['NOSSO_NUMERO'], 6), 25);
            $segmento .= $data['MOEDA'].Util::alignToRight('', 16).Util::alignToLeft('', 50).Util::alignToRight(number_format($data['VALOR_TARIFA'], 2, '', ''), 15);
            $segmento .= Util::alignToLeft('', 27).PHP_EOL;

            return $segmento;
        }

        public function getSegmentoU($data) {
            $segmento = "10400013".Util::alignToRight(++self::$lote_counter, 5)."U ".$data['COD_MOVIMENTO'];
            $segmento .= Util::alignToRight(number_format($data['VALOR_ENCARGOS'], 2, '', ''), 15);
            $segmento .= Util::alignToRight(number_format($data['VALOR_DESCONTO_CONCEDIDO'], 2, '', ''), 15);
            $segmento .= Util::alignToRight(number_format($data['VALOR_ABATIMENTO'], 2, '', ''), 15);
            $segmento .= Util::alignToRight('', 15).Util::alignToRight(number_format($data['VALOR_PAGO'], 2, '', ''), 15);
            $segmento .= Util::alignToRight(number_format($data['VALOR_CREDITADO'], 2, '', ''), 15).Util::alignToRight('', 30);
            $segmento .= Util::FmtDate($data['DATA_PGTO'], '5').Util::FmtDate($data['DATA_CREDITO'], '5').'0000'.Util::FmtDate($data['DATA_PGTO'], '5');
            $segmento .= Util::alignToRight('', 75).PHP_EOL;
            
            return $segmento;
        }

        public function create() {
            $details = "";

            foreach ( $this->records as $billet ) {
                $details .= $this->getDetails($billet);
            }

            $content = "{$this->GenerateFileHeader()}{$this->GenerateLoteHeader()}";
            $content .= "$details";
            $content .= "{$this->GenerateLoteTrailer()}{$this->GenerateFileTrailer()}";

            return $content;
        }

    }