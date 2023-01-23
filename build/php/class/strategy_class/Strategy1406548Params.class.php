<?php

    include_once '/../interfaces/IStrategyConvenioParameters.php';

    class Strategy1406548Params implements IStrategyConvenioParameters {

        public function getParams() {
            $params = array(
                'agencia' => '29068',
                'conta' => '77771',
                'carteira' => '18',
                'variacao' => '086',
                'convenio' => '1406548',
                'tipo_documento' => '2',
                'documento' => '03377700000198',
                'razao_social' => 'SETYDEIAS SERVICOS LTDA',
                'sigla' => 'STY'
            );

            return $params;
        } 

    }