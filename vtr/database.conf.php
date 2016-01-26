<?php
    /**
     * database.conf.php
     * Universal Secure DB configuration
     *
     * @author     IvÃ¡n Valenzuela
     * @copyright  nTIC Ltda.
     * @version    1.0
     */

    class Host{
        public $hosting;
        public $datos = array();

        public function __construct($server){
            $this->hosting = $server;
            $this->obtenerConexion();
        }

        public function obtenerConexion(){
            switch($this->hosting){
                case '1':
                    $this->datos['host'] = 'localhost';
                    $this->datos['user'] = 'imasdgroup';
                    $this->datos['pass'] = '@ServerPlatform2015';
                    $this->datos['bd'] = 'imasdgro_vtr';
                    break;
            }
        }
    }
?>