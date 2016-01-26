function isNumber(input) {
    'use strict';
    return (input - 0) == input && (''+input).replace(/^\s+|\s+$/g, "").length > 0;
}

function currencyFormat(num, extra) {
    'use strict';
    if (isNumber(num)==true) {
        return extra+num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
    }
    return "no definido";
}

function currencyFormat2(num, extra) {
    'use strict';
    if (isNumber(num)==true) {
        return extra+num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
    }
    return "no definido";
}

function aRut(rut) {
    'use strict';
    rut = rut.replace('-', '');
    while(rut.indexOf('.') !== -1){
        rut = rut.replace('.', '');
    }
    var resultado = '',
        i,
        j = 0;
    resultado += rut.charAt(rut.length - 1) + '-';
    for (i = rut.length - 2; i >= 0; i--) {
        resultado += rut.substr(i, 1);
        j++;
        if(j == 3){
            resultado += '.';
            j = 0;
        }
    }
    resultado = resultado.split('').reverse().join('');
    return resultado;
}

function $_GET(param) {
    'use strict';
    var url,
        x,
        p;
    url = document.URL;
    url = String(url.match(/\?+.+/));
    url = url.replace("?", "");
    url = url.split("&");
    x = 0;
    while (x < url.length) {
        p = url[x].split("=");
        if (p[0] === param) {
            return decodeURIComponent(p[1]);
        }
        x++;
    }
}