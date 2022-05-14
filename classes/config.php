<?php 
    class Config {
        private $domain;
        private $urlApi;
        private $urlNode;
        private $apiToken;
        private $validModules;
        private $nomediaImg;
        private $defaultLang;

        public function __construct(){
            $this->validModules = array(
                'signup', 'singin', 'Anime', "Buscador", 'Collection', "Entradas", "Errors",
                'aleatory', 'EpisodesDetails', 'Profiles', "User", "Apidoc", 'Filesystem', 
                'Showerrors', 'Backup', "Home", "Edit", "Events", "Blog", "Cart", 'OpeningsDetails', 
                'EndingsDetails'
            );
            $cnfg = parse_ini_file(__DIR__.'/../conf/config.ini');
            $this->domain = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $cnfg['domain'];
            $this->urlApi = "http://{$this->getDomain()}?r=";
            $this->urlNode = $cnfg['urlNode'];
            $this->apiToken = $cnfg['apiToken'];
            $this->nomediaImg = $cnfg['nomediaImg'];
            $this->defaultLang = $cnfg['defaultLang'];
        }

        public function getApiToken() {
            return $this->apiToken;
        }

        public function getUrlApi(){
            return $this->urlApi;
        }

        public function getValidModules(){
            return $this->validModules;
        }

        public function setValidModules($validModules){
            $this->validModules = $validModules;
        }

        public function getNomediaImg(){
            return $this->nomediaImg;
        }

        public function getUrlNode(){
            return $this->urlNode;
        }

        public function getDefaultLang(){
            return $this->defaultLang;
        }

        public function getDomain(){
            return $this->domain;
        }

        public function getConstructConf(){
            return $this->constructConf;
        }

        public function setConstructConf($constructConf){
            $this->constructConf = $constructConf;
        }
    }
?>