<?php
    /**
     * radius.php
     * Controller for X Server methods
     *
     * @author     Jaime Llanos
     * @copyright  Imasd Group SPA
     * @version    1.0
     * @example    http://url/index.php?op=1
     */

    /* Para utilizar POST, quitar si utilizarÃ¡s GET */
    //$opcion = (isset($_POST['op'])) ? $_POST['op'] : 'none';

    /* Para utilizar GET, quitar si utilizarÃ¡s POST */
    ini_set("session.cookie_lifetime","43200");
    ini_set("session.gc_maxlifetime","43200");
    session_start();
    date_default_timezone_set('America/Argentina/Buenos_Aires');

    $opcion = (isset($_REQUEST['op'])) ? $_REQUEST['op'] : 'none';
    if ($opcion!='none'){
        switch($opcion){
            case '1':
                /* Retorna en formato JSON los servicios disponibles */
                require('tipo_atencion.model.php');
                $getter = new TipoServicios();
                $getter->set_host(1);
                if (isset($_REQUEST['suc'])) {
                    $sucursal = $getter->limpiar_variable($_REQUEST['suc']);
                } else {
                    $sucursal = 0;
                }
                $getter->obtener_servicios_disponibles($sucursal);
                break;
            case '2':
                /* Inserta una nueva atención */
                require('atencion.model.php');
                $atencion = new Atencion();
                $atencion->set_host(1);
                $nuevaAtencion['tipo'] = $_REQUEST['tipo'];
                $nuevaAtencion['estado'] = $_REQUEST['estado'];
                $nuevaAtencion['sucursal'] = $_REQUEST['sucursal'];
                if (isset($_REQUEST['cliente'])) {
                    $nuevaAtencion['cliente'] = $_REQUEST['cliente'];
                } else {
                    $nuevaAtencion['cliente'] = $_SESSION['cliente_id'];
                }
                if (isset ($_REQUEST['hora'])) {
                    $nuevaAtencion['hora'] = $_REQUEST['hora'];
                    if ($nuevaAtencion['hora'] == '') {
                        $hora = 0;
                    } else {
                        if (!$atencion->validar_hora($nuevaAtencion)) {
                            echo '{"success": 0}';
                            exit;
                        }
                        $nuevaAtencion['estado'] = 9;
                    }
                } else {
                    $nuevaAtencion['hora'] = 0;
                }
                $atencion->set_atencion($nuevaAtencion);
                break;
            case '3':
                /* Registrar nueva sucursal */
                require('sucursal.model.php');
                $getter = new Sucursales();
                $getter->set_host(1);
                $sucursal['direccion'] = $getter->limpiar_variable($_REQUEST['dir']);
                $sucursal['latitud'] = $getter->limpiar_variable($_REQUEST['lat']);
                $sucursal['longitud'] = $getter->limpiar_variable($_REQUEST['lon']);
                $sucursal['comuna'] = $getter->limpiar_variable($_REQUEST['com']);
                $getter->registrar_sucursal($sucursal);
                break;
            case '4':
                /*Obtiene las sucursales más cercanas segun unas coordenadas y un radio otorgado*/
                if (!isset($_REQUEST['lat']) || !isset($_REQUEST['lon'])) {
                    $datos['success'] = 0;
                    echo json_encode($datos);
                    exit;
                } else {
                    if ($_REQUEST['lat'] == '' || $_REQUEST['lon'] == '' || $_REQUEST['lat'] == 0 || $_REQUEST['lon'] == 0 ) {
                        $datos['success'] = 0;
                        echo json_encode($datos);
                        exit;
                    }
                    require('sucursal.model.php');
                    $getter = new Sucursales();
                    $getter->set_host(1);
                    $latitud = $getter->limpiar_variable($_REQUEST['lat']);
                    $longitud = $getter->limpiar_variable($_REQUEST['lon']);
                    if ($latitud == undefined || $latitud == null || $longitud == undefined || $longitud == null) {
                        $latitud = -33.437924;
                        $longitud = -70.650485;
                    }
                    if (isset($_REQUEST['rad'])) {
                        $radio = $getter->limpiar_variable($_REQUEST['rad']);
                    } else {
                        $radio = 0;
                    }
                    if (isset($_REQUEST['lim'])) {
                        $limite = $getter->limpiar_variable($_REQUEST['lim']);
                    } else {
                        $limite = 5;
                    }
                    if (isset($_REQUEST['ate'])) {
                        $tipo_atencion = $getter->limpiar_variable($_REQUEST['ate']);
                    } else {
                        $tipo_atencion = 0;
                    }
                    $getter->obtener_sucursales_cercanas($latitud, $longitud, $radio, $limite, $tipo_atencion);
                }
                break;
            case 5:
                /* Obtener atenciones */
                require('atencion.model.php');
                $getter = new Atencion();
                $getter->set_host(1);
                if (isset($_REQUEST['ate'])) {
                    $tipo_atencion = $getter->limpiar_variable($_REQUEST['ate']);
                } else {
                    $tipo_atencion = 1;
                }
                if (isset($_REQUEST['est'])) {
                    $estado = $getter->limpiar_variable($_REQUEST['est']);
                } else {
                    $estado = 0;
                }
                if (isset($_REQUEST['suc'])) {
                    $sucursal = $getter->limpiar_variable($_REQUEST['suc']);
                } else {
                    $sucursal = 0;
                }
                $getter->obtener_atenciones($tipo_atencion, $estado, $sucursal);
                break;
            case 6:
                //Obtener ejecutivos
                require 'ejecutivos.model.php';
                $getter = new Ejecutivo();
                $getter->set_host(1);
                if (isset($_REQUEST['suc'])) {
                    $sucursal = $getter->limpiar_variable($_REQUEST['suc']);
                } else {
                    $sucursal = 0;
                }
                if (isset($_REQUEST['tip'])) {
                    $tipo_atencion = $getter->limpiar_variable($_REQUEST['tip']);
                } else {
                    $tipo_atencion = 0;
                }
                if (isset($_REQUEST['est'])) {
                    $estado = $getter->limpiar_variable($_REQUEST['est']);
                } else {
                    $estado = 0;
                }
                if (isset($_REQUEST['grp'])) {
                    $group = $getter->limpiar_variable($_REQUEST['grp']);
                } else {
                    $group = 0;
                }
                $getter->obtener_ejecutivos($source, $tipo_atencion, $estado, $group);
                break;
            case 7:
                //Abordar cliente
                require 'atencion.model.php';
                $getter = new Atencion();
                $getter->set_host(1);
                if (isset($_REQUEST['eje'])) {
                    $ejecutivo = $getter->limpiar_variable($_REQUEST['eje']);
                } else {
                    if (isset($_SESSION['ejecutivo_id'])) {
                        $ejecutivo = $_SESSION['ejecutivo_id'];
                    } else {
                        $error = true;
                    }
                }
                if (isset($_REQUEST['ate'])) {
                    $atencion = $getter->limpiar_variable($_REQUEST['ate']);
                } else {
                    $error = true;
                }
                if ($error == true) {
                    echo '{"success": 0}';
                } else {
                    if ($getter->estado_atencion($atencion) == 1) {
                        $getter->abordar_cliente($atencion, $ejecutivo);
                    } else {
                        echo '{"success": 0}';
                    }
                }
                break;
            case 8:
                //Cerrar atencion
                require 'atencion.model.php';
                $getter = new Atencion();
                $getter->set_host(1);
                if (isset($_REQUEST['est'])) {
                    $estado = $getter->limpiar_variable($_REQUEST['est']);
                } else {
                    $error = true;
                }
                if (isset($_REQUEST['ate'])) {
                    $atencion = $getter->limpiar_variable($_REQUEST['ate']);
                } else {
                    $error = true;
                }
                if (isset($_REQUEST['cal'])) {
                    $calificacion = $getter->limpiar_variable($_REQUEST['cal']);
                    if ($calificacion == 0) {
                        $calificacion = 1;
                    }
                } else {
                    $calificacion = 1;
                }
                if ($error == true) {
                    echo '{"success": 0}';
                } else {
                    $getter->cerrar_atencion($atencion, $estado, $calificacion);
                }

                break;
            case 9:
                //Obtener clientes en espera
                require 'cliente.model.php';
                $getter = new Clientes();
                $getter->set_host(1);
                if (isset ($_REQUEST['suc'])) {
                    $sucursal = $getter->limpiar_variable($_REQUEST['suc']);
                } else {
                    if (isset($_SESSION['ejecutivo_sucursal'])) {
                        $sucursal = $_SESSION['ejecutivo_sucursal'];
                    } else {
                        $sucursal = 0;
                    }
                }
                if (isset ($_REQUEST['eje'])) {
                    $ejecutivo = $getter->limpiar_variable($_REQUEST['eje']);
                } else {
                    if (isset($_SESSION['ejecutivo_id'])) {
                        $ejecutivo = $_SESSION['ejecutivo_id'];
                    } else {
                        $ejecutivo = 0;
                    }
                }
                if (isset ($_REQUEST['cli'])) {
                    $cliente = $getter->limpiar_variable($_REQUEST['cli']);
                } else {
                    $cliente = 0;
                }
                if (isset ($_REQUEST['est'])) {
                    $estado = $getter->limpiar_variable($_REQUEST['est']);
                } else {
                    $estado = 0;
                }
                if (isset ($_REQUEST['grp'])) {
                    $group = $getter->limpiar_variable($_REQUEST['grp']);
                } else {
                    $group = 0;
                }
                if (isset ($_REQUEST['lim'])) {
                    $limit = $getter->limpiar_variable($_REQUEST['lim']);
                } else {
                    $limit = 0;
                }
                if (isset ($_REQUEST['hoy'])) {
                    $hoy = $getter->limpiar_variable($_REQUEST['hoy']);
                } else {
                    $hoy = 0;
                }
                if (isset($_REQUEST['fec'])) {
                    $fecha = $getter->obtener_fecha_atencion_cliente($cliente, $sucursal);
                } else {
                    $fecha = 0;
                }
                $getter->obtener_clientes($estado, $tipo_atencion, $sucursal, $cliente, $ejecutivo, $group, $limit, $hoy, $fecha);
                break;
            case 10:
                //Login ejecutivos
                require 'ejecutivos.model.php';
                $getter = new Ejecutivo(1);
                $getter->set_host(1);
                if (isset($_REQUEST['user'])) {
                    $user = $getter->limpiar_variable($_REQUEST['user']);
                } else {
                    echo '{"success":0}';
                    exit;
                }
                if (isset($_REQUEST['pass'])) {
                    $pass = $getter->limpiar_variable($_REQUEST['pass']);
                } else {
                    echo '{"success":0}';
                    exit;
                }
                if (isset($_REQUEST['remember'])) {
                    $remember = $getter->limpiar_variable($_REQUEST['remember']);
                } else {
                    $remember = 0;
                }
                $getter->login($user, $pass, $remember);
                break;
            case 11:
                //Obtener datos sesión
                require 'sesion.model.php';
                $getter = new Sesion();
                if (isset($_REQUEST['params'])) {
                    $parametros = $_REQUEST['params'];
                } else {
                    echo '{"success": 0}';
                    exit;
                }
                $getter->obtener_datos_sesion($parametros);
                break;
            case 12:
                //Cerrar sesión
                require 'sesion.model.php';
                $getter = new Sesion();
                $getter->cerrar_sesion();
                break;
            case 13:
                //Login clientes
                require 'cliente.model.php';
                $getter = new Clientes();
                $getter->set_host(1);
                if (isset($_REQUEST['rut'])) {
                    $rut = $getter->limpiar_variable($_REQUEST['rut']);
                } else {
                    echo '{"success": 0}';
                    exit;
                }
                if (isset($_REQUEST['nom'])) {
                    $nombre = $getter->limpiar_variable($_REQUEST['nom']);
                } else {
                    $nombre = '';
                }
                if (isset($_REQUEST['fot'])) {
                    $foto = $getter->limpiar_variable($_REQUEST['fot']);
                } else {
                    $foto = '';
                }
                $getter->login($rut, $nombre, $foto);
                break;
            case 14:
                //Cancelar atención cliente
                require 'cliente.model.php';
                $getter = new Clientes();
                $getter->set_host(1);
                if (isset($_REQUEST['cli'])) {
                    $cliente = $getter->limpiar_variable($_REQUEST['cli']);
                } else {
                    echo '{"success": 0}';
                    exit;
                }
                $getter->cancelar_atencion_cliente($cliente);
                break;
            case 15:
                //Calificar atención cliente
                require 'atencion.model.php';
                $getter = new Atencion();
                $getter->set_host(1);
                $error = 0;
                if (isset($_REQUEST['cal'])) {
                    $params['calificacion'] = $getter->limpiar_variable($_REQUEST['cal']);
                } else {
                    $error++;
                }
                if (isset($_REQUEST['ate'])) {
                    $params['atencion'] = $getter->limpiar_variable($_REQUEST['ate']);
                } else {
                    $error++;
                }
                if ($error == 0) {
                    $getter->calificar_atencion_cliente($params);
                } else {
                    echo '{"success": 0}';
                }
                break;
            default:
                echo '{"success":0}';
                break;
        }
    }
    else
        echo '{"success":0}';
?>