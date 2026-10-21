<?php

namespace Core;

/**
 * A class to run a response at the end of a request.
 * @todo Add compatibility to redirect to any view, append flash messages to a redirect and more features.
 */
class Response {
    /**
     *  Execute a Json response to return a response in API request.
     *
     * @param array $data A associative array with values to return as response.
     * @param int $http_code The HTTP_CODE to return in the headers.
     * @return void
     */
    static function json(array | object $data, int $http_code) {
        http_response_code($http_code);
        header("Content-Type: application/json");

        echo json_encode($data);
        exit;
    }
}