# Web-Harvester
Crawler that will create workers to scrape websites and harvest information to re purpose content from white listed domains

# PhantomJs
Phantom JS is used to render javascript on server side. To get more info look at the documentation http://jonnnnyw.github.io/php-phantomjs/ which is the composer package that is used in this.

NOTE: composer package of phantomjs should be required first before doing below
Otherwise when using `composer require 'jonnyw/php-phantomjs'`the package will find the exe file in it's place rather than a directory

In vagrant box: (TBD on docker)
sudo apt-get install build-essential chrpath libssl-dev libxft-dev libfreetype6 libfreetype6-dev libfontconfig1 libfontconfig1-dev
PHANTOM_JS="phantomjs-1.9.8-linux-x86_64"
cd ~
wget https://bitbucket.org/ariya/phantomjs/downloads/$PHANTOM_JS.tar.bz2
tar -xvjf $PHANTOM_JS.tar.bz2
sudo mv $PHANTOM_JS /usr/local/share
sudo ln -s /usr/local/share/$PHANTOM_JS/bin/phantomjs /usr/bin/phantomjs
phantomjs --version
