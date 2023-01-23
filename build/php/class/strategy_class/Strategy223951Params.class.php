<?php

    include_once '/../interfaces/IStrategyConvenioParameters.php';

    class Strategy223951Params implements IStrategyConvenioParameters {

        public function getParams() {
            $params = array(
                'agencia' => '29254',
                'conta' => '145610',
                'carteira' => '18',
                'variacao' => '019',
                'convenio' => '223951',
                'tipo_documento' => '2',
                'documento' => '07845191000131',
                'razao_social' => 'JOCKEY CLUB CEARENSE',
                'sigla' => 'JKC'
            );

            return $params;
        } 

    }