<?php 
	error_reporting(E_ALL);
	include_once '../class/FirebirdCRUD.class.php';

	$data = json_decode(file_get_contents('php://input'), true);
	$tarifas_originais = $data['tarifas_originais'];
	
	$bb = str_replace(',', '.', $data['bb']);
	$bb17 = str_replace(',', '.', $data['bb17']);
	$bb1705 = str_replace(',', '.', $data['bb1705']);
	$bb1711 = str_replace(',', '.', $data['bb1711']);
	$bblqr = str_replace(',', '.', $data['bblqr']);
	$brd = str_replace(',', '.', $data['brd']);
	$cefint = str_replace(',', '.', $data['cefint']);
	$cefagn = str_replace(',', '.', $data['cefagn']);
	$cefcomp = str_replace(',', '.', $data['cefcomp']);
	$ceflot = str_replace(',', '.', $data['ceflot']);
	$cefct = str_replace(',', '.', $data['cefct']);
	$debito_conta = str_replace(',', '.', $data['debito_conta']);
	$impressao = str_replace(',', '.', $data['impressao']);
	$impressao_grafica = str_replace(',', '.', $data['impressao_grafica']);
	$entrega_individual = str_replace(',', '.', $data['entrega_individual']);
	$entrega_unica = str_replace(',', '.', $data['entrega_unica']);

	$crud = new FirebirdCRUD();

	$dataToUpdate = array(
		'table' => 'TARIFAS',
		'set' => array(
			'BB18' => $bb,
			'BB17_04' => $bb17,
			'BB17_05' => $bb1705,
			'BB17_11' => $bb1711,
			'BB_LQR' => $bblqr,
			'BRD' => $brd,
			'CEF_AUTOAT' => $cefint,
			'CEF_AGENCIA' => $cefagn,
			'CEF_LOTERIAS' => $ceflot,
			'CEF_COMPENSACAO' => $cefcomp,
			'CEF_CT' => $cefct,
			'DEBITO_CONTA' => $debito_conta,
			'IMPRESSAO' => $impressao,
			'IMPRESSAO_GRAFICA' => $impressao_grafica,
			'ENTREGA_INDIVIDUAL' => $entrega_individual,
			'ENTREGA_UNICA' => $entrega_unica
			),
		'where' => array(
			'1' => '1'
			),
		'messageInSuccess' => 'As tarifas foram atualizadas com sucesso'
		);

	//Atualizando os dados na tabela TARIFAS
	$updatedTarifas = $crud->Update($dataToUpdate);

	//Varredura no banco para checar se o cliente possui tarifa padrão
	//Se for padrão, atualiza todas as tarifas
	if ( $updatedTarifas['success'] ) {

		$dataToSelect = array(
			'table' => 'SACADOS s',
			'params' => 's.CODSAC, s.TIPO_TARIFA, s.BB_18, s.BB_1704, s.BB_1705, s.BB_1711, s.BB_LQR, s.CEF_AUTOAT, s.CEF_AGENCIA, s.CEF_LOTERIAS, s.CEF_COMPENSACAO, s.CEF_CT, s.BRADESCO'
			);

		$dataToSelect = $crud->Select($dataToSelect);

		for ( $i = 0; $i < count($dataToSelect['CODSAC']) ; $i++ ) {
			$codsac = $dataToSelect['CODSAC'][$i];
			$tipo_tarifa = $dataToSelect['TIPO_TARIFA'][$i];

			//Altera todas as tarifas para os clientes que possuem tarifa padrão
			if ( $tipo_tarifa == 1 ) {
				$dataToUpdate = array(
					'table' => 'SACADOS',
					'set' => array(
						'BB_18' => $bb,
						'BB_1704' => $bb17,
						'BB_1705' => $bb1705,
						'BB_1711' => $bb1711,
						'BB_LQR' => $bblqr,
						'BRADESCO' => $brd,
						'CEF_AUTOAT' => $cefint,
						'CEF_AGENCIA' => $cefagn,
						'CEF_LOTERIAS' => $ceflot,
						'CEF_COMPENSACAO' => $cefcomp,
						'CEF_CT' => $cefct
						),
					'where' => array(
						'CODSAC' => $codsac
						)
					);
			//Se o cliente possuir alguma tarifa personalizada
			//Então somente as tarifas que continuaram no valor normal serão alteradas
			} else if ( $tipo_tarifa == 2 ) {
				$set = array();
				$tarifas_to_refresh = array();

				if ( (float) $dataToSelect['BB_18'][$i] == $tarifas_originais['bb18'] ) { $tarifas_to_refresh['BB_18'] = $bb; }
				if ( (float) $dataToSelect['BB_1704'][$i] == $tarifas_originais['bb17'] ) { $tarifas_to_refresh['BB_1704'] = $bb17; }
				if ( (float) $dataToSelect['BB_1705'][$i] == $tarifas_originais['bb1705'] ) { $tarifas_to_refresh['BB_1705'] = $bb17; }
				if ( (float) $dataToSelect['BB_1711'][$i] == $tarifas_originais['bb1711'] ) { $tarifas_to_refresh['BB_1711'] = $bb1711; }
				if ( (float) $dataToSelect['BB_LQR'][$i] == $tarifas_originais['bblqr'] ) { $tarifas_to_refresh['BB_LQR'] = $bblqr; }
				if ( (float) $dataToSelect['CEF_AUTOAT'][$i] == $tarifas_originais['cef_autoat'] ) { $tarifas_to_refresh['CEF_AUTOAT'] = $cefint; }
				if ( (float) $dataToSelect['CEF_AGENCIA'][$i] == $tarifas_originais['cef_agencia'] ) { $tarifas_to_refresh['CEF_AGENCIA'] = $cefagn; }
				if ( (float) $dataToSelect['CEF_LOTERIAS'][$i] == $tarifas_originais['cef_loterias'] ) { $tarifas_to_refresh['CEF_LOTERIAS'] = $ceflot; }
				if ( (float) $dataToSelect['CEF_COMPENSACAO'][$i] == $tarifas_originais['cef_compensacao'] ) { $tarifas_to_refresh['CEF_COMPENSACAO'] = $cefcomp; }
				if ( (float) $dataToSelect['CEF_CT'][$i] == $tarifas_originais['cef_ct'] ) { $tarifas_to_refresh['CEF_CT'] = $cefct; }
				if ( (float) $dataToSelect['BRADESCO'][$i] == $tarifas_originais['brd'] ) { $tarifas_to_refresh['BRADESCO'] = $brd; }

				$dataToUpdate = array(
					'table' => 'SACADOS',
					'set' => $tarifas_to_refresh,
					'where' => array(
						'CODSAC' => $codsac
						)
					);
			}

			//Atualiza o registro
			$crud->Update($dataToUpdate);
		}
	}

	echo json_encode($updatedTarifas);