<?php

namespace App\Filament\Resources\CommentReportResource\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToIndexAfterSave;

use App\Filament\Resources\CommentReportResource;
use Filament\Resources\Pages\EditRecord;

class EditCommentReport extends EditRecord
{
    use RedirectsToIndexAfterSave;

    protected static string $resource = CommentReportResource::class;
}
