<?php

namespace App\Support;

use Illuminate\Support\Str;

final class CountryCatalog
{
    public const UNKNOWN_LABEL = 'Non renseigné';

    /**
     * Liste standardisée des pays (Afrique en priorité + diaspora).
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $countries = [
            'Afrique du Sud',
            'Algérie',
            'Angola',
            'Bénin',
            'Botswana',
            'Burkina Faso',
            'Burundi',
            'Cabo Verde',
            'Cameroun',
            'Comores',
            'Congo',
            'Côte d\'Ivoire',
            'Djibouti',
            'Égypte',
            'Érythrée',
            'Eswatini',
            'Éthiopie',
            'Gabon',
            'Gambie',
            'Ghana',
            'Guinée',
            'Guinée-Bissau',
            'Guinée équatoriale',
            'Kenya',
            'Lesotho',
            'Libéria',
            'Libye',
            'Madagascar',
            'Malawi',
            'Mali',
            'Maroc',
            'Maurice',
            'Mauritanie',
            'Mozambique',
            'Namibie',
            'Niger',
            'Nigéria',
            'Ouganda',
            'République centrafricaine',
            'République démocratique du Congo',
            'Rwanda',
            'Sao Tomé-et-Principe',
            'Sénégal',
            'Seychelles',
            'Sierra Leone',
            'Somalie',
            'Soudan',
            'Soudan du Sud',
            'Tanzanie',
            'Tchad',
            'Togo',
            'Tunisie',
            'Zambie',
            'Zimbabwe',
            'Belgique',
            'Canada',
            'États-Unis',
            'France',
            'Royaume-Uni',
            'Suisse',
            'Autre',
        ];

        return array_combine($countries, $countries);
    }

    /**
     * Normalise une saisie libre ou un code ISO en nom de pays canonique.
     */
    public static function normalize(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $trimmed = trim(preg_replace('/\s+/', ' ', $raw) ?? '');

        if ($trimmed === '') {
            return null;
        }

        $lookupKey = self::canonicalKey($trimmed);

        $aliases = self::aliasMap();

        if (isset($aliases[$lookupKey])) {
            return $aliases[$lookupKey];
        }

        foreach (self::options() as $canonical) {
            if (self::canonicalKey($canonical) === $lookupKey) {
                return $canonical;
            }
        }

        return Str::ucfirst($trimmed);
    }

    /**
     * Extrait un nom de pays normalisé depuis les réponses JSON d'une candidature.
     *
     * @param  array<string, mixed>|null  $answers
     */
    public static function extractCountryFromAnswers(?array $answers): ?string
    {
        if (! is_array($answers) || $answers === []) {
            return null;
        }

        $priorityKeys = [
            'country',
            'pays',
            'pays_residence',
            'pays_de_residence',
            'residence_country',
            'pays_origine',
            'pays_d_origine',
            'pays_implantation',
            'nationalite',
        ];

        foreach ($priorityKeys as $key) {
            if (! array_key_exists($key, $answers)) {
                continue;
            }

            $value = self::extractStringValue($answers[$key]);
            $normalized = self::normalize($value);

            if ($normalized !== null) {
                return $normalized;
            }
        }

        foreach ($answers as $key => $rawValue) {
            $keyLower = Str::lower((string) $key);

            if (! str_contains($keyLower, 'pays') && ! str_contains($keyLower, 'country')) {
                continue;
            }

            $value = self::extractStringValue($rawValue);
            $normalized = self::normalize($value);

            if ($normalized !== null) {
                return $normalized;
            }
        }

        return null;
    }

    private static function extractStringValue(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            $first = reset($value);

            return is_string($first) ? $first : null;
        }

        return null;
    }

    private static function canonicalKey(string $value): string
    {
        $ascii = Str::ascii($value);
        $lower = Str::lower($ascii);

        return preg_replace('/[^a-z0-9]+/', '', $lower) ?? $lower;
    }

    /**
     * @return array<string, string>
     */
    private static function aliasMap(): array
    {
        return [
            'ci' => 'Côte d\'Ivoire',
            'civ' => 'Côte d\'Ivoire',
            'cotedivoire' => 'Côte d\'Ivoire',
            'ivorycoast' => 'Côte d\'Ivoire',
            'rci' => 'Côte d\'Ivoire',
            'sn' => 'Sénégal',
            'sen' => 'Sénégal',
            'senegal' => 'Sénégal',
            'bj' => 'Bénin',
            'ben' => 'Bénin',
            'benin' => 'Bénin',
            'tg' => 'Togo',
            'tgo' => 'Togo',
            'togo' => 'Togo',
            'bf' => 'Burkina Faso',
            'bfa' => 'Burkina Faso',
            'burkina' => 'Burkina Faso',
            'burkinafaso' => 'Burkina Faso',
            'ml' => 'Mali',
            'mli' => 'Mali',
            'mali' => 'Mali',
            'ne' => 'Niger',
            'ner' => 'Niger',
            'niger' => 'Niger',
            'cm' => 'Cameroun',
            'cmr' => 'Cameroun',
            'cameroun' => 'Cameroun',
            'cameroon' => 'Cameroun',
            'gn' => 'Guinée',
            'gin' => 'Guinée',
            'guinee' => 'Guinée',
            'guinea' => 'Guinée',
            'ga' => 'Gabon',
            'gabon' => 'Gabon',
            'cd' => 'République démocratique du Congo',
            'rdc' => 'République démocratique du Congo',
            'rdcongo' => 'République démocratique du Congo',
            'congodrc' => 'République démocratique du Congo',
            'cg' => 'Congo',
            'congobrazzaville' => 'Congo',
            'ma' => 'Maroc',
            'maroc' => 'Maroc',
            'morocco' => 'Maroc',
            'tn' => 'Tunisie',
            'tunisie' => 'Tunisie',
            'tunisia' => 'Tunisie',
            'dz' => 'Algérie',
            'algerie' => 'Algérie',
            'algeria' => 'Algérie',
            'gh' => 'Ghana',
            'ghana' => 'Ghana',
            'ng' => 'Nigéria',
            'nigeria' => 'Nigéria',
            'mg' => 'Madagascar',
            'madagascar' => 'Madagascar',
            'rw' => 'Rwanda',
            'rwanda' => 'Rwanda',
            'td' => 'Tchad',
            'tchad' => 'Tchad',
            'chad' => 'Tchad',
            'mr' => 'Mauritanie',
            'mauritanie' => 'Mauritanie',
            'fr' => 'France',
            'france' => 'France',
            'be' => 'Belgique',
            'belgique' => 'Belgique',
            'ch' => 'Suisse',
            'suisse' => 'Suisse',
            'ca' => 'Canada',
            'canada' => 'Canada',
            'us' => 'États-Unis',
            'usa' => 'États-Unis',
        ];
    }
}
