<?php
include __DIR__ . "/../vendor/autoload.php";
const BASELINE = realpath(__DIR__ ."../");
$dotenv =  \Dotenv\Dotenv::create(BASELINE);
$dotenv->load();

