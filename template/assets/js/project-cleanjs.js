"use strict";

//===================================================
// Code below is clean Javascript for compatibility and transferability
//===================================================

function escapeHtml(text) {
    if (null === text || text === '') return '';
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

document.addEventListener('DOMContentLoaded', function() {
    var btnAnalyze = document.getElementById('btn-analyze');
    var btnClear = document.getElementById('btn-clear');
    var progressBar = document.getElementById('my-progress-bar');
    btnAnalyze.disabled = false;

    btnAnalyze.addEventListener('click', function (event) {
        getMethods();
        event.preventDefault();
        return;
    });
    btnClear.addEventListener('click', function (event) {
        if (!confirm("Sure you want to clear?")) {
            event.preventDefault();
            return;
        }
        var resultsTable = document.querySelector('#result');
        var tbody = resultsTable.tBodies.item(0);
        if(tbody) {
            tbody.parentNode.removeChild(tbody);
        }
        btnAnalyze.innerHTML = 'Анализировать';
        btnAnalyze.disabled = false;
        progressBar.classList.add('hide-all');
        btnClear.classList.add('hide-all');
    });

    document.body.addEventListener("click", function (event) {
        // IE6-8 support
        var target = event.target || event.srcElement;
        var needle = target.parentNode;

        if (needle.tagName == 'TR') {
            if (!needle.nextSibling.classList.contains("hide-all")) {
                needle.nextSibling.classList.add('hide-all');
                return;
            }
            if (needle.classList.contains("static-row")) {
                needle.nextSibling.classList.remove('hide-all');
            }
        }
    });
});

function analyze(methods) {
    var _this = this;
    var btnAnalyze = document.getElementById('btn-analyze');
    var btnClear = document.getElementById('btn-clear');
    var progressBar = document.querySelector('#my-progress-bar > div');
    var url = document.getElementById('url').value;
    var markup = document.querySelector('input[type="radio"]:checked').value;
    var inForm = document.querySelector('form > div');

    btnAnalyze.innerHTML = 'Загружаю...'; // (2)
    btnAnalyze.style.cursor = 'progress';
    btnAnalyze.disabled = true;
    progressBar.previousElementSibling.textContent = "Loading...";
    progressBar.parentNode.classList.remove('hide-all');
    progressBar.style.width = "0%";

    if (null !== methods) {
        var length = Object.keys(methods).length;
        var partition = parseInt(100 / length);
        var count = 0;

        btnAnalyze.innerHTML = 'Получаем...';

        var resultsTable = document.querySelector('#result');
        var tbody = document.createElement('tbody');
        resultsTable.appendChild(tbody);

        Object.keys(methods).forEach(function (key) {

            // IE8,9 support
            var XHR = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
            var xhr = new XHR();

            if (!xhr) return;

            xhr.open('POST', location.protocol + "//" + location.host + '/ajax_handler.php', true);
            xhr.timeout = 1200000; // ms (= 120 sec)
            xhr.ontimeout = function() {
                console.log('Timeout: ' + methods[key].name);
            };
            xhr.setRequestHeader('Cache-Control', 'no-cache');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            //xhr.setRequestHeader("Access-Control-Allow-Origin", "*");

            var data = "url=" + url + "&method=" + key + "&markup=" + markup;

            xhr.onreadystatechange = function() { // (3)
                if (xhr.readyState === 4) {
                    count++;
                    if ((count+1) >= length) {
                        progressBar.style.width = "100%";
                        progressBar.previousElementSibling.textContent = "100%";
                        btnAnalyze.innerHTML = 'Готово!';
                        btnAnalyze.style.cursor = 'pointer';
                        btnClear.classList.remove('hide-all');
                    } else {
                        progressBar.style.width = ((partition * count) > 90 ? 100 : (partition * count)) + "%";
                    }

                    if (xhr.status != 200) {
                        // Check that nginx host configuration not lower then:
                        // proxy_buffer_size 256k
                        // proxy_buffers 24 128k
                        console.log('Error: ' + methods[key].name + '. ' + (xhr.status ? xhr.statusText : 'Request failed.'));
                        return false;
                    } else {

                        var addTip = function (type, message) {
                            var tip = document.querySelector("p.tip");
                            if (!tip) {
                                tip = document.createElement("p");
                                tip.classList.add('tip');
                                tip.textContent = message;
                                inForm.appendChild(tip);
                                inForm.classList.add(type);
                            }
                        };
                        var removeTip = function () {
                            var tip = document.querySelector("p.tip");
                            if (tip) tip.parentNode.removeChild(tip);
                            inForm.classList.remove('error');
                        };

                        try {
                            var resultData = JSON.parse(xhr.responseText);

                            if (400 == resultData.status) {
                                addTip('error',  'Некорректный URL адрес. Исправьте!');
                                console.log(key + ': Incorrect URL! Check pls.');
                                return;
                            }

                            if (500 == resultData.status) {
                                //addTip('error', methods[key].name + ': Извините! Ошибка вычислений на сервере');
                                console.log('Error: ' + methods[key].name + '. Calculating error: ' + resultData.message);
                                return;
                            }

                            if (204 == resultData.status) {
                                //addTip('error', methods[key].name + ': Парсер не работает с данным типом документа.');
                                console.log('Error: ' + methods[key].name + '. ' + resultData.message);
                                return;
                            }

                            if (200 == resultData.status) {
                                if (resultData.data != null) {
                                    var html = '';
                                    var styleYes = 'style="color:green;"';
                                    var styleNo = 'style="color:red;"';

                                    html += '<tr class="static-row"><td><span>' + resultData.data.name +
                                        '</span><br/><a href="'+resultData.data.link+'">@link</a></td>';
                                    html += '<td>' + resultData.data.api + '</td>';
                                    html += '<td>' + resultData.data.type + '</td>';
                                    html += '<td>' + ( resultData.data.docTypeHtml === true ?
                                        '<span '+styleYes+'>&check;</span>' :
                                        '<span '+styleNo+'>&cross;</span>' ) + '</td>';
                                    html += '<td>' + ( resultData.data.docTypeXhtml === true ?
                                        '<span '+styleYes+'>&check;</span>' :
                                        '<span '+styleNo+'>&cross;</span>' ) + '</td>';
                                    html += '<td>' + ( resultData.data.docTypeXml === true ?
                                        '<span '+styleYes+'>&check;</span>' :
                                        '<span '+styleNo+'>&cross;</span>' ) + '</td>';
                                    html += '<td>' + ( resultData.data.autodetectType === true ?
                                        '<span '+styleYes+'>Да</span>' :
                                        '<span '+styleNo+'>Нет</span>' ) + '</td>';
                                    html += '<td>' + resultData.data.memory + '</td>';
                                    html += '<td>' + resultData.data.time + '</td>';
                                    var links = '';
                                    if (resultData.data.links != null) {
                                        Object.keys(resultData.data.links).forEach(function (k) {
                                            links += '<div><div>' +
                                                k + '</div><div>' +
                                                escapeHtml(resultData.data.links[k].text) + '</div><div>' +
                                                resultData.data.links[k].href + '</div></div>';
                                        });
                                        html += '<td>' + resultData.data.links.length + '</td>';
                                    } else {
                                        html += '<td>:(</td>';
                                    }
                                    html += '</tr><tr class="dynamic-row hide-all" ><td colspan="10"><div><div>'
                                        + links + '</div><div></td></tr>';

                                    resultsTable.tBodies.item(0).innerHTML += html;
                                }
                            }
                        } catch (err) {
                            var alertsBlock = document.querySelector('#alerts-block');
                            var _html = '<div class="ink-alert block error" role="alert">';
                            _html +=	'<button class="ink-dismiss">&times;</button>';
                            _html += '<h4>Ошибка в данных</h4><p>' + methods[key].name + '. ' +
                                err.name + ': ' + err.message
                                + '<br/>Response: ' + xhr.responseText + '</p></div>';

                            alertsBlock.innerHTML += _html;
                        }
                    }
                }
            };
            xhr.send(data);
            //end of XHR
        });
        //end of forEach
    }
}

function getMethodsTest() {
    var obj = {};
    obj[10] = ['name','DOM'];
    obj[50] = ['name','REGEX'];
    analyze(obj);
}

function getMethods() {
    // https://xhr.spec.whatwg.org/

    // IE8,9 support
    var XHR = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
    var xhr = new XHR();

    xhr.open('POST', location.protocol + "//" + location.host + '/ajax_getopt.php', false); //http://seo.local/ajax_getopt.php
    xhr.setRequestHeader('Cache-Control', 'no-cache');
    //xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.send(null);
    if (xhr.status != 200) {
        console.log('Error: ' + (xhr.status ? xhr.statusText : 'request failed.'));
        return false;
    } else {
        try {
            var resultData = JSON.parse(xhr.responseText);
            analyze(resultData);
        } catch (err) {

        }
    }
}