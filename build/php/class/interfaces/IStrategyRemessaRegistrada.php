<?php

    interface IStrategyRemessaRegistrada {

        public function GenerateFileHeader($params);
        public function GenerateLoteHeader($params);
        public function SegmentoP($params, $data);
        public function SegmentoQ($params, $data);
        public function SegmentoR($params, $data);
        public function GenerateLoteTrailer($params);
        public function GenerateFileTrailer($params);

    }