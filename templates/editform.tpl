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
            left:20px;
            position: relative;
            display: block;
        }

        textarea {
            height: 150px;
            line-height: 25px;
        }
        -->
    </style>
    <form action="$url" method="post">
        <input type="hidden" name="editactive" value="1">
        <label>Title: <input type="text" name="title" value="%%title%%"></label>
        <label>Author: <input type="text" name="author" value="%%author%%"></label>
        <label>Tags: <textarea name="tags">%%tags%%</textarea></label>
        <label>Summary: <textarea name="summary">%%summary%%</textarea></label>
        <button type="submit" id="submit" value="Update Book">Update Book</button>
        <a href="$backurl">Cancel Edit</a>
    </form>
</div>