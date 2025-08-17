<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Enums\ProductVariationTypeEnum;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class ProductVariations extends EditRecord
{
    protected static string $resource = ProductResource::class;
    protected static ?string $navigationIcon = "heroicon-o-clipboard-document-list";
    protected static ?string $title = "Variations";
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    public function form(Form $form): Form
    {
        return $form->schema([
           
        ]);
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $variations = $this->record->variations->toArray();
        return $data;
    }
}
