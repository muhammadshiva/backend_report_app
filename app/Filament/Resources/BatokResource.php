<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BatokResource\Pages;
use App\Models\Batok;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BatokResource extends Resource
{
    protected static ?string $model = Batok::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label = 'Batok';
    protected static ?string $pluralLabel = 'Batok';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('jenis_masukan')
                    ->required()
                    ->label('Jenis Masukan'),
                Forms\Components\DatePicker::make('tanggal')
                    ->required()
                    ->label('Tanggal'),
                Forms\Components\TextInput::make('sumber_batok')
                    ->required()
                    ->label('Sumber Batok'),
                Forms\Components\TextInput::make('jumlah_batok')
                    ->numeric()
                    ->required()
                    ->label('Jumlah Batok'),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jenis_masukan')->label('Jenis Masukan'),
                Tables\Columns\TextColumn::make('tanggal')->label('Tanggal'),
                Tables\Columns\TextColumn::make('sumber_batok')->label('Sumber Batok'),
                Tables\Columns\TextColumn::make('jumlah_batok')->label('Jumlah Batok'),
                Tables\Columns\TextColumn::make('keterangan')->label('Keterangan')->limit(50),
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
            'index' => Pages\ListBatoks::route('/'),
            'create' => Pages\CreateBatok::route('/create'),
            'edit' => Pages\EditBatok::route('/{record}/edit'),
        ];
    }
}
