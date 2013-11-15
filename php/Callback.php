<?php

namespace phpydaemon;

class Callback {

	static function captureOutput() {
		ob_start();
	}

	static function getJob() {
		if (count($_SERVER['argv']) < 2) {
			die("usage: {$_SERVER['argv'][0]} <job_data_as_json>\n");
		}

		$job = json_decode($_SERVER['argv'][1]);
		if (!$job)
			throw new \Exception("Could not decode json: {$_SERVER['argv'][1]}");
		if (!$job->method)
			throw new \Exception("Job data missing 'method'");
		if (!$job->id)
			throw new \Exception("Job data missing 'id'");

		$tmp    = explode('.', $job->method);
		$method = array_pop($tmp);
		$class  = implode('\\', $tmp);
		if (!$class || !$method)
			throw new \Exception("Job.method invalid: {$job->method} - should be [some.namespace.]Class.method");

		$job->class  = $class;
		$job->method = $method;

		if (!$job->args)
			$job->args = array();

		return $job;
	}

	static function runJob($job) {
		$class  = $job->class;
		$object = new $class();
		$output = ob_get_clean();
		$result = call_user_func_array( array($object, $job->method), $job->args );
		print json_encode(array('result' => $result, 'output' => $output));
	}

}

?>
