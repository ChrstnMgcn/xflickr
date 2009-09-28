--------------------
Snippet: XFlickr
--------------------
Version: 0.1
Author: atma <atma@atmaworks.com>
License: GNU GPLv2 (or later at your option)

This component is a simple commenting system. It allows you to easily
put comments anywhere on your site. It allows you to also manage them
via the backend management interface.

Parameters:

- &thread (string) The name of the thread to start.
- &closed (boolean) If set to 1, no comments will be allowed.  
- &dateFormat (string) The default date format to display on dates. 
    Defaults to %b %d, %Y at %I:%M %p
    
Example:
Load a comment thread on each page.
[[XFlickr? &thread=`page[[*id]]`]]


Also, XFlickr allows users to report comments as Spam. This will send
an email to the email address specified in the System Setting
"xflickr.emailsTo". It sends it from the address in the Setting
"xflickr.emailsFrom".


Thanks for using XFlickr!
atma
atma@atmaworks.com