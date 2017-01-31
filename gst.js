
var Spooky = require('spooky'),
    config = require('./config.json'),
    spooky_config = {
        child: {
            command: "./node_modules/.bin/casperjs",
            transport: 'http'
        },
        casper: {
            logLevel: 'debug',
            verbose: true,
            waitTimeout: 3000,
            pageSettings: {
                loadImages: false,
                loadPlugins: false,
                userAgent: 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.2 Safari/537.36'
            }
        }
    },
    radio_input_id = config.reg_name_radio_input_id,
    text_input_id = config.reg_name_text_input_id,
    text_input = 'mydin',
    spookyCallback = function (err) {

        if (err) {
            e = new Error('Failed to initialize SpookyJS');
            e.details = err;
            throw e;
        }

        spooky.start('https://gst.customs.gov.my/TAP/');
        spooky.waitForSelector(config.lookup_label_id);
        spooky.then([config, function() { this.click(lookup_label_id) }]);
        spooky.waitForSelector(radio_input_id);
        spooky.then([{
            radio_input_id: radio_input_id, 
            text_input_id: text_input_id, 
            text_input: text_input
        }, function() { 
            this.click(radio_input_id);
            this.sendKeys(text_input_id, text_input)
                .sendKeys(text_input_id, this.page.event.key.Enter , {keepFocus: true}); 
        }]);
        spooky.waitUntilVisible(config.result_table_id);
        spooky.then([config, function() { 

            results = this.evaluate(function(result_table_id) {
      
              var rows = document.querySelectorAll(result_table_id+' tbody tr.DataRow');
              return Array.prototype.map.call(rows, function(row) {

                var cells = row.querySelectorAll('td');
                  return {
                    gst_no: cells[0].innerText.trim(),
                    taxpayer_name: cells[1].innerText.trim(),
                    trading_name: cells[2].innerText.trim(),
                    commence_date: cells[3].innerText.trim(),
                    status: cells[4].innerText.trim()
                  };
                });
              }, result_table_id);

            this.emit('result', results);
        }]);
        spooky.run(function() {
            this.exit();
        });
    },
    spooky = new Spooky(spooky_config, spookyCallback),
    getByGstNumber = function(input, callback) {},
    getByBusinessRegistrationNumber = function(input, callback) {},
    searchByBusinessName = function(input, callback) {};



spooky.on('error', function (e, stack) {
    console.error(e);

    if (stack) {
        console.log(stack);
    }
});


// Uncomment this block to see all of the things Casper has to say.
// There are a lot.
// He has opinions.
spooky.on('console', function (line) {
    console.log(line);
});


spooky.on('result', function(results) {

    console.log(JSON.stringify({results: results}));
});

spooky.on('log', function (log) {
    if (log.space === 'remote') {
        console.log(log.message.replace(/ \- .*/, ''));
    }
});
