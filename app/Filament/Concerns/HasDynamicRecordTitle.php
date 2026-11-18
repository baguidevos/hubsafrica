<?php

namespace App\Filament\Concerns;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

trait HasDynamicRecordTitle
{
    public function getTitle(): string|Htmlable
    {
        $record = $this->getRecord();
        $label = $this->resolveDynamicRecordLabel($record);

        if (blank($label)) {
            return parent::getTitle();
        }

        if ($this instanceof EditRecord) {
            return "Modifier : {$label}";
        }

        return $label;
    }

    protected function resolveDynamicRecordLabel(mixed $record): ?string
    {
        if (! $record) {
            return null;
        }

        foreach (['title', 'name', 'full_name', 'theme', 'email'] as $attribute) {
            $value = data_get($record, $attribute);

            if (filled($value)) {
                return (string) $value;
            }
        }

        return null;
    }
}
