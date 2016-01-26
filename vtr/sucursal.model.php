<?
    /**
     * request_getter.model.php
     * Model to query the X server
     *
     * @author     Jaime Llanos
     * @copyright  Imasd Group SPA
     * @version    1.0
     */
    class Sucursales{
        protected $sql_con;
        protected $datos = array();
        protected $host;
        protected $having = 0, $where = 0;

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

        public function registrar_sucursal($sucursal){
            $this->datos['success'] = 0;

            $insercion = $this->sql_con->prepare("INSERT INTO sucursal (suc_direccion, suc_latitud, suc_longitud, com_id) VALUES (?, ?, ?, ?)");
            $insercion->bind_param('sssi',
                        $sucursal['direccion'],
                        $sucursal['latitud'],
                        $sucursal['longitud'],
                        $sucursal['comuna']
        );
            $insercion->execute();
            $afectadas = $this->sql_con->affected_rows;
            $insercion->close();
            if ($afectadas > 0) {
                $this->datos['success'] = 1;
            } else {
                $this->datos['success'] = 0;
            }
            $this->datos['filas_ingresadas'] = $afectadas;
        }

        protected function obtener_tiempo_espera_sucursal($sucursal) {
            $consulta = "SELECT ((SUM(pdae.pro_dur_ate_eje_minutos) / COUNT(ej.eje_id)) * COUNT(a.ate_id)) AS promedio FROM ejecutivo ej LEFT JOIN promedio_duracion_atencion_ejecutivo pdae ON ej.eje_id = pdae.eje_id JOIN atencion a ON ej.suc_id = a.suc_id WHERE ej.suc_id = $sucursal AND a.est_id = 1";
            $rs = $this->sql_con->query($consulta);
            if ($rs === false) {
                return 0;
            } else {
                $row = $rs->fetch_assoc();
                if ($row['promedio'] == null) {
                    $row['promedio'] = 0;
                }
                return $row['promedio'];
            }
        }

        public function obtener_sucursales_cercanas($latitud, $longitud, $radio, $limite, $tipo_atencion) {
            /*$consulta = "SELECT SQRT(
                        POW(69.1 * (suc_latitud - $latitud), 2) +
                        POW(69.1 * ($longitude - suc_longitud) * COS(suc_latitud / 57.3), 2)) AS radio, suc_direccion as direccion
                        FROM sucursal HAVING radio < $radio ORDER BY direccion LIMIT $limite;";*/
            /*$consulta = "SELECT ((ACOS(SIN($latitud * PI() / 180) * SIN(suc_latitud * PI() / 180) + COS($latitud * PI() / 180) * COS(suc_latitud * PI() / 180) * COS(($longitud - suc_longitud) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS distance, suc_direccion as direccion FROM sucursal WHERE (suc_latitud BETWEEN ($latitud - $radio) AND ($latitud + $radio) AND suc_longitud BETWEEN ($longitud - $radio) AND ($longitud + $radio)) ORDER BY distance ASC LIMIT $limite;";*/
            require 'funciones.php';
            $funcion = new Funciones();
            $consulta = "SELECT DISTINCT s.suc_id AS id, (acos(sin(radians($latitud)) * sin(radians(s.suc_latitud)) + cos(radians($latitud)) * cos(radians(s.suc_latitud)) * cos(radians($longitud) - radians(s.suc_longitud))) * 6378) AS distancia, s.suc_direccion AS direccion, ta.tip_ate_descripcion AS tipo_atencion, ta.tip_ate_id AS tipo_atencion_id, tas.tip_ate_suc_tiempo_espera AS tiempo_espera, tas.tip_ate_suc_fila_unica AS fila_unica, s.com_id AS comuna_id, c.com_nombre AS comuna_nombre, c.reg_id AS region_id, r.reg_nombre AS region_nombre";
            /*
                AÑADIR CAMPOS DE RETORNO
            */
            $consulta .= " FROM sucursal s";
            /*
                AÑADIR JOINS
            */
            $consulta .= " LEFT JOIN comuna c ON s.com_id = c.com_id LEFT JOIN region r ON c.reg_id = r.reg_id JOIN tipo_atencion_sucursal tas ON s.suc_id = tas.suc_id RIGHT JOIN tipo_atencion ta ON tas.tip_ate_id = ta.tip_ate_id RIGHT JOIN ejecutivo ej ON s.suc_id = ej.suc_id";
            /*
                AÑADIR CONDICIONES
            */
            if ($tipo_atencion > 0) {
                $consulta .= $funcion->concatenar('where', ' AND ');
                $consulta .= "tas.tip_ate_id = $tipo_atencion";
            }
            if ($radio > 0) {
                $consulta .= $funcion->concatenar('having', ' AND ');
                $consulta .= "distancia < $radio";
            }
            /*
                ULTIMAS INSTRUCCIONES ANTES DE CIERRE DE consulta
            */
            $consulta .= " ORDER BY distancia, tipo_atencion";
            $consulta .= " LIMIT $limite;";
            //echo $consulta;
            $rs = $this->sql_con->query($consulta);
            if ($rs === false) {
                $this->datos['success'] = 0;
                //trigger_error('Wrong SQL: ' . $consulta . ' Error: ' . $this->sql_con->error, E_USER_ERROR);
                exit;
            } else {
                $this->datos['success'] = 1;
                $this->datos['sucursales'] = array();
                while ($row = $rs->fetch_assoc()) {
                    $dato = array();
                    foreach ($row as $indice => $value) {
                        $dato[$indice] = $value;
                    }
                    if($dato['fila_unica'] == 0) {
                        $dato['tiempo_espera'] = $this->obtener_tiempo_espera_sucursal($dato['id']);
                    }
                    array_push($this->datos['sucursales'], $dato);
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