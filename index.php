<?php
define('SYSTEM_START_TIME', microtime(true));
defined('SYSTEM_DEBUG') or define('SYSTEM_DEBUG', false);

require_once 'vendor/autoload.php';
require_once "autoload.php";

if (SYSTEM_DEBUG) {
	ini_set('display_errors', 'On');
	ini_set('display_startup_errors', 'On');
	error_reporting(-1);
	ini_set('log_errors', 'On');
} else {
	ini_set('display_errors', 'Off');
	ini_set('display_startup_errors', 'Off');
	error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
	ini_set('log_errors', 'On');
}

const BASE_URL = "https://www.iana.org/domains";

include_once("template/header.php");
?>
<div class="la-3dbar"></div>

<div class="ink-grid vertical-space">
	<h1>Анализ страницы</h1>

	<div class="column-group push-center">
		<div class="all-70 align-center">
			<div>
				<form class="ink-form">
					<div class="control-group validation">
						<label for="url">Введите, пожалуйста, имя сайта (домен):</label>
						<div class="control append-button" role="search">
							<span><input id="url" name="url" type="text" value="<?=BASE_URL?>" placeholder="example.com"></span>
							<button class="ink-button" id="btn-analyze" disabled="disabled" data-anim="la-3dbar">Анализировать</button>
						</div>
					</div>
					<div class="control-group">
						<ul class="control unstyled inline" id="markup">
							<li><input type="radio" id="markup_html" name="markup" value="<?= AnalyzerOptions::SUPPORTS_HTML ?>" >
								<label for="markup_html">HTML</label></li>
							<li><input type="radio" id="markup_xhtml" name="markup" value="<?= AnalyzerOptions::SUPPORTS_XHTML ?>">
								<label for="markup_xhtml">XHTML</label></li>
							<li><input type="radio" id="markup_xml" name="markup" value="<?= AnalyzerOptions::SUPPORTS_XML ?>">
								<label for="markup_xml">XML</label></li>
							<li><input type="radio" id="markup_auto" name="markup" value="<?= AnalyzerOptions::SUPPORTS_AUTO ?>" checked="checked">
								<label for="markup_auto">Определить автоматически</label></li>
						</ul>
					</div>
					<div class="control-group">
						<p>Количество итераций</p>
						<ul class="control unstyled inline" id="iters">
							<li><input type="radio" id="iter_1" name="iters" value="1" checked="checked">
								<label for="iter_1">1</label></li>
							<li><input type="radio" id="iter_5" name="iters" value="5">
								<label for="iter_5">5</label></li>
							<li><input type="radio" id="iter_10" name="iters" value="10">
								<label for="iter_10">10</label></li>
							<li><input type="radio" id="iter_50" name="iters" value="50">
								<label for="iter_50">50</label></li>
							<li><input type="radio" id="iter_100" name="iters" value="100">
								<label for="iter_100">100</label></li>
						</ul>
					</div>
				</form>
			</div>
			<a href="#" id="myModalTrigger2" class="ink-button">Результаты</a>
			<button class="ink-button hide-all" id="btn-clear">Очистить</button>
			<div id="alerts-block"></div>
		</div>
	</div>

</div> <!-- /END INK-GRID -->
	<div class="ink-shade fade">
		<div id="myModal" class="ink-modal fade" data-trigger="#myModalTrigger2" data-width="80%" data-height="80%" role="dialog" aria-hidden="true" aria-labelled-by="modal-title">
			<div class="modal-header">
				<button class="modal-close ink-dismiss"></button>
				<h2 id="modal-title">Результаты</h2>
			</div>
			<div class="modal-body" id="modalContent">
				<table class="ink-table bordered">
					<tbody>

					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<div class="push-right">
					<!-- Anything with the ink-dismiss class will close the modal -->
					<button class="ink-button caution ink-dismiss">Закрыть</button>
				</div>
			</div>
		</div>
	</div>

<div class="ink-grid" >
	<div id="my-progress-bar" class="ink-progress-bar grey hide-all" data-start-value="0">
		<span class="caption">Loading...</span>
		<div class="bar grey"></div>
	</div>
	<hr/>
	<table class="ink-table alternating hover bordered" id="result">
		<thead>
		<tr>
			<th class="align-left" >Средство</th>
			<th class="align-left">Основные API</th>
			<th class="align-left">Память</th>
			<th class="align-left" >Время</th>
			<th class="align-left">Кол-во ссылок</th>
		</tr>
		</thead>
		<tbody>

		</tbody>
	</table>
	<hr/>
	<table class="ink-table alternating hover bordered" id="info">
		<thead>
		<tr>
			<th class="align-left" data-sortable="true">Средство</th>
			<th class="align-left">Основные API</th>
			<th class="align-left">Вид</th>
			<th class="align-left">XPath выр.</th>
			<th class="align-left">CSS сел.</th>
			<th class="align-left">HTML</th>
			<th class="align-left">XHTML</th>
			<th class="align-left">XML</th>
			<th class="align-left">Авто</th>
			<th class="align-left">Определение кодировки</th>
		</tr>
		</thead>
		<tbody>
		<?php
		$methods = AnalyzerOptions::get();
		foreach ($methods as $key => $val) {
			if (isset($methods[$key]['parent'])) {
				continue;
			}
			$method = AnalyzerOptions::getInfo($key);
			?>
			<tr>
				<td><span><?= $method['name'] ?></span><br/>
					<a href="<?= $method['link'] ?>">@link</a></td>
				<td><?= $method['api'] ?></td>
				<td><?= $method['type'] ?></td>
				<td><?= $method['selectorXpath'] === true ? '<span style="color:green;">&check;</span>' :
						'<span style="color:red;">&cross;</span>' ?></td>
				<td><?= $method['selectorCss'] === true ? '<span style="color:green;">&check;</span>' :
						'<span style="color:red;">&cross;</span>' ?></td>
				<td><?= $method['canHtml'] === true ? '<span style="color:green;">&check;</span>' :
					'<span style="color:red;">&cross;</span>' ?></td>
				<td><?= $method['canXhtml'] === true ? '<span style="color:green;">&check;</span>' :
						'<span style="color:red;">&cross;</span>' ?></td>
				<td><?= $method['canXml'] === true ? '<span style="color:green;">&check;</span>' :
						'<span style="color:red;">&cross;</span>' ?></td>
				<td><?= $method['autodetectType'] === true ? '<span style="color:green;">&check;</span>' :
						'<span style="color:red;">&cross;</span>' ?></td>
				<td><?= $method['autodetectEnc'] !== false ? '<span style="color:green;">'.$method['autodetectEnc'].'</span>' :
						'<span style="color:red;">Нет</span>' ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php
	echo "Страница сгенерирована за: " . \profiling\Tester::getLoadTime();

	?>
</div> <!-- /END INK-GRID -->

<div class="push"></div>

	<script type="text/javascript" src="/template/assets/js/project-ink.js?no_cache=<?=time()?>"></script>

<?php include_once("template/footer.php"); ?>