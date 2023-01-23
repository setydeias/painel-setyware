<?php

	include_once 'RemessaRegistradaDAO.class.php';
	include_once 'strategy_class/StrategyRemessaRegistrada.php';

	class RemessaRegistrada {

		public $cod_banco;
		public $banco;
		public $lote;
		public $agencia;
		public $conta;
		public $carteira;
		public $variacao;
		public $convenio;
		public $tipo_documento;
		public $documento;
		public $razao_social;
		public $versao_remessa;
		public $file;
		public $allParams;
		public $strategy;

		public function __construct($data) {
			$this->cod_banco = $data['cod_banco'];
			//Informações de acordo com o código do banco
			$bankName = "";
			$versao_remessa = "";
			$this->lote = "0001";
			$this->dao = new RemessaRegistradaDAO();
			$this->agencia = $data['agencia'];
			$this->conta = $data['conta'];
			$this->carteira = $data['carteira'];
			$this->variacao = $data['variacao'];
			$this->convenio = $data['convenio'];
			$this->tipo_documento = $data['tipo_documento'];
			$this->documento = $data['documento'];
			$this->razao_social = $data['razao_social'];
			switch ( $data['cod_banco'] ) {
				case '001':
					$bankName = "BANCO DO BRASIL S.A.";
					$versao_remessa = "042";
				break;
				case '104':
					$bankName = "CAIXA ECONOMICA FEDERAL";
					$versao_remessa = $this->convenio == '0264151' ? "050" : "040";
				break;
				case '237':
					$bankName = "BRADESCO";
					$versao_remessa = "040";
				break;
				case '756':
					$bankName = "SICOOB";
					$versao_remessa = "081";
				break;
				default:
					$bankName = "INDEFINIDO";
					$versao_remessa = "INDEFINIDO";
				break;
			}
			$this->banco = $bankName;
			$this->versao_remessa = $versao_remessa;
			$this->strategy = new StrategyRemessaRegistrada($this->cod_banco, $this->convenio);
			//Cria o arquivo
			$this->file = isset($data['file']) ? $data['file'] : "C:/A/Teste.txt";
			//Para passar as informações para as funções auxiliares
			$this->allParams = array('cod_banco' => $this->cod_banco, 'banco' => $this->banco, 'lote' => $this->lote,
				'agencia' => $this->agencia, 'conta' => $this->conta, 'carteira' => $this->carteira, 'variacao' => $this->variacao,
				'convenio' => $this->convenio, 'tipo_documento' => $this->tipo_documento, 'documento' => $this->documento, 'razao_social' => $this->razao_social,
				'versao_remessa' => $this->versao_remessa, 'file' => $this->file
			);
			$this->AddHeader($this->allParams);
		}

		public function __destruct() {}

		//Cria os cabeçalhos do arquivo
		public function AddHeader($params) {
			$fp = fopen($this->file, 'a');
			fwrite($fp, $this->GenerateFileHeader($params));
			fwrite($fp, $this->GenerateLoteHeader($params));
			fclose($fp);
		}

		//Insere os segmentos no arquivo
		public function AddSegment($segment) {
			$fp = fopen($this->file, 'a'); //Abre o arquivo
			if ( gettype($segment) == 'array' ) {
				//Loop no array para escrever cada segmento no arquivo
				for ( $i = 0 ; $i < count($segment) ; $i++ ) {
					fwrite($fp, $segment[$i]);
				}
			} else {
				fwrite($fp, $segment); //Escreve o segmento no arquivo
			}
			fclose($fp); //Fecha o arquivo
		}

		//Cria o trailer do arquivo
		public function AddTrailer($params) {
			$fp = fopen($this->file, 'a');
			fwrite($fp, $this->GenerateLoteTrailer($params));
			fwrite($fp, $this->GenerateFileTrailer($params));
			fclose($fp);
		}

		//Header de arquivo
		public function GenerateFileHeader($params) {
			return $this->strategy->GenerateFileHeader($params);
		}

		//Header de lote
		public function GenerateLoteHeader($params) {
			return $this->strategy->GenerateLoteHeader($params);
		}

		//Segmento P
		public function SegmentoP($params, $data) {
			return $this->strategy->SegmentoP($params, $data);
		}

		//Segmento Q
		public function SegmentoQ($params, $data) {
			return $this->strategy->SegmentoQ($params, $data);
		}

		//Segmento R
		public function SegmentoR($params, $data) {
			return $this->strategy->SegmentoR($params, $data);
		}

		//Trailer de lote
		public function GenerateLoteTrailer($params) {
			return $this->strategy->GenerateLoteTrailer($params);
		}

		//Trailer de arquivo
		public function GenerateFileTrailer($params) {
			return $this->strategy->GenerateFileTrailer($params);
		}

		//Incrementa o sequencial da remessa dos bancos informados em @banks
		public function UpdateSeqShipping(array $banks) {
			$this->dao->UpdateSeqShipping($banks);
		}

	}