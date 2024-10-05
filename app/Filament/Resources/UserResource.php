<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Name'),
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->label('Username'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->label('Email'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->label('Password')
                    ->required(fn($context) => $context === 'create') // Hanya required saat create
                    ->dehydrateStateUsing(fn($state) => bcrypt($state)), // Hash password
                Forms\Components\FileUpload::make('profile_picture')
                    ->label('Profile Picture'),
                Forms\Components\TextInput::make('pin')
                    ->label('PIN'),
                Forms\Components\TextInput::make('phone')
                    ->label('Phone'),
                Forms\Components\TextInput::make('position')
                    ->label('Position'),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Name'),
                Tables\Columns\TextColumn::make('username')->label('Username'),
                Tables\Columns\TextColumn::make('email')->label('Email'),
                Tables\Columns\ImageColumn::make('profile_picture')->label('Profile Picture'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
