<?php

    include_once '/../interfaces/IStrategyConvenioParameters.php';

    class Strategy264151Params implements IStrategyConvenioParameters {

        public function getParams() {
            $params = array(
                'agencia' => '15636',
                'conta' => '77770',
                'carteira' => '0',
                'variacao' => '0',
                'convenio' => '264151',
                'tipo_documento' => '2',
                'documento' => '03377700000198',
                'razao_social' => 'SETYDEIAS SERVICOS LTDA',
                'sigla' => 'STY'
            );

            return $params;
        }
    }