<?php

namespace helpers;

class CurlHelper
{
	const PARAM_HTTP_TIMEOUT = 60;

	/**
	 * обёртка для CURL, для более удобного использования
	 *
	 * usage:
	 * ...
	 *
	 * @param array $param
	 * @return string
	 */
	public static function getUrlContent($param = null)
	{
		if (is_array($param))
		{
			$ch = curl_init();
			if ($param['type'] == 'POST')
				curl_setopt($ch, CURLOPT_POST, 1);

			if ($param['type'] == 'GET')
				curl_setopt($ch, CURLOPT_HTTPGET, 1);

			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0');
			if (isset($param['follow']))
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

			if (isset($param['encoding']))
				curl_setopt($ch, CURLOPT_ENCODING, '');

			if (isset($param['header']))
				curl_setopt($ch, CURLOPT_HEADER, 1);

			if (isset($param['ssl_false']))
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			if (isset($param['timeout']))
				curl_setopt($ch, CURLOPT_TIMEOUT, $param['timeout']);

			if (isset($param['returntransfer']))
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($ch, CURLOPT_URL, $param['url']);

			if (isset($param['postfields']))
				curl_setopt($ch, CURLOPT_POSTFIELDS, $param['postfields']);

			if (isset($param['cookie']))
				curl_setopt($ch, CURLOPT_COOKIE, $param['cookie']);

			/*
			if (self::checkCurlVersion() == 'old')
			{
				if (isset($param['sendHeader']))
				{
					$header = array();
					foreach ($param['sendHeader'] as $k => $v)
					{
						$header[] = $k. ': ' . $v . "\r\n";
					}
					curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
				}
			}*/

			if (isset($param['referer']))
				curl_setopt($ch, CURLOPT_REFERER, $param['referer']);

			if (isset($param['userpwd']))
				curl_setopt($ch, CURLOPT_USERPWD, $param['userpwd']);

			/*
			$settingProxy = Database::getProxy();
			if (is_array($settingProxy))
			{
				$proxy = $settingProxy[0]['val'];
				$proxyAddress = $settingProxy[1]['val'];
				$proxyType = $settingProxy[2]['val'];
			}
			if ($proxy)
			{
				curl_setopt($ch, CURLOPT_PROXY, $proxyAddress);
				if ($proxyType == 'SOCKS5')
					curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
				elseif ($proxyType == 'HTTP')
					curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			}

			if (Database::getSetting('debug'))
				curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
			*/

			$result = curl_exec($ch);
			curl_close($ch);

			if (isset($param['convert']))
				$result = iconv($param['convert'][0], $param['convert'][1], $result);

			return $result;
		}
	}

	public static function file_get_contents_curl($url) {
		if( $ch = curl_init() ) {

			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // возвращать данные, вместо вывода в браузер
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_ENCODING, 'utf-8');
			curl_setopt($ch, CURLOPT_TIMEOUT, 8);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

			$data = curl_exec($ch);
			curl_close($ch);

			return $data;
		}
		else return 0;
	}


	public static function checkCurl()
	{
		if (in_array('curl', get_loaded_extensions()))
			return true;
		else
			return false;
	}
}
