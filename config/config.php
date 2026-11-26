<?php
declare(strict_types=1);

/**
 * Application configuration.
 * Default values match a fresh XAMPP install (MySQL user "root", empty password).
 */
const DB_HOST = '127.0.0.1';
const DB_PORT = '3307';
const DB_NAME = 'online_food_delivery';
const DB_USER = 'root';
const DB_PASS = '';

const APP_NAME = 'QuickBite Delivery';
const CURRENCY = 'RWF ';
const APP_DEBUG = false;          // true = show detailed errors (development only)
const SESSION_IDLE_SECONDS = 1800; // auto-logout after 30 min of inactivity

// URL path of the project folder, e.g. "/food-delivery" under htdocs.
define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/'));

ini_set('display_errors', APP_DEBUG ? '1' : '0');
error_reporting(E_ALL);
