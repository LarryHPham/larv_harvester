# Web-Harvester
Crawler that will create workers to scrape websites and harvest information to re purpose content from white listed domains

## Web-Url-Crawler
`crawl:url`
`crawl:start_jobs`

## Web-Url-parser
`parse:url`

## PhantomJs
Phantom JS is used to render javascript on server side. To get more info look at the documentation http://jonnnnyw.github.io/php-phantomjs/ which is the composer package that is used in this.

NOTE: composer package of phantomjs should be required first before doing below
Otherwise when using `composer require 'jonnyw/php-phantomjs'`the package will find the exe file in it's place rather than a directory

In vagrant box: (TBD on docker)
```
 sudo apt-get install build-essential chrpath libssl-dev libxft-dev libfreetype6 libfreetype6-dev libfontconfig1 libfontconfig1-dev
 PHANTOM_JS="phantomjs-1.9.8-linux-x86_64"
 cd ~
 wget https://bitbucket.org/ariya/phantomjs/downloads/$PHANTOM_JS.tar.bz2
 tar -xvjf $PHANTOM_JS.tar.bz2
 sudo mv $PHANTOM_JS /usr/local/share
 sudo ln -s /usr/local/share/$PHANTOM_JS/bin/phantomjs /usr/bin/phantomjs
 phantomjs --version
 ```

[Article Schema](https://sntmedia.atlassian.net/wiki/spaces/DCU/pages/208928769/JSON+schemas)
The parser will output a json file that matches the article schema of expected JSON response

## Jobs Queue
1. Supervisor needs installed to configure multiple workers
  - https://laravel.com/docs/5.5/queues#supervisor-configuration
2. sudo apt-get install supervisor
  - change config of supervisor in box `/etc/supervisor/conf.d/laravel-worker.conf`
3. sudo supervisorctl reread
4. sudo supervisorctl update
5. sudo supervisorctl start laravel-worker:*

6. Update `.env` file `QUEUE_DRIVER=` (pheanstalk OR sqs)

Once Setup get started on getting job queues lined up in code.
