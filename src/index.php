<?php

$connect = new mysqli('mysql', 'david', '123456', 'david', 3306);

if($connect) {
    echo "we have a connection";
} else {
    echo "we do not have a connection";
}