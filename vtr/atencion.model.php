<?
    /**
     * request_getter.model.php
     * Model to query the WS server
     *
     * @author     Ivan Valenzuela
     * @copyright  Imasd Group SPA
     * @version    1.0
     */
    class Atencion{
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

        protected function obtener_tiempo_espera($sucursal) {
            $consulta = "SELECT ((SUM(pdae.pro_dur_ate_eje_minutos) / COUNT(ej.eje_id)) * COUNT(a.ate_id)) AS promedio FROM ejecutivo ej LEFT JOIN promedio_duracion_atencion_ejecutivo pdae ON ej.eje_id = pdae.eje_id JOIN atencion a ON ej.suc_id = a.suc_id WHERE ej.suc_id = $sucursal AND a.est_id = 1";
            $rs = $this->sql_con->query($consulta);
            if ($rs === false) {
                //trigger_error('Wrong SQL: ' . $consulta . ' Error: ' . $this->sql_con->error, E_USER_ERROR);
                return 0;
            } else {
                $row = $rs->fetch_assoc();
                return $row['promedio'];
            }
        }

        public function validar_hora($atencion) {
            $consulta = "SELECT tip_ate_suc_tiempo_espera AS tiempo_espera, tip_ate_suc_fila_unica AS es_fila_unica FROM tipo_atencion_sucursal WHERE suc_id = " . $atencion['sucursal'] . " AND tip_ate_id = " . $atencion['tipo'];
            $rs = $this->sql_con->query($consulta);
            if ($rs === false) {
                //trigger_error('Wrong SQL: ' . $consulta . ' Error: ' . $this->sql_con->error, E_USER_ERROR);
                return false;
            } else {
                $row = $rs->fetch_assoc();
                if ($row['es_fila_unica'] == 0) {
                    $row['tiempo_espera'] = $this->obtener_tiempo_espera($atencion['sucursal']);
                }
                $fecha = date('Y-m-d H:i:s');
                if (!$row['tiempo_espera']) {
                    $row['tiempo_espera'] = 0;
                }
                $row['tiempo_espera'] = round($row['tiempo_espera']);
                $nueva_fecha = date('Y-m-d H:i:s', strtotime('+' . $row['tiempo_espera'] . ' MINUTE', strtotime($fecha)));
                $fecha_atencion = substr($fecha, 0, 10) . ' ' . $atencion['hora'] . ':00';
                if ($nueva_fecha > $fecha_atencion) {
                    return false;
                } else {
                    return true;
                }
            }
        }

        protected function obtener_numero($sucursal, $tipo_atencion) {
            $consulta = "SELECT n.num_numero AS num, n.num_max as max, n.num_min as min, nt.num_tip_id AS tipo_numero FROM numero n RIGHT JOIN numero_tipo nt ON n.num_tip_id = nt.num_tip_id RIGHT JOIN tipo_atencion_sucursal tas ON n.num_id = tas.num_id WHERE tas.tip_ate_id = $tipo_atencion AND tas.suc_id = $sucursal LIMIT 1;";
            $rs = $this->sql_con->query($consulta);
            if ($rs === false) {
                $this->datos['success'] = 0;
                //trigger_error('Wrong SQL: ' . $consulta . ' Error: ' . $this->sql_con->error, E_USER_ERROR);
                exit;
            } else {
                $row = $rs->fetch_assoc();
                if (empty($row)) {
                    $num = "A1";
                }
                $num = preg_replace("/[^0-9]/", "", $row['num']);
                if ($num == '') {
                    $num = 0;
                }
                $letra = preg_replace("/[^a-zA-Z]/", "", $row['num']);
                if ($num >= $row['max']) {
                    echo $row['min'];
                    $num = $row['min'];
                    $abc = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
                    $indice = array_search($letra, $abc);
                    $letra = $abc[$indice + 1];
                } else {
                    $num++;
                }
                $num = $letra . $num;
                return $num;
            }
        }

        protected function actualizar_numero($num, $tipo_atencion, $sucursal) {
            $stmt = $this->sql_con->prepare("UPDATE numero SET num_numero = ? WHERE num_id = (SELECT num_id FROM tipo_atencion_sucursal WHERE tip_ate_id = ? AND suc_id = ?)");
            $stmt->bind_param('sii', $num, $tipo_atencion, $sucursal);
            $stmt->execute();
            $afectadas = $this->sql_con->affected_rows;
            $stmt->close();
            return $afectadas;
        }

        public function set_atencion($nuevaAtencion){
            if ($nuevaAtencion['hora'] == 0) {
                $numero = $this->obtener_numero($nuevaAtencion['sucursal'], $nuevaAtencion['tipo']);
                $hora = date('Y-m-d H:i:s');
            } else {
                $hora = $nuevaAtencion['hora'];
            }
            $insercion = $this->sql_con->prepare("INSERT INTO atencion(ate_id, ate_fecha_inicio, ate_fecha_fin, ate_calificacion_cliente, ate_calificacion_ejecutivo, tip_ate_id, est_id, suc_id, eje_id, cli_id, ate_numero) VALUES (null, ?, null,null,null,?,?,?,null,?, ?)");
            $insercion->bind_param('siiiis',
                    $hora,
                    $nuevaAtencion['tipo'] ,
                    $nuevaAtencion['estado'],
                    $nuevaAtencion['sucursal'],
                    $nuevaAtencion['cliente'],
                    $numero);
            if ($nuevaAtencion['hora'] == 0) {
                $this->datos['numero_actualizado'] = $this->actualizar_numero($numero, $nuevaAtencion['tipo'], $nuevaAtencion['sucursal']);
            }
            if ($insercion->execute()){
                $this->datos['success'] = 1;
                $this->datos['numero'] = $numero;
            }
            else {
                $this->datos['success'] = 0;
            }
            $insercion->close();
        }

        public function estado_atencion($atencion) {
            $consulta = "SELECT a.est_id AS estado FROM atencion a WHERE ate_id = $atencion";
            $rs = $this->sql_con->query($consulta);
            if ($rs === false) {
                return 1;
            } else {
                $row = $rs->fetch_assoc();
                return $row['estado'];
            }
        }

        public function abordar_cliente($atencion, $ejecutivo) {
            $stmt = $this->sql_con->prepare("UPDATE atencion SET eje_id = ?, ate_fecha_real_inicio = NOW(), est_id = 2 WHERE ate_id = ?");
            $stmt->bind_param('ii', $ejecutivo, $atencion);
            $stmt->execute();
            $afectadas = $this->sql_con->affected_rows;
            $stmt->close();
            if ($afectadas > 0) {
                $this->datos['success'] = 1;
            } else {
                $this->datos['success'] = 0;
            }
        }

        public function cerrar_atencion($atencion, $estado, $calificacion) {
            $stmt = $this->sql_con->prepare("UPDATE atencion SET est_id = ?, ate_fecha_real_fin = NOW(), ate_calificacion_ejecutivo = ? WHERE ate_id=?");
            $stmt->bind_param('iii', $estado, $calificacion, $atencion);
            $stmt->execute();
            $afectadas = $this->sql_con->affected_rows;
            $stmt->close();
            if ($afectadas > 0) {
                $this->datos['success'] = 1;
            } else {
                $this->datos['success'] = 0;
            }
        }

        public function obtener_atenciones($tipo_atencion, $estado, $sucursal) {
            require 'funciones.php';
            $funcion = new Funciones();
            $consulta = "SELECT a.ate_id AS id, a.ate_fecha_inicio AS fecha_inicio, a.ate_fecha_fin AS fecha_fin, a.ate_calificacion_cliente AS cal_cliente, a.ate_calificacion_ejecutivo AS cal_eje, a.tip_ate_id AS tipo_atencion_id, ta.tip_ate_descripcion AS tipo_atencion_descripcion, a.est_id AS estado_id, e.est_descripcion AS estado_descripcion, a.suc_id AS sucursal_id, s.suc_direccion AS sucursal_direccion, s.suc_latitud AS sucursal_latitud, s.suc_longitud AS sucursal_longitud, s.com_id AS comuna_sucursal_id, c.com_nombre AS comuna_sucursal_nombre, c.reg_id AS region_sucursal_id, r.reg_nombre AS region_nombre, a.eje_id AS ejecutivo_id, ej.eje_nombre AS ejecutivo_nombre, ej.eje_rut AS ejecutivo_rut, ej.eje_foto AS ejecutivo_foto, a.cli_id AS cliente_id, cl.cli_nombre AS cliente_nombre, cl.cli_rut AS cliente_rut, cl.cli_foto AS cliente_foto FROM atencion a LEFT JOIN tipo_atencion ta ON a.tip_ate_id = ta.tip_ate_id LEFT JOIN estado e ON a.est_id = e.est_id LEFT JOIN sucursal s ON a.suc_id = s.suc_id LEFT JOIN comuna c ON s.com_id = c.com_id LEFT JOIN region r ON c.reg_id = r.reg_id LEFT JOIN ejecutivo ej ON a.eje_id = ej.eje_id LEFT JOIN cliente cl ON a.cli_id = cl.cli_id";
            if ($tipo_atencion != 'all') {
                $consulta .= $funcion->concatenar('having', ' AND ');
                $consulta .= "a.tip_ate_id = $tipo_atencion";
            }
            if ($estado > 0) {
                $consulta .= $funcion->concatenar('having', ' AND ');
                $consulta .= "a.est_id = $estado";
            }
            if ($sucursal > 0) {
                $consulta .= $funcion->concatenar('having', ' AND ');
                $consulta .= "a.suc_id = $sucursal";
            }
            $rs = $this->sql_con->query($consulta);
            if ($rs === false) {
                //trigger_error('Wrong SQL: ' . $consulta . ' Error: ' . $this->sql_con->error, E_USER_ERROR);
                $this->datos['success'] = 0;
                exit;
            } else {
                $this->datos['success'] = 1;
                $this->datos['atenciones'] = array();
                while ($row = $rs->fetch_assoc()) {
                    $dato = array();
                    foreach ($row as $indice => $value) {
                        $dato[$indice] = $value;
                    }
                    array_push($this->datos['atenciones'], $dato);
                }
            }
        }

        public function calificar_atencion_cliente($params) {
            $stmt = $this->sql_con->prepare("UPDATE atencion SET ate_calificacion_cliente = ? WHERE ate_id = ?");
            $stmt->bind_param('ii', $params['calificacion'], $params['atencion']);
            $stmt->execute();
            $afectadas = $this->sql_con->affected_rows;
            $stmt->close();
            if ($afectadas > 0) {
                $this->datos['success'] = 1;
            } else {
                $this->datos['success'] = 0;
            }
        }

        public function limpiar_variable($variable) {
            $variable = mysqli_real_escape_string($this->sql_con, $variable);
            return $variable;
        }

        public function __destruct(){
            if (count($this->datos) > 0)
                echo json_encode($this->datos);
        }
    }
?>