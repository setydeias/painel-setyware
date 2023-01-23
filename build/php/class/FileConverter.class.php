<?php
	
	/*
	* Converte arquivos do padrão CNAB400 para CNAB240
	* @author: Bruno Pontes
	*/

	include_once 'FileConverterDao.class.php';
	include_once 'Util.class.php';
	
	class FileConverter {

		public $header;
		public $detalhe;
		public $trailer;
		public $dao;
		public $hoje;
		public $lote_servico_header = "0000";
		public $lote_servico_lote = "0001";
		public $tipo_registro_header = "0";
		public $tipo_registro_lote = "1";
		public $tipo_operacao = "R";
		public $tipo_inscricao_empresa = "2";
		public $cobranca_cedente = "0014";
		public $versao_leiaute_header = "083";
		public $versao_leiaute_lote = "042";
		public $especie = "17"; //Recibo
		public $aceite = "N";
		private $inscricao_empresa;
		private $nome_empresa;
		private $carteira;
		private $variacao_carteira;
		private $path;

		/*
		* GETTERS E SETTERS
		*/

		public function SetInscricaoEmpresa($i) {
			$this->inscricao_empresa = $i;
		}

		public function SetNomeEmpresa($n) {
			$this->nome_empresa = $n;
		}

		public function SetCarteira($c) {
			$this->carteira = $c;
		}

		public function SetVariacaoCarteira($v) {
			$this->variacao_carteira = $v;
		}

		public function SetPath($p) {
			$this->path = $p;
		}

		public function GetInscricaoEmpresa() {
			return $this->inscricao_empresa;
		}

		public function GetNomeEmpresa() {
			return $this->nome_empresa;
		}

		public function GetCarteira() {
			return $this->carteira;
		}

		public function GetVariacaoCarteira() {
			return $this->variacao_carteira;
		}

		public function GetPath() {
			return $this->path;
		}

		/*
		* [FIM] GETTERS E SETTERS
		*/

		// ================================================================================================= //

		/*
		* CONSTRUCTOR PROPERTIES
		* $cnab400 Objeto do tipo CNAB400
		* $path Destino do arquivo gerado
		*/
		public function __construct(StrategyFileConverter $cnab400, $path) {
			$this->header = $cnab400->getHeader();
			$this->detalhe = $cnab400->getDetails();
			$this->hoje = date('d').date('m').date('Y'); //Capturando a data atual
			$this->SetPath($path);
			$this->SetInscricaoEmpresa('03377700000198');
			$this->SetNomeEmpresa('SETYDEIAS SERVICOS LTDA');
			$this->SetCarteira('17');
			$this->SetVariacaoCarteira('116');
			//DAO
			$this->dao = new FileConverterDao();
		}

		/*
		* Função que gera o arquivo
		*/
		public function Generate() {
			$header = $this->GetHeader();
			$detalhe = $this->GetDetalhes();
			$trailer = $this->GetTrailer();

			$file = $header.$detalhe.$trailer;

			try {
				//Cria o arquivo e escreve o texto
				$arquivo_gerado = "CONVERTIDO_".Util::FmtDate($this->hoje, '3')."_STY_".$this->header['Banco']."_".$this->dao->GetShipNumber().".REM";
				$fp = fopen($this->GetPath().$arquivo_gerado, "a");
				fwrite($fp, $file);
				fclose($fp);
				//Atualiza o número da remessa
				$this->dao->UpdateShipNumber( $this->dao->GetShipNumber() );
				//Copia o arquivo para a pasta de Originais
				if ( !copy($this->GetPath().$arquivo_gerado, "C:\\COBPOP\\Arquivos\\Remessas\\Registrar\\Processadas\\Originais\\$arquivo_gerado") ) :
					return false;
				endif;

				return true;
			} catch ( Exception $e ) {
				echo $e->getMessage();
				return false;
			}
		}

		/*
		* Cria os headers de arquivo e lote
		*/
		public function GetHeader() {	
			$header_file = $this->CreateFileHeader();
			$header_lote = $this->CreateLoteHeader();

			return $header_file.$header_lote;
		}

		/*
		* Cria os registros detalhe
		*/
		public function GetDetalhes() {
			$detalhe = "";
			$p = $this->GetSegmentoP();
			$q = $this->GetSegmentoQ();
			$r = $this->GetSegmentoR();

			//Separa cada linha em um índice de array
			$segmento_p = str_split($p, 240);
			$segmento_q = str_split($q, 240);
			$segmento_r = str_split($r, 240);

			//Concatenando os segmentos
			for ($i = 0; $i < count($this->detalhe); $i++) :
				$detalhe .= $segmento_p[$i].PHP_EOL;
				$detalhe .= $segmento_q[$i].PHP_EOL;
				$detalhe .= $segmento_r[$i].PHP_EOL;
			endfor;

			return $detalhe;
		}

		/*
		* Cria os trailers de arquivo e lote
		*/
		public function GetTrailer() {
			$trailer_lote = $this->CreateLoteTrailer();
			$trailer_file = $this->CreateFileTrailer();

			return $trailer_lote.$trailer_file;
		}

		public function CreateFileHeader() {
			$header_file = null;
			$header_file .= $this->header['Banco'];
			$header_file .= $this->lote_servico_header;
			$header_file .= $this->tipo_registro_header;
			$header_file .= str_pad('', 9, ' ', STR_PAD_RIGHT); //Uso Exclusivo FEBRABAN/CNAB
			$header_file .= $this->tipo_inscricao_empresa;
			$header_file .= $this->GetInscricaoEmpresa();
			$header_file .= str_pad($this->header['Convenio'], 9, '0', STR_PAD_LEFT);
			$header_file .= $this->cobranca_cedente;
			$header_file .= $this->GetCarteira();
			$header_file .= $this->GetVariacaoCarteira();
			$header_file .= str_pad("", 2, " ", STR_PAD_RIGHT);
			$header_file .= str_pad($this->header['Agencia'], 5, '0', STR_PAD_LEFT);
			$header_file .= $this->header['AgenciaDV'];
			$header_file .= str_pad($this->header['Conta'], 12, '0', STR_PAD_LEFT);
			$header_file .= $this->header['ContaDV'];
			$header_file .= str_pad('', 1, ' ', STR_PAD_RIGHT); //Dígito Verificador da Ag/Conta (Caso o dígito verificador tenha mais de 1 dígito)
			$header_file .= str_pad($this->nome_empresa, 30, ' ', STR_PAD_RIGHT);
			//Nome do banco
			switch ($this->header['Banco']) :
				case '001':
					$header_file .= str_pad('BANCO DO BRASIL S.A.', 30, ' ', STR_PAD_RIGHT);
				break;
				case '237':
					$header_file .= str_pad('BRADESCO', 30, ' ', STR_PAD_RIGHT);
				break;
				case '104':
					$header_file .= str_pad('CAIXA ECONOMICA FEDERAL', 30, ' ', STR_PAD_RIGHT);
				break;
				default:
					$header_file .= str_pad('BANCO NÃO ENCONTRADO', 30, ' ', STR_PAD_RIGHT);
				break;
			endswitch;
			$header_file .= str_pad('', 10, ' ', STR_PAD_RIGHT); //Uso Exclusivo FEBRABAN/CNAB
			$header_file .= $this->header['CodigoRemessaRetorno'];
			$header_file .= $this->hoje; //Data de Geração do Arquivo
			//$header_file .= (date('H') - 3).date('i').date('s'); //Hora de Geração do Arquivo
			$horaAtual = date('H') - 4;
			$header_file .= ($horaAtual < 10 ? '0'.$horaAtual : $horaAtual).date('i').date('s'); //Hora de Geração do Arquivo
			$header_file .= str_pad($this->dao->GetShipNumber(), 6, '0', STR_PAD_LEFT);
			$header_file .= $this->versao_leiaute_header; //Versão do Layout
			$header_file .= str_pad('', 5, '0', STR_PAD_RIGHT); //Densidade de Gravação do Arquivo
			$header_file .= str_pad('', 20, ' ', STR_PAD_RIGHT); //Para Uso Reservado do Banco
			$header_file .= str_pad('', 20, ' ', STR_PAD_RIGHT); //Para Uso Reservado da Empresa
			$header_file .= str_pad('', 29, ' ', STR_PAD_RIGHT); //Uso Exclusivo FEBRABAN / CNAB
			$header_file .= PHP_EOL;
			

			return $header_file;
		}

		public function CreateLoteHeader() {
			$header_lote = null;
			$header_lote .= $this->header['Banco'];
			$header_lote .= $this->lote_servico_lote;
			$header_lote .= $this->tipo_registro_lote;
			$header_lote .= $this->tipo_operacao;
			$header_lote .= '01'; //Tipo de Serviço
			$header_lote .= str_pad('', 2, ' ', STR_PAD_RIGHT); //Uso Exclusivo FEBRABAN/CNAB
			$header_lote .= $this->versao_leiaute_lote;
			$header_lote .= str_pad('', 1, ' ', STR_PAD_RIGHT); //Uso Exclusivo FEBRABAN/CNAB
			$header_lote .= $this->tipo_inscricao_empresa;
			$header_lote .= str_pad($this->GetInscricaoEmpresa(), 15, '0', STR_PAD_LEFT);
			$header_lote .= str_pad($this->header['Convenio'], 9, '0', STR_PAD_LEFT);
			$header_lote .= $this->cobranca_cedente;
			$header_lote .= $this->GetCarteira();
			$header_lote .= $this->GetVariacaoCarteira();
			$header_lote .= str_pad("", 2, " ", STR_PAD_RIGHT);
			$header_lote .= str_pad($this->header['Agencia'], 5, '0', STR_PAD_LEFT);
			$header_lote .= $this->header['AgenciaDV'];
			$header_lote .= str_pad($this->header['Conta'], 12, '0', STR_PAD_LEFT);
			$header_lote .= $this->header['ContaDV'];
			$header_lote .= str_pad('', 1, ' ', STR_PAD_RIGHT); //Dígito Verificador da Ag/Conta (Caso o dígito verificador tenha mais de 1 dígito)
			$header_lote .= str_pad($this->GetNomeEmpresa(), 30, ' ', STR_PAD_RIGHT);
			$header_lote .= str_pad('', 40, ' ', STR_PAD_RIGHT); //Mensagem 1
			$header_lote .= str_pad('', 40, ' ', STR_PAD_RIGHT); //Mensagem 2
			$header_lote .= str_pad($this->dao->GetShipNumber(), 8, '0', STR_PAD_LEFT);
			$header_lote .= $this->hoje;
			$header_lote .= str_pad('', 41, ' ', STR_PAD_RIGHT); //Uso Exclusivo FEBRABAN/CNAB
			$header_lote .= PHP_EOL;

			return $header_lote;
		}

		public function GetSegmentoP() {	

			$segmento_p = null;
			$aux = 1;

			for ($i = 0; $i < count($this->detalhe); $i++) :
				$segmento_p .= $this->detalhe[$i]['Banco'];
				$segmento_p .= $this->lote_servico_lote;
				$segmento_p .= "3"; //Tipo de Registro
				$segmento_p .= str_pad($aux, 5, '0', STR_PAD_LEFT);
				$segmento_p .= "P"; //Cód. Segmento do Registro Detalhe
				$segmento_p .= str_pad('', 1, ' ', STR_PAD_RIGHT);
				$segmento_p .= $this->detalhe[$i]['CodigoMovimentacao'];
				$segmento_p .= str_pad($this->detalhe[$i]['Agencia'], 5, '0', STR_PAD_LEFT);
				$segmento_p .= $this->detalhe[$i]['AgenciaDV'];
				$segmento_p .= str_pad($this->detalhe[$i]['Conta'], 12, '0', STR_PAD_LEFT);
				$segmento_p .= $this->detalhe[$i]['ContaDV'];
				$segmento_p .= str_pad('', 1, ' ', STR_PAD_RIGHT);
				$segmento_p .= str_pad($this->detalhe[$i]['NossoNumero'], 20, ' ', STR_PAD_RIGHT);
				$segmento_p .= "7"; //Código da Carteira
				$segmento_p .= "1"; //Forma de Cadastr. do Título no Banco
				$segmento_p .= "0"; //Tipo de Documento
				$segmento_p .= "2"; //Identificação da Emissão do Bloqueto
				$segmento_p .= "2"; //Identificação da Distribuição
				$segmento_p .= (strlen($this->detalhe[$i]['SeuNumero']) < 1) ? str_pad(substr($this->header['Convenio'], 7), 15, ' ', STR_PAD_RIGHT) : str_pad($this->detalhe[$i]['SeuNumero'], 15, ' ', STR_PAD_RIGHT);
				$segmento_p .= Util::FmtDate($this->detalhe[$i]['DataVcto'], '1');
				$segmento_p .= str_pad($this->detalhe[$i]['ValorTitulo'], 15, '0', STR_PAD_LEFT);
				$segmento_p .= str_pad('', 5, '0', STR_PAD_LEFT);
				$segmento_p .= str_pad('', 1, ' ', STR_PAD_RIGHT);
				$segmento_p .= $this->especie;
				$segmento_p .= $this->aceite;
				$segmento_p .= Util::FmtDate($this->detalhe[$i]['DataEmissao'], '1');
				$segmento_p .= "2"; //Código do Juros de Mora (valor ao mês)
				$segmento_p .= Util::FmtDate($this->detalhe[$i]['DataVcto'], '1'); //Data do Juros de Mora (Sempre igual a data do vencimento)
				$segmento_p .= str_pad($this->detalhe[$i]['Juros'], 15, '0', STR_PAD_LEFT);
				$segmento_p .= "1"; //Código do Desconto 1
				$segmento_p .= Util::FmtDate($this->detalhe[$i]['DataDesconto'], '1');
				$segmento_p .= str_pad($this->detalhe[$i]['ValorDesconto'], 15, '0', STR_PAD_LEFT);
				$segmento_p .= str_pad($this->detalhe[$i]['ValorIOF'], 15, '0', STR_PAD_LEFT);
				$segmento_p .= str_pad($this->detalhe[$i]['ValorAbatimento'], 15, '0', STR_PAD_LEFT);
				$segmento_p .= str_pad($this->detalhe[$i]['NossoNumero'], 25, ' ', STR_PAD_RIGHT);
				$segmento_p .= "3"; //Código para Protesto [3 -> Isento]
				$segmento_p .= str_pad('', 2, '0', STR_PAD_LEFT); //Número de Dias para Protesto
				$segmento_p .= str_pad('', 1, '0', STR_PAD_LEFT); //Código para Baixa/Devolução
				$segmento_p .= str_pad('', 3, '0', STR_PAD_LEFT); //Número de Dias para Baixa/Devolução
				$segmento_p .= "09"; //Moeda
				$segmento_p .= str_pad('', 10, '0', STR_PAD_LEFT); //Nº do Contrato da Operação de Créd.
				$segmento_p .= '2'; //Uso Exclusivo FEBRABAN/CNAB

				$aux += 3;
			endfor;


			return $segmento_p;
		}

		public function GetSegmentoQ() {
			$segmento_q = null;
			$aux = 2;
			for ($i = 0; $i < count($this->detalhe); $i++) :
				$segmento_q .= $this->detalhe[$i]['Banco'];
				$segmento_q .= $this->lote_servico_lote;
				$segmento_q .= "3"; //Tipo de Registro
				$segmento_q .= str_pad($aux, 5, '0', STR_PAD_LEFT);
				$segmento_q .= "Q"; //Cód. Segmento do Registro Detalhe
				$segmento_q .= str_pad('', 1, ' ', STR_PAD_RIGHT);
				$segmento_q .= $this->detalhe[$i]['CodigoMovimentacao'];
				$segmento_q .= intval($this->detalhe[$i]['TipoInscicaoSacado']);
				$segmento_q .= str_pad($this->detalhe[$i]['InscricaoSacado'], 15, '0', STR_PAD_LEFT);
				$segmento_q .= str_pad($this->detalhe[$i]['NomeSacado'], 40, ' ', STR_PAD_RIGHT);
				$segmento_q .= str_pad($this->detalhe[$i]['EnderecoSacado'], 40, ' ', STR_PAD_RIGHT);
				$segmento_q .= str_pad($this->detalhe[$i]['BairroSacado'], 15, ' ', STR_PAD_RIGHT);
				$segmento_q .= $this->detalhe[$i]['CEPSacado'];
				$segmento_q .= str_pad($this->detalhe[$i]['CidadeSacado'], 15, ' ', STR_PAD_RIGHT);
				$segmento_q .= $this->detalhe[$i]['UFSacado'];
				$segmento_q .= intval($this->detalhe[$i]['TipoInscricaoBeneficiario']);
				$segmento_q .= str_pad($this->detalhe[$i]['InscricaoBeneficiario'], 15, '0', STR_PAD_LEFT);
				$segmento_q .= str_pad(iconv('UTF-8', 'ASCII//TRANSLIT', $this->header['SacadorAvalista']), 40, ' ', STR_PAD_RIGHT); //Nome do cliente
				$segmento_q .= str_pad('', 3, '0', STR_PAD_LEFT); //Cód. Bco. Corresp. na Compensação
				$segmento_q .= str_pad('', 20, ' ', STR_PAD_RIGHT); //Nosso Nº no Banco Correspondente
				$segmento_q .= str_pad('', 8, ' ', STR_PAD_RIGHT); //Uso Exclusivo FEBRABAN/CNAB

				$aux += 3;
			endfor;

			return $segmento_q;
		}

		public function GetSegmentoR() {

			$segmento_r = null;
			$aux = 3;

			for ($i = 0; $i < count($this->detalhe); $i++) :
				$segmento_r .= $this->detalhe[$i]['Banco'];
				$segmento_r .= $this->lote_servico_lote;
				$segmento_r .= "3"; //Tipo de Registro
				$segmento_r .= str_pad($aux, 5, '0', STR_PAD_LEFT);
				$segmento_r .= "R"; //Cód. Segmento do Registro Detalhe
				$segmento_r .= str_pad('', 1, ' ', STR_PAD_RIGHT);
				$segmento_r .= $this->detalhe[$i]['CodigoMovimentacao'];
				$segmento_r .= str_pad('', 1, '0', STR_PAD_LEFT); //Código do Desconto 2
				$segmento_r .= str_pad('', 8, '0', STR_PAD_LEFT); //Data do Desconto 2
				$segmento_r .= str_pad('', 15, '0', STR_PAD_LEFT); //Desconto 2 Valor/Percentual a ser Concedido
				$segmento_r .= str_pad('', 1, '0', STR_PAD_LEFT); //Código do Desconto 3
				$segmento_r .= str_pad('', 8, '0', STR_PAD_LEFT); //Data do Desconto 3
				$segmento_r .= str_pad('', 15, '0', STR_PAD_LEFT); //Desconto 3 Valor/Percentual a ser Concedido
				$segmento_r .= $this->detalhe[$i]['CodigoMulta'];
				$segmento_r .= Util::FmtDate($this->detalhe[$i]['DataMulta'], '1');
				$segmento_r .= str_pad($this->detalhe[$i]['ValorMulta'], 15, '0', STR_PAD_LEFT);
				$segmento_r .= str_pad('', 10, ' ', STR_PAD_RIGHT); //Informação ao Sacado
				$segmento_r .= str_pad('', 40, ' ', STR_PAD_RIGHT); //Mensagem 3
				$segmento_r .= str_pad('', 40, ' ', STR_PAD_RIGHT); //Mensagem 4
				$segmento_r .= str_pad('', 20, ' ', STR_PAD_RIGHT); //Mensagem 4
				$segmento_r .= str_pad('', 8, '0', STR_PAD_LEFT); //Cód. Ocor. do Sacado
				$segmento_r .= str_pad('', 3, '0', STR_PAD_LEFT); //Cód. do Banco na Conta do Débito
				$segmento_r .= str_pad('', 5, '0', STR_PAD_LEFT); //Código da Agência do Débito
				$segmento_r .= str_pad('', 1, '0', STR_PAD_LEFT); //Verificador da Agência
				$segmento_r .= str_pad('', 12, '0', STR_PAD_LEFT); //Conta Corrente para Débito
				$segmento_r .= str_pad('', 1, '0', STR_PAD_LEFT); //Dígito Verificador da Conta
				$segmento_r .= str_pad('', 1, '0', STR_PAD_LEFT); //Dígito Verificador Ag/Conta
				$segmento_r .= str_pad('', 1, '0', STR_PAD_LEFT); //Aviso para Débito Automático
				$segmento_r .= str_pad('', 9, ' ', STR_PAD_RIGHT); //Uso Exclusivo FEBRABAN/CNAB

				$aux += 3;
			endfor;

			return $segmento_r;

		}

		public function CreateLoteTrailer() {

			$qtde_linhas_lote = ((count($this->detalhe) * 3) + 2);

			$trailer_lote = null;
			$trailer_lote .= $this->header['Banco'];
			$trailer_lote .= $this->lote_servico_lote;
			$trailer_lote .= "5"; //Tipo de Registro
			$trailer_lote .= str_pad('', 9, ' ', STR_PAD_RIGHT); //Uso Exclusivo FEBRABAN/CNAB
			$trailer_lote .= str_pad($qtde_linhas_lote, 6, '0', STR_PAD_LEFT);
			$trailer_lote .= str_pad('', 217, ' ', STR_PAD_RIGHT); //Uso Exclusivo FEBRABAN/CNAB
			$trailer_lote .= PHP_EOL;

			return $trailer_lote;
		}

		public function CreateFileTrailer() {
			$qtde_linhas_file = ((count($this->detalhe) * 3) + 4);

			$trailer_file = null;
			$trailer_file .= $this->header['Banco'];
			$trailer_file .= "9999"; //Lote de Serviço
			$trailer_file .= "9"; //Tipo de Registro
			$trailer_file .= str_pad('', 9, ' ', STR_PAD_RIGHT); //Uso Exclusivo FEBRABAN/CNAB
			$trailer_file .= str_pad('1', 6, '0', STR_PAD_LEFT); //Quantidade de Lotes do Arquivo
			$trailer_file .= str_pad($qtde_linhas_file, 6, '0', STR_PAD_LEFT); //Quantidade de Registros do Arquivo
			$trailer_file .= str_pad('', 6, ' ', STR_PAD_RIGHT); //Qtde de Contas p/ Conc. (Lotes)
			$trailer_file .= str_pad('', 205, ' ', STR_PAD_RIGHT).PHP_EOL; //Qtde de Contas p/ Conc. (Lotes)

			return $trailer_file;
		}

	}