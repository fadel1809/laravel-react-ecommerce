<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Enums\ProductVariationTypeEnum;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
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
        $types = $this->record->variationTypes;
        $fields = [];
        return $form->schema([
           
        ]);
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $variations = $this->record->variations->toArray();
        $data['variations'] = $this->mergeCartesianWithExisting($this->record->variationTypes, $variations);
        return $data;
    }
    private function mergeCartesianWithExisting($variationTypes,$existingData):array
    {
        $defaultQuantity = $this->record->quantity;
        $defaultPrice = $this->record->price;
        $cartesianProduct = $this->cartesianProduct($variationTypes,$defaultQuantity,$defaultPrice);
        $mergeResult = [];
        foreach($cartesianProduct as $product){
            $optionIds = collect($product)
            ->filter(fn($value,$key) => str_starts_with($key,'variation_type'))
            ->map(fn($option) => $option['id'])
            ->values()
            ->toArray();

            $match = array_filter($existingData,function($existingOption) use ($optionIds){
                return $existingOption['variation_type_option_ids'] === $optionIds;
            }) ;

            if(!empty($match)){
                $existingEntry = reset($match);
                $product['quantity'] = $existingEntry['quantity'];
                $product['price'] = $existingEntry['price'];
            }else{
                $product['quantity'] = $defaultQuantity;
                $product['price'] = $defaultPrice;
            }
            $mergeResult[] = $product;
        }
        return $mergeResult;
    }

    private function cartesianProduct($variationTypes,$defaultQuantity = null,$defaultPrice=null):array {
        $result = [[]];

        foreach($variationTypes as $index => $variationType){
            $temp = [];

            foreach($variationTypes->options as $option){
                foreach($result as $combination){
                    $newCombination = $combination + [
                        'variation_type_'. ($variationType->id) => [
                            'id' => $option->id,
                            'name' => $option->name,
                            'label' => $variationType->name,

                        ]
                    ];
                    $temp[] = $newCombination;
                }
            }
            $result[] = $temp;
        }

        foreach($result as $combination){
            if(count($combination) === count($variationTypes)){
                $combination['quantity'] = $defaultQuantity;
                $combination['price'] = $defaultPrice;

            }
        }
        return $result;
    }
}
