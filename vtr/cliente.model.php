<?php

    /**
     * cliente.model.php
     * Model to query the X server
     *
     * @author     Jaime Llanos
     * @copyright  Imasd Group SPA
     * @version    1.0
     */

    class Clientes {

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

        public function obtener_fecha_atencion_cliente($cliente, $sucursal) {
            $consulta = "SELECT a.ate_fecha_inicio AS fecha FROM atencion a WHERE a.cli_id = $cliente AND a.suc_id = $sucursal AND est_id = 1";
            $rs = $this->sql_con->query($consulta);
            if ($rs === false) {
                //trigger_error('Wrong SQL: ' . $consulta . ' Error: ' . $this->sql_con->error, E_USER_ERROR);
                return 0;
            } else {
                $row = $rs->fetch_assoc();
                return $row['fecha'];
            }
        }

        public function obtener_clientes($estado, $tipo_atencion, $sucursal, $cliente, $ejecutivo, $group, $limit, $hoy, $fecha) {
            require 'funciones.php';
            $funcion = new Funciones();
            $consulta = "SELECT cl.cli_id AS cliente_id, cl.cli_nombre AS cliente_nombre, cl.cli_rut AS cliente_rut, cl.cli_foto AS cliente_foto, a.ate_id AS atencion_id, a.ate_fecha_inicio AS atencion_fecha_inicio, a.ate_fecha_fin AS atencion_fecha_fin, a.ate_calificacion_cliente AS atencion_calificacion_cliente, a.ate_calificacion_ejecutivo AS atencion_calificacion_ejecutivo, a.ate_numero AS numero, a.ate_modulo AS modulo, a.tip_ate_id AS tipo_atencion_id, ta.tip_ate_descripcion AS tipo_atencion_descripcion, a.est_id AS estado_id, e.est_descripcion AS estado_descripcion, a.suc_id AS sucursal, s.suc_direccion AS sucursal_direccion, s.suc_latitud AS sucursal_latitud, s.suc_longitud AS sucursal_longitud, s.com_id AS comuna_id, c.com_nombre AS comuna_nombre, c.reg_id AS region_id, r.reg_nombre AS region_nombre, a.eje_id AS ejecutivo_id, ej.eje_nombre AS ejecutivo_nombre, ej.eje_rut AS ejecutivo_rut, ej.eje_foto AS ejecutivo_foto FROM cliente cl LEFT JOIN atencion a ON cl.cli_id = a.cli_id LEFT JOIN estado e ON a.est_id = e.est_id LEFT JOIN tipo_atencion ta ON a.tip_ate_id = ta.tip_ate_id LEFT JOIN sucursal s ON a.suc_id = s.suc_id LEFT JOIN tipo_atencion_ejecutivo tae ON a.tip_ate_id = tae.tip_ate_id LEFT JOIN comuna c ON s.com_id = c.com_id LEFT JOIN region r ON c.reg_id = r.reg_id LEFT JOIN ejecutivo ej ON a.eje_id = ej.eje_id";
            if ($estado > 0) {
                $consulta .= $funcion->concatenar('where', ' AND ');
                $consulta .= "a.est_id = $estado";
            }
            if ($tipo_atencion > 0) {
                $consulta .= $funcion->concatenar('where', ' AND ');
                $consulta .= "ta.tip_ate_id = $tipo_atencion";
            }
            if ($sucursal > 0) {
                $consulta .= $funcion->concatenar('where', ' AND ');
                $consulta .= "s.suc_id = $sucursal";
            }
            if ($cliente > 0 && $fecha == 0 ) {
                $consulta .= $funcion->concatenar('where', ' AND ');
                $consulta .= "cl.cli_id = $cliente";
            }
            if ($ejecutivo > 0) {
                $consulta .= $funcion->concatenar('where', ' AND ');
                $consulta .= "tae.eje_id = $ejecutivo";
                $consulta .= $funcion->concatenar('where', ' AND (');
                $consulta .= "a.eje_id IS NULL";
                $consulta .= $funcion->concatenar('where', ' OR ');
                $consulta .= "a.eje_id = $ejecutivo)";
            }
            if ($hoy > 0) {
                $hoy = date('Y-m-d');
                $consulta .= $funcion->concatenar('where', ' AND ');
                $consulta .= "a.ate_fecha_real_inicio LIKE '$hoy%'";
            }
            if ($fecha != 0) {
                $consulta .= $funcion->concatenar('where', ' AND ');
                $consulta .= "a.ate_fecha_inicio < '$fecha'";
            }
            if ($group > 0) {
                $consulta .= ' GROUP BY cl.cli_id';
            }
            /*if ($estado > 0) {
                $consulta .= $funcion->concatenar('order', ', ');
                $consulta .= "e.est_id";
            }
            if ($tipo_atencion > 0) {
                $consulta .= $funcion->concatenar('order', ', ');
                $consulta .= "ta.tip_ate_id";
            }
            if ($sucursal > 0) {
                $consulta .= $funcion->concatenar('order', ', ');
                $consulta .= "s.suc_id";
            }
            if ($cliente > 0) {
                $consulta .= $funcion->concatenar('order', ', ');
                $consulta .= "cl.cli_id";
            }*/
            $consulta .= " ORDER BY a.ate_fecha_real_inicio DESC, a.ate_fecha_inicio ASC";
            if ($limit > 0) {
                $consulta .= " LIMIT $limit";
            }
            $rs = $this->sql_con->query($consulta);
            if ($rs === false) {
                //trigger_error('Wrong SQL: ' . $consulta . ' Error: ' . $this->sql_con->error, E_USER_ERROR);
                $this->datos['success'] = 0;
                exit;
            } else {
                $this->datos['success'] = 1;
                $this->datos['clientes'] = array();
                while ($row = $rs->fetch_assoc()) {
                    $dato = array();
                    foreach ($row as $indice => $value) {
                        $dato[$indice] = $value;
                    }
                    array_push($this->datos['clientes'], $dato);
                }
            }
        }

        protected function registrar_cliente($rut, $nombre, $foto) {
            $insercion = $this->sql_con->prepare("INSERT INTO cliente (cli_rut, cli_nombre, cli_foto) VALUES (?, ?, ?)");
            $insercion->bind_param('sss', $rut, $nombre, $foto);
            $insercion->execute();
            $id = $this->sql_con->insert_id;
            $insercion->close();
            return $id;
        }

        protected function actualizar_cliente($valores, $parametros, $rut) {
            $consulta = "UPDATE cliente SET ";
            if (count($parametros) == 0 || count($valores) === 0) {
                return 0;
            }
            for ($i = 0; $i < count($parametros); $i++) {
                if ($i > 0) {
                    $consulta .= ',';
                }
                switch ($parametros[$i]) {
                    case 'nombre':
                        $param = 'cli_nombre';
                        break;
                }
                $consulta .= " $param = '$valores[$i]'";
            }
            $consulta .= " WHERE cli_rut = '$rut'";
            $rs = $this->sql_con->query($consulta);
            if ($rs === false) {
                //trigger_error('Wrong SQL: ' . $consulta . ' Error: ' . $this->sql_con->error, E_USER_ERROR);
                return 0;
            } else {
                $afectadas = $this->sql_con->affected_rows;
                return $afectadas;
            }
        }

        public function login($rut, $nombre, $foto) {
            $consulta = "SELECT count(*) AS cont, cl.cli_id AS id, cl.cli_nombre AS nombre FROM cliente cl WHERE cl.cli_rut = '$rut'";
            $rs = $this->sql_con->query($consulta);
            if ($rs === false) {
                $this->datos['success'] = 0;
                //trigger_error('Wrong SQL: ' . $consulta . ' Error: ' . $this->sql_con->error, E_USER_ERROR);
                exit;
            } else {
                $row = $rs->fetch_assoc();
                if ($row['cont'] == 0) {
                    $id = $this->registrar_cliente($rut, $nombre, $foto);
                } else {
                    $id = $row['id'];
                    if ($nombre == '') {
                        $nombre = $row['nombre'];
                    } else {
                        $this->actualizar_cliente([$nombre], ['nombre'], $rut);
                    }
                }
                $_SESSION['cliente_id'] = $id;
                $_SESSION['cliente_rut'] = $rut;
                $_SESSION['cliente_nombre'] = $nombre;
                $_SESSION['foto'] = $foto;
                $_SESSION['type'] = 'cli';
                $this->datos['success'] = 1;
            }
        }

        public function cancelar_atencion_cliente($cliente) {
            $stmt = $this->sql_con->prepare("UPDATE atencion SET est_id = 4 WHERE cli_id = ?");
            $stmt->bind_param('i', $cliente);
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

        public function __destruct() {
            echo json_encode($this->datos);
        }

    }

?>
