<?php

//Reserved errors
const ERR_PARSE = ["code" => "-32700", "message" => "Ошибка анализа запроса"];
const ERR_INVALID_REQUEST = ["code" => "-32600", "message" => "Некорректный запрос"];
const ERR_METHOD_NOT_FOUND = ["code" => "-32601", "message" => "Метод не найден"];
const ERR_INVALID_PARAMS = ["code" => "-32602", "message" => "Неверные параметры"];
const ERR_INTERNAL = ["code" => "-32603", "message" => "Внутрення ошибка"];

//Encode and decode errors
const ERR_DECODE = ["code" => "-32048", "message" => "JSON decode error"];
const ERR_ENCODE = ["code" => "-32049", "message" => "JSON encode error"];

//Other
const ERR_EMPTY_METHOD = ["code" => "-32015", "message" => "Пустой метод"];