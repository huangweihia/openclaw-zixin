<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use App\Support\EmailTemplateBuilder\EmailTemplateBuilderCompiler;
use App\Support\EmailTemplateBuilder\EmailTemplateMergeKeys;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class EmailTemplateResource extends BaseAdminResource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = '营销与触达';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = '邮件模板';

    protected static ?string $pluralModelLabel = '邮件模板';

    public static function canCreate(): bool
    {
        return static::canViewAny() && true;
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny() && true;
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny() && true;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function compileBuilderToStorage(array $data): array
    {
        $layout = $data['builder_layout'] ?? null;
        if (is_array($layout) && count($layout) > 0) {
            $compiled = EmailTemplateBuilderCompiler::compile($layout);
            $data['content'] = $compiled['html'];
            $data['plain_text'] = $compiled['plain'];
            $data['variables'] = $compiled['variables'];
        }

        return $data;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('基本信息')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('名称')
                        ->maxLength(255)
                        ->required(),
                    Forms\Components\TextInput::make('key')
                        ->label('模板键 key')
                        ->maxLength(255)
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('subject')
                        ->label('邮件主题')
                        ->maxLength(255)
                        ->required()
                        ->helperText('可使用占位符，例如 {{site_name}}、{{date}}，与正文一致。'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('启用')
                        ->default(true),
                ])
                ->columns(2),

            Forms\Components\Tabs::make('bodyTabs')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('拖拽搭建')
                        ->icon('heroicon-o-squares-2x2')
                        ->schema([
                            Forms\Components\Builder::make('builder_layout')
                                ->label('正文区块（可拖动排序）')
                                ->addActionLabel('添加区块')
                                ->collapsible()
                                ->live()
                                ->blocks([
                                    Forms\Components\Builder\Block::make('heading')
                                        ->label('标题')
                                        ->schema([
                                            Forms\Components\TextInput::make('text')
                                                ->label('文字')
                                                ->required(),
                                            Forms\Components\Select::make('level')
                                                ->label('级别')
                                                ->options(['h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3'])
                                                ->default('h2')
                                                ->required(),
                                        ]),
                                    Forms\Components\Builder\Block::make('paragraph')
                                        ->label('段落')
                                        ->schema([
                                            Forms\Components\Textarea::make('body')
                                                ->label('内容')
                                                ->rows(4)
                                                ->required(),
                                        ]),
                                    Forms\Components\Builder\Block::make('divider')
                                        ->label('分隔线')
                                        ->schema([]),
                                    Forms\Components\Builder\Block::make('button')
                                        ->label('按钮链接')
                                        ->schema([
                                            Forms\Components\TextInput::make('label')
                                                ->label('按钮文字')
                                                ->default('查看详情')
                                                ->required(),
                                            Forms\Components\TextInput::make('url')
                                                ->label('链接 URL')
                                                ->placeholder('https:// 或 {{manage_url}}')
                                                ->required(),
                                        ]),
                                    Forms\Components\Builder\Block::make('merge')
                                        ->label('插入占位符')
                                        ->schema([
                                            Forms\Components\Select::make('key')
                                                ->label('占位符')
                                                ->options(EmailTemplateMergeKeys::options())
                                                ->searchable()
                                                ->required(),
                                        ]),
                                    Forms\Components\Builder\Block::make('db_field')
                                        ->label('数据表字段（发送时取库内首条/示例；摘要邮件与订阅邮件已支持 db_* 键）')
                                        ->schema([
                                            Forms\Components\Select::make('entity')
                                                ->label('表')
                                                ->options([
                                                    'articles' => '文章 articles',
                                                    'projects' => '项目 projects',
                                                    'users' => '用户 users',
                                                ])
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(fn (Set $set) => $set('field', null)),
                                            Forms\Components\Select::make('field')
                                                ->label('字段')
                                                ->options(function (Get $get): array {
                                                    return match ($get('entity')) {
                                                        'articles' => [
                                                            'title' => '标题 title',
                                                            'summary' => '摘要 summary',
                                                            'url' => '前台文章链接 url',
                                                        ],
                                                        'projects' => [
                                                            'name' => '名称 name',
                                                            'url' => '前台项目链接 url',
                                                        ],
                                                        'users' => [
                                                            'name' => '姓名 name',
                                                            'email' => '邮箱 email',
                                                        ],
                                                        default => [],
                                                    };
                                                })
                                                ->required(),
                                        ]),
                                ])
                                ->columnSpanFull(),
                            Forms\Components\Placeholder::make('builder_preview')
                                ->label('中间预览（示例数据）')
                                ->content(function (Get $get): HtmlString {
                                    $layout = $get('builder_layout');
                                    $html = EmailTemplateBuilderCompiler::previewHtml(is_array($layout) ? $layout : null);

                                    return new HtmlString(
                                        '<div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm max-h-[520px] overflow-y-auto dark:border-gray-700 dark:bg-gray-900">'.$html.'</div>'
                                    );
                                })
                                ->columnSpanFull(),
                        ]),
                    Forms\Components\Tabs\Tab::make('HTML 源码')
                        ->icon('heroicon-o-code-bracket')
                        ->schema([
                            Forms\Components\Textarea::make('content')
                                ->label('邮件 HTML')
                                ->rows(14)
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('plain_text')
                                ->label('纯文本')
                                ->rows(5)
                                ->columnSpanFull(),
                            Forms\Components\TagsInput::make('variables')
                                ->label('变量名列表')
                                ->helperText('使用拖拽保存时会自动覆盖为文中出现的占位符。'),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->limit(40)->toggleable()->label('名称'),
                Tables\Columns\TextColumn::make('key')->limit(40)->toggleable()->label('key'),
                Tables\Columns\IconColumn::make('builder_layout')
                    ->label('拖拽')
                    ->boolean()
                    ->getStateUsing(fn (EmailTemplate $record): bool => is_array($record->builder_layout) && count($record->builder_layout) > 0)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subject')->limit(40)->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->label('启用')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailTemplates::route('/'),
            'create' => Pages\CreateEmailTemplate::route('/create'),
            'edit' => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
