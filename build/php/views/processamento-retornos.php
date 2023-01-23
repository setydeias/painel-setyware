<?php
	set_time_limit(0);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	ini_set('memory_limit', '-1');
	error_reporting(0);
	include_once '../header.php';
	include_once '../class/FirebirdCRUD.class.php';
	include_once '../class/Customer.class.php';
	include_once '../class/ConvenioCobranca.class.php';
	include_once '../class/FilesHandler.class.php';
	include_once '../class/DirManager.class.php';
	include_once '../class/Convenio.class.php';
	include_once '../class/AnalysisRecord.class.php';
	include_once '../functions.php';
	include_once '../../../vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
	include_once '../../../vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';
	//Parâmetros via POST
	$insertRecordIntoDb = $_POST['insert_record_db'];
	$writeInCT = $_POST['ct'];
	$checarDuplicidade = $_POST['duplicidades'];
	//Verifica se vai escrever nas Contas Transitorias
	if ( $writeInCT == '01' ) {
		$dataT = $_POST['dataTransf'];
		$dataE = $_POST['dataEvent'];
	}
	//Verifica se a checagem de duplicidade está ativa
	//Se estiver, verifica a quantidade de dias para a checagem
	if ($checarDuplicidade == '1') {
		$diasDuplicidades = $_POST['diasChecagemDuplicidade'];
	}
	//CRUD
	$crud = new FirebirdCRUD();
	$DirManager = new DirManager();
	$customer = new Customer();
	$Convenio = new Convenio();
	$AnalysisRecord = new AnalysisRecord();
	//Get path params
	$paths = $DirManager->getDirs(array('PROCESSAMENTO_RETORNOS', 'RETORNOS_PROCESSADOS', 'RETORNOS_ORIGINAIS', 'PAGAMENTOS_EM_CHEQUE'));
	//Params
	$path = $paths['PROCESSAMENTO_RETORNOS'][0];
	$processed = $paths['RETORNOS_PROCESSADOS'][0];
	$orig = $paths['RETORNOS_ORIGINAIS'][0];
	$pathCheque = $paths['PAGAMENTOS_EM_CHEQUE'][0];
	$allowed_extensions = array('ret', 'RET', 'Ret', 'srq', 'srt'); //Extensões permitidas
	//Se existirem arquivos já processados na pasta, apagá-los
	$DirManager->deleteFiles($processed, $allowed_extensions);
	//Seleciona os clientes que possuem tarifas personalizadas
	$dataToSelectCustomCustomer = array(
		'table' => 'SACADOS s',
		'params' => 's.CLI_SIGLA, s.BB_18, s.BB_1704, s.BB_1705, s.BB_1711, s.CEF_AUTOAT, s.CEF_AGENCIA, s.CEF_COMPENSACAO, s.CEF_LOTERIAS, s.CEF_CT, s.BRADESCO',
		'where' => array( 's.TIPO_TARIFA' => 2 )
	);
	$CustomCustomers = $crud->Select($dataToSelectCustomCustomer);
	//Retorna todas as tarifas do BB que estão no banco de dados
	$dataToSelectTax = array(
		'table' => 'TARIFAS t',
		'params' => 't.BB18, t.BB17_04, t.BB17_05, t.BB17_11, t.BRD, t.CEF_AUTOAT, t.CEF_AGENCIA, t.CEF_LOTERIAS, t.CEF_COMPENSACAO, t.CEF_CT' );
	$customerTars = $crud->Select($dataToSelectTax);
	//Obtém os convênios para processar
	$convenios = $Convenio->get();
	//Convênios da STY
	$filter_setydeiasConvs = array_filter($Convenio->get(), function($data) { return $data['TIPO'] === '1'; });
	$setydeiasConvs = array_map(function($data) { return $data['CONVENIO']; }, $filter_setydeiasConvs);
	//Convênios próprios
	$filter_conveniosProprios = array_filter($Convenio->get(), function($data) { return $data['TIPO'] === '2'; });
	$conveniosProprios = array_map(function($d) { return array('MANTENEDOR' => $d['MANTENEDOR'], 'CONVENIO' => $d['CONVENIO']); }, $filter_conveniosProprios);
	$just_convenios_proprios = array_map( function($d) { return $d['CONVENIO']; }, $conveniosProprios );
	//Arrays auxiliares
	$cefTaxs = array('295', '228', '224', '161', '149'); //Tarifas da Caixa
	$clientsWithPayments = array(); //Matriz que guarda a sigla dos clientes que receberam pagamentos
	$pgtoCHQ = array(); //Pagamento em cheque
	$records_to_add = array(); //Registros do retorno que serão inseridos no banco de dados
