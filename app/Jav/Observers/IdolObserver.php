<?php

namespace App\Jav\Observers;

use App\Jav\Mail\WordPressIdolPost;
use App\Models\Idol;
use App\Models\WordPressPost;
use Illuminate\Support\Facades\Mail;

class IdolObserver
{
    public function created(Idol $idol)
    {
        if (!$idol->cover) {
            return;
        }

        // Send idol post
        if (WordPressPost::where(['title' => $idol->name])->exists()) {
            return;
        }
        Mail::send(new WordPressIdolPost($idol));
        WordPressPost::create(['title' => $idol->name]);
    }
}
