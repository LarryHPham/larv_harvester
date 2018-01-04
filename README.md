# Web-Harvester
Crawler that will create workers to scrape websites and harvest information to re purpose content from white listed domains

### Installation
1. pull repository into local directory
  - `git clone https://github.com/passit/Web-Harvester.git`
2. cd /path/to/repo
3. `composer install`

## Stanford NLP Processor

The natural language processing is done using the [Stanford NLP server](https://stanfordnlp.github.io/CoreNLP/corenlp-server.html). All files in `app/Library/NLPParser` EXCEPT `KeywordParser.php` are from the server files found [here](https://stanfordnlp.github.io/CoreNLP/index.html#download).

1. Obtain the English models using `wget -O app/Library/NLPParser/stanford-corenlp-3.8.0-models.jar https://nlp.stanford.edu/software/stanford-english-corenlp-2017-06-09-models.jar`
2. Install a Java Runtime Environment (e.g. `sudo apt-get install default-jre`)

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

6. Update `.env` file `QUEUE_DRIVER=` (beanstalkd OR sqs)

## Migration
1. `php artisan migrate --seed` (--seed) are values that are to be defaultly entered into migration tables

## Web-Url-Crawler
`crawl:url` This command will take in a given url (protocol:required) and add a new row into the `crawl_order` && `urls` table to be crawled and|or parsed for url|content.
`crawl:start_jobs {job_count}` This command take in the number of jobs to start from `urls` && `crawl_order` table.  Each job will look into a url table on the database and parse through each row and run the crawler|parser based on given fields in the table.

## Web-Url-parser
`parse:url` This command will take in a given url and parse the content for information, store that information as json, and index it in the ledger table located on another server with table `article_libary`.

Once Setup get started on getting job queues lined up in code.
