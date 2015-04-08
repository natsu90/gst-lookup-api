<?php
namespace Slim\Middleware;
use Desarrolla2\Cache\Cache as CacheAdapter;
 
Class Cache extends \Slim\Middleware
{
	protected $cache;
 
    public function __construct(CacheAdapter $cache)
    {
        $this->cache = $cache;
    }
 
    public function call()
    {
		$query = $this->app->request->get();
		$uri   = $this->app->request()->getResourceUri();
		$rsp   = $this->app->response();
        
        $queryParams = array();
        foreach ($query as $key => $value) {
            if(!empty($value)){
                array_push($queryParams, $key);
                array_push($queryParams, $value);
            }
        }

        $key = $uri."/".implode("/",array_map(function($val){ 
            return urlencode($val); 
        },$queryParams));

        $key = urlencode($key); // fix for file cache adapter
		// $key2 = md5($uri.json_encode($query));
        $data = $this->fetch($key);
        if ($data) {
            
            // cache hit... return the cached content
            $rsp["Content-Type"] = $data['content_type'];
            $rsp->body($data['body']);
            return;
        }
 
        // cache miss... continue on to generate the page
        $this->next->call();
 
        if ($rsp->status() == 200) {
            // cache result for future look up
            $ttl = 3600;
            if(preg_match("/gst_no/", $uri)){
                if(preg_match("/results/", $rsp->body())){
                    $ttl = null;    
                }
            } 
            $this->save($key, $rsp["Content-Type"], $rsp->body(),$ttl);
        }
    }
 
    protected function fetch($key)
    {
        $cache = $this->cache->get($key);
        return unserialize($cache);
    }
 
    protected function save($key, $contentType, $body,$ttl)
    {
    	$cache = array('content_type' => $contentType, 'body' => $body);
        $cache = serialize($cache);
        $this->cache->set($key,$cache,$ttl);
    }
}