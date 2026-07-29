<?php

function prefer_curl_setopt_array_single(\CurlHandle $ch): void
{
    // Valid: single curl_setopt call
    curl_setopt($ch, CURLOPT_URL, 'https://example.com');
}

function prefer_curl_setopt_array_used(\CurlHandle $ch): void
{
    // Valid: curl_setopt_array used instead of multiple calls
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
}

function prefer_curl_setopt_array_non_consecutive(\CurlHandle $ch): void
{
    // Valid: non-consecutive curl_setopt calls (other statement in between)
    curl_setopt($ch, CURLOPT_URL, 'https://example.com');
    $ready = true;
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
}

function prefer_curl_setopt_array_different_handles(\CurlHandle $ch, \CurlHandle $other): void
{
    // Valid: consecutive curl_setopt on different handles
    curl_setopt($ch, CURLOPT_URL, 'https://example.com');
    curl_setopt($other, CURLOPT_URL, 'https://other.example');
}

function prefer_curl_setopt_array_fully_qualified(\CurlHandle $ch): void
{
    // Valid: fully qualified single call
    \curl_setopt($ch, CURLOPT_HEADER, false);
}

function prefer_curl_setopt_array_top_level_style(): void
{
    // Valid: single call after curl_init
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://example.com');
}

function prefer_curl_setopt_array_separated_from_array(\CurlHandle $ch): void
{
    // Valid: a non-curl statement breaks the option-setting sequence
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $ready = true;
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
}

function prefer_curl_setopt_array_same_sequence_different_handles(\CurlHandle $ch, \CurlHandle $other): void
{
    // Valid: consecutive option setters on different handles are separate sequences
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
    ]);
    curl_setopt($other, CURLOPT_TIMEOUT, 10);
}
