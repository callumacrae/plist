# callumacrae/plist

This plist parser simply parses a plist file and returns an array of data found in the file. It is extremely easy to use; the following line of code is an example of how it works:

```php
$array = plist::Parse('path/to/file');
```

*DO NOT USE THE DEMO ON A LIVE SERVER.* It can use a lot of resources, and is also vulnerable to RFI injection (kind of intentional to keep the script simpler).

It is released under the Creative Commons Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0) license:
http://creativecommons.org/licenses/by-sa/3.0/