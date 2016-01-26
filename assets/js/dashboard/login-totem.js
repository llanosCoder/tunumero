/*global $, document, window */

$(document).on('ready', function () {
    'use strict';

    $('#form_cliente').validate({
        rules: {
            rut: {
                required: true,
                rut: true
            },
            nombre: {
                lettersonly: true
            },
            submitHandler: function (e) {
                e.preventDefault();
            }
        },
        highlight: function (element) {
            $(element).parent().removeClass('has-success').addClass('has-error');
        },
        unhighlight: function (element) {
            $(element).parent().removeClass('has-error').addClass('has-success');
        }
    });

    function verificarLogin() {
        var hayerror = false,
            url = 'http://www.imasdgroup.cl/ws/vtr/',
            rut,
            nombre;
        $("#form_cliente").find(':input').each(function () {
            var elemento = this;

            if ($("#" + elemento.id).parent().hasClass('has-error')) {

                hayerror = true;
            }
        });

        if ($('#rut').val() === '') {
            $('#rut').parent().addClass('has-error');
            hayerror = true;
        }

        if (!hayerror) {
            rut = $('#rut').val();
            nombre = $('#nombre').val();
            $.post(
                url,
                {
                    op: 13,
                    rut: rut,
                    nom: nombre
                },
                function (data) {
                    if (data.success === 1) {
                        window.location.href = 'totem.html';
                    } else {
                        $.Notification.autoHideNotify('error', 'bottom right', 'Ha ocurrido un problema. Por favor, contacte a un supervisor');
                    }
                },
                'json'
            );
        } else {
            $.Notification.autoHideNotify('error', 'bottom right', 'Ingrese los datos correctamente');
        }
    }

    $('#btn_login').on('click', verificarLogin);

    $('#form_cliente').on('submit', function (event) {
        event.preventDefault();
    });

    $('#register-wrapper').on('keyup', function (e) {
        //alert(e.keyCode);
        if (e.keyCode === 13) {
            verificarLogin();
        }
    });

});