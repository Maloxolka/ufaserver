<?php

class LogWriter {

    public function logEntry($file, $method, $error): void {
        $message = "[ ".date(
                DATE_RSS
            )." ]: Method \"".$method."\" in \"".$file."\" , Error: \" ".$error["message"]." \"; \r\n";
        error_log($message, 3, "errors.log");
    }

}