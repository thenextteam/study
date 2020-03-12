<?php
class Father
{
    public function __construct()
    {
        echo 'Father construct';
    }
}

class Son extends Father
{
    public function __construct()
    {
        echo 'Son construct';
        echo '<br />';
        parent::__construct();

    }
}

$br = '<br />';
$Father = new Father;

echo $br;

$Son = new Son;