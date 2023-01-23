<?php
	
	/*
	* Converter arquivos do padrão CNAB400 para CNAB240 com os dados da Setydeias
	* @author: Bruno Pontes
	*/

	include_once 'interfaces/IStrategyFileConverter.php';

	class StrategyCNAB4006Posicoes implements IStrategyFileConverter {

		public $file;
		public $fileType;
		public $tipo_operacao;
		public $codigo_remessa_retorno;
		public $RegistrosDetalhe = array();

		public function __construct($file, $fileType) {
			$this->file = $file;
			$this->fileType = $fileType;
			//Tipo de Operação
			if ($fileType == 'Retorno') :
				$this->tipo_operacao = 'T';
				$this->codigo_remessa_retorno = '2';
			elseif ($fileType == 'Remessa') :
				$this->tipo_operacao = 'R';
				$this->codigo_remessa_retorno = '1';
			endif;
		}

		/*
		* HEADER
		*/
		public function GetHeaderInfo() {

			return array(
				'Banco' => $this->GetBanco(),
				'TipoOperacao' => $this->tipo_operacao,
				'Convenio' => $this->GetConvenio(),
				'Agencia' => $this->GetAgencia(),
				'AgenciaDV' => $this->GetAgenciaDV(),
				'Conta' => $this->GetConta(),
				'ContaDV' => $this->GetContaDV(),
				'SacadorAvalista' => $this->GetSacadorAvalista(),
				'SequencialArquivo' => $this->GetSequencialArquivo(),
				'CodigoRemessaRetorno' => $this->codigo_remessa_retorno,
				'DataCriacao' => $this->GetDataCriacao()
			);
		}

		public function GetRegistrosDetalhe() {
			$countAux = 0;
			for ($i = 1; $i < count(file($this->file)) - 1; $i++) :
				$IDTransacao = substr(file($this->file)[$i], 0, 1);
				if ($IDTransacao == '1') :
					$this->RegistrosDetalhe[$countAux]['TipoInscricaoBeneficiario'] = substr(file($this->file)[$i], 1, 2); // OK
					$this->RegistrosDetalhe[$countAux]['InscricaoBeneficiario'] = substr(file($this->file)[$i], 3, 14); // OK
					$this->RegistrosDetalhe[$countAux]['Agencia'] = substr(file($this->file)[$i], 17, 4); // OK
					$this->RegistrosDetalhe[$countAux]['AgenciaDV'] = substr(file($this->file)[$i], 21, 1); // OK
					$this->RegistrosDetalhe[$countAux]['Conta'] = substr(file($this->file)[$i], 22, 8); // OK
					$this->RegistrosDetalhe[$countAux]['ContaDV'] = substr(file($this->file)[$i], 30, 1); // OK
					$this->RegistrosDetalhe[$countAux]['Convenio'] = substr(file($this->file)[$i], 31, 6); // OK
					$this->RegistrosDetalhe[$countAux]['NossoNumero'] = substr(file($this->file)[$i], 63, 11);  // OK
					$this->RegistrosDetalhe[$countAux]['VariacaoCarteira'] = substr(file($this->file)[$i], 91, 3);
					$this->RegistrosDetalhe[$countAux]['TipoCobranca'] = substr(file($this->file)[$i], 101, 5);
					$this->RegistrosDetalhe[$countAux]['Carteira'] = substr(file($this->file)[$i], 106, 2);
					$this->RegistrosDetalhe[$countAux]['CodigoMovimentacao'] = substr(file($this->file)[$i], 108, 2);
					$this->RegistrosDetalhe[$countAux]['SeuNumero'] = substr(file($this->file)[$i], 110, 10);
					$this->RegistrosDetalhe[$countAux]['DataVcto'] = substr(file($this->file)[$i], 120, 6);
					$this->RegistrosDetalhe[$countAux]['ValorTitulo'] = substr(file($this->file)[$i], 126, 13);
					$this->RegistrosDetalhe[$countAux]['Banco'] = substr(file($this->file)[$i], 139, 3);
					$this->RegistrosDetalhe[$countAux]['Especie'] = substr(file($this->file)[$i], 147, 2);
					$this->RegistrosDetalhe[$countAux]['Aceite'] = substr(file($this->file)[$i], 149, 1);
					$this->RegistrosDetalhe[$countAux]['DataEmissao'] = substr(file($this->file)[$i], 150, 6);
					$this->RegistrosDetalhe[$countAux]['Juros'] = substr(file($this->file)[$i], 160, 13);
					$this->RegistrosDetalhe[$countAux]['DataDesconto'] = substr(file($this->file)[$i], 173, 6);
					$this->RegistrosDetalhe[$countAux]['ValorDesconto'] = substr(file($this->file)[$i], 179, 13);
					$this->RegistrosDetalhe[$countAux]['ValorIOF'] = substr(file($this->file)[$i], 192, 13);
					$this->RegistrosDetalhe[$countAux]['ValorAbatimento'] = substr(file($this->file)[$i], 205, 13);
					$this->RegistrosDetalhe[$countAux]['TipoInscicaoSacado'] = substr(file($this->file)[$i], 218, 2);
					$this->RegistrosDetalhe[$countAux]['InscricaoSacado'] = substr(file($this->file)[$i], 220, 14);
					$this->RegistrosDetalhe[$countAux]['NomeSacado'] = substr(file($this->file)[$i], 234, 37);
					$this->RegistrosDetalhe[$countAux]['EnderecoSacado'] = substr(file($this->file)[$i], 274, 40);
					$this->RegistrosDetalhe[$countAux]['BairroSacado'] = substr(file($this->file)[$i], 314, 12);
					$this->RegistrosDetalhe[$countAux]['CEPSacado'] = substr(file($this->file)[$i], 326, 8);
					$this->RegistrosDetalhe[$countAux]['CidadeSacado'] = substr(file($this->file)[$i], 334, 15);
					$this->RegistrosDetalhe[$countAux]['UFSacado'] = substr(file($this->file)[$i], 349, 2);
					$this->RegistrosDetalhe[$countAux]['NumDiasProtesto'] = substr(file($this->file)[$i], 391, 2);
					$this->RegistrosDetalhe[$countAux]['SequencialRegistro'] = substr(file($this->file)[$i], 394, 6);
				elseif ($IDTransacao == '5') :
					$this->RegistrosDetalhe[$countAux]['CodigoMulta'] = substr(file($this->file)[$i], 3, 1);
					$this->RegistrosDetalhe[$countAux]['DataMulta'] = substr(file($this->file)[$i], 4, 6);
					$this->RegistrosDetalhe[$countAux]['ValorMulta'] = substr(file($this->file)[$i], 10, 12);
					//Fundindo as Informações
					$this->RegistrosDetalhe[$countAux - 1] = array_merge($this->RegistrosDetalhe[$countAux - 1], $this->RegistrosDetalhe[$countAux]);
					$countAux--;
				endif;
				$countAux++;
			endfor;
			//Retira o último elemento do array (resto do IDTransacao do tipo 5)
			$last = array_pop($this->RegistrosDetalhe);
			return $this->RegistrosDetalhe;
		}

		/*
		* AUX FUNCTIONS
		*/

		//==================> HEADER <==================

		public function GetBanco() {
			return mb_substr(file($this->file)[0], 76, 3);
		}

		public function GetConvenio() {
			$convenio = null;

			if ($this->fileType == 'Retorno') :
				$convenio = mb_substr(file($this->file)[0], 149, 7);
			elseif ($this->fileType == 'Remessa') :
				$convenio = mb_substr(file($this->file)[0], 40, 6);
			endif;

			return $convenio;
		}

		public function GetAgencia() {
			return mb_substr(file($this->file)[0], 26, 4);	
		}
		
		public function GetAgenciaDV() {
			return mb_substr(file($this->file)[0], 30, 1);
		}
		
		public function GetConta() {
			return mb_substr(file($this->file)[0], 31, 8);
		}
		
		public function GetContaDV() {
			return mb_substr(file($this->file)[0], 39, 1);
		}

		public function GetSacadorAvalista() {
			return mb_substr(file($this->file)[0], 46, 30);	
		}

		public function GetSequencialArquivo() {
			$sequencial = null;

			if ($this->fileType == 'Retorno') :
				$sequencial = mb_substr(file($this->file)[0], 107, 7);
			elseif ($this->fileType == 'Remessa') :
				$sequencial = mb_substr(file($this->file)[0], 100, 7);
			endif;

			return $sequencial;
		}
		
		public function GetDataCriacao() {
			return mb_substr(file($this->file)[0], 94, 6);
		}
		
	}