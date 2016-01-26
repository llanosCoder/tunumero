/*global $, document, window, navigator, setTimeout, alertify, clearTimeout, setInterval, clearInterval, $_GET*/
/*jslint plusplus: true */

$(document).on('ready', function () {
    'use strict';
    var url = 'http://www.imasdgroup.cl/ws/vtr/',
        //lat,
        //lon,
        clienteId,
        clienteNombre = '',
        sucursalActual = $_GET('suc');

    if (sucursalActual === undefined) {
        sucursalActual = 1;
    }

    function salir() {
        $.post(
            url,
            {
                op: 12
            },
            function (data) {
                if (data.success !== 1) {
                    $.Notification.autoHideNotify('error', 'bottom right', 'Su sesión pudo no haberse cerrado correctamente');
                }
                window.location.href = 'totem_login.html';
            },
            'json'
        );
    }

    function esconderTablas() {
        $('#tabla_servicios_wrapper').fadeOut('fast');
        $('#tabla_sucursales_wrapper').fadeOut('fast');
        $('#tu_numero_wrapper').fadeOut('fast');
    }

    function cancelarAtencion(tomarNuevamente) {
        $.post(
            url,
            {
                op: 14,
                cli: clienteId
            },
            function (data) {
                if (data.success === 1) {
                    if (tomarNuevamente) {
                        window.location.href = 'http://www.imasdgroup.cl/ejecutivos/tu_numero.html?suc=1';
                    } else {
                        salir();
                    }
                } else {
                    $.Notification.autoHideNotify('error', 'bottom right', 'Ha ocurrido un error al cancelar su número');
                }
            },
            'json'
        );
    }

    function obtenerFila(id, numero) {

        var contadorFila = 0,
            tuNumero = '';
        $.post(
            url,
            {
                op: 9,
                grp: 1,
                tip: id,
                est: 1,
                suc: sucursalActual,
                fec: 1,
                cli: clienteId
            },
            function (data) {
                if (data.success === 1) {
                    contadorFila = data.clientes.length;
                }
            },
            'json'
        ).done(function () {
            esconderTablas();
            $('#title_page').html(clienteNombre);
            $('#tu_numero_wrapper').fadeIn('fast');
            tuNumero += '<h2>Tu número es: ' + numero + '</h2>';
            //contadorFila--;
            if (contadorFila > 0) {
                tuNumero += '<h2>' + clienteNombre + ', hay ' + contadorFila;
                if (contadorFila === 1) {
                    tuNumero += 'persona antes que tú</h2>';
                    $.Notification.autoHideNotify('success', 'bottom right', '¡Falta poco para tu atención!');
                } else {
                    tuNumero += 'personas antes que tú</h2>';
                }
            } else {
                tuNumero += '<h2>¡' + clienteNombre + ' eres el siguiente!</h2>';
                $.Notification.autoHideNotify('success', 'bottom right', '¡Eres el siguiente!');
            }
            tuNumero += '<br><button class="btn btn-danger btn-flat" id="btn_cancelar">Cancelar atención</button><button class="btn btn-primary btn-flat" id="btn_tomar_numero">Tomar nuevo número</button>';
            $('#tu_numero').html(tuNumero);

            $('#btn_cancelar').off('click');
            $('#btn_cancelar').on('click', function () {
                cancelarAtencion(false);
            });
            $('#btn_tomar_numero').off('click');
            $('#btn_tomar_numero').on('click', function () {
                cancelarAtencion(true);
            });
        });
    }

    function estaEsperando() {
        var numAtenciones = 0,
            tipoAtencion,
            numero;
        $.post(
            url,
            {
                op: 9,
                cli: clienteId,
                est: 1
            },
            function (data) {
                if (data.success === 1) {
                    $.each(data.clientes, function (i, cliente) {
                        numAtenciones++;
                        tipoAtencion = cliente.tipo_atencion;
                        numero = cliente.numero;
                    });
                    if (numAtenciones > 0) {
                        obtenerFila(tipoAtencion, numero);
                        $('.btn_cerrar').on('click', salir);
                        setTimeout(function () {
                            salir();
                        }, 3000 * 10);
                        $('.btn_volver').hide();
                    }
                }
                return false;
            },
            'json'
        );
    }

    function prepararSalida() {
        var cuentaRegresiva = 15,
            espera,
            intervalo;
        espera = setTimeout(function () {
            alertify.confirm('Atención<i class="fa fa-exclamation-triangle fa-2x" style="color:yellow;"></i>',  'Han pasado más de 5 minutos, ¿Necesita más tiempo?<br> <span id="cuenta_regresiva">15</span> segundos para la salida automática',
                function () {
                    clearTimeout(espera);
                    clearInterval(intervalo);
                    prepararSalida();
                },
                function () {
                    salir();
                }
                ).set('labels', {
                ok: 'Sí',
                cancel: 'No, salir'
            });
            intervalo = setInterval(function () {
                $('#cuenta_regresiva').html(cuentaRegresiva);
                cuentaRegresiva--;
                if (cuentaRegresiva === -1) {
                    salir();
                }
            }, 1000);
        }, 2000 * 60);
    }

    function obtenerDatosSesion() {
        $.post(
            url,
            {
                op: 11,
                params: ['type', 'cliente_id', 'cliente_rut', 'cliente_nombre', 'foto']
            },
            function (data) {
                if (data.success === 1) {
                    if (data.sesion.type !== 'cli') {
                        window.location.href = 'http://www.imasdgroup.cl/ejecutivos/totem_login.html';
                    } else {
                        //$('.info .username').html(data.sesion.cliente_nombre);
                        clienteNombre = data.sesion.cliente_nombre;
                        /*if (data.sesion.foto !== null && data.sesion.foto !== undefined) {
                            //$('.avatar .img-circle').attr('src', data.sesion.foto);
                        } else {
                            //$('.avatar .img-circle').attr('src', 'src/foto');
                        }*/
                        clienteId = data.sesion.cliente_id;
                    }
                }
            },
            'json'
        ).done(function () {
            estaEsperando();
            setInterval(function () {
                estaEsperando();
            }, 1000 * 20);
        });
    }

    /*function coordenadas(position) {
        lat = position.coords.latitude;
        lon = position.coords.longitude;
    }

    function get_loc() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(coordenadas);
        } else {
            lat = -33.437924;
            lon = -70.650485;
        }
    }*/

    /*function obtenerSucursalesCercanas(id) {

        if (lat === undefined || lon === undefined) {
            lat = -33.437924;
            lon = -70.650485;
        }
        var tabla = '<table class="table table-hover responsive" id="tabla_sucursales"><thead><tr><th>#</th><th>Dirección</th><th>Distancia</th><th>Tiempo espera (aprox.)</th><th>Hora</th><th>Escoger</th></tr></thead>"';
        $.post(
            url,
            {
                op: 4,
                ate: id,
                rad: 25,
                lat: lat,
                lon: lon,
                lim: 30
            },
            function (data) {
                if (data.success === 1) {
                    $.each(data.sucursales, function (i, sucursal) {
                        tabla += '<tr>';
                        tabla += '<td>' + i + '</td>';
                        tabla += '<td>' + sucursal.direccion + '</td>';
                        tabla += '<td>' + sucursal.distancia.substr(0, 4) + ' kms.</td>';
                        tabla += '<td>' + sucursal.tiempo_espera + ' mins.</td>';
                        tabla += '<td><input type="text" id="input_hora_' + sucursal.id + '" class="hora"></td>';
                        tabla += '<td><button class="btn btn-flat btn-primary btn_sucursal" suc="' + sucursal.id + '">Escoger</button></td>';
                        tabla += '</tr>';
                    });
                }
            },
            'json'
        ).done(function () {
            esconderTablas();
            $('.btn_volver').attr('paso', 2);
            $('#tabla_sucursales_wrapper').html(tabla);
            $('.hora').timepicker({
                'scrollDefault': 'now',
                'minTime': '9:00am',
                'maxTime': '6:30pm',
                'timeFormat': 'H:i'
            });
            $('.btn_sucursal').on('click', function () {
                tomarNumero($(this).attr('suc'));
            });
            $('#tabla_sucursales').DataTable({
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
            $('#tabla_sucursales_wrapper').fadeIn('fast');
        });
    }*/

    function obtenerServicios() {

        function tomarNumero(id) {
            //var hora = $('#input_hora_' + sucursalActual).val();
            var numero;
            $.post(
                url,
                {
                    op: 2,
                    estado: 1,
                    tipo: id,
                    sucursal: sucursalActual
                },
                function (data) {
                    if (data.success === 1) {
                        esconderTablas();
                        if (data.numero === null) {
                            $('#tu_numero').html('Su atención será activada a la hora solicitada');
                        } else {
                            $('#tu_numero').html('Tu número de atención es:<br>' + data.numero);
                            numero = data.numero;
                        }
                        $('#tu_numero_wrapper').fadeIn('fast');
                        obtenerFila(id, numero);
                        setInterval(function () {
                            obtenerFila(id, numero);
                        }, 1000 * 60);
                        $('.btn_cerrar').on('click', salir);
                        setTimeout(function () {
                            salir();
                        }, 3000 * 10);
                        $('.btn_volver').hide();
                    }
                },
                'json'
            );
        }

        var tabla = '<br><table class="table table-hover responsive" id="tabla_servicios"><thead><tr><th>#</th><th>Servicio</th><th>Motivo</th></tr></thead><tbody>';
        $.post(
            url,
            {
                op: 1
            },
            function (data) {
                if (data.success === 1) {
                    $.each(data.servicios, function (i, datos) {
                        tabla += '<tr>';
                        tabla += '<td>' + i + '</td>';
                        tabla += '<td>' + datos.servicio + '</td>';
                        tabla += '<td><button class="btn btn-flat btn-primary btn_servicio" serv="' + datos.id + '">Escoger</button></td>';
                        tabla += '</tr>';
                    });
                    tabla += '</tbody></table>';
                }
            },
            'json'
        ).done(function () {
            esconderTablas();
            $('#tabla_servicios_wrapper').fadeIn('fast');
            $('.btn_volver').attr('paso', 1);
            $('#tabla_servicios_wrapper').html(tabla);
            $('.btn_servicio').on('click', function () {
                tomarNumero($(this).attr('serv'));
            });
            $('#tabla_servicios').DataTable({
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
        });
    }

    function volver(paso) {
        switch (paso) {
        case '1':
            salir();
            break;
        case '2':
            obtenerServicios();
            break;
        }
    }
    obtenerServicios();
    //get_loc();
    obtenerDatosSesion();

    prepararSalida();

    $('.btn_volver').on('click', function () {
        volver($(this).attr('paso'));
    });
});