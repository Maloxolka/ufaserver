<?php

require_once '../../src/Log/LogWriter.php';
require_once '../../src/ConfigLoader.php';
require_once '../../src/errors.php';

class JSON_RPC {

    public LogWriter $log;
    public ConfigLoader $config;

    public function __construct() {
        $this->log = new LogWriter();
        $this->config = new ConfigLoader();
    }

    public function makeResultResponse($result) {
        $response = [
            "result" => $result,
            "error" => null
        ];
        try {
            error_log(json_encode($response, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE).PHP_EOL, 3, "errors.log");

            return json_encode($response, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch(JsonException $e) {
            $this->log->logEntry(__CLASS__, __METHOD__, ERR_ENCODE);
            return null;
        }
    }

    public function makeErrorResponse($file, $error, $method = "empty") {
        $response = [
            "result" => null,
            "error" => $error
        ];

        $this->log->logEntry($file, $method, $error);

        try {
            error_log(json_encode($response, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE).PHP_EOL, 3, "errors.log");
            return json_encode($response, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch(JsonException $e) {
            $this->log->logEntry(__CLASS__, __METHOD__, ERR_ENCODE);
            return null;
        }
    }

    public function checkRequestFormat($request): bool {
        return (!empty($request->method) && is_string($request->method) && $request->v === $this->config->get("api.v"));
    }
}