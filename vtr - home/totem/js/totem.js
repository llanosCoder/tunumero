/*global $, document, $_GET */

$(document).ready(function () {
    'use strict';

    var url = 'http://www.imasdgroup.cl/ws/vtr/',
        sucursal = $_GET('suc');

    function newOpcion(id, desc) {

        function obtenerNumero(id) {
            $.post(
                url,
                {
                    op: 4,
                    tipo: id,
                    sucursal: sucursal,
                    estado: 1
                },
                function (data) {
                    if (data.success === 1) {
                        $.each(data.clientes, function (i, cliente) {
                            $('#id').html(cliente.numero);
                        });
                    }
                },
                'json'
            );
        }

        var data = '<li style="padding:5px !important;" id="' + id + '"><a href="#">' + desc + '</a></li>';
        $('#opciones').append(data);
        $('#' + id).on('click', function () {
            obtenerNumero($(this).attr('id'));
        });
    }

    $('#boton').on('click', function () {
        $.post(url, {
            op: 1
        }, function (tipoAtencion) {
            $('#opciones').html('');
            if (tipoAtencion.success === 1) {
                $.each(tipoAtencion.servicios, function (i, servicio) {
                    newOpcion(servicio.id, servicio.servicio);
                });
            }
        }, 'json');
    });

});


/*
<li><a href="#">Action</a>
</li>
<li><a href="#">Another action</a>
</li>
<li><a href="#">Something else here</a>
</li>
<li><a href="#">Separated link</a>
</li>
*/