// Example using HTTP POST operation
"use strict";

//Тут объявляю несколько юзерагентов, типа мы под разными браузерами заходим постоянно
var useragent = [];
useragent.push('Opera/9.80 (X11; Linux x86_64; U; fr) Presto/2.9.168 Version/11.50');
useragent.push('Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25');
useragent.push('Opera/12.02 (Android 4.1; Linux; Opera Mobi/ADR-1111101157; U; en-US) Presto/2.9.201 Version/12.02');

//Здесь находится страничка, которую нужно спарсить
var system = require('system');
var siteUrl = system.args[1];
var page = require('webpage').create();

//Отключение картинок
page.settings.loadImages = false;
page.settings.resourceTimeout = 5000;
page.settings.userAgent = useragent[Math.floor(Math.random() * useragent.length)];


//Здесь я отключаю загрузку сторонних скриптов для ускореняи парсинга
page.onResourceRequested = function(requestData, request) {
    if ((/http:\/\/.+?\.css/gi).test(requestData['url']) || requestData.headers['Content-Type'] == 'text/css') {
        //console.log('The url of the request is matching. Aborting: ' + requestData['url']);
        request.abort();
    }
};

//Дебаг ошибок (если нужно)
page.onError = function(msg, trace) {
    var msgStack = ['ERROR: ' + msg];
    if (trace && trace.length) {
        msgStack.push('TRACE:');
        trace.forEach(function(t) {
            msgStack.push(' -> ' + t.file + ': ' + t.line + (t.function ? ' (in function "' + t.function + '")' : ''));
        });
    }
    // uncomment to log into the console
    // console.error(msgStack.join('\n'));
};

//String.prototype.stripTags = function() {  return this.replace(/<\/?[^>]+>/g, ''); };

//viewportSize being the actual size of the headless browser
page.viewportSize = { width: 1024, height: 768 };
//the clipRect is the portion of the page you are taking a screenshot of
page.clipRect = { top: 0, left: 0, width: 1024, height: 768 };


function sleep(ms) {
    ms += new Date().getTime();
    while (new Date() < ms){}
}

var t = Date.now();
page.onLoadFinished = function (status) {
    if (status !== 'success') {
        console.log(
            "Error open" + page.reason
        );
        console.log("Connection failed.");
        phantom.exit(1);
    } else {
        t = Date.now() - t;
        //page.render('asd.png');
        //page.injectJs('//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js');

        var Links = page.evaluate(function() {
            var elems = document.querySelectorAll('a');
            var result = [];
            for(var i=0; i<elems.length; i++) {
                result.push( {
                    "text":elems[i].textContent,
                    "href":elems[i].href,
                    "rel":elems[i].rel,
                });
            }
            return result;
        });
        var res = {
          //  's': system.args[1],
            'loading': t,
          //  'size': page.content.length,
            'data': Links,
        };

        console.log(JSON.stringify(res));
        phantom.exit();
    }
};

//page.onConsoleMessage = function (msg) { console.log(msg); };
//page.onConsoleMessage = function (msg) { system.stderr.writeLine('console: ' + msg); };

page.open(siteUrl);
