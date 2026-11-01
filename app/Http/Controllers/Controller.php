<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    public function __construct(
        protected Request $request,
    ) {}
}
