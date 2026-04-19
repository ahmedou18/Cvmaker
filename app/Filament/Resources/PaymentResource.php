<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'المدفوعات';

    protected static ?int $navigationSort = 3;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('تفاصيل الدفع')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->label('المستخدم')
                        ->required()
                        ->disabled(),

                    Forms\Components\Select::make('plan_id')
                        ->relationship('plan', 'name')
                        ->label('الباقة')
                        ->required()
                        ->disabled(),

                    Forms\Components\TextInput::make('amount')
                        ->label('المبلغ')
                        ->prefix('أوقية')
                        ->disabled(),

                    Forms\Components\TextInput::make('currency')
                        ->label('العملة')
                        ->disabled(),

                    Forms\Components\TextInput::make('payment_method')
                        ->label('طريقة الدفع')
                        ->disabled(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'pending_manual' => 'قيد الانتظار',
                            'completed' => 'مكتمل',
                            'failed' => 'فشل',
                        ])
                        ->label('الحالة')
                        ->required()
                        ->default('pending_manual'),

                    Forms\Components\Placeholder::make('screenshot_preview')
                        ->label('صورة التحويل')
                        ->content(function ($record) {
                            if (!$record || !$record->screenshot_path) {
                                return 'لا توجد صورة مرفوعة.';
                            }
                            $url = asset('storage/' . $record->screenshot_path);
                            $html = "<img src='{$url}' style='max-width:300px; max-height:200px; border-radius:8px; border:1px solid #ddd;'><br><a href='{$url}' target='_blank' class='text-primary-600 underline'>فتح الصورة كاملة</a>";
                            return new HtmlString($html);
                        })
                        ->columnSpanFull()
                        ->hidden(fn ($record) => !$record || !$record->screenshot_path),
                ])->columns(2),
        ]);
}
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('الباقة')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->prefix('أوقية')
                    ->sortable(),

                Tables\Columns\TextColumn::make('currency')
                    ->label('العملة')
                    ->badge(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'moosyl' => 'Moosyl',
                        'manual' => 'يدوي',
                        'bankily' => 'Bankily',
                        'masrivi' => 'Masrivi',
                        'click' => 'Click',
                        'bimbank' => 'BIM Bank',
                        default => $state,
                    }),

                Tables\Columns\ImageColumn::make('screenshot_path')
                    ->label('صورة التحويل')
                    ->circular(false)
                    ->size(50)
                    ->disk('public')
                    ->visibility('public'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'warning' => 'pending_manual',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending_manual' => 'قيد الانتظار',
                        'completed' => 'مكتمل',
                        'failed' => 'فشل',
                        'cancelled' => 'ملغى',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'قيد الانتظار (Moosyl)',
                        'pending_manual' => 'قيد المراجعة (يدوي)',
                        'completed' => 'مكتمل',
                        'failed' => 'فشل',
                        'cancelled' => 'ملغى',
                    ]),
                
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'moosyl' => 'Moosyl',
                        'manual' => 'يدوي',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
    ->label('موافقة')
    ->icon('heroicon-o-check-circle')
    ->color('success')
    ->requiresConfirmation()
    ->visible(fn ($record) => $record->status === 'pending_manual')
    ->action(function ($record) {
        $record->update(['status' => 'completed']);
        // سيتم تشغيل PaymentObserver تلقائياً عند التحديث
    }),

Tables\Actions\Action::make('reject')
    ->label('رفض')
    ->icon('heroicon-o-x-circle')
    ->color('danger')
    ->requiresConfirmation()
    ->visible(fn ($record) => $record->status === 'pending_manual')
    ->action(function ($record) {
        $record->update(['status' => 'cancelled']);
    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListPayments::route('/'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
