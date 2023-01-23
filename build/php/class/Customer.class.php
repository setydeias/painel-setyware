<?php

	include_once 'FirebirdCRUD.class.php';
	include_once 'DirManager.class.php';

	class Customer {

		public $con;

		public function __construct() {
			$this->con = new FirebirdCRUD();
			$this->dir = new DirManager();
		}

		public function __destruct() {
			$this->con = null;
		}

		public function getData($context) {
			$dataToSelect = array(
				'table' => 'SACADOS s',
				'params' => 's.CODSAC, s.CODAUXSAC, s.TPDOCSAC, s.DOCSAC, s.NOMSAC, s.RESPONSAVEL, s.DTNASCSAC_DIA, s.DTNASCSAC_MES, s.DTNASCSAC_ANO, s.DATA_ASSOCIACAO, s.ENDSAC, s.CIDSAC, s.UFSAC, s.CEP, s.DICAEND, s.CLI_SIGLA, s.REPASSE, s.BANCO, s.AGENCIA, s.OPERACAO, s.CONTA_CORRENTE',
				'where' => array('s.CLI_SIGLA' => strtoupper($context))
			);
			
			$data = $this->con->Select($dataToSelect);
		
			if ( count($data) === 0 ) return array();

			$customer = array();
			
			for ( $i = 0; $i < count($data['CODSAC']); $i++ ) {
				$customer[] = array(
					'CODSAC' => $data['CODSAC'][$i],
					'CODAUXSAC' => $data['CODAUXSAC'][$i],
					'TPDOCSAC' => $data['TPDOCSAC'][$i],
					'DOCSAC' => $data['DOCSAC'][$i],
					'NOMSAC' => $data['NOMSAC'][$i],
					'RESPONSAVEL' => $data['RESPONSAVEL'][$i],
					'DTNASCSAC_DIA' => $data['DTNASCSAC_DIA'][$i],
					'DTNASCSAC_MES' => $data['DTNASCSAC_MES'][$i],
					'DTNASCSAC_ANO' => $data['DTNASCSAC_ANO'][$i],
					'DATA_ASSOCIACAO' => $data['DATA_ASSOCIACAO'][$i],
					'ENDSAC' => $data['ENDSAC'][$i],
					'CIDSAC' => $data['CIDSAC'][$i],
					'UFSAC' => $data['UFSAC'][$i],
					'CEP' => $data['CEP'][$i],
					'DICAEND' => $data['DICAEND'][$i],
					'CLI_SIGLA' => $data['CLI_SIGLA'][$i],
					'REPASSE' => $data['REPASSE'][$i],
					'BANCO' => $data['BANCO'][$i],
					'AGENCIA' => $data['AGENCIA'][$i],
					'OPERACAO' => $data['OPERACAO'][$i],
					'CONTA_CORRENTE' => $data['CONTA_CORRENTE'][$i]
				);
			}
			
			return $customer[0];
		}

		public function getCustomerByOurNumber($sigla, $number) {
			try {
				$data = array('customer' => 'NÃO IDENTIFICADO');
				$pathname = $this->GetPathNameBySigla($sigla);
				$laboratorio_folder = $this->dir->getDirs(array('LABORATORIO'))['LABORATORIO'][0];
				$host = "$laboratorio_folder$pathname\\$pathname.gdb";
				$con = ibase_connect($host, "SYSDBA", "masterkey");
				$query = "SELECT FIRST 1 s.NOMSAC FROM TITULOS t LEFT JOIN SACADOS s ON s.CODSAC = t.CODSAC WHERE t.NOSSONUM = $number";
				$result = ibase_query($con, $query);
				
				while ( $row = ibase_fetch_object($result) ) {
					$data['customer'] = utf8_encode($row->NOMSAC);
				}
				
				return $data;
				ibase_free_result($result);
				ibase_close($con);
			} catch ( Exception $e ) {
				return "NÃO IDENTIFICADO";
			}
		}

		/*
		* Obtém a sigla do cliente a partir do código do sacado
		*/

		public function GetSiglaByCodSac($codsac) {
			$data = null;
			
			$dataToSelect = array(
				'table' => 'SACADOS s',
				'params' => 's.CLI_SIGLA',
				'where' => array(
					's.REPASSE_VARIACAO' => $codsac
					)
				);

			$data = $this->con->Select($dataToSelect);
			$data = count($data) < 1 ? "INDEFINIDO" : $data['CLI_SIGLA'][count($data['CLI_SIGLA']) - 1];

			return $data;
		}

		/*
		* Obtém o código do sacado a partir da sigla
		*/

		public function GetCodSacBySigla($sigla) {
			$dataToSelect = array(
				'table' => 'SACADOS s',
				'params' => 's.CODSAC',
				'where' => array(
					's.CLI_SIGLA' => $sigla
					)
				);

			$data = $this->con->Select($dataToSelect);

			return $data;
		}

		/*
		* Verifica se o cliente é repasse
		*/

		public function IsRepasse($sigla) {
			$dataToSelect = array(
				'table' => 'SACADOS s',
				'params' => 's.REPASSE',
				'where' => array('s.CLI_SIGLA' => $sigla)
			);

			$data = count($this->con->Select($dataToSelect)) > 0 ? $this->con->Select($dataToSelect)['REPASSE'][0] === "1" : false;
			
			return $data;
		}

		public function isentoDebitoAutomatico($sigla) {
			$dataToSelect = array(
				'table' => 'SACADOS s',
				'params' => 's.ISENTO_DEBITO_AUTOMATICO',
				'where' => array('s.CLI_SIGLA' => $sigla)
			);

			$data = count($this->con->Select($dataToSelect)) > 0 ? $this->con->Select($dataToSelect)['ISENTO_DEBITO_AUTOMATICO'][0] === "1" : false;
			
			return $data;
		}

		/*
		* Insere a remessa processada no banco
		*/
		public function addProcessedShipping($data) {
			foreach ( $data['PACOTES'] as $pacote ) {
				$data_recebimento = explode(' ', $data['DATA_RECEBIMENTO']);
				$data_recebimento = "$data_recebimento[0] $data_recebimento[2]";

				$columns = array(
					'SIGLA' => $data['SIGLA'],
					'SEQUENCIAL' => $data['SEQUENCIAL'],
					'DATA_RECEBIMENTO' => $data_recebimento,
					'DATA_PAGAMENTO' => Util::FmtDate($data['DATA_PAGAMENTO'], '23'),
					'MAIOR_VCTO' => $data['MAIOR_VCTO'],
					'MENOR_VCTO' => $data['MENOR_VCTO'],
					'QTDE_TITULOS' => $pacote['QTDE_TITULOS'],
					'CUSTO_ENTREGA' => $pacote['CUSTO']['ENTREGA'],
					'CUSTO_IMPRESSAO' => $pacote['CUSTO']['IMPRESSAO'],
					'CUSTO_TOTAL' => $pacote['CUSTO']['ENTREGA'] + $pacote['CUSTO']['IMPRESSAO'],
					'DESCRICAO' =>  substr(Util::RemoverAcentos($pacote['DESC']), 0, 59),
					'BANCO' => array_keys($pacote['BANCO'])[0],
					'CONVENIO' => array_keys($pacote['BANCO'][array_keys($pacote['BANCO'])[0]])[0],
					'ENVIAR_EMAIL' => (int) $data['send_mail'],
					'ANEXAR_ARQUIVO' => (int) $data['attach'],
					'ESCREVER_CT' => (int) $data['write_ct'],
					'ESCREVER_ADMINPJ' => (int) $data['write_adminpj'],
					'REPASSE' => (int) $data['REPASSE'],
					'PROCESSADO_POR' => $data['PROCESSADO_POR']
				);

				$inserted = $this->con->Insert(array(
					'table' => 'REMESSAS_PROCESSADAS_GRAFICA',
					'columns' => $columns
				));

				if ( !$inserted['success'] ) {
					return false;
				}
			}

			return true;
		}

		/*
		* Retorna as remessas processadas do cliente
		*/
		public function getProcessedShipping($sigla, $sequencial) {
			$dataToSelect = array(
				'table' => 'REMESSAS_PROCESSADAS_GRAFICA rpg',
				'params' => 'rpg.ID_REMESSA, rpg.SIGLA, rpg.SEQUENCIAL, rpg.DATA_RECEBIMENTO, rpg.DATA_PAGAMENTO, rpg.MAIOR_VCTO, rpg.MENOR_VCTO, rpg.QTDE_TITULOS, rpg.CUSTO_ENTREGA, rpg.CUSTO_IMPRESSAO, rpg.CUSTO_TOTAL, rpg.BANCO, rpg.CONVENIO, rpg.ENVIAR_EMAIL, rpg.ANEXAR_ARQUIVO, rpg.ESCREVER_CT, rpg.ESCREVER_ADMINPJ, rpg.REPASSE, rpg.PROCESSADO_POR, rpg.DATA_PROCESSAMENTO',
				'where' => array('rpg.SIGLA' => $sigla, 'rpg.SEQUENCIAL' => $sequencial)
			);
			
			$data = $this->con->Select($dataToSelect);
			$processed_shipping = array();
			
			if ( count($data) > 0 ) {
				for ( $i = 0; $i < count($data['ID_REMESSA']); $i++ ) {
					$processed_shipping[] = array(
						'ID_REMESSA' => $data['ID_REMESSA'][$i],
						'SIGLA' => $data['SIGLA'][$i],
						'SEQUENCIAL' => $data['SEQUENCIAL'][$i],
						'DATA_RECEBIMENTO' => $data['DATA_RECEBIMENTO'][$i],
						'DATA_PAGAMENTO' => $data['DATA_PAGAMENTO'][$i],
						'MAIOR_VCTO' => $data['MAIOR_VCTO'][$i],
						'MENOR_VCTO' => $data['MENOR_VCTO'][$i],
						'QTDE_TITULOS' => $data['QTDE_TITULOS'][$i],
						'CUSTO_ENTREGA' => $data['CUSTO_ENTREGA'][$i],
						'CUSTO_IMPRESSAO' => $data['CUSTO_IMPRESSAO'][$i],
						'CUSTO_TOTAL' => $data['CUSTO_TOTAL'][$i],
						'BANCO' => $data['BANCO'][$i],
						'CONVENIO' => $data['CONVENIO'][$i],
						'ENVIAR_EMAIL' => $data['ENVIAR_EMAIL'][$i],
						'ANEXAR_ARQUIVO' => $data['ANEXAR_ARQUIVO'][$i],
						'ESCREVER_CT' => $data['ESCREVER_CT'][$i],
						'ESCREVER_ADMINPJ' => $data['ESCREVER_ADMINPJ'][$i],
						'REPASSE' => $data['REPASSE'][$i],
						'PROCESSADO_POR' => $data['PROCESSADO_POR'][$i],
						'DATA_PROCESSAMENTO' => $data['DATA_PROCESSAMENTO'][$i]
					);
				}
			} 
			
			return $processed_shipping;
		}

		/*
		* Obtém o email do cliente
		*/

		public function getMail($sigla) {
			$codsac = $this->GetCodSacBySigla($sigla);
			
			if ( count($codsac) > 0 ) {
				$dataToSelect = array(
					'table' => 'CONTATOS c',
					'params' => 'c.EMAIL',
					'where' => "c.CODSAC = '".$codsac['CODSAC'][0]."' AND c.EMAIL is not null ORDER BY c.CODCON ASC"
				);

				$data = $this->con->Select($dataToSelect);
				
				return count($data) > 0 ? $data['EMAIL'][0] : '';
			}

			return '';
		}

		/*
		* Obtém a string SIGLA+MATRÍCULA do cliente a partir da sigla
		* Ex: JAC -> JAC00098
		*/

		public function GetPathNameBySigla($sigla) {
			$dataToSelect = array(
				'table' => 'SACADOS s',
				'params' => 's.CODSAC',
				'where' => array('s.CLI_SIGLA' => strtoupper($sigla))
			);

			$data = $this->con->Select($dataToSelect);
			$pathname = strtolower($sigla).str_pad($data['CODSAC'][0], 5, '0', STR_PAD_LEFT);

			return $pathname;	
		}

		/*
		* Obtém o nome do cliente através da sigla
		*/

		public function getSacadoNameBySigla($sigla) {
			$dataToSelect = array(
				'table' => 'SACADOS s',
				'params' => 's.NOMSAC',
				'where' => array('s.CLI_SIGLA' => strtoupper($sigla))
			);

			$data = $this->con->Select($dataToSelect)['NOMSAC'][0];
			
			return $data;
		}

		/*
		* Obtém a string SIGLA+MATRÍCULA do cliente a partir da matrícula
		* Ex: 125 -> SPM00125
		*/

		public function GetPathNameByCod($cod) {
			$dataToSelect = array(
				'table' => 'SACADOS s',
				'params' => 's.CLI_SIGLA, s.CODSAC',
				'where' => "s.CODSAC = '$cod' or s.REPASSE_VARIACAO = '$cod'"
			);

			$data = $this->con->Select($dataToSelect);
			$pathname = $data['CLI_SIGLA'][0].str_pad($data['CODSAC'][0], 5, '0', STR_PAD_LEFT);

			return $pathname;	
		}

		/*
		* Obtém o site do cliente de acordo com a sigla
		*/

		public function GetSite($sigla) {
			$dataToSelect = array(
				'table' => 'SACADOS s',
				'params' => 's.SITE',
				'where' => array(
					's.CLI_SIGLA' => $sigla
					)
				);

			$data = $this->con->Select($dataToSelect);
			$site = $data['SITE'][0];

			return $site;
		}

		/*
		* Obtém o tipo do documento/documento do beneficiário de acordo com a sigla
		*/

		public function GetDocSacBySigla($sigla) {
			$dataToSelect = array(
				'table' => 'SACADOS s',
				'params' => 's.TPDOCSAC, s.DOCSAC',
				'where' => array(
					's.CLI_SIGLA' => $sigla
					)
				);

			$data = $this->con->Select($dataToSelect);

			$info = array(
				'TPDOC' => $data['TPDOCSAC'][0],
				'DOC' => $data['DOCSAC'][0]
				);

			return $info;
		}

		/*
		* Retorna tarifa LQR atual (apenas BB)
		*/

		public function getLqrTax() {

			$dataToSelect = array(
				'table' => 'TARIFAS t',
				'params' => 't.BB_LQR'
			);

			return $this->con->Select($dataToSelect)['BB_LQR'][0];
		}

		/*
		* Obtém as tarifas de cada cliente
		*/
		
		public function getCustomerTaxes($customer = null) {
			
			$paramsToSelect = array(
				'table' => 'SACADOS s',
				'params' => 's.CLI_SIGLA, s.BB_1704, s.BB_1711, s.BB_18, s.BB_LQR, s.CEF_CT'
			);

			if ( !is_null($customer) ) $paramsToSelect['where'] = "s.CLI_SIGLA = '".$customer."'";

			$tarifas = $this->con->Select($paramsToSelect);

			//Formatando a matriz para ajustar os dados
			$customers_tax = array();
			for ( $i = 0 ; $i < count($tarifas['CLI_SIGLA']) ; $i++ ) {
				$customers_tax[$tarifas['CLI_SIGLA'][$i]] = array(
					'2308855' => number_format($tarifas['BB_1704'][$i], 2, '.', ''),
					'2814485' => number_format($tarifas['BB_1711'][$i], 2, '.', ''),
					'3061856' => number_format($tarifas['BB_1711'][$i], 2, '.', ''),
					'1406548' => number_format($tarifas['BB_18'][$i], 2, '.', ''),
					'CEF' => number_format($tarifas['CEF_CT'][$i], 2, '.', '')
				);
			}

			return $customers_tax;
		}

		/*
		* Obtém as tarifas de impressão/entrega
		*/

		public function getPrintDeliveryTaxes() {

			$paramsToSelect = array(
				'table' => 'TARIFAS t',
				'params' => 't.IMPRESSAO, t.IMPRESSAO_GRAFICA, t.DEBITO_CONTA, t.ENTREGA_INDIVIDUAL, t.ENTREGA_UNICA'
			);

			$tarifas = $this->con->Select($paramsToSelect);
			
			return array(
				'DEBITO_CONTA' => number_format($tarifas['DEBITO_CONTA'][0], 2, '.', ''),
				'IMPRESSAO' => number_format($tarifas['IMPRESSAO'][0], 2, '.', ''),
				'IMPRESSAO_GRAFICA' => number_format($tarifas['IMPRESSAO_GRAFICA'][0], 2, '.', ''),
				'ENTREGA_INDIVIDUAL' => number_format($tarifas['ENTREGA_INDIVIDUAL'][0], 2, '.', ''),
				'ENTREGA_UNICA' => number_format($tarifas['ENTREGA_UNICA'][0], 2, '.', '')
			);
		}
	}