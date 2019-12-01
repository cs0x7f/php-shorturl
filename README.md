# php-shorturl

nginx conf
```
location ~ /([0-9A-Z]*|list|set|short)$ {
    rewrite ^/(.+)$ /short.php?path=$1 last;
}
```

mysql init
```
CREATE TABLE `shorturls` (
  `uid` int(15) UNSIGNED NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `create` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM AUTO_INCREMENT=1679616 DEFAULT CHARSET=utf8;
```