<?php

    include_once '/../interfaces/IStrategyConvenioParameters.php';

    class Strategy3109077Params implements IStrategyConvenioParameters {

        public function getParams() {
            $params = array(
                'agencia' => '32964',
                'conta' => '410616',
                'carteira' => '17',
                'variacao' => '019',
                'convenio' => '3109077',
                'tipo_documento' => '2',
                'documento' => '63476493000150',
                'razao_social' => 'CONDOMINIO EDIFICIO CORAL',
                'sigla' => 'CRL'
            );

            return $params;
        }

    }