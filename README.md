# Web-Harvester
Crawler that will create workers to scrape websites and harvest information to re purpose content from white listed domains

# PhantomJs
sudo apt-get install build-essential chrpath libssl-dev libxft-dev libfreetype6 libfreetype6-dev libfontconfig1 libfontconfig1-dev
PHANTOM_JS="phantomjs-1.9.8-linux-x86_64"
cd ~
wget https://bitbucket.org/ariya/phantomjs/downloads/$PHANTOM_JS.tar.bz2
tar -xvjf $PHANTOM_JS.tar.bz2
sudo mv $PHANTOM_JS /usr/local/share
sudo ln -s /usr/local/share/$PHANTOM_JS/bin/phantomjs /usr/bin/phantomjs
phantomjs --version
