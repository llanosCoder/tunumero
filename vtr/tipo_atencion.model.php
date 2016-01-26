<?
    /**
     * tipo_atencion.model.php
     * Model to query the X server
     *
     * @author     Jaime Llanos
     * @copyright  Imasd Group SPA
     * @version    1.0
     */
    class TipoServicios{
        protected $sql_con;
        protected $datos = array();
        protected $host;

        public function __construct(){
            require('database.conf.php');
        }

        protected function set_conexion(){
            $hosteo = new Host($this->host);
            $this->sql_con = new mysqli($hosteo->datos['host'], $hosteo->datos['user'], $hosteo->datos['pass'], $hosteo->datos['bd']);
            $this->sql_con->set_charset('utf8');
        }

        public function set_host($host) {
            $this->host = $host;
            $this->set_conexion();
        }

        public function obtener_servicios_disponibles($sucursal){
            $consulta ='SELECT ta.tip_ate_id AS id, ta.tip_ate_descripcion AS servicio FROM tipo_atencion ta';
            if ($sucursal > 0) {
                $consulta .= " RIGHT JOIN tipo_atencion_sucursal tas ON ta.tip_ate_id = tas.tip_ate_id WHERE tas.suc_id = $sucursal";
            }
            $result = $this->sql_con->query($consulta);

            if($result === false) {
                trigger_error('Wrong SQL: ' . $consulta . ' Error: ' . $this->sql_con->error, E_USER_ERROR);
                $this->datos['success'] = 0;
            } else {
                $this->datos['success'] = 1;
                $this->datos['servicios'] = array();
                while ($row = $result->fetch_assoc()) {
                    foreach ($row as $indice => $value) {
                        $dato[$indice] = $value;
                    }
                    array_push($this->datos['servicios'], $dato);
                }
            }
        }

        public function limpiar_variable($variable) {
            $variable = mysqli_real_escape_string($this->sql_con, $variable);
            return $variable;
        }

        public function __destruct(){
            echo json_encode($this->datos);
        }
    }
?>