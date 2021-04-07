<?php

/**
 * Including vendor autoload to load libraries and custom namespace created
 */
include dirname(__FILE__) . "/vendor/autoload.php";


use Supermetrics\SDK\Authenticate;
use Supermetrics\SDK\SocialAnalytics;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Here we can use .env and load in $_ENV or $_SERVER using vulcas/dotenv package
 * but just for the sake of simplicity i have not used that library
 */
$supermetricsCredentials = include 'supermetric_credentials.php';

$auth = new Authenticate(
    $supermetricsCredentials['client_id'],
    $supermetricsCredentials['email'],
    $supermetricsCredentials['name']
);

try {
    $slToken = $auth->getSLToken();
} catch (\Exception $e) {
    die($e->getMessage());
} catch (GuzzleException $e) {
    die("There is something wrong with supermtrics api " . $e->getMessage());
}


$postAnalytics = new SocialAnalytics($slToken);
$stats = $postAnalytics->posts()->stats();
// To access posts you can access
//var_dump($postAnalytics->posts);

echo json_encode($stats, JSON_PRETTY_PRINT);