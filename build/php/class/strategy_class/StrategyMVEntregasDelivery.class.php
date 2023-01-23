<?php

    include_once '/../interfaces/IStrategyGetDelivery.php';

    class StrategyMVEntregasDelivery implements IStrategyGetDelivery {

        public function getDelivery() {
            $delivery = array(
                'COD_REGISTRO' => '02',
                'COD_AG_ENTREGADOR' => '003',
                'NOME_AG_ENTREGADOR' => str_pad('MV ENTREGAS', 50, ' ', STR_PAD_RIGHT),
                'ENDERECO_AG_ENTREGADOR' => str_pad('Rua da Bravura, 50 AP 201 - Bom Jardim', 60, ' ', STR_PAD_RIGHT),
                'DICAS_ENDERECO_AG_ENTREGADOR' => str_pad('', 50, ' ', STR_PAD_RIGHT),
                'CIDADE_AG_ENTREGADOR' => str_pad('Fortaleza', 27, ' ', STR_PAD_RIGHT),
                'UF_AG_ENTREGADOR' => 'CE',
                'CEP_AG_ENTREGADOR' => '60544773',
                'PAIS_AG_ENTREGADOR' => str_pad('Brasil', 25, ' ', STR_PAD_RIGHT),
                'CONTATO_AG_ENTREGADOR' => utf8_decode(str_pad('Vitor ou Claudia', 50, ' ', STR_PAD_RIGHT)),
                'TELEFONES_AG_ENTREGADOR' => str_pad('(85) 98763.8492 - (85) 98763.8492', 35, ' ', STR_PAD_RIGHT),
                'EMAIL_AG_ENTREGADOR' => str_pad('mventregas@hotmail.com', 50, ' ', STR_PAD_RIGHT)
            );

            return $delivery;
        } 

    }