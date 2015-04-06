## Malaysia Good and Service Tax (GST) Status Lookup API

### Inspired from https://github.com/maelzx/gstcustoms_phantomjs

## Dependencies
* PhantomJS 1.9.8
* CasperJS 1.1.0-beta3

## Demo
#### https://gst-lookup-api.herokuapp.com

## Easy Installation
[![Deploy](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy)

## Make it work with PhantomJS v 2.0 (workaround until CasperJS 2.0 release)
* Edit file __CasperJS_dir__/bin/bootstrap.js
* Find "// phantom check" (for me its on line 40)
* Replace "//phantom check" with this code:
```
// phantom check
if (!('phantom' in this)) {
    console.error('CasperJS needs to be executed in a PhantomJS environment http://phantomjs.org/');
}
else {
    if (phantom.version.major === 2) {
        //setting other phantom.args if using phantomjs 2.x
        var system = require('system');
        var argsdeprecated = system.args;
        argsdeprecated.shift();
        phantom.args = argsdeprecated;
    }
 }
```
* And comment out "function(version) {" fucntion on line 103 (around there) like this:
```
/* (function(version) {
        // required version check
        if (version.major !== 1) {
            return __die('CasperJS needs PhantomJS v1.x');
        } if (version.minor < 8) {
            return __die('CasperJS needs at least PhantomJS v1.8 or later.');
        }
        if (version.minor === 8 && version.patch < 1) {
            return __die('CasperJS needs at least PhantomJS v1.8.1 or later.');
        }
    })(phantom.version); */
```
