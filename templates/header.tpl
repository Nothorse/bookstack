<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name='viewport' content='width=620,user-scalable=true' />	<title>TH's Library Devel</title>
    <script src="/jquery.js" type="text/javascript" language="javascript" charset="utf-8"></script>
    <script>
        function reload() {
            window.location = '%%self%%';
            //location.reload(true);
        }
        function myFunction() {
            var input, filter, div, ul, li, a, i;
            input = document.getElementById("myInput");
            filter = input.value.toUpperCase();
            div = document.getElementById("bookswide");
            ul = div.getElementsByTagName('ul');
            li = ul[0].getElementsByTagName("li");
            for (i = 0; i < li.length; i++) {
                a = li[i].getElementsByClassName("titlelink")[0];
                if (filter.length > 2) {
                    if (a.innerHTML.toUpperCase().indexOf(filter) > -1) {
                        li[i].style.display = "";
                        a.nextElementSibling.style.display = "block";
                    } else {
                        li[i].style.display = "none";
                        a.nextElementSibling.style.display = "none";
                    }
                } else {
                    li[i].style.display = "";
                    a.nextElementSibling.style.display = "none";
                }
            }
        }
    </script>
    <link rel="stylesheet" href="/ui.css" type="text/css" media="all">
</head>
<body style='padding:0;margin:0;'>
<div id="bluebar">
    <div class="reloadbtn"><span onclick="reload()">🔁</span></div>
    <input type="search" id="myInput" results=5
           autosave='filtersearch' onkeyup="myFunction()" onmouseup="myFunction()"
           placeholder="filter" />
    <ul>
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
            <a href="%%self%%?sort=tags">Tags</a>
        </li>
        <li class="category">
            <a href="%%self%%?sort=list">Lists</a>
        </li>
    </ul>
</div>