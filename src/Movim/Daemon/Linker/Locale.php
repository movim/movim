<?php

namespace Movim\Daemon\Linker;

use App\User;
use Movim\i18n\Locale as I18nLocale;

class Locale
{
    public ?string $language = null;
    private array $translations = [];
    private ?User $user = null;

    public function __construct(private string $browserLanguage) {}

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function loadTranslations()
    {
        $locale = I18nLocale::start();

        if ($this->user?->language) {
            $this->language = $this->user->language;
        } elseif ($this->browserLanguage) {
            $this->language = $locale->detect($this->browserLanguage);
        }

        $this->translations = $locale->load($this->language);
    }

    public function translate(string $key, ?array $args = null): string
    {
        $locale = I18nLocale::start();
        return $locale->translate($this->language, $this->translations, $key, $args);
    }
}
