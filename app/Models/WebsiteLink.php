<?php

namespace App\Models;

use Database\Factories\WebsiteLinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteLink extends Model
{
    /** @use HasFactory<WebsiteLinkFactory> */
    use HasFactory;

    protected $guarded = [];
}
