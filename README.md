php_common_util
===============

Put all common utility class/function for copy/paste and reuse.

- HtmlBranchParser
There are lot of case when we try to parser a html file, and get the certain block of the <div> or <table>
in an easy case we can do regular expression, "/<div>(.*?)<\/div>/". 
In real world, There are lot of nesty div embeded. so regular expression will be hard to determine.

So this class will just need to specify the start tag and end tag. will get the correct html block.
It is fast, we do not need to break every dom into an object, just pick the block we needed.

