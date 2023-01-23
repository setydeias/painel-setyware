<?php

    include_once '/../interfaces/IStrategyConvenioParameters.php';

    class Strategy3061856Params implements IStrategyConvenioParameters {

        public function getParams() {
            $params = array(
                'agencia' => '29068',
                'conta' => '77771',
                'carteira' => '17',
                'variacao' => '051',
                'convenio' => '3061856',
                'tipo_documento' => '2',
                'documento' => '03377700000198',
                'razao_social' => 'SETYDEIAS SERVICOS LTDA',
                'sigla' => 'STY'
            );

            return $params;
        } 

    }