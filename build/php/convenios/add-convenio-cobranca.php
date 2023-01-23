<?php
	ini_set('error_reporting', E_ALL);
	include_once '../pdo-firebird.php';
	include_once '../functions.php';

	$data = json_decode(file_get_contents('php://input'), true);
	$cliente = $data['cliente'];
	$codigo_cliente = substr($cliente, -3);
	$banco = $data['banco'];
	$agencia = $data['agencia'];
	$agencia_dv = $data['agencia-dv'];
	$conta = $data['conta'];
	$conta_dv = $data['conta-dv'];
	$numero_convenio = $data['numero-convenio'];
	$carteira = isset($data['carteira']) ? $data['carteira'] : null;
	$variacao = isset($data['variacao']) ? $data['variacao'] : null;
	$tipo_multa = $data['tipo-multa'];
	$multa = isset($data['multa']) ? $data['multa'] : 0;
	$tipo_juros = $data['tipo-juros'];
	$juros = isset($data['juros']) ? $data['juros'] : 0;
	$protesto = $data['protesto'];
	$validade = $data['validade'];
	$carencia = $data['carencia'];
	$padrao = $data['padrao'] ? 'S' : 'N';
	$database = "D:\\Laboratorio - Setyware\\Setydeias\\Setyware\\$cliente\\$cliente.gdb";
	$conn = connectPDO($database); //Inicia uma conexão
	
	switch ( $banco ) {
		case '001':
			$descricao = $carteira == '17' ? '001CONV717' : '001CONV718';
			$banco_dv = "9";
			break;
		case '104':
			$descricao = "104SIGCB";
			$banco_dv = "0";
			$carteira = "SR";
			break;
		case '237':
			$descricao = "237COB06";
			$banco_dv = "2";
			$carteira = "06";
			break;
	}
	
	try {

		if ( $data['padrao'] ) {
			$sql = "UPDATE CEDENTES_CONVENIOS SET PADRAO = 'N'";
			$stmt = $conn->prepare($sql);
			$stmt->execute();
		}
		
		$sql = " INSERT INTO CEDENTES_CONVENIOS (ID_CEDENTES_CONVENIOS, BANCO, BANCO_DV, CODIGO_CEDENTE, CODIGO_CEDENTE_DV, CARTEIRA, VARIACAO, ";
		$sql .= "MULTA, JUROS, PROTES, MAXDIAS, CARDIAS, NOSSONUMSEQ, DESCRICAO, PADRAO, AGENCIA, AGENCIA_DV, CONTA_NUMERO, CONTA_NUMERO_DV, MULTA_TIPO_VALOR, ";
		$sql .= "JUROS_TIPO_VALOR, ID_DOCUMENTOS_ARRECADACAO, ID_ESPECIE_DOCUMENTO, CONVENIO_COBRANCA, NOSSONUM_FIXO, NOSSONUM_TAM, LOCAL_RECEBIMENTO, ESPECIE) ";
		$sql .= "VALUES ((SELECT MAX(ID_CEDENTES_CONVENIOS) FROM CEDENTES_CONVENIOS) + 1, '$banco', '$banco_dv', '$numero_convenio', '0', '$carteira', '$variacao', ";
		$sql .= "'$multa', '$juros', '$protesto', '$validade', '$carencia', '0', '$descricao', '$padrao', '$agencia', '$agencia_dv', '$conta', '$conta_dv', ";
		$sql .= "'$tipo_multa', '$tipo_juros', '0', 'RC', '$descricao', '$codigo_cliente', NULL, 'EM TODA A REDE BANCARIA ATE O VENCIMENTO', 'RC')";
		
		$stmt_insert = $conn->prepare($sql);
		
		if ( $stmt_insert->execute() ) {
			if ( $data['padrao'] ) {
				$sql = "UPDATE EVENTOS_CONTABEIS SET CODCONV = (SELECT MAX(cc.ID_CEDENTES_CONVENIOS) FROM CEDENTES_CONVENIOS cc)";
				$stmt = $conn->prepare($sql);
				if ( !$stmt->execute() ) {
					return http_response_code(400);
				}

				$sql = "UPDATE TIPOS_LANCAMENTOS SET USAR_VENCIMENTO = (SELECT MAX(cc.ID_CEDENTES_CONVENIOS) FROM CEDENTES_CONVENIOS cc)";
				$stmt = $conn->prepare($sql);
				if ( !$stmt->execute() ) {
					return http_response_code(400);
				}
			}

			http_response_code(201);
		} else {
			http_response_code(400);
		}
	} catch ( PDOException $e ) {
		http_response_code(400);
		echo $e->getMessage();
	}
	exit;
	if ( $conn ) {
		if ( count($convenios) > 0 ) {
			$stmt = $conn->prepare("SELECT ID_CEDENTES_CONVENIOS, MULTA, JUROS, PROTES, MAXDIAS, CARDIAS, NOSSONUMSEQ, MULTA_TIPO_VALOR, JUROS_TIPO_VALOR, NOSSONUM_FIXO,
			INSTRU_6, INSTRU_7, INSTRU_8, MSG_1, MSG_2, MSG_3, MSG_4, MSG_5, MSG_6, MSG_7, MSG_8, MSG_9, MSG_10, MSG_11, MSG_12, MSG_13, MSG_14, MSG_15, MSG_16, MSG_17, MSG_18,
			MSG_19, MSG_20, MSG_21 FROM CEDENTES_CONVENIOS WHERE ID_CEDENTES_CONVENIOS = (SELECT MAX(ID_CEDENTES_CONVENIOS) FROM CEDENTES_CONVENIOS)");
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_OBJ)) :
				$params = array(
					'id' => $row->ID_CEDENTES_CONVENIOS, 'multa' => (int) $row->MULTA, 'juros' => (int) $row->JUROS, 'protesto' => $row->PROTES, 'validade' => $row->MAXDIAS,
				    'carencia' => $row->CARDIAS, 'nossnumseq' => $row->NOSSONUMSEQ, 'multa_tipo' => $row->MULTA_TIPO_VALOR,
				    'juros_tipo' => $row->JUROS_TIPO_VALOR, 'nossonum' => $row->NOSSONUM_FIXO
				);
			endwhile;

			$id = $params['id'];
			
			//execute query
			function execQuery($c, $stmt, $query) {
				$stmt = $c->prepare($query);
				$stmt->execute();
			}

			for ($i = 0; $i < count($convenios); $i++) :
				switch ($convenios[$i]) :
					/*case '1406548':
						$id += ($i+1);
						$sql = "INSERT INTO CEDENTES_CONVENIOS (ID_CEDENTES_CONVENIOS, BANCO, BANCO_DV, CODIGO_CEDENTE, CODIGO_CEDENTE_DV, CARTEIRA, VARIACAO, MULTA, JUROS, PROTES, MAXDIAS, CARDIAS, NOSSONUMSEQ, DESCRICAO, PADRAO, AGENCIA, AGENCIA_DV, CONTA_NUMERO, CONTA_NUMERO_DV, MULTA_TIPO_VALOR, JUROS_TIPO_VALOR, ID_DOCUMENTOS_ARRECADACAO, ID_ESPECIE_DOCUMENTO, CONVENIO_COBRANCA, NOSSONUM_FIXO, NOSSONUM_TAM, LOCAL_RECEBIMENTO, ESPECIE) VALUES ('$id', '001', '9', '1406548', '0', '18', '08', '{$params['multa']}', '{$params['juros']}', '{$params['protesto']}', '{$params['validade']}', '{$params['carencia']}', '0', '001CONV718', 'N', '2906', '8', '7777', '1', '{$params['multa_tipo']}', '{$params['juros_tipo']}', '0', 'RC', '001CONV718', '{$params['nossonum']}', NULL, 'EM TODA A REDE BANCARIA ATE O VENCIMENTO', 'RC');".PHP_EOL;
						execQuery($conn, $stmtInsert, $sql);
						$param['add'] = true;
						break;
					case '2308855':
						$id += ($i+1);
						$sql = "INSERT INTO CEDENTES_CONVENIOS (ID_CEDENTES_CONVENIOS, BANCO, BANCO_DV, CODIGO_CEDENTE, CODIGO_CEDENTE_DV, CARTEIRA, VARIACAO, MULTA, JUROS, PROTES, MAXDIAS, CARDIAS, NOSSONUMSEQ, DESCRICAO, PADRAO, AGENCIA, AGENCIA_DV, CONTA_NUMERO, CONTA_NUMERO_DV, MULTA_TIPO_VALOR, JUROS_TIPO_VALOR, ID_DOCUMENTOS_ARRECADACAO, ID_ESPECIE_DOCUMENTO, CONVENIO_COBRANCA, NOSSONUM_FIXO, NOSSONUM_TAM, LOCAL_RECEBIMENTO, ESPECIE) VALUES ('$id', '001', '9', '2308855', '0', '17', '04', '{$params['multa']}', '{$params['juros']}', '{$params['protesto']}', '{$params['validade']}', '{$params['carencia']}', '0', '001CONV717', 'N', '2906', '8', '7777', '1', '{$params['multa_tipo']}', '{$params['juros_tipo']}', '0', 'RC', '001CONV717', '{$params['nossonum']}', NULL, 'EM TODA A REDE BANCARIA ATE O VENCIMENTO', 'RC');".PHP_EOL;
						execQuery($conn, $stmtInsert, $sql);
						$param['add'] = true;
						break;*/
					case '2814485':
						$id += ($i+1);
						$sql = "INSERT INTO CEDENTES_CONVENIOS (ID_CEDENTES_CONVENIOS, BANCO, BANCO_DV, CODIGO_CEDENTE, CODIGO_CEDENTE_DV, CARTEIRA, VARIACAO, MULTA, JUROS, PROTES, MAXDIAS, CARDIAS, NOSSONUMSEQ, DESCRICAO, PADRAO, AGENCIA, AGENCIA_DV, CONTA_NUMERO, CONTA_NUMERO_DV, MULTA_TIPO_VALOR, JUROS_TIPO_VALOR, ID_DOCUMENTOS_ARRECADACAO, ID_ESPECIE_DOCUMENTO, CONVENIO_COBRANCA, NOSSONUM_FIXO, NOSSONUM_TAM, LOCAL_RECEBIMENTO, ESPECIE) VALUES ('$id', '001', '9', '2814485', '0', '17', '11', '{$params['multa']}', '{$params['juros']}', '{$params['protesto']}', '{$params['validade']}', '{$params['carencia']}', '0', '001CONV717', 'N', '2906', '8', '7777', '1', '{$params['multa_tipo']}', '{$params['juros_tipo']}', '0', 'RC', '001CONV717', '{$params['nossonum']}', NULL, 'EM TODA A REDE BANCARIA ATE O VENCIMENTO', 'RC');".PHP_EOL;
						execQuery($conn, $stmtInsert, $sql);
						$param['add'] = true;
						break;	
					case '0021777':
						$id += ($i+1);
						$sql = "INSERT INTO CEDENTES_CONVENIOS (ID_CEDENTES_CONVENIOS, BANCO, BANCO_DV, CODIGO_CEDENTE, CODIGO_CEDENTE_DV, CARTEIRA, VARIACAO, MULTA, JUROS, PROTES, MAXDIAS, CARDIAS, NOSSONUMSEQ, DESCRICAO, PADRAO, AGENCIA, AGENCIA_DV, CONTA_NUMERO, CONTA_NUMERO_DV, MULTA_TIPO_VALOR, JUROS_TIPO_VALOR, ID_DOCUMENTOS_ARRECADACAO, ID_ESPECIE_DOCUMENTO, CONVENIO_COBRANCA, NOSSONUM_FIXO, NOSSONUM_TAM, LOCAL_RECEBIMENTO, ESPECIE) VALUES ('$id', '237', '2', '0021777', '0', '06', NULL, '{$params['multa']}', '{$params['juros']}', '{$params['protesto']}', '{$params['validade']}', '{$params['carencia']}', '0', '237COB06', 'N', '0649', '1', '21777', '8', '{$params['multa_tipo']}', '{$params['juros_tipo']}', '0', 'RC', '237COB06', '{$params['nossonum']}', NULL, 'EM TODA A REDE BANCARIA ATE O VENCIMENTO', 'RC');".PHP_EOL;
						execQuery($conn, $stmtInsert, $sql);
						$param['add'] = true;
						break;
					case '264151':
						$id += ($i+1);
						$sql = "INSERT INTO CEDENTES_CONVENIOS (ID_CEDENTES_CONVENIOS, BANCO, BANCO_DV, CODIGO_CEDENTE, CODIGO_CEDENTE_DV, CARTEIRA, VARIACAO, MULTA, JUROS, PROTES, MAXDIAS, CARDIAS, NOSSONUMSEQ, DESCRICAO, PADRAO, AGENCIA, AGENCIA_DV, CONTA_NUMERO, CONTA_NUMERO_DV, MULTA_TIPO_VALOR, JUROS_TIPO_VALOR, ID_DOCUMENTOS_ARRECADACAO, ID_ESPECIE_DOCUMENTO, CONVENIO_COBRANCA, NOSSONUM_FIXO, NOSSONUM_TAM, LOCAL_RECEBIMENTO, ESPECIE) VALUES ('$id', '104', '0', '264151', '0', 'SR', NULL, '{$params['multa']}', '{$params['juros']}', '{$params['protesto']}', '{$params['validade']}', '{$params['carencia']}', '0', '104SIGCB', 'N', '1563', '6', '7777', '0', '{$params['multa_tipo']}', '{$params['juros_tipo']}', '0', 'RC', '104SIGCB', '{$params['nossonum']}', NULL, 'EM TODA A REDE BANCARIA ATE O VENCIMENTO', 'RC');".PHP_EOL;
						execQuery($conn, $stmtInsert, $sql);
						$param['add'] = true;
						break;
					case '689494':
						$id += ($i+1);
						$sql = "INSERT INTO CEDENTES_CONVENIOS (ID_CEDENTES_CONVENIOS, BANCO, BANCO_DV, CODIGO_CEDENTE, CODIGO_CEDENTE_DV, CARTEIRA, VARIACAO, MULTA, JUROS, PROTES, MAXDIAS, CARDIAS, NOSSONUMSEQ, DESCRICAO, PADRAO, AGENCIA, AGENCIA_DV, CONTA_NUMERO, CONTA_NUMERO_DV, MULTA_TIPO_VALOR, JUROS_TIPO_VALOR, ID_DOCUMENTOS_ARRECADACAO, ID_ESPECIE_DOCUMENTO, CONVENIO_COBRANCA, NOSSONUM_FIXO, NOSSONUM_TAM, LOCAL_RECEBIMENTO, ESPECIE) VALUES ('$id', '104', '0', '689494', '0', 'SR', NULL, '{$params['multa']}', '{$params['juros']}', '{$params['protesto']}', '{$params['validade']}', '{$params['carencia']}', '0', '104SIGCB', 'N', '1563', '6', '7777', '0', '{$params['multa_tipo']}', '{$params['juros_tipo']}', '0', 'RC', '104SIGCB', '{$params['nossonum']}', NULL, 'EM TODA A REDE BANCARIA ATE O VENCIMENTO', 'RC');".PHP_EOL;
						execQuery($conn, $stmtInsert, $sql);
						$param['add'] = true;
						break;
					case '040450':
						$id += ($i+1);
						$sql = "INSERT INTO CEDENTES_CONVENIOS (ID_CEDENTES_CONVENIOS, BANCO, BANCO_DV, CODIGO_CEDENTE, CODIGO_CEDENTE_DV, CARTEIRA, VARIACAO, MULTA, JUROS, PROTES, MAXDIAS, CARDIAS, NOSSONUMSEQ, DESCRICAO, PADRAO, AGENCIA, AGENCIA_DV, CONTA_NUMERO, CONTA_NUMERO_DV, MULTA_TIPO_VALOR, JUROS_TIPO_VALOR, ID_DOCUMENTOS_ARRECADACAO, ID_ESPECIE_DOCUMENTO, CONVENIO_COBRANCA, NOSSONUM_FIXO, NOSSONUM_TAM, LOCAL_RECEBIMENTO, ESPECIE) VALUES ('$id', '104', '0', '040450', NULL, 'SR', NULL, '{$params['multa']}', '{$params['juros']}', '{$params['protesto']}', '{$params['validade']}', '{$params['carencia']}', '0', '104SINCO18', 'N', '1559', '8', '600', '6', '{$params['multa_tipo']}', '{$params['juros_tipo']}', '0', 'RC', '104SINCO01', '{$params['nossonum']}', NULL, 'EM TODA A REDE BANCARIA ATE O VENCIMENTO', 'RC');".PHP_EOL;
						execQuery($conn, $stmtInsert, $sql);
						$param['add'] = true;
						break;
				endswitch;
			endfor;
		} else {
			$param = array('add' => false, 'erro' => 'Nenhum convênio foi selecionado!');
		}
	} else {
		$param = array('add' => false, 'erro' => 'Nenhuma conexão foi estabelecida!');
	}
	$conn = null;
	echo json_encode($param);