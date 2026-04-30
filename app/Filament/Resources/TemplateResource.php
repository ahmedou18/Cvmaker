<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplateResource\Pages;
use App\Models\Template;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TemplateResource extends Resource
{
    protected static ?string $model = Template::class;
    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?string $navigationLabel = 'قوالب السير';
    protected static ?string $modelLabel = 'قالب';
    protected static ?string $pluralModelLabel = 'القوالب';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('اسم القالب')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('المعرف (slug)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                FileUpload::make('thumbnail')
                    ->label('الصورة المصغرة')
                    ->image()
                    ->disk('template_thumbnails')   // ✅ القرص المخصص: public/assets/images/templates
                    ->directory('')                 // لا مجلد فرعي
                    ->visibility('public')
                    ->required(),
                Select::make('view_path')
                    ->label('ملف القالب')
                    ->options(self::getTemplateOptions())
                    ->required()
                    ->helperText('اختر من ملفات القوالب الموجودة في resources/views/templates'),
                Toggle::make('is_premium')
                    ->label('مدفوع؟')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('الصورة')
                    ->disk('template_thumbnails') // اختياري لكنه يضمن الاتساق
                    ->size(60)
                    ->circular(),
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('المعرف'),
                TextColumn::make('view_path')
                    ->label('ملف القالب'),
                BooleanColumn::make('is_premium')
                    ->label('مدفوع'),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('j M Y'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (Template $record) => $record->resumes()->count() === 0)
                    ->tooltip(fn (Template $record) => $record->resumes()->count() > 0
                        ? 'لا يمكن حذف قالب مستخدم في سير ذاتية'
                        : null),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemplates::route('/'),
            'create' => Pages\CreateTemplate::route('/create'),
            'edit' => Pages\EditTemplate::route('/{record}/edit'),
        ];
    }

    /**
     * جلب خيارات القوالب من ملفات views/templates
     */
    protected static function getTemplateOptions(): array
    {
        $templatesPath = resource_path('views/templates');
        if (!is_dir($templatesPath)) {
            return [];
        }

        $files = glob($templatesPath . '/*.blade.php');
        $options = [];
        foreach ($files as $file) {
            $filename = basename($file, '.blade.php');
            $options['templates.' . $filename] = $filename;
        }

        return $options;
    }
}