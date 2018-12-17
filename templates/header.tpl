<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>TH's Library Devel</title>
    <script src="/rsrc/jquery.js" type="text/javascript" language="javascript" charset="utf-8"></script>
    <script>
        function reload() {
            window.location = '%%self%%';
            window.location.reload(true);
        }
        function filterList() {
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
           autosave='filtersearch' onkeyup="filterList()" onblur="filterList()" onmouseup="filterList()"
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
    <div id="showhidedl" style="float:right;" onclick="$('#downloader').slideToggle()">➕</div>
</div>
