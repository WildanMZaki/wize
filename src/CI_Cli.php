<?php

class CI_Cli extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
}

function &get_instance()
{
    return CI_Cli::get_instance();
}
