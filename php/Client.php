<?php

namespace phpydaemon;

require_once 'phpydaemon/PhpyDaemonException.php';

class Client {

	private static $config_file = '/etc/phpydaemon.json';

	function __construct() {
		$config = array();
		if (file_exists(self::$config_file)) {
			$config = json_decode(file_get_contents(self::$config_file), true);
		}
		$host = ($config['http']['host']) ? $config['http']['host'] : 'localhost';
		$port = ($config['http']['port']) ? $config['http']['port'] : 9713;
		$this->url = "http://{$host}:{$port}";
	}

	function queue($method, $args, $userId) {
		return $this->curl_post('/api/run', array(
			'method' => $method,
			'args'   => $args,
			'userId' => $userId
		));
	}

	function getStats() {
		return $this->curl_get('/api/stats');
	}

	function getJobs() {
		return $this->curl_get('/api/jobs');
	}

	function getStatusHtml() {
		return $this->curl_get('/status', false);
	}

	private function curl_post($url, $data=null) {
		return $this->curl_call($url, $data, true, true);
	}

	private function curl_get($url, $is_json=true) {
		return $this->curl_call($url, null, false, $is_json);
	}

	private function curl_call($url, $data, $is_post, $is_json) {
		$url = $this->url . $url;
		if (!$data)
			$data = array();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if ($is_post) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($ch);
		$response = curl_getinfo($ch);
		curl_close($ch);
		if ($response['http_code'] != 200)
			throw new PhpyDaemonException("Error calling url: $url - Response code: {$response['http_code']}\nOutput: {$content}");
		return ($is_json) ? json_decode($content, true) : $content;
	}

}

?>
