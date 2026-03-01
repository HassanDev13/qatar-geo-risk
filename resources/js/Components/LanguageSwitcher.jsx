import { useLanguage } from '@/Contexts/LanguageContext';
import { Button } from '@/Components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Languages, Check } from 'lucide-react';

export default function LanguageSwitcher() {
    const { locale, availableLocales, switchLanguage } = useLanguage();

    const languages = {
        en: { name: 'English', flag: '🇬🇧' },
        ar: { name: 'العربية', flag: '🇸🇦' },
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="outline" size="sm" className="gap-2">
                    <Languages className="h-4 w-4" />
                    <span className="hidden sm:inline">
                        {languages[locale]?.flag} {languages[locale]?.name}
                    </span>
                    <span className="sm:hidden">
                        {languages[locale]?.flag}
                    </span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {availableLocales.map((lang) => (
                    <DropdownMenuItem
                        key={lang}
                        onClick={() => switchLanguage(lang)}
                        className="cursor-pointer gap-2"
                    >
                        <span className="text-lg">{languages[lang]?.flag}</span>
                        <span className="flex-1">{languages[lang]?.name}</span>
                        {locale === lang && <Check className="h-4 w-4" />}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
