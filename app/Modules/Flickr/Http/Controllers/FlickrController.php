<?php

namespace App\Modules\Flickr\Http\Controllers;

use Illuminate\Routing\Controller;

class FlickrController extends Controller
{
    public function index()
    {
        return view('flickr::index');
    }
}
