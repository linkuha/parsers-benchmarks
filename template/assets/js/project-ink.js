"use strict";

// Ink JavaScript
// http://ink.sapo.pt/javascript/

Ink.requireModules(
    [
        'Ink.Dom.Loaded_1',
        'Ink.Dom.Event_1',
        'Ink.Net.Ajax_1',
        'Ink.UI.Close_1',
        'Ink.UI.ProgressBar_1',
        'Ink.Util.String_1',
        'Ink.Util.Json_1',
        'Ink.UI.Table_1',
        'Ink.Dom.Element_1'
    ],
    function (Loaded, InkEvent, Ajax, Close, ProgressBar, InkString, InkJson, Table, Element) {
        new Close();  // That was close
        var myProgressBar = new ProgressBar('#my-progress-bar');
        var tableResults = Ink.s('#result');
        var tableInfo = Ink.s('#info');
        var tableObj = new Table(tableInfo, {
            allowResetSorting: true,
            getSortKey: function (cell) {
                if (0 === cell.columnIndex) {
                    return cell.element.firstChild.textContent;
                }
            }
        });
        var tableObj2 = null;
        function sortTable(table) {
            return new Table(table, {
                allowResetSorting: true,
                getSortKey: function (cell) {
                    if (3 === cell.columnIndex) {
                        return parseFloat(cell.element.firstChild.textContent);
                    }
                    if (2 === cell.columnIndex) {
                        return parseInt(cell.element.getAttribute('data-sort-key'));
                    }
                    if (0 === cell.columnIndex) {
                        return cell.element.firstChild.textContent;
                    }
                }
            });
        }

        var inProgress = false;
        //    var tbody = document.createElement('tbody');
        //    tableElement.appendChild(tbody);
        var btnAnalyze = Ink.i('btn-analyze');
        var btnClear = Ink.i('btn-clear');
        var anim = btnAnalyze.getAttribute('data-anim'),
            animEl = Ink.s( '.' + anim );
        var modalResults = Ink.s('#modalContent > table');

        function fileSizeSI(size) {
            var e = (Math.log(size) / Math.log(1e3)) | 0;
            return +(size / Math.pow(1e3, e)).toFixed(2) + ' ' + ('kMGTPEZY'[e - 1] || '') + 'B';
        }

        function analyze(params) {
            var _this = this;
            var markup = Ink.s('#markup input[type="radio"]:checked').value;
            var inForm = Ink.s('form > div');
            var url = Ink.i('url').value;
            var iterations = Ink.s('#iters input[type="radio"]:checked').value;

            btnAnalyze.innerHTML = 'Загружаю...'; // (2)
            btnAnalyze.style.cursor = 'progress';
            btnAnalyze.disabled = true;
            myProgressBar.getElement().childNodes[1].textContent = "Загрузка...";
            myProgressBar.getElement().classList.remove('hide-all');
            myProgressBar.setValue(0);

            var addTip = function (type, message) {
                var tip = Ink.s("p.tip");
                if (!tip) {
                    tip = document.createElement("p");
                    tip.classList.add('tip');
                    tip.textContent = message;
                    inForm.appendChild(tip);
                    inForm.classList.add(type);
                }
            };
            var removeTip = function () {
                var tip = Ink.s("p.tip");
                if (tip) tip.parentNode.removeChild(tip);
                inForm.classList.remove('error');
            };

            if (null !== params.options) {
                var length = Object.keys(params.options).length;
                var partition = parseInt(100 / length);
                var count = 0;

                btnAnalyze.innerHTML = 'Получаем...';

                Object.keys(params.options).forEach(function (key) {
                    var check = function (count, length) {
                        if (count >= length) {
                            myProgressBar.setValue(100);
                            myProgressBar.getElement().childNodes[1].textContent = "100%";
                            btnAnalyze.innerHTML = 'Готово!';
                            btnAnalyze.style.cursor = 'pointer';
                            btnClear.classList.remove('hide-all');
                            inProgress = false;
                            animEl.classList.remove('la-animate');
                        } else {
                            myProgressBar.setValue(partition * count);
                        }
                    };
                    var uri = location.protocol + "//" + location.host + '/ajax_handler.php?_method='
                         + InkString.htmlEntitiesEncode(params.options[key].name)
                        + '&_type=' + InkString.htmlEntitiesEncode(markup);
                    new Ajax(uri, {
                        method: 'POST',
                        asynchronous: true,
                        timeout: 360,
                        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                        requestHeaders: {
                            'Cache-Control': 'no-cache',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        parameters: {
                            url: url,
                            method: key,
                            markup: markup,
                            iterations: iterations
                        },
                        onComplete: function() {
                            count++;
                            Ink.log('Completed ' + count+ ' parser of ' + length);
                            check(count, length);
                            if (count === length) {
                                tableResults.tHead.rows[0].children[3].setAttribute('data-sortable', 'true');
                                tableResults.tHead.rows[0].children[2].setAttribute('data-sortable', 'true');
                                tableResults.tHead.rows[0].children[0].setAttribute('data-sortable', 'true');

                                tableObj2 = sortTable(tableResults);

                                Ink.log('Table sorting is ready');
                            }
                        },
                        onFailure: function() {   // status !== 200
                            // Check that nginx host configuration not lower then:
                            // proxy_buffer_size 256k
                            // proxy_buffers 24 128k
                            // proxy_send_timeout 5m;
                            // proxy_read_timeout 5m;
                            Ink.warn('Error: ' + params.options[key].name + '. Request failed.');
                            return false;
                        },
                        onTimeout: function() {
                            Ink.log('Timeout: ' + params.options[key].name);
                            this.options.onComplete();
                        },
                        onSuccess: function(xhrObj, req) {
                            try {
                                var resultData = JSON.parse(xhrObj.responseText);

                                if (400 == resultData.status) {
                                    addTip('error', 'Некорректный URL адрес. Исправьте!');
                                    Ink.log(key + ': Incorrect URL! Check pls.');
                                    return;
                                }
                                if (500 == resultData.status) {
                                    //addTip('error', params.options[key].name + ': Извините! Ошибка вычислений на сервере');
                                    Ink.warn('Error: ' + params.options[key].name + '. Calculating error: ' + resultData.message);
                                    return;
                                }
                                if (204 == resultData.status) {
                                    //addTip('error', params.options[key].name + ': Парсер не работает с данным типом документа.');
                                    Ink.log('Error: ' + params.options[key].name + '. ' + resultData.message);
                                    return;
                                }
                                if (200 == resultData.status) {
                                    if (resultData.data != null) {
                                        var html = '';
                                        var styleYes = 'style="color:green;"';
                                        var styleNo = 'style="color:red;"';

                                        html += '<tr class="static-row"><td>' + resultData.data.name + '</td>';
                                        html += '<td>' + resultData.data.api + '</td>';
                                        html += '<td data-sort-key="' + resultData.data.memory + '">' + fileSizeSI(resultData.data.memory) + '</td>';
                                        html += '<td>' + resultData.data.time + '</td>';
                                        var links = '';
                                        if (resultData.data.links != null) {
                                            Object.keys(resultData.data.links).forEach(function (k) {
                                                links += '<div><div>' +
                                                    k + '</div><div>' +
                                                    InkString.htmlEntitiesEncode(resultData.data.links[k].text) + '</div><div>' +
                                                    resultData.data.links[k].href + '</div></div>';
                                            });
                                            html += '<td>' + resultData.data.links.length + '</td>';
                                        } else {
                                            html += '<td>:(</td>';
                                        }
                                        html += '</tr>';
                                        modalResults.tBodies.item(0).innerHTML += '<tr><td>'+resultData.data.name+'</td></tr><tr class="dynamic-row"><td><div><div>' + links + '</div></div></td></tr>';
                                        //    html += '</tr><tr class="dynamic-row hide-all" ><td colspan="10"></td></tr>';
                                        tableResults.tBodies.item(0).innerHTML += html;

                                    }
                                }
                            } catch (err) {
                                var alertsBlock = document.querySelector('#alerts-block');
                                var _html = '<div class="ink-alert block error" role="alert">';
                                _html += '<button class="ink-dismiss">&times;</button>';
                                _html += '<h4>Ошибка в данных</h4><p>' + params.options[key].name + '. ' +
                                    err.name + ': ' + err.message
                                    + '<br/>Response: ' + xhrObj.responseText + '</p></div>';

                                alertsBlock.innerHTML += _html;
                            }
                        }
                    });
                });
                //end of forEach
            }
        }

        function getMethodsTest() {
            var obj = {};
            obj[10] = ['name', 'DOM'];
            obj[50] = ['name', 'REGEX'];
            analyze(obj);
        }

        function getMethods() {
            var iterations = Ink.s('#iters input[type="radio"]:checked').value;
            var uri = location.protocol + "//" + location.host + '/ajax_getopt.php';
            new Ajax(uri, {
                method: 'POST',
                asynchronous: false,
                parameters: {
                    iterations: iterations
                },
                requestHeaders: {
                    'Cache-Control': 'no-cache',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                onSuccess: function(xhrObj, req) {
                    var resultData = InkJson.parse(xhrObj.responseText);
                    analyze(resultData);
                },
                onFailure: function() {
                    Ink.log('Request failed');
                },
                onTimeout: function() {
                    Ink.log('Request timeout');
                },
                timeout: 5
            });
        }

        Loaded.run(function () { // run on DOMContentLoaded

            btnAnalyze.disabled = false;

            InkEvent.observe(btnAnalyze, 'click', function (event) {
                getMethods();
                InkEvent.stop(event);
            });
            InkEvent.observe(btnClear, 'click', function (event) {
                if (!confirm("Sure you want to clear?")) {
                    InkEvent.stop(event);
                    return;
                }
                var tbody = tableResults.tBodies.item(0);
                if (tbody) {
                    tbody.innerHTML = '';
                }

                // todo HOW REMOVE SORTING CACHE? (INK)
                tableObj2 = null;

                modalResults.tBodies.item(0).innerHTML = '';
                btnAnalyze.innerHTML = 'Анализировать';
                btnAnalyze.disabled = false;
                myProgressBar.getElement().classList.add('hide-all');
                btnClear.classList.add('hide-all');
            });

            //var btn3D = Ink.s('button.btn-8g');
            function activate() {
                var self = this, activatedClass = 'btn-activated';
                if (!confirm("Ты дурак?")) {
                    activatedClass = 'btn-error3d';
                } else {
                    activatedClass = 'btn-success3d';
                }
                if(!btn3D.classList.contains(activatedClass)) {
                    btn3D.classList.add(activatedClass);
                    setTimeout( function() { btn3D.classList.remove(activatedClass) }, 1000 );
                }
            }
            //InkEvent.observe(btn3D, 'click', activate, false);

            InkEvent.observe(btnAnalyze, 'click', function() {
                if(inProgress) return false;
                inProgress = true;
                animEl.classList.add('la-animate');
            } );

        /*
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
            });*/
        });
    }
);