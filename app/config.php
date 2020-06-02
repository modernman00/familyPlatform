<?php

const BASELINE = realpath(__DIR__ ."../.env");

$dotenv = new \Dotenv\Dotenv::createImmutable(BASELINE);
$dotenv->load();

