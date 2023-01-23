<?php

    include_once '/../interfaces/IStrategyConvenioParameters.php';

    class Strategy2880844Params implements IStrategyConvenioParameters {

        public function getParams() {
            $params = array(
                'agencia' => '32964',
                'conta' => '226076',
                'carteira' => '17',
                'variacao' => '035',
                'convenio' => '2880844',
                'tipo_documento' => '2',
                'documento' => '41656034000116',
                'razao_social' => 'CONDOMINIO MORADA DO SOL NASCENTE',
                'sigla' => 'MSN'
            );

            return $params;
        }

    }