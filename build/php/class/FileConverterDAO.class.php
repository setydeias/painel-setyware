<?php
	
	include_once 'FirebirdCRUD.class.php';

	class FileConverterDao {

		public $con;

		public function __construct() {
			$this->con = new FirebirdCRUD();
		}

		public function GetShipNumber() {
			$ship_number = "";
			
			$stmt = $this->con->Select(array(
				'table' => 'REMESSAS_REGISTRADAS r',
				'params' => 'r.REMESSA_CONVERTIDA'
			));

			if ( count($stmt) > 0 ) {
				$ship_number = $stmt['REMESSA_CONVERTIDA'][0];
				
				//Retorna o valor atual
				return $ship_number;
			} else {
				return false;
			}
		}

		//Atualiza o número da remessa no banco de dados
		public function UpdateShipNumber($number) {
			//Valor que será armazenado no banco
			$updated_value = str_pad($number + 1, 6, '0', STR_PAD_LEFT);
			//Update
			$statement = $this->con->Update(array(
				'table' => 'REMESSAS_REGISTRADAS r',
				'set' => array('r.REMESSA_CONVERTIDA' => $updated_value),
				'where' => array('1' => '1')
			));

			return true;
		}

	}