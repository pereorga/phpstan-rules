<?php

function prefer_curl_setopt_array_two_calls(\CurlHandle $ch): void
{
    // Invalid: two subsequent curl_setopt calls on the same handle
    curl_setopt($ch, CURLOPT_URL, 'https://example.com');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
}

function prefer_curl_setopt_array_three_calls(\CurlHandle $ch): void
{
    // Invalid: three subsequent calls, each after the first is reported
    curl_setopt($ch, CURLOPT_URL, 'https://example.com');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, false);
}

function prefer_curl_setopt_array_assigned(\CurlHandle $ch): void
{
    // Invalid: assigned return values still count as subsequent calls
    $ok = curl_setopt($ch, CURLOPT_URL, 'https://example.com');
    $ok = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
}

function prefer_curl_setopt_array_fully_qualified(\CurlHandle $ch): void
{
    // Invalid: fully qualified name
    \curl_setopt($ch, CURLOPT_URL, 'https://example.com');
    \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
}

function prefer_curl_setopt_array_mixed_handles(\CurlHandle $ch, \CurlHandle $other): void
{
    // Invalid: consecutive same-handle calls (different handle does not break a later pair)
    curl_setopt($other, CURLOPT_URL, 'https://other.example');
    curl_setopt($ch, CURLOPT_URL, 'https://example.com');
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
}

function prefer_curl_setopt_array_after_init(): void
{
    // Invalid: subsequent calls after curl_init
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://example.com');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
}

function prefer_curl_setopt_array_before_setopt_array(\CurlHandle $ch): void
{
    // Invalid: standalone option can be folded into the following array call
    curl_setopt($ch, CURLOPT_URL, 'https://example.com');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
    ]);
}

function prefer_curl_setopt_array_after_setopt_array(\CurlHandle $ch): void
{
    // Invalid: standalone option can be folded into the previous array call
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
}

function prefer_curl_setopt_array_around_setopt_array(\CurlHandle $ch): void
{
    // Invalid: both standalone options can be folded into the array call
    curl_setopt($ch, CURLOPT_URL, 'https://example.com');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
}