?>
<div id="content">
<?php
	//Transfer files to path where is the original files
	$pathCpDir = dir($path);	
	while ( $f = $pathCpDir->read() ) {
		if ( pathinfo($f, PATHINFO_EXTENSION) == 'ret' || pathinfo($f, PATHINFO_EXTENSION) == 'RET' ) {
			if ( substr(file($path.$f)[0], 0, 3) == '001' ) {
				if ( substr($f, 0, 7) != 'IEDCBR_' ) {
					$conv = substr(file($path.$f)[0], 34, 7);
					$seq = substr(file($path.$f)[0], 157, 6);
					$data = substr(file($path.$f)[0], 143, 8); 
					//Capturing the client
		 			if ( in_array($conv, $setydeiasConvs) ) {
		 				$cli = 'STY';
		 			} else {
						$customer_index = array_search($conv, $just_convenios_proprios);
						$cli = !$customer_index ? 'INDEFINIDO' : $conveniosProprios[$customer_index]['MANTENEDOR'];
					}
		 			$arqCp = 'IEDCBR_'.fmtDateBRD($data).'_'.$cli.'_001_'.$conv.'_'.$seq.'.ret';
		 			copy($path.$f, $orig.$arqCp);
		 		}
			} else if ( substr(file($path.$f)[0], 0, 3) == '104' ) {
				$conv = substr(file($path.$f)[0], 58, 6);
				$seq = substr(file($path.$f)[0], 157, 6);
				$data = substr(file($path.$f)[0], 143, 8);
		 		if ( substr($f, 0, 1) == 'R' ) {
		 			$arqCp = 'IEDCBR_'.fmtDatePattern($data, '9').'_STY_104_'.str_pad($conv, 7, '0', STR_PAD_LEFT).'_'.$seq.'.ret';
		 			copy($path.$f, $orig.$arqCp);
		 		} else if ( substr($f, 0, 3) == 'COB' ) {
		 			$arqCp = 'IEDCBR_'.fmtDatePattern($data, '9').'_STY_104_'.str_pad($conv, 7, '0', STR_PAD_LEFT).'_'.$seq.'.ret';
		 			copy($path.$f, $orig.$arqCp);
		 		}
	 		} else if ( substr(file($path.$f)[0], 0, 3) == '02R' || substr(file($path.$f)[0], 0, 3) == '237' && substr(file($path.$f)[0], 76, 11) == '237BRADESCO' ) {
		 		$seq = substr(file($path.$f)[0], 107, 6);
		 		$data = substr(file($path.$f)[0], 94, 6); 
		 		if ( substr($f, 0, 2) == 'CB' ) {
		 			$arqCp = 'IEDCBR_'.fmtDatePattern($data, '6').'_STY_237_0021777_'.$seq.'.ret';
		 			copy($path.$f, $orig.$arqCp);
		 		}
			}
		}
	}
	//Reading the path's files that are in @path var and adding them to the respective bank
	$dir = dir($path);
	//Creating arrays for each bank
	$bb = array();
	$brd = array();
	$ceft = array();
	$cefp = array();
	while ( $file = $dir->read() ) {
		$fileName = explode('_', $file);
		//List just the files where the extension is at @allowed_extensions array
		if ( in_array(pathinfo($file, PATHINFO_EXTENSION), $allowed_extensions) ) {
			//If 001 is Banco do Brasil
			if ( substr(file($path.$file)[0], 0, 3) == '001' ) {
				$bb[] = $path.$file;
			//If 02R or 237 is Bradesco
			} else if ( substr(file($path.$file)[0], 0, 3) == '02R' || substr(file($path.$file)[0], 0, 3) == '237' ) {
				$brd[] = $path.$file;
			//If 104 is Caixa Econômica Federal
			} else if ( substr(file($path.$file)[0], 0, 3) == '104' ) {
				if ( substr(file($path.$file)[0], 58, 6) == '0040450' ) {
					$ceft[] = $path.$file;
				} elseif ( substr(file($path.$file)[0], 58, 6) == '0264151' ) {
					$cefp[] = $path.$file;
				}
			} else if ( substr($file, 0, 3) == 'DBT' ) {
				$data = fmtDateDBT(substr(file($path.$file)[0], 65, 8));
				$cnv = str_pad(substr(file($path.$file)[0], 2, 5), 7, 0, STR_PAD_LEFT);
				$sqnc = str_pad(substr(file($path.$file)[0], 75, 4), 6, 0, STR_PAD_LEFT);
				$arquivo = 'DBT_'.$data.'_XXX_001_'.$cnv.'_'.$sqnc.'.Ret';
				copy($path.$file, 'C:\\Bancobrasil\\BBTransf\\retorno\\'.$arquivo);
				copy($path.$file, $orig.$arquivo);
			}
		}
	}
	//Take the sum of file's size
	$sumFileSize = 0;
	/*
	* Array auxiliar para escrever na conta transitória
	* Em caso de duplicidade
	*/
	$duplicidadect = array();
	//List the bb's files
	if ( count($bb) > 0 ) include_once '../bancos/bb.php';
	//List the brd's files
	if ( count($brd) > 0 ) include_once '../bancos/brd.php';
	//Showing the bank's image
	if ( count($ceft) > 0 || count($cefp) > 0 ) echo '<img src="/painel/build/images/cef.jpg" width="100" height="23" /> <br /><br />';
	//List the cef terra da luz's files
	if ( count($ceft) > 0 ) include_once '../bancos/ceft.php';
	//List the cef parangaba's files
	if ( count($cefp) > 0 ) include_once '../bancos/cefp.php';
	//Verifica se a opção de inserir os títulos no banco está ativada
	if ( $insertRecordIntoDb == '01' ) {
		$AnalysisRecord->init($records_to_add);
	}
	//Add transfer's string on clients' CT
	count($clientsWithPayments) > 0 ? addTransfString($clientsWithPayments, $dataT, $dataE, $duplicidadect) : '';
	//Check if exists some file then send them to the form
	if ( count($bb) > 0 || count($brd) > 0 || count($ceft) > 0 || count($cefp) > 0 ) :
	echo "<span class='glyphicon glyphicon-exclamation-sign' style='color:red;'></span> Tarifas com este sinal indicam que o cliente possui tarifas personalizadas<br /><br />";
?>
	<hr />
	<section class="row" style="margin: 20px 0;">
		<label for="unlink-files">
			<input type="checkbox" class="unlink-files" id="unlink-files" checked="checked" /> Excluir os arquivos após o processamento
		</label>
	</section>
	<button type="button" class="btn btn-primary" id="btnPrint"><span class="glyphicon glyphicon-print"></span> Imprimir Relatório</button>&nbsp;
	<button type="button" class="btn btn-success" id="btnTransfer"><span class="glyphicon glyphicon-cloud-upload"></span> Transferir para Servidor nas Nuvens</button>
	<button type="button" class="btn btn-warning pull-right" id="btnSendMail"><span class="glyphicon glyphicon-send"></span> Enviar retornos por email</button>
<?php 
	else : 
?>
	<h4><span class='glyphicon glyphicon-alert'></span> Não existem arquivos para o processamento!</h4>
<?php
	endif;
	echo '<div id="msg"></div>';
	//Destroying original files
	$DirManager->deleteFiles($path, $allowed_extensions);
?>
</div>
<script src="/painel/dist/processamento.js"></script>