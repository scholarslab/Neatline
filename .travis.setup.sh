#! /usr/bin/env bash

if [ -z $OMEKA_BRANCH ]; then
  OMEKA_BRANCH=master
fi

export PLUGIN_DIR=`pwd`
export OMEKA_DIR=`pwd`/omeka

mysql -e "create database IF NOT EXISTS omeka_test;" -uroot;
git clone --recursive https://github.com/omeka/Omeka.git $OMEKA_DIR

# check out the correct branch
cd $OMEKA_DIR && git checkout $OMEKA_BRANCH
cd $PLUGIN_DIR

# move configuration files
mv $OMEKA_DIR/application/config/config.ini.changeme $OMEKA_DIR/application/config/config.ini
mv $OMEKA_DIR/application/tests/config.ini.changeme $OMEKA_DIR/application/tests/config.ini

# set up testing config
sed -i 's/db.host = ""/db.host = "localhost"/' $OMEKA_DIR/application/tests/config.ini
sed -i 's/db.username = ""/db.username = "root"/' $OMEKA_DIR/application/tests/config.ini
sed -i 's/db.dbname = ""/db.dbname = "omeka_test"/' $OMEKA_DIR/application/tests/config.ini
sed -i 's/email.to = ""/email.to = "test@example.com"/' $OMEKA_DIR/application/tests/config.ini
sed -i 's/email.administator = ""/email.administrator = "admin@example.com"/' $OMEKA_DIR/application/tests/config.ini
sed -i 's/paths.maildir = ""/paths.maildir = "\/tmp"/' $OMEKA_DIR/application/tests/config.ini
sed -i 's/paths.imagemagick = ""/paths.imagemagick = "\/usr\/bin\/"/' $OMEKA_DIR/application/tests/config.ini
sed -i 's/256M/512M/' $OMEKA_DIR/application/tests/bootstrap.php

phpver=`php -v | grep -Eow '^PHP [^ ]+' | gawk '{ print $2 }'`
echo "PHP version: $phpver..."
if [ $(version $phpver) -gt $(version 5.3.1) ]; then
  # composer is only available on 5.3.2+
  composer install
fi

phpenv rehash # refresh the path, just in case

# symlink the plugin
cd $OMEKA_DIR/plugins && ln -s $PLUGIN_DIR
ls $OMEKA_DIR/themes
bundle install
npm install
bower install -f
grunt build

