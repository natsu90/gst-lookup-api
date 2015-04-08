<?php
require 'vendor/autoload.php';
use Symfony\Component\Process\Process;
use Desarrolla2\Cache\Adapter\File;
use Desarrolla2\Cache\Cache as CacheAdapter;
 
class Cache extends \Slim\Middleware
{
	protected $cache;
 
    public function __construct(CacheAdapter $cache)
    {
        $this->cache = $cache;
    }
 
    public function call()
    {
		$uri = $this->app->request()->getResourceUri();
		$rsp = $this->app->response();
        $key = urlencode($uri);
		
        $data = $this->fetch($key);
        if ($data) {
            
            $rsp["Content-Type"] = $data['content_type'];
            $rsp->body($data['body']);
            return;
        }
 
        $this->next->call();
 
        if ($rsp->status() == 200) {
            
            $ttl = 3600;
            if(preg_match("/gst_no/", $uri) && preg_match("/results/", $rsp->body()))
				$ttl = null;

            $this->save($key, $rsp["Content-Type"], $rsp->body(), $ttl);
        }
    }
 
    protected function fetch($key)
    {
        $cache = $this->cache->get($key);
        return unserialize($cache);
    }
 
    protected function save($key, $contentType, $body, $ttl)
    {
    	$cache = array('content_type' => $contentType, 'body' => $body);
        $cache = serialize($cache);
        $this->cache->set($key, $cache, $ttl);
    }
}

$adapter = new File("cache");

$app = new \Slim\Slim();
$app->add(new Cache(new CacheAdapter($adapter)));

$app->get('/', function () use($app) {
    $app->render('doc.php');
});

$app->get('/api/v1/:query_type/:query_value', function ($query_type, $query_value) use($app) {

	$process = new Process(sprintf('casperjs gst.proc %s "%s" --disk-cache=true', $query_type, $query_value));
	$process->run();

	if (!$process->isSuccessful()) {
		throw new \RuntimeException($process->getErrorOutput());
	}

	$response = $app->response();
	$response['Content-Type'] = 'application/json';
	$response->status(200);
	$response->body(json_encode(json_decode($process->getOutput()), JSON_PRETTY_PRINT));
});

$app->run();