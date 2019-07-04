<?php

namespace App\Http\Controllers;

use App\Http\ResponseWrapper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller;

class ExampleController extends Controller
{
    public function index(Request $request)
    {
        return new Response('', 403);
    }
}
