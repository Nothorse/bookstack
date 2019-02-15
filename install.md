# How to install

* create a library directory. can be anywhere.
* copy config-template.php to config.php
* fill out local values
  * TRASH <- either /dev/null or a trash dir that you will need to empty out manually
  * BASEDIR <- where you want your epub files to be placed
  * BASEURL <- the URL where you can access the application
* create a SQLite3 database:
* ```sqlite3 <BASEDIR>/.library.db < rsrc/schema/schema.sql```
* point your webserver to the BASEURL
* enjoy

To make upload and downloading via [fanficfare](https://github.com/JimmXinu/FanFicFare) work, add the following lines to your crontab and personal.ini:

_crontab:_

```* * * * * www-data /<path>/<to>/<bookstack-dir>/script/queue_download.php```

_personal.ini:_

```post_process_cmd: 	/<path>/<to>/<bookstack-dir>/script/addbook.php -f "${output_filename}"```
