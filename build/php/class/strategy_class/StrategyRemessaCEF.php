<?php

    include_once '/../interfaces/IStrategyRemessaRegistrada.php';

    class StrategyRemessaCEF implements IStrategyRemessaRegistrada {

        public function GenerateFileHeader($params) {
            $header = $params['cod_banco'].'00000'.str_pad('', 9, ' ', STR_PAD_RIGHT).$params['tipo_documento'].$params['documento'].str_pad('', 20, '0', STR_PAD_RIGHT);
			$header .= str_pad($params['agencia'], 6, '0', STR_PAD_LEFT).$params['convenio'].str_pad('', 8, '0', STR_PAD_LEFT).str_pad(substr($params['razao_social'], 0 , 30), 30, ' ', STR_PAD_RIGHT);
			$header .= str_pad(substr($params['banco'], 0 , 30), 30, ' ', STR_PAD_RIGHT).str_pad('', 10, ' ', STR_PAD_RIGHT).'1'.date('dmY').date('His').'000001'.$params['versao_remessa'];
			$header .= str_pad('', 5, '0', STR_PAD_LEFT).str_pad('', 20, ' ', STR_PAD_RIGHT).str_pad('REMESSA-PRODUCAO', 20, ' ', STR_PAD_RIGHT).str_pad('', 29, ' ', STR_PAD_RIGHT).PHP_EOL;

			return $header;
        }

        public function GenerateLoteHeader($params) {
            $header = $params['cod_banco'].$params['lote'].'1R01'.str_pad('', 2, ' ', STR_PAD_RIGHT).'030 '.$params['tipo_documento'].str_pad($params['documento'], 15, '0', STR_PAD_LEFT);
			$header .= $params['convenio'].str_pad('', 14, '0', STR_PAD_LEFT).str_pad($params['agencia'], 6, '0', STR_PAD_LEFT).$params['convenio'].str_pad('', 7, '0', STR_PAD_LEFT);
			$header .= '0'.str_pad(substr($params['razao_social'], 0 , 30), 30, ' ', STR_PAD_RIGHT).str_pad('', 80, ' ', STR_PAD_RIGHT).'00000001';
			$header .= date('dmY').str_pad('', 41, ' ', STR_PAD_LEFT).PHP_EOL;

			return $header;
        }

        public function SegmentoP($params, $data) {
            //Params
			$banco = substr($params['cod_banco'], 0 , 3);
			$lote = substr($params['lote'], 0 , 4);
            $convenio = substr($params['convenio'], 0, 6);
			$registro = substr($data['registro'], 0 , 1);
			$num_registro = str_pad(substr($data['num_registro'], 0 , 5), 5, '0', STR_PAD_LEFT);
			$segmento = substr($data['segmento'], 0 , 1);
			$cod_mov = substr($data['cod_mov'], 0 , 2);
			$agencia = str_pad(substr($data['agencia'], 0 , 6), 6, '0', STR_PAD_LEFT);
			$conta = str_pad(substr($data['conta'], 0 , 13), 13, '0', STR_PAD_LEFT);
			$nosso_numero = str_pad(substr($data['nosso_numero'], 0 , 17), 17, ' ', STR_PAD_RIGHT);
			$cod_carteira = substr($data['cod_carteira'], 0 , 1);
			$forma_cadastramento = substr($data['forma_cadastramento'], 0 , 1);
			$tipo_documento = substr($data['tipo_documento'], 0 , 1);
			$id_emissao = substr($data['id_emissao'], 0 , 1);
			$id_distribuicao = substr($data['id_distribuicao'], 0 , 1);
			$num_documento = str_pad(substr($data['num_documento'], -10), 15, ' ', STR_PAD_RIGHT);
			$data_vencimento = $data['data_vencimento'];
			$valor_titulo = str_pad(substr($data['valor_titulo'], 0 , 15), 15, '0', STR_PAD_LEFT);
			$especie = substr($data['especie'], 0 , 2);
			$aceite = substr($data['aceite'], 0 , 1);
			$data_emissao = $data['data_emissao'];
			$cod_juros = substr($data['cod_juros'], 0 , 1);
			$data_juros = str_pad(substr($data['data_juros'], 0 , 8), 8, '0', STR_PAD_LEFT);
			$juros = str_pad(substr($data['juros'], 0 , 15), 15, '0', STR_PAD_LEFT);
			$cod_desc_1 = substr($data['cod_desc_1'], 0 , 1);
			$data_desc_1 = str_pad(substr($data['data_desc_1'], 0 , 8), 8, '0', STR_PAD_LEFT);
			$valor_desc_1 = str_pad(substr($data['valor_desc_1'], 0 , 15), 15, '0', STR_PAD_LEFT);
			$iof = str_pad(substr($data['iof'], 0 , 15), 15, '0', STR_PAD_LEFT);
			$valor_abatimento = str_pad(substr($data['valor_abatimento'], 0 , 15), 15, '0', STR_PAD_LEFT);
			$cod_protesto = substr($data['cod_protesto'], 0 , 1);
			$num_dias_protesto = str_pad(substr($data['num_dias_protesto'], 0 , 2), 2, '0', STR_PAD_LEFT);
			$cod_baixa = substr($data['cod_baixa'], 0 , 1);
			$num_dias_baixa = str_pad(substr($data['num_dias_baixa'], 0 , 3), 3, '0', STR_PAD_LEFT);
			$moeda = substr($data['moeda'], 0 , 2);

            $segmento_p = $banco.$lote.$registro.$num_registro.$segmento.' '.$cod_mov.$agencia.$convenio.str_pad('', 11, '0', STR_PAD_LEFT).$nosso_numero.'102';
			$segmento_p .= '20'.$num_documento.$data_vencimento.$valor_titulo.str_pad('', 6, '0', STR_PAD_LEFT).$especie.$aceite.$data_emissao;
			$segmento_p .= $cod_juros.$data_juros.$juros.$cod_desc_1.$data_desc_1.$valor_desc_1.$iof.$valor_abatimento.str_pad($nosso_numero, 25, ' ', STR_PAD_RIGHT);
			$segmento_p .= $cod_protesto.$num_dias_protesto.$cod_baixa.$num_dias_baixa.$moeda.str_pad('', 10, '0', STR_PAD_LEFT).' '.PHP_EOL;

			return $segmento_p;
        }

        public function SegmentoQ($params, $data) {
            //Params
			$banco = substr($params['cod_banco'], 0 , 3);
			$lote = substr($params['lote'], 0 , 4);
			$registro = substr($data['registro'], 0, 1);
			$num_registro = str_pad(substr($data['num_registro'], 0 , 5), 5, '0', STR_PAD_LEFT);
			$segmento = substr($data['segmento'], 0, 1);
			$cod_mov = substr($data['cod_mov'], 0, 2);
			$tipo_inscricao = substr($data['tipo_inscricao'], 0, 1);
			$numero_inscricao = str_pad(substr($data['numero_inscricao'], 0, 15), 15, '0', STR_PAD_LEFT);
			$nome = str_pad(substr($data['nome'], 0, 40), 40, ' ', STR_PAD_RIGHT);
			$endereco = str_pad(substr($data['endereco'], 0, 40), 40, ' ', STR_PAD_RIGHT);
			$bairro = str_pad(substr($data['bairro'], 0, 15), 15, ' ', STR_PAD_RIGHT);
			$cep = substr($data['cep'], 0, 8);
			$cidade = str_pad(substr($data['cidade'], 0, 15), 15, ' ', STR_PAD_RIGHT);
			$uf = substr($data['uf'], 0, 2);
			$tp_inscricao_avalista = substr($data['tp_inscricao_avalista'], 0, 1);
			$documento_avalista = str_pad(substr($data['documento_avalista'], 0, 15), 15, '0', STR_PAD_LEFT);
			$nome_avalista = str_pad(substr($data['nome_avalista'], 0, 40), 40, ' ', STR_PAD_RIGHT);

			$segmento_q = $banco.$lote.$registro.$num_registro.$segmento.' '.$cod_mov.$tipo_inscricao.$numero_inscricao.$nome.$endereco.$bairro.$cep.$cidade.$uf;
			$segmento_q .= $tp_inscricao_avalista.$documento_avalista.$nome_avalista.'000'.str_pad('', 28, ' ', STR_PAD_RIGHT).PHP_EOL;

			return $segmento_q;
        }

        public function SegmentoR($params, $data) {
            //Params
			$banco = substr($params['cod_banco'], 0 , 3);
			$lote = substr($params['lote'], 0 , 4);
			$registro = substr($data['registro'], 0, 1);
			$num_registro = str_pad(substr($data['num_registro'], 0 , 5), 5, '0', STR_PAD_LEFT);
			$segmento = substr($data['segmento'], 0, 1);
			$cod_mov = substr($data['cod_mov'], 0, 2);
			$cod_desc_2 = substr($data['cod_desc_2'], 0, 1);
			$data_desc_2 = str_pad(substr($data['data_desc_2'], 0, 8), 8, '0', STR_PAD_LEFT);
			$valor_desc_2 = str_pad(substr($data['valor_desc_2'], 0, 15), 15, '0', STR_PAD_LEFT);
			$cod_desc_3 = substr($data['cod_desc_3'], 0, 1);
			$data_desc_3 = str_pad(substr($data['data_desc_3'], 0, 8), 8, '0', STR_PAD_LEFT);
			$valor_desc_3 = str_pad(substr($data['valor_desc_3'], 0, 15), 15, '0', STR_PAD_LEFT);
			$cod_multa = substr($data['cod_multa'], 0, 1);
			$data_multa = str_pad(substr($data['data_multa'], 0, 8), 8, '0', STR_PAD_LEFT);
			$valor_multa = str_pad(substr($data['valor_multa'], 0, 15), 15, '0', STR_PAD_LEFT);

			$segmento_r = $banco.$lote.$registro.$num_registro.$segmento.' '.$cod_mov.$cod_desc_2.$data_desc_2.$valor_desc_2.$cod_desc_3.$data_desc_3.$valor_desc_3;
			$segmento_r .= $cod_multa.$data_multa.$valor_multa.str_pad('', 151, ' ', STR_PAD_RIGHT).PHP_EOL;

			return $segmento_r;
        }

        public function GenerateLoteTrailer($params) {
            $trailer = $params['cod_banco'].$params['lote'].'5'.str_pad('', 9, ' ', STR_PAD_RIGHT).str_pad(count(file($params['file'])), 6, '0', STR_PAD_LEFT).str_pad('', 217, ' ', STR_PAD_RIGHT).PHP_EOL;
			return $trailer;
        }

        public function GenerateFileTrailer($params) {
            $trailer = $params['cod_banco'].'99999'.str_pad('', 9, ' ', STR_PAD_RIGHT).'000001'.str_pad(count(file($params['file'])) + 1, 6, '0', STR_PAD_LEFT).str_pad('', 211, ' ', STR_PAD_RIGHT);
			return $trailer;
        }
    }