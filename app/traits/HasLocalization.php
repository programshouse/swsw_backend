<?php

namespace App\Traits;

trait HasLocalization
{
    /**
     * Determine the current language from request headers or app locale.
     */
    private function currentLang(): string
    {
        $lang = request()->header('lang');

        if ($lang) {
            $lang = strtolower(substr($lang, 0, 2));
            return in_array($lang, ['ar', 'en']) ? $lang : 'en';
        }

        $acceptLanguage = request()->header('Accept-Language');

        if ($acceptLanguage && str_contains(strtolower($acceptLanguage), 'ar')) {
            return 'ar';
        }

        return 'en';
    }

    /**
     * Parse Accept-Language header and return 'ar' or 'en' if present.
     */
    private function extractLangFromAcceptLanguage(?string $header): ?string
    {
        if (!$header) return null;

        // parse like "en-GB,en;q=0.9,ar-EG;q=0.8,ar;q=0.7,en-US;q=0.6"
        preg_match_all('/([a-z]{2})(?:-[A-Z]{2})?/', $header, $matches);

        foreach ($matches[1] as $code) {
            if (in_array($code, ['ar', 'en'])) return $code;
        }

        return null;
    }

    /**
     * Get localized 'name' attribute.
     */
    public function getNameAttribute()
    {
        $lang = $this->currentLang();

        return $this->{'name_' . $lang}
            ?? $this->name_en
            ?? $this->name_ar
            ?? null;
    }

    /**
     * Get localized 'title' attribute.
     */
    public function getTitleAttribute()
    {
        $lang = $this->currentLang();

        return $this->{'title_' . $lang}
            ?? $this->title_en
            ?? $this->title_ar
            ?? null;
    }

    /**
     * Get localized full name.
     */
    public function getFullNameLocalizedAttribute()
    {
        $lang = $this->currentLang();

        $englishName = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        $arabicName = trim((string) ($this->full_name_ar ?? ''));

        if ($lang === 'ar') {
            return $arabicName !== '' ? $arabicName : ($englishName !== '' ? $englishName : null);
        }

        return $englishName !== '' ? $englishName : ($arabicName !== '' ? $arabicName : null);
    }
}