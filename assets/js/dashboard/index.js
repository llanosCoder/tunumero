/*global $, document, window, aRut, setInterval */
/*jslint plusplus: true */

$(document).on('ready', function () {
    'use strict';
    var tabla,
        url = 'http://www.imasdgroup.cl/ws/vtr/';

    function obtenerDatosSesion() {
        $.post(
            url,
            {
                op: 11,
                params: ['user', 'ejecutivo_nombre', 'ejecutivo_foto']
            },
            function (data) {
                if (data.success === 1) {
                    if (data.sesion.user === null || data.sesion.user === '') {
                        window.location.href = 'http://www.imasdgroup.cl/ejecutivos/login.html';
                    } else {
                        $('.info .username').html(data.sesion.ejecutivo_nombre);
                        $('.avatar .img-circle').attr('src', data.sesion.ejecutivo_foto);
                    }
                } else {
                    window.location.href = 'http://www.imasdgroup.cl/ejecutivos/login.html';
                }
            },
            'json'
        );
    }

    function cargarClientesEspera() {

        function ocultarCliente(rut) {
            $('#row_' + rut).hide();
        }

        function abordarCliente(atencion, rut, foto, nombre, automatico) {

            function atenderCliente() {

                function calificarCliente(calificacion, estadoAtencion) {
                    $.post(
                        url,
                        {
                            op: 15,
                            ate: atencion,
                            cal: calificacion,
                            est: estadoAtencion
                        },
                        function (data) {
                            if (data.success === 1) {
                                $.Notification.autoHideNotify('success', 'bottom right', 'Atención calificada exitosamente');
                            }
                        },
                        'json'
                    );
                }

                $.post(
                    url,
                    {
                        op: 8,
                        est: 3,
                        ate: atencion
                    },
                    function (data) {
                        if (data.success === 1) {
                            $.Notification.autoHideNotify('success', 'bottom right', 'Atención finalizada exitosamente');
                            $('.btn_atencion').on('click', function () {
                                var calificacion = $('#stars > .fa-star').length,
                                    estadoAtencion;
                                estadoAtencion = $('#estado_atencion').find('.active').attr('est');
                                calificarCliente(calificacion, estadoAtencion);
                            });
                            $('#modal_cliente').removeClass('md-show');
                        }
                    },
                    'json'
                );
            }

            if (!automatico) {
                $.post(
                    url,
                    {
                        op: 7,
                        ate: atencion
                    },
                    function (data) {
                        if (data.success === 1) {
                            cargarClientesEspera();
                            $.Notification.autoHideNotify('success', 'bottom right', 'Cliente asignado exitosamente');
                            if (nombre !== '' && nombre !== undefined) {
                                $('.modal_cliente_nombre').html(nombre);
                            }
                            if (rut !== null && rut !== undefined) {
                                $('.modal_cliente_rut').html(aRut(rut));
                            }
                            if (foto === undefined || foto === '') {
                                $('.modal_cliente_foto').attr('src', 'src/foto.png');
                            } else {
                                $('.modal_cliente_foto').attr('src', foto);
                            }
                            $('.btn_terminar_atencion').off('click');
                            $('.btn_terminar_atencion').on('click', function () {
                                atenderCliente();
                            });
                            $('.btn_terminar_atencion').modalEffects();
                        } else {
                            $.Notification.autoHideNotify('warning', 'bottom right', 'El cliente ya ha sido abordado');
                            $('#modal_cliente').removeClass('md-show');
                        }
                    },
                    'json'
                );
            } else {
                if (nombre !== '' && nombre !== undefined) {
                    $('.modal_cliente_nombre').html(nombre);
                }
                if (rut !== null && rut !== undefined) {
                    $('.modal_cliente_rut').html(aRut(rut));
                }
                if (foto === undefined || foto === '') {
                    $('.modal_cliente_foto').attr('src', 'src/foto.png');
                } else {
                    $('.modal_cliente_foto').attr('src', foto);
                }
                $('.btn_terminar_atencion').off('click');
                $('.btn_terminar_atencion').on('click', function () {
                    atenderCliente();
                });
                $('.btn_terminar_atencion').modalEffects();
            }

        }

        function estaAtendiendo() {

            var datosCliente = {},
                cont = 0;

            $.post(
                url,
                {
                    op: 9,
                    est: 2,
                    grp: 1,
                    hoy: 1
                },
                function (data) {
                    if (data.success === 1) {
                        $.each(data.clientes, function (i, cliente) {
                            datosCliente.atencion = cliente.atencion_id;
                            datosCliente.rut = cliente.cliente_rut;
                            datosCliente.foto = cliente.cliente_foto;
                            datosCliente.nombre = cliente.cliente_nombre;
                            cont++;
                        });
                        if (cont > 0) {
                            abordarCliente(datosCliente.atencion, datosCliente.rut, datosCliente.foto, datosCliente.nombre, true);
                            $('#modal_cliente').addClass('md-show');
                        }

                    }
                },
                'json'
            );
        }

        var tablaClientes = '<table class="table table-hover m-n responsive nowrap display" cellspacing="0" width="100%" id="tabla_clientes"><thead><tr><th>Abordar</th><!--th>Foto</th--><th>RUT</th><th>Nombre</th><th>Motivo</th><!--th>Ocultar <i class="fa fa-info-circle pull-right tt" data-toggle="tooltip" data-placement="left" title="Esta acción se puede deshacer refrescando la lista de clientes"></i></th--></tr></thead><tbody>';
        $.get(
            url,
            {
                op: 9,
                grp: 1,
                est: 1
            },
            function (data) {
                $.each(data.clientes, function (i, datos) {
                    tablaClientes += '<tr id="row_' + datos.cliente_rut + '" class="row_tabla">';
                    tablaClientes += '<td><button class="btn btn-success btn-flat btn_abordar" rut="' + datos.cliente_rut + '" atencion="' + datos.atencion_id + '" foto="' + datos.cliente_foto + '" nombre="' + datos.cliente_nombre + '" data-modal="modal_cliente">Abordar</button></td>';
                    /*if (datos.cliente_foto !== '' && datos.cliente_foto !== undefined) {
                        tablaClientes += '<td class="vam"><img src="' + datos.cliente_foto + '" class="img-tableavatar img-circle"></td>';
                    } else {
                        tablaClientes += '<td class="vam"><img src="src/foto.png" class="img-tableavatar img-circle"></td>';
                    }*/
                    if (datos.cliente_rut !== null && datos.cliente_rut !== undefined) {
                        tablaClientes += '<td>' + aRut(datos.cliente_rut) + '</td>';
                    } else {
                        tablaClientes += '<td>-</td>';
                    }
                    if (datos.cliente_nombre !== null && datos.cliente_nombre !== undefined) {
                        tablaClientes += '<td>' + datos.cliente_nombre + '</td>';
                    } else {
                        tablaClientes += '<td>-</td>';
                    }
                    tablaClientes += '<td>' + datos.tipo_atencion_descripcion + '</td>';

                    // tablaClientes += '<td><button class="btn btn-warning btn-flat btn_ocultar" rut="' + datos.cliente_rut + '">Ocultar</button></td>';
                    tablaClientes += '</tr>';
                });
                tablaClientes += '</tbody></table>';
            },
            'json'
        ).done(function () {
            $('#tabla_clientes-wrapper').html(tablaClientes);
            $('.tt').tooltip();
            $('.btn_ocultar').off('click');
            $('.btn_ocultar').on('click', function () {
                ocultarCliente($(this).attr('rut'));
            });
            $('.btn_abordar').off('click');
            $('.btn_abordar').on('click', function () {
                abordarCliente($(this).attr('atencion'), $(this).attr('rut'), $(this).attr('foto'), $(this).attr('nombre'), false);
            });
            $('.btn_abordar').modalEffects();
            tabla = $('#tabla_clientes').DataTable({
                "oLanguage": {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "No se han encontrado registros",
                    "sEmptyTable": "No se han encontrado registros",
                    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix": "",
                    "sSearch": "Buscar: ",
                    "sUrl": "",
                    "sInfoThousands": ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Último",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                },
                responsive: true
            });
            $('.row_tabla').show();

            $('#tabla_clientes-wrapper').on('click', function () {
                $('.btn_abordar').modalEffects();
                $('.btn_abordar').off('click');
                $('.btn_abordar').on('click', function () {
                    abordarCliente($(this).attr('atencion'), $(this).attr('rut'), $(this).attr('foto'), $(this).attr('nombre'), false);
                });
            });

        });

        estaAtendiendo();

    }

    $('.ti-reload').on('click', cargarClientesEspera);

    cargarClientesEspera();

    obtenerDatosSesion();

    setInterval(function () {
        cargarClientesEspera();
    }, 1000 * 200);

});
