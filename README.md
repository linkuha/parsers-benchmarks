Данный проект создавался в учебных целях для сравнения производительности различных парсеров, на различных версиях PHP и различных ОС, с различными типами документов, в различной кодировке и различным размером файлов.

Преимущественно PHP парсеры. Так же применен Javascript парсер, выполняемый в PhantomJS headless-браузере.

**Дата проекта:** декабрь 2016

Сопутствующая статья с результатами анализа и описание будут приложены позднее.

В Docker можно будет обернуть позднее, на тот момент не было таких условий для тестов.

Основные обёртки парсеров, которые можно взять за пример использования, лежат в: `./classes/parsing/wrappers`

Веб-интерфейс (UI построен с помощью Ink framework) для запуска парсеров веб-страницы не предполагает качественного анализа, для полноценных нагрузочных тестов необходимо использовать сценарии. Используется для примерной визуализации работы проекта.

![UI screenshot 1](https://i.ibb.co/nRvzm8n/parsers-benchmarks1.jpg "UI example")

![UI screenshot 2](https://i.ibb.co/pwXmY7Z/parsers-benchmarks2.jpg "UI example")

CLI скрипты для автоматизации запусков в Linux (bash) и Windows (PowerShell, batch) и получения результатов: `./cli `

Ресурсы для анализа парсерами находятся в `./resources/test-docs`, из репозитория убраны тяжелые файлы объёмом от 100Мб до 2Гб, применяемые в анализе в рамках исследования.

**Помощь по установке вкратце:**

должны быть установлены gcc g++ компиляторы, команда time

`apt-get install build-essential time`

далее Tidy https://github.com/htacg/tidy-html5/blob/master/README/README.md

установка сборка Libtidy (cmake)
(для windows добавить путь к tidy в переменную среды PATH)

для php 7 debian расширение
`apt-get install php-xml`

_How to start analyze example_

To run series of tests use snippets like
```
for C in $(echo "10 50 100 400 600 1000"); do ./run.sh $C | tee output_$C.txt; done
```

*Get results*

To convert results to CSV file, use to_csv.py

```
./run.sh 5000 | tee output.txt
./to_csv.py < output.txt > results.txt
```

or for series
```
for C in $(echo "10 50 100 400 600 1000"); do ./to_csv.py < output_$C.txt > results-$C.csv; done
```