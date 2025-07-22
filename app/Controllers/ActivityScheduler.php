<?php

namespace App\Controllers;


class ActivityScheduler extends BaseController
{

    public function index()
    {
        return view('activity');
    }

    public function bmkg_api()
    {
        return view('bmkg_api');
    }
}