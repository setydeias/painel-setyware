<?php

    include_once '/../interfaces/IStrategyGetDelivery.php';

    class StrategyCorreiosDelivery implements IStrategyGetDelivery {

        public function getDelivery() {
            $delivery = array(
                'COD_REGISTRO' => '02',
                'COD_AG_ENTREGADOR' => '002',
                'NOME_AG_ENTREGADOR' => str_pad('ECT/ACF-CORREIOS Av Bezerra de Menezes', 50, ' ', STR_PAD_RIGHT),
                'ENDERECO_AG_ENTREGADOR' => utf8_decode(str_pad('Av Bezerra de Menezes, 1351 - Parquelandia', 60, ' ', STR_PAD_RIGHT)),
                'DICAS_ENDERECO_AG_ENTREGADOR' => str_pad('vizinho ao DETRAN - Bez Menezes', 50, ' ', STR_PAD_RIGHT),
                'CIDADE_AG_ENTREGADOR' => str_pad('Fortaleza', 27, ' ', STR_PAD_RIGHT),
                'UF_AG_ENTREGADOR' => 'CE',
                'CEP_AG_ENTREGADOR' => '60325004',
                'PAIS_AG_ENTREGADOR' => str_pad('Brasil', 25, ' ', STR_PAD_RIGHT),
                'CONTATO_AG_ENTREGADOR' => utf8_decode(str_pad('Dora ou Ieda', 50, ' ', STR_PAD_RIGHT)),
                'TELEFONES_AG_ENTREGADOR' => str_pad('(85) 3287.3737 - (85) 98768.3137', 35, ' ', STR_PAD_RIGHT),
                'EMAIL_AG_ENTREGADOR' => str_pad('acfbezerrademenezes@uol.com.br', 50, ' ', STR_PAD_RIGHT)
            );

            return $delivery;
        } 

    }