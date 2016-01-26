/*global $, document, setTimeout, setInterval, aRut, $_GET */
/*jslint plusplus: true */

$(document).on('ready', function () {
    'use strict';

    var url = 'http://www.imasdgroup.cl/ws/vtr/',
        excluidos = [],
        sucursal = $_GET('suc');

    function obtenerClientes() {
        var listado = '<ul class="event-list">',
            cont = 0;
        $.post(
            url,
            {
                op: 9,
                grp: 1,
                suc: sucursal,
                est: 1
            },
            function (data) {
                if (data.success === 1) {
                    $.each(data.clientes, function (i, cliente) {
                        listado += '<li>';
                        listado += '<time><span class="day">';
                        if (cliente.numero !== undefined) {
                            listado += cliente.numero;
                        }
                        listado += '</span></time>';
                        if (cliente.cliente_foto !== undefined && cliente.cliente_foto !== '') {
                            listado += '<img src="http://www.imasdgroup.cl/ejecutivos/' + cliente.cliente_foto + '" />';
                        } else {
                            listado += '<img src="http://www.imasdgroup.cl/ejecutivos/src/foto.png" />';
                        }
                        listado += '<div class="info"><h2 class="title">';
                        if (cliente.cliente_nombre !== undefined) {
                            listado += cliente.cliente_nombre.substr(0, 9);
                            if (cliente.cliente_nombre.substr(0, 9).indexOf(' ') !== -1) {
                                listado += '.';
                            }
                        }
                        listado += '</h2>';
                        listado += '<p class="desc">';
                        if (cliente.cliente_rut !== undefined) {
                            listado += aRut(cliente.cliente_rut.substr(0, 5) + 'XXXX');
                        }
                        listado += '</p></div>';
                        listado += '</li>';
                        cont++;
                    });
                }
                listado += '</ul>';
            },
            'json'
        ).done(function () {
            $('.event-list').html(listado);
            if (cont === 0) {
                $('#listado_atendiendo').addClass('col-sm-offset-6');
            } else {
                $('#listado_atendiendo').removeClass('col-sm-offset-6');
            }
        });
    }

    function estaExcluido(excluidos, rut) {
        var i;
        for (i = 0; i < excluidos.length; i++) {
            if (excluidos[i] === rut) {
                return true;
            }
        }
        return false;
    }

    function obtenerAtendiendo() {
        var listado = '',
            maxClientes = 2,
            contadorClientes = 0;
        $.post(
            url,
            {
                op: 9,
                est: 2,
                suc: sucursal,
                grp: 1,
                hoy: 1
            },
            function (data) {
                if (data.success === 1) {
                    $.each(data.clientes, function (i, cliente) {
                        if (contadorClientes >= maxClientes) {
                            return false;
                        }
                        if (!estaExcluido(excluidos, cliente.cliente_rut)) {
                            contadorClientes++;
                            listado += '<div class="panel panel-default" id="' + cliente.cliente_rut + '"><div class="panel-heading"><h3 class="panel-title">Atendiendo</h3></div>';
                            listado += '<div class="panel-body"><div class="row"><div class="col-md-3 col-lg-3 hidden-xs hidden-sm"><img class="img-circle" src="http://www.imasdgroup.cl/ejecutivos/';
                            if (cliente.cliente_foto !== undefined && cliente.cliente_foto !== '') {
                                listado += cliente.cliente_foto;
                            } else {
                                listado += 'src/foto.png';
                            }
                            listado += '" alt="User Pic"></div>';
                            listado += '<div class="col-xs-2 col-sm-2 hidden-md hidden-lg"><img class="img-circle" src="http://www.imasdgroup.cl/ejecutivos/';
                            if (cliente.cliente_foto !== undefined && cliente.cliente_foto !== '') {
                                listado += cliente.cliente_foto;
                            } else {
                                listado += 'src/foto.png';
                            }
                            listado += '" alt="User Pic"></div>';
                            listado += '<div class="col-xs-10 col-sm-10 hidden-md hidden-lg"><strong>';
                            if (cliente.cliente_nombre !== undefined && cliente.cliente_nombre !== '') {
                                listado += cliente.cliente_nombre;
                            } else {
                                listado += cliente.cliente_rut.substr(0, 5) + 'XXX-X';
                            }
                            listado += '</strong><br><dl><dt>Ejecutivo:</dt><dd>Módulo</dd></dl></div>';
                            listado += '<div class=" col-md-9 col-lg-9 hidden-xs hidden-sm"><strong>';
                            if (cliente.cliente_nombre !== undefined && cliente.cliente_nombre !== '') {
                                listado += cliente.cliente_nombre;
                            } else {
                                listado += cliente.cliente_rut.substr(0, 5) + 'XXX-X';
                            }
                            listado += '</strong><br><table class="table table-user-information"><tbody><tr><td>Ejecutivo:</td><td>';
                            if (cliente.ejecutivo_nombre !== undefined && cliente.ejecutivo_nombre !== '') {
                                listado += cliente.ejecutivo_nombre;
                            } else {
                                listado += cliente.ejecutivo_rut.substr(0, 5) + 'XXX-X';
                            }
                            listado += '</td></tr><tr><td>Módulo</td><td>';
                            if (cliente.modulo !== undefined) {
                                listado += cliente.modulo;
                            }
                            listado += '</td></tr></tbody></table></div></div></div></div><br>';
                            if (!$('#' + cliente.cliente_rut).length) {
                                setTimeout(function () {
                                    $('#' + cliente.cliente_rut).fadeOut('fast');
                                    excluidos.push(cliente.cliente_rut);
                                }, 5000 * 60);
                            }
                        }

                    });
                }
            },
            'json'
        ).done(function () {
            $('#listado_atendiendo').html(listado);
        });
    }

    obtenerClientes();
    obtenerAtendiendo();
    setInterval(function () {
        obtenerClientes();
        obtenerAtendiendo();
    }, 1000 * 10);
});