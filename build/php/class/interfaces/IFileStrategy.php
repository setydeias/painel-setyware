<?php

    interface IFileStrategy {

        public function GenerateFileHeader();
        public function GenerateLoteHeader();
        public function GenerateLoteTrailer();
        public function GenerateFileTrailer();
        public function create();

    }