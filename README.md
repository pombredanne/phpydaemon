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

_The **fallback** item here is the default, so if you don't configure anything, phpydaemon will run two instances of any PHP call in paralell._


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
require_once 'phpydaemon/Client.php';
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
It's available at http://localhost:9713 (or whatever host/port you configured under http in phpydaemon.json).

You can also fetch status info via the webservice.

Fetch statistics about running, queued and complete jobs:
```
$ curl -X GET \
  -H 'Content-Type: application/json' \
  http://localhost:9713/api/stats
```

Or in PHP:
```php
require_once 'phpydaemon/Client.php';
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
require_once 'phpydaemon/Client.php';
$client = new Client();
print_r($client->getJobs());
```

Fetch the status page as html (f.ex. to include it inside some other page in your app):
```php
require_once 'phpydaemon/Client.php';
$client = new Client();
$html = $client->getStatusHtml();
```
