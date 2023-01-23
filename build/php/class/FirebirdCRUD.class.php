<?php

	include_once 'FirebirdConnection.class.php';

	class FirebirdCRUD {

		public $con;

		public function __construct($host = null) {
			$this->con = FirebirdConnection::getConnection($host);
		}

		public function __destruct() {
			$this->con = null;
		}

		//Insere registros no banco
		public function Insert(array $data) {
			//Zerando qualquer informação acumulada
			$this->info = array();
			//Tabela
			$table = $data['table'];
			//Colunas
			$columns = $data['columns'];
			//Mensagem de sucesso customizada
			$messageInSuccess = isset($data['messageInSuccess']) ? $data['messageInSuccess'] : false;
			//Obter retorno JSON
			$jsonResponse = isset($data['jsonResponse']) ? $data['jsonResponse'] : false;
			//Retorna o ID do objeto inserido
			$lastId = isset($data['lastId']) ? $data['lastId'] : false;

			//Atualizando o GENERATOR
			$gen_id = $this->GetGenId($table) + 1;
			$stmtGen = $this->con->prepare("SET GENERATOR $table TO $gen_id");
			$stmtGen->execute();
			
			//Extraindo os dados
			$column = array();
			$values = array();
			foreach ($columns as $key => $value) {
				$column[] = $key;
				$values[] = $value;
			}

			$column = "(".implode(", ", $column).")";
			$values = "('".implode("', '", $values)."')";
			
			//String de inserção
			$query = "INSERT INTO $table $column VALUES $values";
			$stmt = $this->con->prepare($query);

			if ( $stmt->execute() ) {
				$this->info['success'] = true;
				$this->info['status'] = ($messageInSuccess !== false) ? $messageInSuccess : 'Alerta de sucesso da operação não foi definida';
			} else {
				$this->info['success'] = false;
				$this->info['status'] = 'Erro ao inserir o registro: '.$stmt->errorCode();
			}
		
			return $this->info;
		}

		//Lista todos os clientes cadastrados
		public function Select(array $data) {
			//Zerando qualquer informação acumulada
			$this->info = array();
			//TABLE
			$table = $data['table'];
			//DISTINCT
			$distinct = isset($data['distinct']) && $data['distinct'] ? 'DISTINCT' : '';
			//PARAMS
			$params = $data['params'];
			//INNER JOIN
			$inner_join = isset($data['inner_join']) ? $data['inner_join'] : false;
			$inner_join_cmd = "";
			//INNER JOIN 2
			$inner_join2 = isset($data['inner_join2']) ? $data['inner_join2'] : false;
			$inner_join_cmd2 = "";
			//INNER JOIN 3
			$inner_join3 = isset($data['inner_join3']) ? $data['inner_join3'] : false;
			$inner_join_cmd3 = "";
			//LEFT JOIN
			$left_join = isset($data['left_join']) ? $data['left_join'] : false;
			$left_join_cmd = "";
			//WHERE
			$where = isset($data['where']) ? $data['where'] : false;
			$where_cmd = "";
			//LIKE
			$like = isset($data['like']) ? $data['like'] : false;
			$like_cmd = "";
			//ORDER BY
			$order = isset($data['order']) ? $data['order'] : false;
			$order_cmd = "";

			//Montando o INNER JOIN
			if ( $inner_join !== false ) {
				$table_join = $inner_join['table'];
				$on = explode(', ', $inner_join['on']);
				$inner_join_cmd = "INNER JOIN $table_join ON $on[0] = $on[1]";
			}
			
			//Montando o segundo INNER JOIN
			if ( $inner_join2 !== false ) {
				$table_join = $inner_join2['table'];
				$on = explode(', ', $inner_join2['on']);
				$inner_join_cmd2 = "INNER JOIN $table_join ON $on[0] = $on[1]";
			}

			//Montando o terceiro INNER JOIN
			if ( $inner_join3 !== false ) {
				$table_join = $inner_join3['table'];
				$on = explode(', ', $inner_join3['on']);
				$inner_join_cmd3 = "INNER JOIN $table_join ON $on[0] = $on[1]";
			}

			//Montando o LEFT JOIN
			if ( $left_join !== false ) {
				$table_join = $left_join['table'];
				$on = explode(', ', $left_join['on']);
				$left_join_cmd = "LEFT JOIN $table_join ON $on[0] = $on[1]";
			}

			//Extraindo os dados da cláusula where
			if ( $where !== false ) {
				if ( gettype($where) == 'array' ) {
					$whereParams = "";
					foreach ($where as $key => $value) {
						$str = (preg_match("/null/i", $value)) ? $key." is ".$value : $key." = '".$value."'";
						$whereParams .= (strlen($whereParams) === 0) ? $str : " AND ".$str;
					}
					$where_cmd = "WHERE $whereParams";
				} else if ( gettype($where) == 'string' ) {
					$where_cmd = "WHERE $where";
				}
			}

			//Montando o like
			if ( $like !== false ) {
				$field = $like['field'];
				$param = $like['param'];
				$format = isset($like['format']) ? $like['format'] : false;

				if ( $format !== false ) {
					if ( $format == 'lowercase' ) {
						$like_cmd = "WHERE LOWER($field) LIKE LOWER('%$param%')";
					} else if ($format == 'uppercase') {
						$like_cmd = "WHERE UPPER($field) LIKE UPPER('%$param%')";
					}
				} else {
					$like_cmd = "WHERE $field LIKE '%$param%'";
				}
			}

			//Formando o ORDER BY
			if ( $order !== false ) {
				$param_order = $order['param_order'];
				$order_by = $order['order_by'];
				$order_cmd = "ORDER BY $param_order $order_by";
			}

			$query = "SELECT $distinct $params FROM $table $left_join_cmd $inner_join_cmd $inner_join_cmd2 $inner_join_cmd3 $where_cmd $like_cmd $order_cmd";
			$stmt = $this->con->prepare($query);
			if ( $stmt->execute() ) {
				//Tratamento do nome do campo selecionado
				//Ex: s.NOMSAC, NOMSAC
				//Transforma a string em um array;
				$params = explode(',', trim($params));
				foreach ( $params as $key => $value ) {
					$params[$key] = explode('.', trim($value));
				}

				//Retorna os objetos
				if ( $this->con->query($query)->fetchAll() > 0 ) {
					while ( $row = $stmt->fetch(PDO::FETCH_OBJ) ) {
						for ( $i = 0; $i < count($params); $i++ ) {
							if ( isset($params[$i][1]) ) {
								//echo mb_convert_encoding($row->$params[$i][1], "UTF-8", "ASCII");
								$this->info[$params[$i][1]][] = is_null($row->$params[$i][1]) ? null : mb_convert_encoding($row->$params[$i][1], "UTF-8", "ASCII");
							} else {
								$this->info[$params[$i][0]][] = is_null($row->$params[$i][0]) ? null : mb_convert_encoding($row->$params[$i][0], "UTF-8", "ASCII");
							}
						}
					}
				} else {
					$this->info['success'] = false;
					$this->info['ErrorMsg'] = 'Nenhum registro foi encontrado';
				}
			} else {
				$this->info['success'] = false;
				$this->info['ErrorMsg'] = 'Erro ao recuperar os registros';
			}
			
			return $this->info;
		}

		//Atualiza um registro no banco de dados
		//Não permite UPDATE sem WHERE
		public function Update($data) {
			//Zerando qualquer informação acumulada
			$this->info = array();
			//Tabela
			$table = isset($data['table']) ? $data['table'] : false;
			//Parâmetros
			$set = isset($data['set']) ? $data['set'] : false;
			//Where
			$where = isset($data['where']) ? $data['where'] : false;
			//Mensagem de sucesso customizada
			$messageInSuccess = isset($data['messageInSuccess']) ? $data['messageInSuccess'] : false;

			if (!$table || !$set || !$where) {
				$this->info['success'] = false;
				$this->info['status'] = 'Informe todos os parâmetros obrigatórios para executar o comando';
			} else {
				//Extraindo os dados
				$set_cmd = array();
				foreach ( $set as $key => $value ) {
					$set_cmd[] = is_null($value) ? "$key = NULL" : "$key = '$value'";
				}
				$set_cmd = implode(', ', $set_cmd);

				//Extraindo os dados da cláusula where
				$where_cmd = "";
				if ( $where !== false ) {
					$whereParams = "";
					foreach ($where as $key => $value) {
						$str = (preg_match("/null/i", $value)) ? $key." is ".$value : $key." = '".$value."'";
						$whereParams .= (strlen($whereParams) === 0) ? $str : " AND ".$str;
					}
					$where_cmd = "WHERE $whereParams";
				}

				//SQL
				$query = "UPDATE $table SET $set_cmd $where_cmd";
				$stmt = $this->con->prepare($query);

				if ( $stmt->execute() ) {
					if ( $stmt->rowCount() > 0 ) {
						$this->info['success'] = true;
						$this->info['status'] = (!$messageInSuccess) ? 'Registro atualizado com sucesso' : $messageInSuccess;	
					} else {
						$this->info['success'] = false;
						$this->info['status'] = 'Nenhum registro foi modificado';	
					}
				} else {
					$this->info['success'] = false;
					$this->info['status'] = 'Não foi possível executar a atualização';
				}
			}
			
			return $this->info;
		}

		//Deletar informações
		public function Delete($data) {
			//Zerando qualquer informação acumulada
			$this->info = array();
			//Tabela
			$table = $data['table'];
			//Colunas
			$columns = isset($data['columns']) ? $data['columns'] : false;
			$columns_cmd = "";
			//Mensagem de sucesso customizada
			$messageInSuccess = isset($data['messageInSuccess']) ? $data['messageInSuccess'] : false;

			//Extraindo os dados
			if ( $columns !== false ) {
				foreach ($columns as $key => $value) {
					$columns_cmd .= (strlen($columns_cmd) === 0) ? "WHERE ".$key." = '".$value."'" : " AND ".$key." = '".$value."'";
				}
			}

			$sql = "DELETE FROM $table $columns_cmd";
			$stmt = $this->con->prepare($sql);
			
			if ( $stmt->execute() ) {
				if ( $stmt->rowCount() > 0 ) {
					$this->info['success'] = true;
					$this->info['status'] = ($messageInSuccess !== false) ? $messageInSuccess : 'Alerta de sucesso da operação não foi definida';
				} else {
					$this->info['success'] = true;
					$this->info['status'] = 'Nenhum registro foi excluído';	
				}
			} else {
				$this->info['success'] = false;
				$this->info['status'] = 'Erro ao executar o comando SQL, tente novamente';
			}

			return $this->info;
		}

		//Retorna a sequência atual do generetor
		public function GetGenId($gen_name) {
			$gen = "";

			$stmt = $this->con->prepare("SELECT GEN_ID($gen_name, 0) FROM SACADOS");
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
				$gen = $row->GEN_ID;
			}

			return $gen;
		}

	}