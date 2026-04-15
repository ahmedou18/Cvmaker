<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages;
use App\Models\User;
use App\Models\Plan;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $label = 'مستخدم';
    protected static ?string $pluralLabel = 'المستخدمون';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('المعلومات الأساسية')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('الاشتراك والباقة')
                    ->schema([
                        Forms\Components\Select::make('plan_id')
                            ->label('الباقة')
                            ->options(Plan::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\TextInput::make('ai_credits_balance')
                            ->label('رصيد الذكاء الاصطناعي')
                            ->numeric()
                            ->default(3),

                        Forms\Components\TextInput::make('cover_letters_balance')
                            ->label('رصيد خطابات التغطية')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),

                Forms\Components\Section::make('رمز الدعم')
                    ->description('يُستخدم للتحقق من هوية المستخدم عند التواصل مع الدعم الفني')
                    ->schema([
                        Forms\Components\TextInput::make('support_code')
                            ->label('رمز التحقق (6 أرقام)')
                            ->maxLength(6)
                            ->disabled()
                            ->helperText('يتم توليده تلقائياً عند الحاجة للدعم')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('الباقة')
                    ->badge()
                    ->color(fn (User $record): string => $record->plan ? 'success' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('support_code')
                    ->label('رمز الدعم')
                    ->copyable()
                    ->badge()
                    ->color('warning')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan_id')
                    ->label('الباقة')
                    ->options(Plan::query()->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
