<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\ArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = ArticleResource::class;
}
