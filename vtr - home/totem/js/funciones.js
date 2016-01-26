/*global $, document */
/*jslint plusplus: true, regexp: true */

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