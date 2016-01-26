<?php
/**
 * funciones.php
 *
 * Diversas funciones de utilidad
 *
 * @author     Jaime Llanos
 * @copyright  Imasd Group SPA
 * @version    1.0
 * @example    http://url/funciones.php
 */

class Funciones{

    protected $having = 0, $where = 0, $order = 0;

    public function concatenar($op, $op2) {
        switch ($op) {
            case 'having':
                if ($this->having <= 0) {
                    $retorno = ' HAVING ';
                    $this->having++;
                } else {
                    $retorno = $op2;
                }
                break;
            case 'where':
                    if ($this->where <= 0) {
                        $retorno = ' WHERE ';
                        $this->where++;
                    } else {
                        $retorno = $op2;
                    }
                    break;
            case 'order':
                if ($this->order <= 0) {
                    $retorno = ' ORDER BY ';
                    $this->order++;
                } else {
                    $retorno = $op2;
                }
                break;
            default:
                $retorno = '';
                break;
        }
        return $retorno;
    }
}

?>
