<?php

define("DB_NAME", 'vulnlibrary');
define("DB_ADDR", 'localhost');
define("DB_USER", 'root');
define("DB_PASS", 'root');

ini_set("session.save_handler", "memcached");
ini_set("session.save_path", ""); // memcachedのパスを設定