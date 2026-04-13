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
                            ->required(),
                        
                        Forms\Components\Select::make('plan_id')
                            ->relationship('plan', 'name')
                            ->label('الباقة')
                            ->required(),
                        
                        Forms\Components\TextInput::make('transaction_reference')
                            ->label('رقم المعاملة')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('المبلغ')
                            ->prefix('أوقية')
                            ->disabled(),
                        
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'moosyl' => 'Moosyl (إلكتروني)',
                                'manual' => 'دفع يدوي',
                            ])
                            ->label('طريقة الدفع')
                            ->disabled(),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'قيد الانتظار (Moosyl)',
                                'pending_manual' => 'قيد المراجعة (يدوي)',
                                'completed' => 'مكتمل',
                                'failed' => 'فشل',
                                'cancelled' => 'ملغى',
                            ])
                            ->label('الحالة')
                            ->required()
                            ->live(),
                        
                        Forms\Components\FileUpload::make('screenshot_path')
                            ->label('صورة التحويل')
                            ->directory('payment-screenshots')
                            ->visibility('public')
                            ->image()
                            ->downloadable()
                            ->openable()
                            ->hidden(fn ($record) => !$record || $record->payment_method !== 'manual'),
                        
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('ملاحظات الإدارة')
                            ->rows(3)
                            ->columnSpanFull(),
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
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'pending_manual',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'gray' => 'cancelled',
                    ]),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('الطريقة')
                    ->formatStateUsing(fn ($state) => $state === 'manual' ? 'يدوي' : 'Moosyl'),
                
                Tables\Columns\IconColumn::make('has_screenshot')
                    ->label('صورة')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->screenshot_path !== null),
                
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
                        
                        // TODO: Activate plan features for user here
                        // Example: $record->user->activatePlan($record->plan);
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
