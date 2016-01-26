/*global $, document, window */

$(document).on('ready', function () {
    'use strict';

    function login(user, pass, remember) {
        var url = 'http://www.imasdgroup.cl/ws/vtr/';
        $.post(
            url,
            {
                op: 10,
                user: user,
                pass: pass,
                remember: remember
            },
            function (data) {
                if (data.success === 1) {
                    switch (data.login) {
                    case 1:
                        window.location.href = 'index.html';
                        break;
                    case 0:
                        $.Notification.autoHideNotify('error', 'bottom right', 'El nombre de usuario y la contraseña no coinciden');
                        break;
                    default:
                        $.Notification.autoHideNotify('error', 'bottom right', 'Ha ocurrido un error inesperado.');
                        break;
                    }
                } else {
                    $.Notification.autoHideNotify('error', 'bottom right', 'Ha ocurrido un error inesperado.');
                }
            },
            'json'
        );
    }

    function verificarLogin() {
        var user,
            pass,
            remember,
            hayError = false;
        user = $('#user').val();
        pass = $('#pass').val();
        if ($('#remember_me').is(':checked')) {
            remember = 1;
        } else {
            remember = 0;
        }
        if (user === '' || user === ' ') {
            $('#user').parent().addClass('has-error');
            $.Notification.autoHideNotify('error', 'bottom right', 'Ingrese su nombre de usuario.');
            hayError = true;
        } else {
            $('#user').parent().removeClass('has-error');
        }
        if (pass === '' || pass === ' ') {
            $('#pass').parent().addClass('has-error');
            $.Notification.autoHideNotify('error', 'bottom right', 'Ingrese su contraseña.');
            hayError = true;
        } else {
            $('#pass').parent().removeClass('has-error');
        }
        if (!hayError) {
            login(user, pass, remember);
        }
    }

    $('#btn_login').on('click', verificarLogin);

    $('#register-wrapper').on('keyup', function (e) {
        //alert(e.keyCode);
        if (e.keyCode === 13) {
            verificarLogin();
        }
    });

});
