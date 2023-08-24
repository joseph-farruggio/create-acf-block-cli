<?php

namespace App;

class Timer
{
    private $startTime;

    public function start()
    {
        $this->startTime = microtime(true);
    }

    public function elapsed()
    {
        return microtime(true) - $this->startTime;
    }

    public function log($message)
    {
        // echo $message . ': ' . $this->elapsed() . " seconds\n";
    }
}