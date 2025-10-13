# Aplikacja - Just In Case (MVP)

## Główny problem

Ludzie nie żyją wiecznie i mogą ulec wypadkom, zaginąć lub niespodziewanie umrzeć. W takich sytuacjach bliskie osoby często nie mają dostępu do kluczowych informacji, które mogą być niezbędne:
- Hasła i dostępy do kont
- Dokumenty prawne i finansowe
- Informacje o zobowiązaniach i aktywach
- Ostatnie życzenia i dyspozycje
- Ważne kontakty

Aplikacja "Just In Case" rozwiązuje ten problem, umożliwiając bezpieczne przechowywanie i automatyczne przekazanie wybranych informacji określonym osobom w przypadku śmierci, wypadku lub zaginięcia użytkownika.

## Najmniejszy zestaw funkcjonalności

- **Autentykacja i zarządzanie kontem** - Użytkownik może się zarejestrować, zalogować i utworzyć swoje konto w systemie.

- **Powiernictwa (Custodians)** - Użytkownik może tworzyć i zarządzać wieloma niezależnymi "powiernictwami", z których każde strzeże określonego zestawu informacji przeznaczonych dla wybranej osoby / osób.

- **Interwały czasowe** - Dla każdego powiernictwa można zdefiniować interwał czasowy, po którego upływie system automatycznie wyśle przygotowaną wiadomość do wyznaczonego odbiorcy.

- **Reset timerów** - Użytkownik może regularnie resetować liczniki czasu dla aktywnych powiernictw, potwierdzając że wszystko jest w porządku i informacje nie powinny zostać jeszcze ujawnione.

- **Załączniki** - Możliwość wgrywania i załączania dokumentów do każdego powiernictwa (podobnie jak w przypadku komponowania wiadomości email).

- **Szyfrowanie** - Wszystkie przechowywane dokumenty są szyfrowane przy użyciu zewnętrznego klucza, zapewniając maksymalne bezpieczeństwo danych.

## Co NIE wchodzi w zakres MVP

- **Współdzielenie powiernictw** - Możliwość udostępniania zarządzania powiernictwem innym użytkownikom nie jest częścią MVP.

- **Delegowanie resetowania timerów** - Funkcja pozwalająca innym osobom na resetowanie timerów w imieniu użytkownika nie jest uwzględniona.

- **Aplikacja mobilna** - MVP obejmuje wyłącznie wersję webową aplikacji, natywne aplikacje mobilne (iOS/Android) nie są planowane.

- **Zaawansowana autentykacja resetowania** - Różne metody weryfikacji tożsamości przy resetowaniu timerów (np. biometryczna, dwuskładnikowa, profil zaufany) wykraczają poza zakres MVP.

## Kryteria sukcesu

- **Szybki onboarding** - Użytkownik powinien być w stanie przejść pełną ścieżkę od rejestracji do założenia i skonfigurowania pierwszego powiernika w czasie nie dłuższym niż 5 minut.

- **Wzrost bazy użytkowników** - Aplikacja powinna pozyskiwać minimum 30 nowych użytkowników miesięcznie w pierwszym roku działania.

- **Aktywność użytkowników** - Ponad 90% wszystkich zarejestrowanych użytkowników powinno mieć co najmniej jednego aktywnego powiernika.
