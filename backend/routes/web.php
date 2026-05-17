<?php

use App\Livewire\Website\BlogPage;
use App\Livewire\Website\HomePage;
use App\Livewire\Website\PostPage;
use App\Livewire\Website\TagPage;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class)->name('home');
Route::get('/blog', BlogPage::class)->name('blog');
Route::get('/blog/{post:slug}', PostPage::class)->name('post');
Route::get('/tag/{tag:slug}', TagPage::class)->name('tag');
