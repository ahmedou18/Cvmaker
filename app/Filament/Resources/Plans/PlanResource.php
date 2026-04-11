<?php

namespace App\Filament\Resources\Plans;

use App\Filament\Resources\Plans\Pages;
use App\Models\Plan;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Forms\Form; // استخدام Form بدلاً من Schema للاستقرار
use Filament\Forms\Set;
use Illuminate\Support\Str;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    // الأيقونة بشكلها القياسي المستقر
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'باقة';
    protected static ?string $pluralLabel = 'الباقات';

    /**
     * تعريف النموذج باستخدام Form المتوافق مع الإصدار الثالث المستقر
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // القسم الأول: البيانات الأساسية
                Forms\Components\Section::make('المعلومات الأساسية')
                    ->description('البيانات الظاهرة للمستخدم وسعر الباقة')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الباقة')
                            ->placeholder('مثال: الباقة الاحترافية')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('الرابط التقني (Slug)')
                            ->placeholder('pro-plan')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('price')
                            ->label('السعر')
                            ->numeric()
                            ->prefix('$')
                            ->required(),

                        Forms\Components\TextInput::make('duration_in_days')
                            ->label('مدة الصلاحية (بالأيام)')
                            ->numeric()
                            ->default(30)
                            ->required(),
                    ])->columns(2),

                // القسم الثاني: المميزات والحدود
                Forms\Components\Section::make('المميزات والحدود')
                    ->schema([
                        Forms\Components\TextInput::make('cv_limit')
                            ->label('عدد السير المسموحة')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('ai_credits')
                            ->label('رصيد الذكاء الاصطناعي')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Textarea::make('description')
                            ->label('وصف الباقة')
                            ->placeholder('اكتب مميزات إضافية هنا...')
                            ->columnSpanFull(),
                    ])->columns(2),

                // القسم الثالث: الإعدادات والخصائص
                Forms\Components\Section::make('خصائص إضافية')
                    ->schema([
                        Forms\Components\Toggle::make('remove_watermark')
                            ->label('إزالة العلامة المائية'),
                        
                        Forms\Components\Toggle::make('has_cover_letter')
                            ->label('رسالة تحفيزية'),
                        
                        Forms\Components\Toggle::make('priority_support')
                            ->label('دعم فني سريع'),
                        
                        Forms\Components\Toggle::make('is_popular')
                            ->label('باقة مشهورة (شارة مميزة)'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('تفعيل الباقة')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الباقة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money('usd')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cv_limit')
                    ->label('حد السير الذاتية')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
            ])
            ->filters([
                // يمكن إضافة فلاتر الحالة (نشط/غير نشط) هنا
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}