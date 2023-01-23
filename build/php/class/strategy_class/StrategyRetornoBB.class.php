<?php
    
    error_reporting(0);
    date_default_timezone_set('America/Sao_Paulo');
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Util.class.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Convenio.class.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Customer.class.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/interfaces/IFileStrategy.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/interfaces/IRetornoStrategy.php';

    class StrategyRetornoBB implements IFileStrategy, IRetornoStrategy {

        public static $lote_counter = 0;

        public function __construct($data_arquivo, $convenio, $records) {
            $this->strategy = null;
            $this->data_arquivo = Util::FmtDate($data_arquivo, '5');
            $this->records = $records;
            $convenios = new Convenio();
            $customer = new Customer();
            
            $this->params = $convenios->getByConv($convenio);

            if ( is_null($this->params) ) {
                throw new Exception($convenio);
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
                    'razao_social' => iconv("UTF-8", "ISO-8859-1//IGNORE", strtoupper($params['NOMSAC']))
                );
            }
        }

        public function GenerateFileHeader() {
            $header = "001".Util::alignToRight('', 5).Util::alignToLeft('', 9).$this->customer['tipo_documento'].Util::alignToRight($this->customer['documento'], 14);
            $header .= Util::alignToRight($this->params['CONVENIO'], 9)."0014".substr($this->params['CARTEIRA'], 0, 2);
            $header .= Util::alignToRight($this->params['VARIACAO'], 3)."  ".Util::alignToRight($this->params['AGENCIA'], 6);
            $header .= Util::alignToRight($this->params['CONTA'], 13)." ".Util::alignToLeft(substr($this->customer['razao_social'], 0, 30), 30);
            $header .= Util::alignToLeft('BANCO DO BRASIL', 40)."2{$this->data_arquivo}".date('His')."00000008400000".Util::alignToLeft('', 69).PHP_EOL;

            return $header;
        }

        public function GenerateLoteHeader() {
            $header = "00100011T01".Util::alignToRight('043', 5).' '.$this->customer['tipo_documento'];
            $header .= Util::alignToRight($this->customer['documento'], 15).Util::alignToRight($this->params['CONVENIO'], 9);
            $header .= "0014".substr($this->params['CARTEIRA'], 0, 2).Util::alignToRight($this->params['VARIACAO'], 3)."  ";
            $header .= Util::alignToRight($this->params['AGENCIA'], 6).Util::alignToRight($this->params['CONTA'], 13)." ";
            $header .= Util::alignToLeft(substr($this->customer['razao_social'], 0, 30), 30).Util::alignToLeft('', 80)."00000000";
            $header .= date('dmY').Util::alignToLeft('', 41).PHP_EOL;

            return $header;
        }

        public function GenerateLoteTrailer() {
            $trailer = "00100015".Util::alignToLeft('', 9).Util::alignToRight('1', 6).Util::alignToLeft('', 217).PHP_EOL;

            return $trailer;
        }

        public function GenerateFileTrailer() {
            $trailer = "00199999".Util::alignToLeft('', 9).Util::alignToRight('1', 6);
            $trailer .= Util::alignToRight(count($this->records), 6)."000000";
            $trailer .= Util::alignToLeft('', 205).PHP_EOL;
            
            return $trailer;
        }

        public function getDetails($data) {
            return "{$this->getSegmentoT($data)}{$this->getSegmentoU($data)}";
        }

        public function getSegmentoT($data) {
            $segmento = "00100013".Util::alignToRight(++self::$lote_counter, 5)."T ".$data['COD_MOVIMENTO'].Util::alignToRight($data['AGENCIA'], 5);
            $segmento .= $data['AGENCIA_DV'].Util::alignToRight($data['CONTA_CORRENTE'], 12).$data['CONTA_CORRENTE_DV']." ";
            $segmento .= Util::alignToLeft($data['NOSSO_NUMERO'], 20)."1".Util::alignToLeft(substr($data['NOSSO_NUMERO'], 7), 15);
            $segmento .= Util::FmtDate($data['DATA_VCTO'], '5').Util::alignToRight(number_format($data['VALOR'], 2, '', ''), 15).$data['BANCO_RECEBEDOR'];
            $segmento .= Util::alignToRight($data['AGENCIA_RECEBEDORA'], 5).$data['AGENCIA_RECEBEDORA_DV'].Util::alignToLeft(substr($data['NOSSO_NUMERO'], 7), 25);
            $segmento .= $data['MOEDA'].Util::alignToRight('', 66).Util::alignToRight(number_format($data['VALOR_TARIFA'], 2, '', ''), 15);
            $segmento .= Util::alignToLeft('', 10).Util::alignToLeft($this->params['CARTEIRA'].$this->params['VARIACAO'].$this->params['CONVENIO'], 17).PHP_EOL;

            return $segmento;
        }

        public function getSegmentoU($data) {
            $segmento = "00100013".Util::alignToRight(++self::$lote_counter, 5)."U ".$data['COD_MOVIMENTO'];
            $segmento .= Util::alignToRight(number_format($data['VALOR_ENCARGOS'], 2, '', ''), 15);
            $segmento .= Util::alignToRight(number_format($data['VALOR_DESCONTO_CONCEDIDO'], 2, '', ''), 15);
            $segmento .= Util::alignToRight(number_format($data['VALOR_ABATIMENTO'], 2, '', ''), 15);
            $segmento .= Util::alignToRight('', 15).Util::alignToRight(number_format($data['VALOR_PAGO'], 2, '', ''), 15);
            $segmento .= Util::alignToRight(number_format($data['VALOR_CREDITADO'], 2, '', ''), 15).Util::alignToRight('', 30);
            $segmento .= Util::FmtDate($data['DATA_PGTO'], '5').Util::FmtDate($data['DATA_CREDITO'], '5');
            $segmento .= Util::alignToLeft('', 12).Util::alignToRight('', 15).Util::alignToLeft('', 60).PHP_EOL;
            
            return $segmento;
        }

        public function create() {
            $details = "";

            foreach ( $this->records as $billet ) {
                $details .= $this->getDetails($billet);
            }

            $content = "{$this->GenerateFileHeader()}{$this->GenerateLoteHeader()}$details";
            $content .= "{$this->GenerateLoteTrailer()}{$this->GenerateFileTrailer()}";

            return $content;
        }

    }