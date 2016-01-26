function currencyFormat (num , extra) {
    if(isNumber(num)==true)
        return extra+num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.")
    return "no definido";
}

function currencyFormat2 (num , extra) {
    if(isNumber(num)==true)
        return extra+num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
    return "no definido";
}

function isNumber(input){
    return (input - 0) == input && (''+input).replace(/^\s+|\s+$/g, "").length > 0;
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