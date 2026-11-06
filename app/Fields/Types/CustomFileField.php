<?php

namespace App\Fields\Types;

use Filament\Schemas\Components\Component;
use Packstub\FormBuilder\Fields\Field;
use Packstub\FormBuilder\Fields\Types\FileField;

class CustomFileField extends FileField
{
    /**
     * Règles de validation pour le champ fichier.
     * Pour les uploads multiples (multiple = true), on conserve la règle 'array' et le nombre max de fichiers.
     * Pour les uploads simples (multiple = false), l'état transmis peut être une chaîne scalaire (chemin stocké),
     * une instance UploadedFile ou null : on n'impose donc pas la règle 'array' afin d'éviter l'erreur
     * "The ... field must be an array." tout en préservant les validations de taille et d'extension (elementRules).
     *
     * @return array<int, mixed>
     */
    public function rules(Field $field): array
    {
        if ($this->isMultiple($field)) {
            $rules = ['array'];

            if (filled($max = $field->option('max_files'))) {
                $rules[] = 'max:'.(int) $max;
            }

            return $rules;
        }

        return [];
    }

    /**
     * Configure le composant FileUpload de Filament pour le formulaire Livewire.
     */
    public function formComponent(Field $field): Component
    {
        return parent::formComponent($field);
    }
}
