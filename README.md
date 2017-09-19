Handlebars
==========

This is a TYPO3 CMS extension to render native handlebars templates

 

Helper
------

Within the extension there are a few Handlebars helpers configured by default,
such as `content`, `block`, `json`, `lookup`. On the top of that we can add new
custom handlebars helper which can be declared in `ext_localconf.php` as follows

```
\JFB\Handlebars\HelperRegistry::getInstance()->register(
    'foo', 
    function ($labels, $key) {
        return $labels[$key];
    }
);
```
