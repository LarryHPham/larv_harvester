#!/bin/bash

# Run the migration
php /home/vagrant/code/artisan migrate --force

# Download the NLP models
wget -O /home/vagrant/code/app/Library/NLPParser/stanford-corenlp-3.8.0-models.jar https://nlp.stanford.edu/software/stanford-english-corenlp-2017-06-09-models.jar

# Install or update supervisor
if [ ! /etc/supervisor ]; then
    sudo apt-get install supervisor
    sudo supervisord -c /home/vagrant/code/local.ebextensions/supervisor_conf
else
    sudo supervisorctl -c /home/vagrant/code/local.ebextensions/supervisor_conf update
fi
