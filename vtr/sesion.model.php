<?php
    class Sesion {

        protected $datos = array();

        public function __construct() {
        }

        public function obtener_datos_sesion($parametros) {
            $this->datos['sesion'] = array();
            for ($i = 0; $i < count($parametros); $i++) {
                $this->datos['sesion'][$parametros[$i]] = $_SESSION[$parametros[$i]];
            }
            $this->datos['success'] = 1;
        }

        public function cerrar_sesion() {
            setcookie("datos_sesion","",time()-3600);
            $parametros_cookies = session_get_cookie_params();
            setcookie(session_name(),0,1,$parametros_cookies["path"]);
            if(session_destroy())
                $this->datos['success'] = 1;
            else
                $this->datos['success'] = 0;
        }

        public function __destruct() {
            echo json_encode($this->datos);
        }

    }
?>