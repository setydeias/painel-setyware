<?php

    include_once 'StrategyCorreiosDelivery.class.php';
    include_once 'StrategyMVEntregasDelivery.class.php';

    class StrategyDelivery {

        private $strategy;

        public function __construct($package_kind) {
            switch ( $package_kind ) {
                case 'U':
                    $this->strategy = new StrategyMVEntregasDelivery();
                break;
                case 'D':
                    $this->strategy = new StrategyCorreiosDelivery();
                break;
            }
        }

        public function getDelivery() {
            return $this->strategy->getDelivery();
        }

    }