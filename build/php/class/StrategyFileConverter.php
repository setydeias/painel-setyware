<?php
	
	include_once 'StrategyCNAB4006Posicoes.class.php';
	include_once 'StrategyCNAB4007Posicoes.class.php';

	class StrategyFileConverter {

		private $strategy = null;

		public function __construct($s, $file, $fileType) {
			switch ( $s ) {
				case '7': //CBR641 -> Convênio de 7 posições
					$this->strategy = new StrategyCNAB4007Posicoes($file, $fileType);
				break;
				case '6': //CBR641 -> Convênio de 6 posições
					$this->strategy = new StrategyCNAB4006Posicoes($file, $fileType);
				break;
			}
		}

		public function getHeader() {
			return $this->strategy->GetHeaderInfo();
		}

		public function getDetails() {
			return $this->strategy->GetRegistrosDetalhe();
		}

	}