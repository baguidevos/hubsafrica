<?php

namespace App\Fields\Types;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Component;
use Packstub\FormBuilder\Fields\Field;
use Packstub\FormBuilder\Fields\Types\FileField;

class CustomFileField extends FileField
{
    /**
     * Configure le composant FileUpload de Filament pour qu'il produise un tableau d'éléments,
     * même lorsqu'un seul fichier est autorisé (maxFiles: 1).
     * Cela garantit la compatibilité avec la règle de validation 'array' imposée par Packstub.
     */
    public function formComponent(Field $field): Component
    {
        $component = parent::formComponent($field);

        if ($component instanceof FileUpload && ! $this->isMultiple($field)) {
            $component->multiple()->maxFiles(1);
        }

        return $component;
    }
}
