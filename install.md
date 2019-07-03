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

To make upload and downloading via [fanficfare](https://github.com/JimmXinu/FanFicFare) work, add the following lines to your crontab, copy your ```personal.ini``` to ```/<path>/<to>/<bookstack-dir>/lib/ebooklib.ini``` and edit/add the lines below

_crontab:_

```* * * * * www-data /<path>/<to>/<bookstack-dir>/script/queue_download.php```

_ebooklib.ini:_

```post_process_cmd: 	/<path>/<to>/<bookstack-dir>/script/addbook.php -f "${output_filename}"```
```output_filename: /<path>/<to>/<bookstack-dir>/tmp/${title}-${siteabbrev}_${storyId}${formatext}```
(you might have the output_filename config in sitespecific sections, They'll need to be changed too, of course)
