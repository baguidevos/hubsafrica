<?php

namespace App\Support;

use Illuminate\Support\Str;

final class FormDefinitionBuilder
{
    /**
     * @var array<int, string>
     */
    public const ALLOWED_FIELD_TYPES = [
        'text',
        'textarea',
        'email',
        'phone',
        'number',
        'select',
        'radio',
        'checkboxes',
        'checkbox',
        'date',
        'file',
        'rating',
    ];

    /**
     * @var array<int, string>
     */
    private const CHOICE_FIELD_TYPES = ['select', 'radio', 'checkboxes'];

    /**
     * Convertit une liste simple de questions en structure Packstub FormBuilder (sections + champs).
     *
     * @param  array<int, array<string, mixed>>  $rawFields
     * @return array<int, array<string, mixed>>
     */
    public static function buildPackstubSections(array $rawFields, string $sectionLabel = 'Informations du formulaire'): array
    {
        $usedKeys = [];
        $normalizedFields = [];

        foreach ($rawFields as $index => $rawField) {
            $label = trim((string) ($rawField['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $type = strtolower(trim((string) ($rawField['type'] ?? 'text')));
            if (! in_array($type, self::ALLOWED_FIELD_TYPES, true)) {
                $type = 'text';
            }

            $fieldKey = self::makeUniqueKey($label, $index, $usedKeys);
            $usedKeys[$fieldKey] = true;

            $fieldData = [
                'key' => $fieldKey,
                'label' => $label,
                'required' => (bool) ($rawField['required'] ?? false),
                'width' => 'full',
            ];

            $placeholder = trim((string) ($rawField['placeholder'] ?? ''));
            if ($placeholder !== '') {
                $fieldData['placeholder'] = $placeholder;
            }

            if (in_array($type, self::CHOICE_FIELD_TYPES, true)) {
                [$choices, $options] = self::normalizeChoices((array) ($rawField['options'] ?? []));
                $fieldData['choices'] = $choices;
                $fieldData['options'] = $options;
            }

            $normalizedFields[] = [
                'type' => $type,
                'data' => $fieldData,
            ];
        }

        $cleanSectionLabel = trim($sectionLabel) !== '' ? trim($sectionLabel) : 'Informations du formulaire';

        return [
            [
                'type' => 'section',
                'data' => [
                    'key' => Str::slug($cleanSectionLabel, '_') ?: 'section_principale',
                    'label' => $cleanSectionLabel,
                    'fields' => $normalizedFields,
                ],
            ],
        ];
    }

    /**
     * @param  array<string, bool>  $usedKeys
     */
    private static function makeUniqueKey(string $label, int $index, array $usedKeys): string
    {
        $baseKey = Str::slug($label, '_');
        if ($baseKey === '') {
            $baseKey = 'champ_'.($index + 1);
        }

        if (! isset($usedKeys[$baseKey])) {
            return $baseKey;
        }

        $suffix = 2;
        while (isset($usedKeys["{$baseKey}_{$suffix}"])) {
            $suffix++;
        }

        return "{$baseKey}_{$suffix}";
    }

    /**
     * @param  array<int, mixed>  $rawOptions
     * @return array{0: array<string, string>, 1: array<int, array{value: string, label: string}>}
     */
    private static function normalizeChoices(array $rawOptions): array
    {
        $choices = [];
        $options = [];

        foreach ($rawOptions as $rawOption) {
            $optionLabel = trim((string) $rawOption);
            if ($optionLabel === '') {
                continue;
            }

            $optionValue = Str::slug($optionLabel, '_') ?: $optionLabel;
            $choices[$optionValue] = $optionLabel;
            $options[] = [
                'value' => $optionValue,
                'label' => $optionLabel,
            ];
        }

        return [$choices, $options];
    }
}
