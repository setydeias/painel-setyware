<?php

    include_once '/../interfaces/IStrategyConvenioParameters.php';

    class Strategy21777Params implements IStrategyConvenioParameters {

        public function getParams() {
            $params = array(
                'agencia' => '06491',
                'conta' => '217778',
                'carteira' => '09',
                'variacao' => '0',
                'convenio' => '0021777',
                'tipo_documento' => '2',
                'documento' => '03377700000198',
                'razao_social' => 'SETYDEIAS SERVICOS LTDA',
                'sigla' => 'STY'
            );

            return $params;
        } 

    }