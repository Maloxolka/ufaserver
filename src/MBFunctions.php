<?php


class MBFunctions {

    public function mb_trim($string, $trim_chars = '\s') {
        return preg_replace('/^['.$trim_chars.']*(?U)(.*)['.$trim_chars.']*$/u', '\\1', $string);
    }

    public function mb_ucfirst($string, $encoding = 'utf-8'): string {
        if (function_exists('mb_strtoupper') && function_exists('mb_substr') && !empty($string)) {
            $string = mb_strtolower($string, $encoding);
            $upper = mb_strtoupper($string, $encoding);
            preg_match('#(.)#us', $upper, $matches);
            $string = $matches[1].mb_substr($string, 1, mb_strlen($string, $encoding), $encoding);
        } else {
            $string = ucfirst($string);
        }
        return $string;
    }

}