<?php

    include_once '/../interfaces/IStrategyConvenioParameters.php';

    class Strategy040450Params implements IStrategyConvenioParameters {

        public function getParams() {
            $params = array(
                'agencia' => '15598',
                'conta' => '6006',
                'carteira' => '0',
                'variacao' => '0',
                'convenio' => '040450',
                'tipo_documento' => '2',
                'documento' => '03377700000198',
                'razao_social' => 'SETYDEIAS SERVICOS LTDA',
                'sigla' => 'STY'
            );

            return $params;
        }

    }