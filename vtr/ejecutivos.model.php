<?php

class Ejecutivo{

    protected $sql_con;
    protected $datos = array();

    public function __construct() {
        require 'database.conf.php';
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

    public function obtener_ejecutivos($sucursal, $tipo_atencion, $estado, $group) {
        require 'funciones.php';
        $funcion = new Funciones();
        $consulta = "SELECT DISTINCT ej.eje_id as ejecutivo_id, ej.eje_nombre AS ejecutivo_nombre, tas.tip_ate_suc_tiempo_espera as tiempo_espera FROM atencion a RIGHT JOIN tipo_atencion ta ON a.tip_ate_id = ta.tip_ate_id RIGHT JOIN tipo_atencion_sucursal tas ON ta.tip_ate_id = tas.tip_ate_id RIGHT JOIN sucursal s ON tas.suc_id = s.suc_id RIGHT JOIN ejecutivo ej ON s.suc_id = ej.suc_id RIGHT JOIN tipo_atencion_ejecutivo tae ON ej.eje_id = tae.eje_id";
        if ($sucursal > 0) {
            $consulta .= $funcion->concatenar('where', ' AND ');
            $consulta .= "s.suc_id = $sucursal";
        }
        if ($tipo_atencion > 0) {
            $consulta .= $funcion->concatenar('where', ' AND ');
            $consulta .= "ta.tip_ate_id = $tipo_atencion";
        }
        if ($estado > 0) {
            $consulta .= $funcion->concatenar('where', ' AND ');
            $consulta .= "ej.eje_est_id = $estado";
        }
        if ($group == 1) {
            $consulta .= ' GROUP BY ej.eje_id';
        }
        $consulta .= " ORDER BY tas.tip_ate_suc_tiempo_espera";
        $rs = $this->sql_con->query($consulta);
        if ($rs === false) {
            //trigger_error('Wrong SQL: ' . $consulta . ' Error: ' . $this->sql_con->error, E_USER_ERROR);
            $this->datos['success'] = 0;
            exit;
        } else {
            $this->datos['success'] = 1;
            $this->datos['ejecutivos'] = array();
            while ($row = $rs->fetch_assoc()) {
                $dato = array();
                foreach ($row as $indice => $value) {
                    $dato[$indice] = $value;
                }
                array_push($this->datos['ejecutivos'], $dato);
            }
        }
    }

    public function login($user, $pass, $remember) {
        $consulta = "SELECT count(*) AS cont, ej.eje_id AS eje_id, ej.eje_username AS user, ej.eje_nombre AS nombre, ej.eje_foto AS foto, ej.suc_id AS eje_suc FROM ejecutivo ej JOIN ejecutivo_pass ep ON ej.eje_id = ep.eje_id WHERE eje_pas_password COLLATE latin1_general_ci LIKE md5('$pass') AND ej.eje_username = '$user'";
        $rs = $this->sql_con->query($consulta);
        if ($rs === false) {
            trigger_error('Wrong SQL: ' . $consulta . ' Error: ' . $this->sql_con->error, E_USER_ERROR);
            $this->datos['success'] = 0;
            exit;
        } else {
            $this->datos['success'] = 1;
            $row = $rs->fetch_assoc();
            if ($row['cont'] > 0) {
                $this->datos['login'] = 1;
                $_SESSION['ejecutivo_id'] = $row['eje_id'];
                $_SESSION['user'] = $row['user'];
                $_SESSION['ejecutivo_nombre'] = $row['nombre'];
                $_SESSION['ejecutivo_foto'] = $row['foto'];
                $_SESSION['ejecutivo_sucursal'] = $row['eje_suc'];
                $_SESSION['type'] = 'eje';
                if($remember == 1){
                    setcookie("datos_sesion", implode(',', $_SESSION), time()+60*60*24*6004);
                }
            } else {
                $this->datos['login'] = 0;
            }
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