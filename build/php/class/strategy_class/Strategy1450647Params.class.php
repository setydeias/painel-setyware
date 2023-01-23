<?php

    include_once '/../interfaces/IStrategyConvenioParameters.php';

    class Strategy1450647Params implements IStrategyConvenioParameters {

        public function getParams() {
            $params = array(
                'agencia' => '29173',
                'conta' => '1112856',
                'carteira' => '18',
                'variacao' => '019',
                'convenio' => '1450647',
                'tipo_documento' => '1',
                'documento' => '42557550353',
                'razao_social' => 'FRANCISCO GLAYSON DE SOUSA LIMA',
                'sigla' => 'GLN'
            );

            return $params;
        }

    }