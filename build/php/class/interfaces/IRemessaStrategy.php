<?php

    interface IRemessaStrategy {

        public function getSegmentoP($data);
        public function getSegmentoQ($data);
        public function getSegmentoR($data);

    }