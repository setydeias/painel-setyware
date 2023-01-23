<?php

    include_once 'dao/RetornoDAO.class.php';
    include_once 'Util.class.php';
    include_once 'Setydeias.class.php';

    class Retorno {

        public function __construct(array $data) {
            $this->dao = new RetornoDAO();
            $this->customer = $data['customer'];
            $this->convenio = $data['convenio'];
            $this->data_de = Util::FmtDate($data['data_arquivo']['de'], '27');
            $this->data_ate = isset($data['data_arquivo']['ate']) ? Util::FmtDate($data['data_arquivo']['ate'], '27') : '';
        }

        /**
         * Obtém os títulos pagos de acordo com os parâmetros do array
         *
         * @return array
         */
        public function getPaid() {
            return $this->dao->getPaidBillet(array(
                'customer' => $this->customer,
                'convenio' => $this->convenio,
                'data_de' => $this->data_de,
                'data_ate' => $this->data_ate
            ));
        }

    }