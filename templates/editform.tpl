<div id="edit">
    <style type="text/css" title="text/css">
        <!--
        #edit {
            border: 2px #000 solid;
            padding: 3px;
            width: 95%;
            margin: 0 auto;
        }

        form {
            width: 90%;
            position: relative;
        }
        label {
            font-size: 13px;
            font-weight: bold;
            display: block;
            line-height: 25px;
            margin: 0 0 5px 0;
            width: 90%;
            position:relative;
        }

        input, textarea {
            width: 80%;
            height: 25px;
            font-size: 13px;
            border: 1px #ccc inset;
            position: relative;
            display: block;
        }

        textarea {
            height: 150px;
            line-height: 25px;
        }

        a.button {
          font-size: 13px;
          font-style: normal;
          font-weight: normal;
        }
        input.sname {
          width: 60%;
          display: inline-block;
        }
        input.svolume {
          width: 15%;
          display: inline-block;
        }
        -->
    </style>
    <form action="$url" method="post" enctype="multipart/form-data">
        <input type="hidden" name="editactive" value="1">
        <label>Title: <input type="text" name="title" value="%%title%%"></label>
        <label>Author: <input type="text" name="author" value="%%author%%"></label>
        <label>Series: <br><input class="sname" type="text" name="seriesname" value="%%seriesname%%"> |
          <input class="svolume" type="text" name="series_volume" value="%%seriesvol%%"></label>
        <label>Tags: <textarea name="tags">%%tags%%</textarea></label>
        <label>Summary: <textarea name="summary">%%summary%%</textarea></label>
        <label>Coverimage: <input type="file" name="illu"></label>
        <label>Regenerate Cover:  <input type="checkbox" name="updatecover"></label>
        <button type="submit" id="submit" value="Update Book">Update Book</button>
        <a class="button" href="%%backurl%%">Cancel Edit</a>
    </form>
</div>
