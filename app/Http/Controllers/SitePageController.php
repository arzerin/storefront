<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use Illuminate\View\View;

class SitePageController extends Controller
{
    public function show(SitePage $page): View
    {
        abort_if($page->is_home || ! $page->published_revision_id, 404);
        return view('site-builder.page', ['page' => $page, 'sections' => $page->publishedSections(), 'isPreview' => false]);
    }
}
