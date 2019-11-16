<?php

namespace profiling;

/**
 * Simple class to benchmark code
 *
 * @example
 * $benchmark = new Benchmark();
 * $benchmark->time( 'Closure test', function () {
 *     $fn = function ( $item ) { return $item; };
 *     $fn( 'test' );
 * }, true );
 *
 * var_dump( $benchmark->get_results() );
 */

class Tester
{
	/**
	 * Stores the tests that we are to run.
	 *
	 * @var array
	 */
	protected $tests = [];

	/**
	 * This will contain the results of the benchmarks.
	 * There is no distinction between averages and just one runs
	 */
	protected $_resultsTime = array();
	protected $_resultsMemory = array();

	/**
	 * Disable PHP's time limit and optionally PHP's memory limit!
	 * These benchmarks may take some resources
	 * @param int $memory_limit
	 */
	public function __construct($memory_limit = null)
	{
		set_time_limit(0);
		if($memory_limit !== null) {
			$memory_limit = preg_replace('/[^0-9]/', '', (string)$memory_limit) . 'M';
			if($memory_limit != 'M') {
				ini_set( 'memory_limit', $memory_limit );
			}
		}
	}

	public function printConfig()
	{
		echo "Макс. доступно памяти: " . ini_get('memory_limit') . "</br>";
		echo "Макс. размер тела POST запроса: " . ini_get('post_max_size') . "</br>";
		echo "Макс. размер одного загружаемого файла: " . ini_get('upload_max_filesize') . "</br>";
		echo "Макс. время на выполнение скрипта: " . (ini_get('max_execution_time') == 0 ? "нет ограничения" : ini_get('max_execution_time')) . "</br>";
		echo "Макс. время на обработку GET POST: " . ini_get('max_input_time') . "</br>";
		echo "Поддержка URL wrappers (allow_url_fopen), для работы file_get_contents: " . (ini_get('allow_url_fopen') == 1 ? "Да" : "Нет") . "</br>";
	}

	/**
	 * Adds a test to run.
	 *
	 * Tests are simply closures that the user can define any sequence of
	 * things to happen during the test.
	 *
	 * @param                   $name
	 * @param callable|\Closure $closure
	 * @return $this
	 * @throws \Exception
	 */
	public function addTest($name, \Closure $closure, $arguments = [])
	{
		if(!is_callable($name) || $name instanceof \Closure) {
			throw new \Exception(__CLASS__.'::'.__FUNCTION__.' requires argument $test to be callable.');
		}
		$name = strtolower($name);
		$this->tests[$name] = [
			'func' => $closure,
			'args' => $arguments
			];
		return $this;
	}

	/**
	 * Runs through all of the tests that have been added, recording
	 * time to execute the desired number of iterations, and the approximate
	 * memory usage used during those iterations.
	 *
	 * @param int $iterations
	 * @return array
	 * @internal param bool $output
	 *
	 */
	public function runTestsTimes($iterations = 1000)
	{
		foreach ($this->tests as $name => $test) {
			// clear memory before start
			gc_collect_cycles();
			$start     = microtime(true);
			$start_mem = $max_memory = memory_get_usage(true);
			for ($i = 0; $i < $iterations; $i++)
			{
				$result = call_user_func_array($test['func'], $test['args']);
				$max_memory = max($max_memory, memory_get_usage(true));
				unset($result);
			}
			$envelop = microtime(true) - $start;
			$this->_resultsMemory[] = [
				'name' => $name,
				'time'   => $envelop,
				'memory' => $max_memory - $start_mem,
				'loops'      => $iterations,
			];
		}
		return $this->_resultsMemory;
	}

	/**
	 * Get results.
	 * @param array $results
	 * @return string
	 */
	public function getReport($results)
	{
		if (empty($results)) {
			return 'No results to display.';
		}
		// Template
		$tpl = "<table><thead>
				<tr>
					<td>Test</td>
					<td>Time</td>
					<td>Memory</td>
				</tr>
			</thead><tbody>
				{rows}
			</tbody>
			</table>";
		$rows = "";
		foreach ($results as $name => $result)
		{
			$rows .= "<tr>
				<td>{$result['name']}</td>
				<td>".number_format($result['time'], 4)."</td>
				<td>{$result['memory']}</td>
			</tr>";
		}
		$tpl = str_replace('{rows}', $rows, $tpl);
		return $tpl ."<br/>";
	}

	/**
	 * Creates a loop that lasts for $allowed_time and logs how many
	 * times a function was able to run
	 *
	 * @param string            $name the name of the test
	 * @param callable|\closure $test the function to run
	 * @param integer           $allowed_time seconds to run a function
	 * @return string
	 * @throws \Exception
	 */
	public static function runTestInTime($name, $test, $args = [], $allowed_time = 10) {
		if (!is_callable($test) || $test instanceof \Closure) {
			throw new \Exception(__CLASS__.'::'.__FUNCTION__.' requires argument $test to be callable.');
		}
		$start_time = microtime(true);
		$times_run = 0;

		// don't allow output
		ob_start();

		// run the $test function until time is up
		do {
			call_user_func_array($test, $args);
			$times_run++;
		} while (number_format(microtime(true) - $start_time, 0) < $allowed_time);

		// end output buffering
		ob_end_clean();

		// return the formatted results
		return self::resultsTestForTime($name, $times_run, $allowed_time);
	}

	/**
	 * Formats results for easy reading
	 *
	 * @param string $name name of the test
	 * @param integer $times_run number of times the test ran
	 * @param integer $allowed_time how long the test was allowed to run
	 * @return string
	 * @author Baylor Rae'
	 */
	private static function resultsTestForTime($name, $times_run, $allowed_time) {
		$output = '<h2>Results for ' . $name . '</h2>';
		$output .= '<dl>';

		$output .= '<dt>Times Run (cycles)</dt>';
		$output .= '<dd>' . number_format($times_run, 0) . '</dd>';

		$output .= '<dt>Ran For</dt>';
		$output .= '<dd>' . $allowed_time . 's</dd>';

		$output .= '</dl>';
		return $output;
	}


	/**
	 * Convert a file's size into a readable format
	 *
	 * @param  int     $size
	 * @param  string  $format
	 * @return string
	 */
	public static function sizeToString($size, $format = null)
	{
		// adapted from code at http://aidanlister.com/repos/v/function.size_readable.php
		$sizes = array('bytes', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		if(is_null($format))
		{
			$format = '%01.2f %s';
		}
		$lastsizestring = end($sizes);
		$cursizestring = $sizes[0];

		foreach ($sizes as $sizestring)	{
			if ($size < 1024) {
				$cursizestring = $sizestring;
				break;
			}
			if ($sizestring != $lastsizestring)	{
				$size /= 1024;
			}
		}
		// Bytes aren't normally fractional
		if($cursizestring == $sizes[0]) {
			$format = '%01d %s';
		}
		return sprintf($format, $size, $cursizestring);
	}

	public static function sizeToString2($size)
	{
		$unit = array('b','kb','mb','gb','tb','pb');
		return @round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
	}

	/**
	 * Get the current execution time
	 *
	 * @param  int  $decimals
	 * @return int
	 */
	public static function getLoadTime($decimals = 5)
	{
		return number_format(microtime(TRUE) - SYSTEM_START_TIME, $decimals);
	}
}