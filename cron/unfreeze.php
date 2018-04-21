<?php
$dbh = new PDO('mysql:host=127.0.0.1;dbname=cl17106_iseason', 'cl17106_iseason', 'brooklin');
$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
$dbh->exec('CALL `services`()');