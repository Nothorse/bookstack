<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Ebook Library</title>
    <meta name="viewport" content="width=device-width, initial-scale=0.7">
    <script src="/rsrc/jquery.js" type="text/javascript" language="javascript" charset="utf-8"></script>
    <script>
        function reload() {
            window.location = '%%self%%';
            window.location.reload(true);
        }
        function doSearch(url, replacediv) {
          $.ajax({
                url: url,
                error: function() {
                   var data = "<div class='error'>Something went wrong, maybe try again</div>";
                   $('#booklist').before(data);
                },
                success: function(data) {
                  replacediv.replaceWith(data);
                },
                type: 'GET'
             });
        }
        function remoteFilter() {
          var input, filter, url, searchRequest, replacediv;
          input = $('#myInput');
          replacediv = $('#bookswide');
          filter = input.val().toUpperCase();
          url = '/index.php/search/' + filter;
          if (filter.length < 3) {
            console.log('too short');
            if (filter.length == 0) {
              doSearch(url, replacediv);
            }
          }
          console.log("url: " +url);
          doSearch(url, replacediv);
        }
        function toggleExpand(element) {
            var el = $(element).nextUntil(2)[1];
            if (el.style.display == 'none') {
                el.style.display = 'block';
            } else {
                el.style.display = 'none';
            }
        }
    </script>
    <link rel="stylesheet" href="/rsrc/ui.css" type="text/css" media="all">
</head>
<body style='padding:0;margin:0;' class="normal">
<div id="bluebar">
    <div class="reloadbtn"><span onclick="reload()">🔁</span></div>
    <input type="search" id="myInput" results=5
           autosave='filtersearch' onkeyup="remoteFilter()" onblur="remoteFilter()" onmouseup="remoteFilter()"
           placeholder="filter" />
    <ul>
      <li class="category">
          <a href="%%self%%">🔝</a>
      </li>
        <li class="category">
            <a href="%%self%%?sort=date">Date</a>
        </li>
        <li class="category">
            <a href="%%self%%?sort=name">Title</a>
        </li>
        <li class="category">
            <a href="%%self%%?sort=author">Authors</a>
        </li>
        <li class="category">
            <a href="%%self%%/add/">Add/Log</a>
        </li>
    </ul>
</div>
