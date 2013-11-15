phpydaemon
==========

Webservice for calling long-running PHP functions as subprocesses.

                                                                                                                                                                                                                     
Installation
------------

Install from PyPI:
```
$ pip install phpy
```

Or install manually:
```
$ git clone https://github.com/stianstr/phpydaemon.git
$ cd phpydaemon
$ python setup.py install
```


Configuration 
-------------

Create a config based on the defaults
```
$ cp /etc/phpydaemon.json-dist /etc/phpydaemon.json
$ vim /etc/phpydaemon.json
```

You need to point php.callback to the script you want to use to run PHP jobs
```javascript
{
...
  "php": {
    "callback": "/path/to/your/app/phpydaemon-callback.php"
  }
...
}
```
_You can find an example callback script in **/usr/share/php/phpydaemon/callback-example.php**_


Configure workers (optional)
----------------------------

To get started, you can skip this part. But when you feel the need to control the number of paralell PHP processes to run for different jobs, you can configure the **workers** section in the config

```javascript
{
  "workers": {
    // Allow two instances of myMethod() in class MyClass to run at the same time
    { "method": "MyClass.myMethod", "paralell": 2 },
    // Allow just one at a time for method someMethod() in class OtherClass in namespace \my\namespace
    { "method": "my.namespace.OtherClass.someMethod", "paralell": 1 },
    // Allow two paralell jobs for all other methods
    { "fallback": true, "paralell": 2 }
  }
}
```

_The **fallback** item here is the default, so if you don't configure anything, phpydaemon will run two PHP calls in paralell at a time._


Usage
-----

Start the daemon
```
$ phpydaemon start
```

### Queueing jobs

Queue a PHP job to run (using CURL)
```
$ curl -X POST \
  -H 'Content-Type: application/json' \
  -d '{"method": "myapp.FileSystem.delete", "args": ["/"]}' \
  http://localhost:9713/api/queue
```

Queue a PHP job to run (using PHP)
```php
require 'phpydaemon/Client.php';
use phpydaemon\Client;
$client = new Client();
$jobId = $client->queue('myapp.FileSystem.delete', array('/'));
```

The phpydaemon web server will then receive this job, queue it, and when a worker
that matches the method is available, it will spawn a subprocess that calls
you callback, asking it to do this:

```php
$object = new \myapp\FileSystem();
$object->delete('/');
```

### Getting statistics and job status

The daemon exposes a simple web UI that shows the different workers and running jobs.

```
google-chrome http://localhost:9713
```

You can also fetch stats via the webservice:

```
$ curl -X GET \
  -H 'Content-Type: application/json' \
  http://localhost:9713/api/stats
```

Or in PHP:
```php
require 'phpydaemon/Client.php';
use phpydaemon\Client;
$client = new Client();
print_r($client->getStats());
```

Fetch details about running jobs:
```
$ curl -X GET \
  -H 'Content-Type: application/json' \
  http://localhost:9713/api/jobs
```

Or in PHP:
```php
require 'phpydaemon/Client.php';
use phpydaemon\Client;
$client = new Client();
print_r($client->getJobs());
```

Fetch the stats and running jobs as an HTML page:
```php
require 'phpydaemon/Client.php';
use phpydaemon\Client;
$client = new Client();
$html = $client->getStatusHtml();
```

### Preserving userId

In many cases it may be useful to let the PHP background job run as the same user that queued it.
You can pass user id as a parameter when queueing the job, and act on this in your PHP callback:

Queue the job with reference to a user id:
```php
require 'phpydaemon/Client.php';
use phpydaemon\Client;
$client = new Client();
$jobId = $client->queue('myapp.FileSystem.delete', array('/'), $myApp->getUser()->getId());
```

Switch to that user before running the job in your callback:
```php
require 'phpydaemon/Callback.php';
use phpydaemon\Callback;

$job = Callback::getJob();
MyApp::switchUser($job->userId);
Callback::runJob($job);
```


Todo
----
* Process return data and status
 * Callback.php prepares return data and stdout as JSON but Python daemon does not use it for anything yet
 * Detect if PHP job is successfull or not, and add option to log/act upon job status
* Clean up python code
 * Split up into one file for each class
 * Remove unused imports, code, commented out blocks


Credits
-------

This tool was developed as part of my work at [Availo AS](http://availo.no) and [eonBIT as](http://eonbit.com).

