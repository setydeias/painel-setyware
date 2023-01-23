<?php

    include_once '/../interfaces/IStrategyConvenioParameters.php';

    class StrategySICOOBParams implements IStrategyConvenioParameters {

        public function getParams() {
            $params = array(
                'agencia' => '33570',
                'conta' => '77771',
                'carteira' => '0',
                'variacao' => '0',
                'convenio' => '0',
                'tipo_documento' => '2',
                'documento' => '03377700000198',
                'razao_social' => 'SETYDEIAS SERVICOS LTDA',
                'sigla' => 'STY'
            );

            return $params;
        } 

    }