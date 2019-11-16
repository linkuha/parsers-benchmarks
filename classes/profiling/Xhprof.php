<?php

namespace profiling;

define('XHPROF_ROOT', __DIR__ . '/xhprof');

class Xhprof
{
	public static function begin() {
		if (extension_loaded('xhprof') && SYSTEM_DEBUG === true)
		{
			include_once XHPROF_ROOT . '/xhprof_lib/utils/xhprof_lib.php';
			include_once XHPROF_ROOT . '/xhprof_lib/utils/xhprof_runs.php';

			xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
		}
	}

	public static function end() {
		if (extension_loaded('xhprof') && SYSTEM_DEBUG === true) {
			$profilerNamespace = 'TEST';
			$xhprofData = xhprof_disable();
			$xhprofRuns = new \XHProfRuns_Default();
			$runId = $xhprofRuns->save_run($xhprofData, $profilerNamespace);

			$url = XHPROF_ROOT . '/xhprof_html/index.php?run=%s&source=%s';
			$profilesUrl = sprintf($url, $runId, $profilerNamespace);
			return '<a href="' . $profilesUrl . '">Profiler Output</a>';
		}
	}
}