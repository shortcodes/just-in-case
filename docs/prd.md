# Dokument wymagań produktu (PRD) - Just In Case

## 1. Przegląd produktu

### 1.1 Nazwa produktu
Just In Case

### 1.2 Opis produktu
Just In Case to webowa aplikacja typu information delivery service, która umożliwia użytkownikom bezpieczne przechowywanie kluczowych informacji (hasła, dokumenty, instrukcje, ostatnie życzenia) i automatyczne przekazanie ich wybranym osobom w przypadku śmierci, wypadku lub zaginięcia użytkownika.

### 1.3 Model biznesowy
Aplikacja działa w modelu freemium:

Plan Free:
- Maksymalnie 3 powiernictwa na użytkownika
- Maksymalnie 10MB załączników na powiernictwo (suma wszystkich plików)
- Maksymalnie 2 odbiorców na powiernictwo

### 1.4 Technologie
- Backend: Laravel 12, PHP 8.2+
- Frontend: Vite, Tailwind CSS 4.0
- Baza danych: SQLite (dev), MySQL 8.0 (production)
- Storage: AWS S3 (załączniki)
- Email: Mailgun lub podobne (SendGrid, AWS SES, Postmark)
- Deployment: Laravel Sail (Docker)

### 1.5 Cel produktu
Umożliwienie użytkownikom prostego i szybkiego sposobu na przygotowanie "cyfrowej kapsuły czasu" bez potrzeby angażowania prawników czy skomplikowanych narzędzi, z naciskiem na prostotę onboardingu (poniżej 5 minut) i wysoką aktywność użytkowników (ponad 90% z aktywnym powiernictwem).

### 1.6 Ważne zastrzeżenia
Just In Case NIE jest testamentem ani usługą prawną. Jest to wyłącznie automatyczny system dostarczania wiadomości oparty na mechanizmie timerów.

## 2. Problem użytkownika

### 2.1 Główny problem
Ludzie nie żyją wiecznie i mogą ulec wypadkom, zaginąć lub niespodziewanie umrzeć. W takich sytuacjach bliskie osoby często nie mają dostępu do kluczowych informacji, które mogą być niezbędne:

- Hasła i dostępy do kont (email, bankowość, social media, kryptowaluty)
- Dokumenty prawne i finansowe (testament, polisy, umowy)
- Informacje o zobowiązaniach i aktywach (kredyty, inwestycje, nieruchomości)
- Ostatnie życzenia i dyspozycje (pogrzeb, darowizny)
- Ważne kontakty (prawnik, księgowy, lekarze)

### 2.2 Grupy docelowe
- Freelancerzy i właściciele małych firm posiadający aktywa cyfrowe (kryptowaluty, domeny, IP)
- Osoby starsze z testamentami i skomplikowanymi sprawami finansowymi
- Młode osoby z wieloma kontami online i cyfrowymi hasłami
- Rodzice chcący zabezpieczyć dostęp do informacji dla dzieci
- Małżonkowie chcący zabezpieczyć swoje dane na wypadek śmierci lub zaginięcia.

### 2.3 Obecne rozwiązania i ich problemy
- Zapisywanie haseł w notesach - ryzyko nieuprawnionego dostępu, brak automatyzacji
- Menedżery haseł z funkcją dziedziczenia - skomplikowane, drogie, nie obsługują dokumentów
- Prawnicze usługi przechowywania - kosztowne, czasochłonne, formalne
- Informowanie rodziny ustnie - zawodne, niekompletne, nieaktualne

### 2.4 Rozwiązanie oferowane przez Just In Case
Prosty, zautomatyzowany sposób na przekazanie informacji oparty na regularnym potwierdzaniu "żyję i wszystko w porządku" poprzez resetowanie timerów. Jeśli użytkownik przestanie resetować timer, system automatycznie wysyła przygotowane wiadomości z załącznikami do wybranych osób.

## 3. Wymagania funkcjonalne

### 3.1 Autentykacja i zarządzanie kontem

REQ-001: Rejestracja użytkownika
- System umożliwia rejestrację za pomocą adresu email i hasła
- Walidacja: email musi być unikalny, hasło minimum 8 znaków
- Po rejestracji wysyłany jest link aktywacyjny na podany email

REQ-002: Weryfikacja email
- Link aktywacyjny weryfikuje adres email użytkownika
- Link ważny przez określony czas (np. 24h)
- Możliwość ponownego wysłania linku aktywacyjnego

REQ-003: Logowanie
- Użytkownik może się zalogować przed aktywacją email
- Logowanie za pomocą email + hasło
- Sesja utrzymywana zgodnie ze standardami Laravel

REQ-004: Resetowanie hasła
- Użytkownik może zresetować hasło poprzez link wysłany na adres email
- Formularz resetowania dostępny przez link "Zapomniałeś hasła?" na stronie logowania
- System wysyła link resetujący hasło na podany adres email
- Link ważny przez określony czas (np. 1h)
- Po kliknięciu linku użytkownik może ustawić nowe hasło
- Walidacja nowego hasła: minimum 8 znaków

REQ-005: Ograniczenia przed aktywacją
- Użytkownik przed aktywacją email może tworzyć tylko powiernictwa w statusie "draft"
- Timery nie startują dla powiernictw w statusie draft
- Po aktywacji użytkownik może ręcznie aktywować drafty

### 3.2 Zarządzanie powiernictwami

REQ-006: Tworzenie powiernictwa
- Użytkownik może utworzyć nowe powiernictwo z następującymi polami:
  - Nazwa powiernictwa (wymagane, maks. 255 znaków)
  - Treść wiadomości (textarea/editor, bez limitu znaków)
  - Interwał czasowy w dniach (np. 30, 90, 180 dni)
  - Lista odbiorców (1-2 emaile w planie free)
  - Załączniki (suma do 10MB w planie free)
- Status domyślny: draft (jeśli email nieaktywny) lub active (jeśli email aktywny)

REQ-007: Walidacja limitów free
- System blokuje utworzenie 4+ powiernictwa w planie free
- System blokuje dodanie 3+ odbiorców w planie free
- System blokuje upload załączników przekraczających 10MB w sumie
- Użytkownik otrzymuje czytelny komunikat o osiągnięciu limitu

REQ-008: Edycja powiernictwa
- Użytkownik może edytować wszystkie pola powiernictwa
- Edycja NIE resetuje automatycznie timera
- Po zapisaniu zmian system wyświetla modal z pytaniem: "Czy chcesz zresetować timer?"
- Użytkownik wybiera: "Tak, resetuj" lub "Nie, zostaw obecny timer"

REQ-009: Zmiana interwału czasowego
- Przy zmianie interwału system przelicza next_trigger_at bez resetu
- Formuła: next_trigger_at = last_reset_at + nowy_interwał_dni
- Jeśli nowy next_trigger_at < teraz, powiernictwo od razu kwalifikuje się do wysłania
- Po zmianie interwału wyświetlany jest modal z opcją resetu timera

REQ-010: Usuwanie powiernictwa (hard delete)
- Użytkownik klika "Usuń powiernictwo"
- System wyświetla modal z:
  - Ostrzeżeniem: "Ta akcja jest nieodwracalna"
  - Checkbox: "Rozumiem, że wszystkie dane zostaną trwale usunięte"
  - Pole tekstowe: "Wpisz nazwę powiernictwa aby potwierdzić"
- Po potwierdzeniu system usuwa:
  - Rekord powiernictwa z bazy
  - Wszystkie powiązane załączniki z S3
  - Wszystkie logi resetów dla tego powiernictwa
  - Wszystkie powiązane rekordy odbiorców

REQ-011: Statusy powiernictw
- draft: Timer nie działa, powiernictwo nieaktywne
- active: Timer działa, powiernictwo aktywne
- completed: Wiadomość wysłana pomyślnie
- delivery_failed: Wiadomość nie została dostarczona (bounce)

REQ-012: Aktywacja powiernictwa z draft
- Użytkownik może ręcznie aktywować powiernictwo draft
- Po aktywacji status zmienia się na active
- Timer startuje z last_reset_at = teraz i next_trigger_at = teraz + interwał

### 3.3 Zarządzanie odbiorcami

REQ-013: Dodawanie odbiorców
- Odbiorca nie musi być użytkownikiem systemu
- Wystarczy podać email odbiorcy
- System waliduje format email
- Użytkownik może dodać 1-2 odbiorców w planie free
- Każde powiernictwo ma własną niezależną listę odbiorców

REQ-014: Edycja i usuwanie odbiorców
- Użytkownik może zmienić email odbiorcy
- Użytkownik może usunąć odbiorcy z listy
- Usunięcie odbiorcy nie resetuje timera

REQ-015: Brak weryfikacji tożsamości odbiorcy
- System nie weryfikuje czy email odbiorcy istnieje przed wysłaniem
- System nie wymaga od odbiorcy rejestracji ani logowania
- Odbiorca nie wie o istnieniu powiernictwa do momentu otrzymania emaila

### 3.4 Zarządzanie załącznikami

REQ-016: Upload załączników
- Użytkownik może wgrywać pliki dowolnego typu
- Suma rozmiarów wszystkich załączników w powiernictwie: max 10MB (plan free)
- System wyświetla progress bar podczas uploadu
- Po uploadzie użytkownik widzi listę załączników z nazwą i rozmiarem

REQ-017: Przechowywanie załączników
- Załączniki przechowywane na AWS S3 (lub podobnym)
- Bucket bez publicznego dostępu
- Szyfrowanie at-rest (server-side encryption)
- Aplikacja działa jako proxy do pobierania

REQ-018: Usuwanie załączników
- Użytkownik może usunąć załącznik przed wysłaniem powiernictwa
- Po usunięciu załącznika plik usuwany z S3
- Usunięcie załącznika nie resetuje timera

REQ-019: Generowanie linków do załączników
- Po wysłaniu wiadomości system generuje unikalny tokenizowany URL (UUID) na powiernictwo
- Link ważny bezterminowo
- Aplikacja loguje każde pobranie: timestamp, IP, user agent

REQ-020: Dostęp do załączników przez odbiorców
- Odbiorca klika link w emailu
- Aplikacja weryfikuje token UUID
- Jeśli OK: aplikacja pobiera plik z S3 i serwuje odbiorcy

### 3.5 System timerów

REQ-021: Mechanizm timera
- Każde powiernictwo ma:
  - interval_days: interwał w dniach, godzinach, minutach (np. 30, 90, 180)
  - last_reset_at: timestamp ostatniego resetu
  - next_trigger_at: timestamp kiedy timer wygaśnie (last_reset_at + interval_days)
- Timer odlicza w tle, użytkownik nie musi być zalogowany

REQ-022: Sprawdzanie timerów
- Cron job uruchamiany co minutę
- Job sprawdza wszystkie powiernictwa gdzie next_trigger_at <= now() AND status = 'active'
- Dla każdego wygasłego powiernictwa system inicjuje wysyłkę emaila

REQ-023: Resetowanie timera - przycisk
- Użytkownik widzi przycisk "Reset Timer" (lub "I'm OK") w:
  - Widoku pojedynczego powiernictwa
  - Liście powiernictw na dashboardzie (szybki reset)
- Kliknięcie przycisku:
  - Ustawia last_reset_at = teraz
  - Przelicza next_trigger_at = teraz + interval_days
  - Loguje reset w audit log (tabela resets)

REQ-024: Audit log resetów
- Każdy reset zapisywany w tabeli resets
- Użytkownik może przeglądać historię resetów (opcjonalnie w MVP)

REQ-025: Brak resetu dla powiernictw z zerowym czasem
- Jeśli next_trigger_at <= now() (timer wygasł), przycisk reset jest disabled
- System nie pozwala resetować powiernictw w statusie completed lub delivery_failed
- Komunikat: "To powiernictwo zostało już wysłane. Nie można go zresetować."

REQ-026: Definicja aktywnego powiernictwa
- Aktywne powiernictwo: status = 'active' AND next_trigger_at > now()
- Nieaktywne: status != 'active' OR next_trigger_at <= now()

### 3.6 Wysyłka email

REQ-027: Automatyczna wysyłka przy wygaśnięciu timera
- Gdy cron job wykryje wygasłe powiernictwo, system:
  1. Generuje unikalny link UUID do załączników
  2. Komponuje email z treścią użytkownika + link
  3. Wysyła email do wszystkich odbiorców powiernictwa (osobno do każdego)
  4. Loguje próbę wysyłki

REQ-028: Treść emaila
- Subject: określony przez system (np. "Wiadomość od [user_name] - Just In Case")
- Body:
  - Wprowadzenie: "Otrzymujesz tę wiadomość, ponieważ [user_name] przestał resetować timer w aplikacji Just In Case."
  - Treść zdefiniowana przez użytkownika (bez placeholderów w MVP)
  - Link do załączników (jeśli istnieją)
  - Footer: disclaimer prawny, link do Just In Case

REQ-029: Dostawca email
- Integracja z Mailgun lub podobnym (SendGrid, AWS SES, Postmark)
- Konfiguracja webhooków do śledzenia statusu dostarczenia
- System zapisuje: message_id, status, timestamp

REQ-030: Retry logic
- W przypadku przejściowych błędów (timeout, 5xx) system ponawia wysyłkę
- Maksymalnie 3 próby z exponential backoff (1min, 5min, 15min)
- Po 3 nieudanych próbach: status = delivery_failed

REQ-031: Obsługa bounce
- Webhook od dostawcy email informuje o bounce (hard bounce, spam, invalid email)
- System zmienia status powiernictwa na delivery_failed
- System wysyła alert do twórcy powiernictwa i administratora

REQ-032: Weryfikacja dostarczenia
- System wykorzystuje webhooks dostawcy email do potwierdzenia dostarczenia
- Po udanym dostarczeniu: status = completed
- System loguje delivered_at timestamp

### 3.7 Dashboard

REQ-033: Widok listy powiernictw
- Użytkownik widzi wszystkie swoje powiernictwa w formie listy/karty
- Dla każdego powiernictwa wyświetlane są:
  - Nazwa powiernictwa
  - Lista odbiorców (emaile)
  - Progress timerów: "X dni pozostało z Y" (gdzie X = dni do next_trigger_at, Y = interval_days)
  - Kolorowy status:
    - Zielony: > 30 dni pozostało
    - Żółty: 7-30 dni pozostało
    - Czerwony: < 7 dni pozostało
  - Przycisk "Reset Timer"
  - Przyciski akcji: Edit, Delete

REQ-034: Konfiguracja progów kolorów
- Progi kolorów (30 dni, 7 dni) konfigurowalne w pliku konfiguracyjnym Laravel (config/custodianship.php)
- Progi NIE są edytowalne przez użytkownika w MVP
- Administrator może zmienić progi globalnie dla wszystkich użytkowników

REQ-035: Sortowanie i filtrowanie
- Domyślne sortowanie: najbliższe wygaśnięcia na górze
- Opcjonalne filtry: status (draft, active, completed, failed)

REQ-036: Szybki reset z dashboardu
- Użytkownik może zresetować timer bezpośrednio z listy, bez wchodzenia w szczegóły

### 3.8 Powiadomienia

REQ-037: Przypomnienie przed wygaśnięciem
- System wysyła jedno przypomnienie email do użytkownika przed wygaśnięciem timera
- Próg: konfigurowalny w config (np. 7 dni przed wygaśnięciem)
- Email zawiera: nazwę powiernictwa, liczbę dni do wygaśnięcia, link do resetu
- Post-MVP: opcja dla użytkownika do włączenia/wyłączenia powiadomień i zmiany progu

REQ-038: Alert przy delivery failure
- Gdy email nie zostanie dostarczony (bounce):
  - System wysyła email do twórcy powiernictwa z informacją o błędzie
  - System wysyła email do administratora systemu
- Email zawiera: nazwę powiernictwa, email odbiorcy, kod błędu

REQ-039: Brak powiadomienia o otwarciu linku
- W MVP system NIE powiadamia twórcy powiernictwa gdy odbiorca otwiera link do załączników
- Funkcja może być dodana post-MVP

### 3.9 Limity freemium

REQ-040: Sprawdzanie limitów przed akcjami
- Przed utworzeniem powiernictwa: sprawdź czy użytkownik nie ma już 3 powiernictw
- Przed dodaniem odbiorcy: sprawdź czy powiernictwo nie ma już 2 odbiorców
- Przed uplodem załącznika: sprawdź czy suma załączników nie przekroczy 10MB
- W przypadku przekroczenia: wyświetl komunikat o limicie + CTA do upgradu (post-MVP)

REQ-041: Liczenie limitów
- Limity odnoszą się do aktywnych powiernictw (draft, active, completed, delivery_failed)
- Usunięte powiernictwa nie wliczają się do limitu
- Powiernictwa zakończone (completed i delivery_failed) także się nie zaliczają do limitu.

## 4. Granice produktu

### 4.1 Funkcje NIE wchodzące w zakres MVP

OUT-001: Współdzielenie powiernictw
- Brak możliwości udostępniania zarządzania powiernictwem innym użytkownikom
- Tylko twórca powiernictwa może je edytować i resetować timer

OUT-002: Delegowanie resetowania timerów
- Brak funkcji pozwalającej innym osobom (np. trusted contacts) na resetowanie timerów w imieniu użytkownika
- To może być istotna funkcja dla scenariuszy awaryjnych (użytkownik umiera 2 dni po resecie 90-dniowego timera)

OUT-003: Aplikacja mobilna
- MVP obejmuje wyłącznie wersję webową (responsive web design)
- Natywne aplikacje iOS/Android nie są planowane w MVP

OUT-004: Zaawansowana autentykacja resetowania
- Brak biometrycznej autoryzacji
- Brak dwuskładnikowej autoryzacji (2FA) przy resetowaniu timerów
- Brak "profilu zaufanego" (np. zaufane urządzenia)
- Post-MVP: 2FA dla logowania może być dodane

OUT-005: Placeholdery w treści wiadomości
- Brak dynamicznych zmiennych typu {{recipient_name}}, {{date}}, {{custodianship_name}}
- Użytkownik pisze treść wiadomości ręcznie

OUT-006: Backup recipients
- Brak alternatywnych odbiorców w przypadku nieudanego dostarczenia do głównego odbiorcy
- Post-MVP: może być silnym argumentem za konwersją free → paid

OUT-007: Powiadomienia SMS
- MVP obsługuje tylko email
- SMS może być dodany w paid planie post-MVP

OUT-008: Video tutorial w onboardingu
- Brak wideo instruktażowego
- Onboarding oparty na tekstach, tooltipach i prostym UI

OUT-009: Powiadomienie o otwarciu linku
- Twórca powiernictwa nie otrzymuje powiadomienia gdy odbiorca otwiera link do załączników
- Post-MVP: może być dodane jako opcja (tracking pixels)

OUT-010: Personalizowane progi powiadomień
- Użytkownik nie może w MVP zmieniać progów powiadomień (np. "przypomnij mi 14 dni przed")
- Progi globalne, konfigurowalne tylko przez administratora

OUT-011: Ścieżka zamykania konta przez rodzinę zmarłego
- Brak dedykowanego procesu dla rodziny zmarłego do zamknięcia konta
- RODO: dane nieaktywnych kont będą automatycznie usuwane po określonym czasie

OUT-012: Testament prawny / usługa prawna
- Just In Case NIE jest testamentem ani usługą prawną
- Wymagany wyraźny disclaimer w aplikacji i ToS

### 4.2 Kwestie do rozstrzygnięcia przed uruchomieniem

UNRESOLVED-001: Compliance prawny
- Czy istnieją przepisy w Polsce/UE regulujące "information delivery services"?
- Action: Konsultacja prawna przed publicznym uruchomieniem
- Disclaimer: potrzebny wyraźny komunikat że aplikacja nie jest testamentem

UNRESOLVED-002: Model monetyzacji paid plan
- Free tier zdefiniowany, ale paid plan wymaga doprecyzowania
- Pytania: jakie limity? jakie dodatkowe features? jaka cena ($5-10/m)?
- Action: Research konkurencji, analiza willingness to pay

UNRESOLVED-003: Emergency scenarios
- Problem: Co jeśli użytkownik umrze 2 dni po resecie 90-dniowego timera?
- Obecnie: rodzina musi poczekać 88 dni
- Pytanie: Czy to akceptowalne? Czy potrzebujemy "trusted contact" w przyszłości?

UNRESOLVED-004: Email deliverability partner
- Mailgun wzmiankowany jako opcja, ale nie finalna decyzja
- Opcje: Mailgun, SendGrid, AWS SES, Postmark
- Kryteria: reliability, delivery rate, cost, API quality
- Action: Porównanie dostawców przed implementacją

UNRESOLVED-005: GTM strategy
- Bez GTM strategii trudno osiągnąć KPI "30 nowych użytkowników/miesiąc"
- Pytania: Kto są early adopters? Jakie kanały dystrybucji?
- Action: Opracowanie minimalnego GTM przed/zaraz po uruchomieniu MVP

## 5. Historyjki użytkowników

### 5.1 Autentykacja i konto

US-001: Rejestracja nowego użytkownika
Jako nowy użytkownik
Chcę zarejestrować się w aplikacji za pomocą emaila i hasła
Aby móc utworzyć swoje pierwsze powiernictwo

Kryteria akceptacji:
- Strona rejestracji zawiera pola: email, hasło, powtórz hasło
- Walidacja email: unikalny, poprawny format
- Walidacja hasła: minimum 8 znaków
- Po wysłaniu formularza system tworzy konto
- System wysyła email z linkiem aktywacyjnym
- Użytkownik widzi komunikat: "Sprawdź swoją skrzynkę email, aby aktywować konto"
- Link aktywacyjny ważny przez 24h

US-002: Aktywacja konta emailem
Jako użytkownik z nieaktywowanym kontem
Chcę kliknąć link aktywacyjny w emailu
Aby móc w pełni korzystać z aplikacji

Kryteria akceptacji:
- Kliknięcie linku aktywacyjnego otwiera aplikację
- System weryfikuje token z linku
- Jeśli token ważny: konto zostaje aktywowane, użytkownik widzi potwierdzenie
- Jeśli token nieważny/wygasły: komunikat błędu + opcja ponownego wysłania
- Po aktywacji użytkownik może przekształcić drafty w aktywne powiernictwa

US-003: Ponowne wysłanie linku aktywacyjnego
Jako użytkownik z nieaktywowanym kontem
Chcę ponownie wysłać link aktywacyjny
Aby aktywować konto gdy poprzedni link wygasł

Kryteria akceptacji:
- Strona logowania zawiera link "Nie otrzymałeś emaila aktywacyjnego?"
- Użytkownik podaje email
- System wysyła nowy link aktywacyjny
- Komunikat: "Nowy link aktywacyjny został wysłany"

US-004: Logowanie do aplikacji
Jako zarejestrowany użytkownik
Chcę zalogować się do aplikacji
Aby zarządzać moimi powiernictwami

Kryteria akceptacji:
- Strona logowania zawiera pola: email, hasło
- Przycisk "Zaloguj"
- Link "Zapomniałeś hasła?"
- Poprawne dane: użytkownik przekierowany do dashboardu
- Błędne dane: komunikat "Nieprawidłowy email lub hasło"
- Użytkownik może się zalogować przed aktywacją emaila (z ograniczeniami)

US-005: Reset hasła
Jako użytkownik
Chcę zresetować hasło gdy je zapomnę
Aby odzyskać dostęp do konta

Kryteria akceptacji:
- Link "Zapomniałeś hasła?" prowadzi do formularza reset
- Użytkownik podaje email
- System wysyła link reset hasła
- Link ważny przez 1h
- Kliknięcie linku otwiera formularz nowego hasła
- Po ustawieniu nowego hasła użytkownik może się zalogować

### 5.2 Onboarding

US-006: Pierwszy onboarding po rejestracji
Jako nowy użytkownik po pierwszym zalogowaniu
Chcę zobaczyć prosty przewodnik
Aby szybko zrozumieć jak działa aplikacja

Kryteria akceptacji:
- Po pierwszym zalogowaniu użytkownik widzi welcome screen
- Screen zawiera:
  - Krótkie wyjaśnienie jak działa Just In Case (2-3 zdania)
  - Przycisk "Utwórz pierwsze powiernictwo"
  - Link "Pomiń" (użytkownik może wrócić później)
- Jeśli email nieaktywowany: banner "Aktywuj email aby uruchomić timery"
- Cały proces od rejestracji do pierwszego powiernictwa: < 5 minut

### 5.3 Zarządzanie powiernictwami

US-007: Utworzenie pierwszego powiernictwa
Jako nowy użytkownik
Chcę utworzyć moje pierwsze powiernictwo
Aby przygotować informacje dla bliskiej osoby

Kryteria akceptacji:
- Przycisk "Utwórz powiernictwo" na dashboardzie
- Formularz zawiera pola:
  - Nazwa powiernictwa (wymagane, maks. 255 znaków)
  - Treść wiadomości (textarea, opcjonalne)
  - Interwał czasowy (select: 30, 60, 90, 180, 365 dni)
  - Lista odbiorców (1-2 emaile, przycisk "+ Dodaj odbiorcy")
  - Upload załączników (drag & drop lub browse)
- Przycisk "Zapisz jako draft" i "Zapisz i aktywuj"
- Po zapisaniu: użytkownik przekierowany do dashboardu
- Jeśli email nieaktywowany: tylko "Zapisz jako draft" dostępne

US-008: Edycja istniejącego powiernictwa
Jako użytkownik z aktywnym powiernictwem
Chcę edytować treść lub ustawienia powiernictwa
Aby zaktualizować informacje bez tworzenia nowego

Kryteria akceptacji:
- Przycisk "Edytuj" w widoku powiernictwa lub na dashboardzie
- Formularz z wypełnionymi obecnymi wartościami
- Użytkownik może zmienić dowolne pole
- Po zapisaniu: modal "Czy chcesz zresetować timer?"
  - "Tak, resetuj" - last_reset_at = teraz, next_trigger_at przeliczony
  - "Nie, zostaw obecny timer" - timer bez zmian
- Komunikat potwierdzenia: "Powiernictwo zaktualizowane"

US-009: Zmiana interwału czasowego
Jako użytkownik z aktywnym powiernictwem
Chcę zmienić interwał z 90 na 180 dni
Aby dostosować częstotliwość resetowania do mojej sytuacji

Kryteria akceptacji:
- Edycja powiernictwa, zmiana pola "Interwał czasowy"
- Po zapisaniu: system przelicza next_trigger_at = last_reset_at + nowy_interwał
- Jeśli nowy next_trigger_at < teraz: powiernictwo od razu kwalifikuje się do wysłania (ostrzeżenie)
- Modal "Czy chcesz zresetować timer?" wyświetlany
- Dashboard pokazuje nowy progress bar z nowym interwałem

US-010: Dodanie odbiorcy do powiernictwa
Jako użytkownik
Chcę dodać drugiego odbiorcy do mojego powiernictwa
Aby więcej osób otrzymało informacje

Kryteria akceptacji:
- Przycisk "+ Dodaj odbiorcy" w formularzu powiernictwa
- Pole email odbiorcy
- Walidacja: poprawny format email
- Użytkownik w planie free może dodać maksymalnie 2 odbiorców
- Próba dodania 3 odbiorcy: komunikat "Osiągnąłeś limit odbiorców (2) w planie free"
- Lista odbiorców wyświetlana z możliwością usunięcia

US-011: Usunięcie odbiorcy z powiernictwa
Jako użytkownik
Chcę usunąć odbiorcy z listy
Aby nie wysyłać informacji do tej osoby

Kryteria akceptacji:
- Przycisk "Usuń" przy każdym odbiorcy na liście
- Kliknięcie usuwa odbiorcy z listy (bez dodatkowego potwierdzenia)
- Usunięcie odbiorcy nie resetuje timera
- Jeśli zostanie 0 odbiorców: ostrzeżenie "Dodaj przynajmniej jednego odbiorcę"

US-012: Upload załącznika do powiernictwa
Jako użytkownik
Chcę załączyć dokument PDF z instrukcjami
Aby odbiorca miał dostęp do szczegółowych informacji

Kryteria akceptacji:
- Sekcja "Załączniki" w formularzu powiernictwa
- Drag & drop lub przycisk "Browse"
- Progress bar podczas uploadu
- Po uploadzie: lista załączników z nazwą i rozmiarem
- Suma załączników w planie free: max 10MB
- Próba przekroczenia limitu: komunikat "Osiągnąłeś limit 10MB załączników"
- Plik zapisywany na S3 z szyfrowaniem

US-013: Usunięcie załącznika z powiernictwa
Jako użytkownik
Chcę usunąć załącznik który nie jest już aktualny
Aby nie wysyłać nieaktualnych informacji

Kryteria akceptacji:
- Przycisk "Usuń" przy każdym załączniku na liście
- Kliknięcie usuwa załącznik z listy
- Plik usuwany z S3
- Suma załączników przeliczana
- Użytkownik może dodać nowy załącznik w zwolnione miejsce

US-014: Usunięcie powiernictwa
Jako użytkownik
Chcę trwale usunąć powiernictwo
Aby usunąć nieaktualne informacje

Kryteria akceptacji:
- Przycisk "Usuń powiernictwo" w widoku powiernictwa
- Modal z:
  - Ostrzeżeniem: "Ta akcja jest nieodwracalna. Wszystkie dane zostaną trwale usunięte."
  - Checkbox: "Rozumiem konsekwencje"
  - Pole tekstowe: "Wpisz nazwę powiernictwa aby potwierdzić: [nazwa]"
  - Przyciski: "Anuluj" i "Usuń permanentnie" (czerwony)
- Po potwierdzeniu:
  - Rekord powiernictwa usunięty z bazy
  - Wszystkie załączniki usunięte z S3
  - Logi resetów usunięte
  - Użytkownik przekierowany do dashboardu
  - Komunikat: "Powiernictwo zostało trwale usunięte"

US-015: Aktywacja powiernictwa draft
Jako użytkownik po aktywacji emaila
Chcę aktywować moje powiernictwa draft
Aby uruchomić timery

Kryteria akceptacji:
- Po aktywacji emaila: banner na dashboardzie "Masz X powiernictw w draft. Aktywuj je aby uruchomić timery."
- Przycisk "Aktywuj" przy każdym drafcie
- Kliknięcie zmienia status na active
- Timer startuje: last_reset_at = teraz, next_trigger_at = teraz + interwał
- Draft nie może być aktywowany jeśli email nieaktywowany

### 5.4 System timerów i resetowanie

US-016: Reset timera pojedynczego powiernictwa
Jako użytkownik z aktywnym powiernictwem
Chcę zresetować timer
Aby potwierdzić że wszystko jest w porządku i informacje nie powinny być wysłane

Kryteria akceptacji:
- Przycisk "Reset Timer" (lub "I'm OK") w widoku powiernictwa
- Kliknięcie:
  - Ustawia last_reset_at = teraz
  - Przelicza next_trigger_at = teraz + interwał
  - Zapisuje reset w audit log (timestamp, IP, user_agent)
  - Komunikat: "Timer zresetowany. Następne sprawdzenie: [data]"
- Progress bar na dashboardzie aktualizuje się
- Przycisk disabled dla powiernictw z wygasłym timerem (next_trigger_at <= teraz)

US-017: Szybki reset z dashboardu
Jako użytkownik z wieloma powiernictwami
Chcę zresetować timer bezpośrednio z listy na dashboardzie
Aby nie wchodzić w szczegóły każdego powiernictwa

Kryteria akceptacji:
- Przycisk "Reset" przy każdym powiernictwie na dashboardzie
- Kliknięcie resetuje timer (jak w US-016)
- Komunikat toast: "Timer zresetowany dla [nazwa powiernictwa]"
- Progress bar aktualizuje się bez przeładowania strony

US-018: Reset wszystkich timerów jednocześnie
Jako użytkownik z wieloma powiernictwami
Chcę zresetować wszystkie timery jednym kliknięciem
Aby zaoszczędzić czas

Kryteria akceptacji:
- Przycisk "Reset wszystkich" na dashboardzie (opcjonalne w MVP)
- Modal potwierdzenia: "Czy chcesz zresetować X powiernictw?"
- Po potwierdzeniu: wszystkie aktywne powiernictwa resetowane
- Komunikat: "Zresetowano X powiernictw"
- Audit log zapisuje każdy reset osobno

US-019: Przeglądanie historii resetów
Jako użytkownik
Chcę zobaczyć historię resetów mojego powiernictwa
Aby wiedzieć kiedy ostatnio resetowałem timer

Kryteria akceptacji:
- Sekcja "Historia resetów" w widoku powiernictwa (opcjonalne w MVP)
- Lista resetów: data, godzina, IP, metoda (manual_button, post_edit_modal)
- Sortowanie: najnowsze na górze
- Brak opcji edycji lub usuwania historii

US-020: Próba resetu wygasłego powiernictwa
Jako użytkownik
Chcę zresetować timer który już wygasł
Aby przedłużyć powiernictwo

Kryteria akceptacji:
- Jeśli next_trigger_at <= teraz: przycisk "Reset Timer" disabled
- Tooltip: "Nie można zresetować wygasłego powiernictwa. Wiadomość zostanie wysłana wkrótce."
- Użytkownik może edytować powiernictwo ale nie resetować timera
- Po wysłaniu wiadomości status zmienia się na completed

### 5.5 Dashboard i monitoring

US-021: Przeglądanie listy powiernictw na dashboardzie
Jako użytkownik
Chcę zobaczyć wszystkie moje powiernictwa w jednym miejscu
Aby monitorować ich statusy i timery

Kryteria akceptacji:
- Dashboard zawiera listę wszystkich powiernictw użytkownika
- Dla każdego powiernictwa wyświetlane:
  - Nazwa
  - Lista odbiorców (emaile)
  - Progress bar: "X dni pozostało z Y"
  - Kolorowy status (badge):
    - Zielony: > 30 dni
    - Żółty: 7-30 dni
    - Czerwony: < 7 dni
  - Przyciski: Reset, Edit, Delete
- Domyślne sortowanie: najbliższe wygaśnięcia na górze

US-022: Filtrowanie powiernictw po statusie
Jako użytkownik
Chcę zobaczyć tylko aktywne powiernictwa
Aby skupić się na tych które wymagają uwagi

Kryteria akceptacji:
- Dropdown "Filtruj po statusie" z opcjami: Wszystkie, Draft, Aktywne, Wysłane, Błąd
- Wybór opcji filtruje listę
- Liczba powiernictw każdego typu wyświetlana w dropdown (opcjonalnie)

US-023: Widzenie alertu gdy timer bliski wygaśnięcia
Jako użytkownik
Chcę zobaczyć wyraźny alert gdy timer zbliża się do zera
Aby nie przegapić resetu

Kryteria akceptacji:
- Powiernictwo z < 7 dni do wygaśnięcia: czerwony badge na dashboardzie
- Banner na górze dashboardu: "Masz X powiernictw wymagających resetu"
- Przycisk "Reset wszystkich" w bannerze

US-024: Puste stato dashboardu dla nowego użytkownika
Jako nowy użytkownik bez powiernictw
Chcę zobaczyć helpful empty state
Aby wiedzieć co zrobić dalej

Kryteria akceptacji:
- Dashboard bez powiernictw wyświetla:
  - Ilustrację (opcjonalnie)
  - Nagłówek: "Nie masz jeszcze żadnych powiernictw"
  - Opis: "Utwórz pierwsze powiernictwo aby zabezpieczyć ważne informacje"
  - Duży przycisk "Utwórz pierwsze powiernictwo"

### 5.6 Powiadomienia

US-025: Otrzymanie przypomnienia przed wygaśnięciem timera
Jako użytkownik
Chcę otrzymać email przypominający o zbliżającym się wygaśnięciu
Aby nie przegapić resetu

Kryteria akceptacji:
- System sprawdza codziennie powiernictwa z next_trigger_at w przedziale [teraz + 7 dni, teraz + 6 dni]
- Email wysyłany do użytkownika z:
  - Subject: "Just In Case - Przypomnienie o resecie timera"
  - Treść: "Twoje powiernictwo '[nazwa]' wygaśnie za X dni. Zresetuj timer jeśli wszystko w porządku."
  - Przycisk/link: "Zresetuj teraz" (prowadzi do dashboardu)
- Jedno przypomnienie per powiernictwo (nie wysyła ponownie)

US-026: Otrzymanie alertu o nieudanym dostarczeniu
Jako użytkownik
Chcę otrzymać powiadomienie gdy moja wiadomość nie zostanie dostarczona
Aby móc poprawić dane odbiorcy

Kryteria akceptacji:
- Webhook od dostawcy email informuje o bounce
- System wysyła email do twórcy powiernictwa:
  - Subject: "Just In Case - Błąd dostarczenia wiadomości"
  - Treść: "Wiadomość dla powiernictwa '[nazwa]' nie została dostarczona do [email]. Powód: [kod błędu]"
  - Przycisk: "Edytuj powiernictwo"
- System wysyła email do administratora z tymi samymi informacjami
- Status powiernictwa zmienia się na delivery_failed

US-027: Brak powiadomienia dla odbiorcy przed wysłaniem
Jako odbiorca
Nie chcę otrzymywać żadnych powiadomień przed wysłaniem wiadomości
Aby nie znać o istnieniu powiernictwa

Kryteria akceptacji:
- Odbiorca nie otrzymuje żadnych emaili od Just In Case do momentu wygaśnięcia timera
- Odbiorca nie ma dostępu do systemu
- Odbiorca nie może zweryfikować czy jest na liście odbiorców

### 5.7 Wysyłka wiadomości i dostęp do załączników

US-028: Automatyczna wysyłka wiadomości po wygaśnięciu timera
Jako użytkownik
Chcę aby moja wiadomość została automatycznie wysłana gdy nie zresetuję timera
Aby odbiorca otrzymał informacje w razie mojego wypadku

Kryteria akceptacji:
- Cron job co minutę sprawdza powiernictwa z next_trigger_at <= teraz AND status = active
- Dla każdego wygasłego powiernictwa:
  - System generuje unikalny link UUID do załączników
  - System komponuje email z treścią użytkownika + link
  - System wysyła email do wszystkich odbiorców (osobno do każdego)
  - System loguje próbę wysyłki (timestamp, message_id, recipient)
  - Status zmienia się na completed (jeśli sukces) lub delivery_failed (jeśli błąd)

US-029: Odbiorca otrzymuje wiadomość z linkiem
Jako odbiorca
Chcę otrzymać email z informacjami i linkiem do załączników
Aby mieć dostęp do przygotowanych przeze mnie informacji

Kryteria akceptacji:
- Email do odbiorcy zawiera:
  - Subject: "[user_name] wysłał Ci ważną wiadomość - Just In Case"
  - Header: "Otrzymujesz tę wiadomość, ponieważ [user_name] przestał resetować timer w aplikacji Just In Case."
  - Treść zdefiniowana przez użytkownika
  - Link do załączników: "Pobierz załączniki" (jeśli istnieją)
  - Footer: Disclaimer prawny, informacje o Just In Case
- Email wysyłany z adresu noreply@justincase.com (lub podobnego)

US-030: Odbiorca pobiera załączniki
Jako odbiorca
Chcę pobrać załączniki z linku w emailu
Aby mieć dostęp do dokumentów

Kryteria akceptacji:
- Kliknięcie linku w emailu otwiera stronę Just In Case
- Strona zawiera:
  - Nagłówek: "Załączniki od [user_name]"
  - Lista załączników z nazwami i rozmiarami
  - Przycisk "Pobierz" przy każdym pliku
- Kliknięcie "Pobierz" inicjuje download pliku
- Link chroniony rate limitingiem: max 10 pobrań/h z jednego IP
- Przekroczenie limitu: komunikat "Zbyt wiele prób. Spróbuj za godzinę."
- System loguje każde pobranie: timestamp, IP, user_agent

US-031: Rate limiting dla linków do załączników
Jako administrator systemu
Chcę aby linki do załączników były chronione rate limitingiem
Aby zapobiec brute-force i nadużyciom

Kryteria akceptacji:
- Endpoint pobierania: /custodianships/{uuid}/attachments/{attachment_id}
- Rate limiting: max 10 requestów na godzinę z jednego IP
- Przekroczenie: HTTP 429 Too Many Requests
- Header: Retry-After z czasem oczekiwania
- Log każdego requestu: timestamp, IP, user_agent, success/failure

US-032: Retry logic dla nieudanych wysyłek
Jako administrator systemu
Chcę aby system ponowił próbę wysłania w przypadku przejściowych błędów
Aby maksymalizować deliverability

Kryteria akceptacji:
- Błędy przejściowe (timeout, 5xx): system ponawia wysyłkę
- Maksymalnie 3 próby z exponential backoff: 1min, 5min, 15min
- Każda próba logowana: attempt_number, timestamp, error_message
- Po 3 nieudanych próbach: status = delivery_failed, alert wysłany
- Błędy trwałe (hard bounce, invalid email): brak retry, od razu delivery_failed

### 5.8 Bezpieczeństwo i autoryzacja

US-033: Tylko właściciel może edytować powiernictwo
Jako użytkownik
Chcę aby tylko ja mógł edytować moje powiernictwa
Aby chronić moje prywatne informacje

Kryteria akceptacji:
- System sprawdza autoryzację przed każdym CRUD na powiernictwie
- Policy: tylko user_id == custodianship.user_id może edytować
- Próba edycji cudzego powiernictwa: HTTP 403 Forbidden
- Administrator systemu może przeglądać (tylko odczyt) wszystkie powiernictwa

US-034: Szyfrowanie załączników at-rest
Jako użytkownik
Chcę aby moje załączniki były zaszyfrowane
Aby chronić moje dane przed nieautoryzowanym dostępem

Kryteria akceptacji:
- Załączniki przechowywane na S3 z szyfrowaniem server-side (AES-256)
- Bucket bez publicznego dostępu (all block public access)
- Aplikacja jako jedyny pośrednik do pobierania
- Klucze szyfrowania zarządzane przez AWS (SSE-S3 lub SSE-KMS)

US-035: Bezpieczne linki do załączników
Jako administrator systemu
Chcę aby linki do załączników były bezpieczne i nie do zgadnięcia
Aby zapobiec nieautoryzowanemu dostępowi

Kryteria akceptacji:
- Link zawiera UUID v4 (128-bit, praktycznie nie do zgadnięcia)
- Format: /custodianships/{uuid}/download
- UUID generowany przy utworzeniu powiernictwa lub przy wysłaniu (do ustalenia)
- Brak możliwości wyliczenia UUID (random, nie sekwencyjny)

US-036: Logowanie aktywności użytkownika
Jako administrator systemu
Chcę logować kluczowe aktywności użytkowników
Aby móc audytować system w przypadku problemów

Kryteria akceptacji:
- Audit log dla:
  - Resetów timerów (tabela resets)
  - Wysyłek wiadomości (tabela deliveries)
  - Pobrań załączników (tabela downloads)
- Każdy log zawiera: timestamp, user_id lub IP, akcja, szczegóły
- Logi nie są edytowalne przez użytkowników

### 5.9 Limity freemium

US-037: Blokada tworzenia 4 powiernictwa w planie free
Jako użytkownik w planie free z 3 powiernictwami
Chcę zobaczyć komunikat o limicie gdy próbuję utworzyć 4 powiernictwo
Aby wiedzieć że osiągnąłem limit

Kryteria akceptacji:
- Przycisk "Utwórz powiernictwo" sprawdza liczbę istniejących powiernictw
- Jeśli użytkownik ma już 3 powiernictwa: modal
  - Tytuł: "Osiągnąłeś limit powiernictw"
  - Treść: "W planie free możesz mieć maksymalnie 3 powiernictwa. Usuń istniejące lub przejdź na plan paid."
  - Przycisk: "Przejdź na paid" (opcjonalnie w MVP, może być disabled)
- Użytkownik nie może utworzyć 4 powiernictwa

US-038: Blokada dodania 3 odbiorcy w planie free
Jako użytkownik w planie free
Chcę zobaczyć komunikat o limicie gdy próbuję dodać 3 odbiorcy do powiernictwa
Aby wiedzieć że osiągnąłem limit

Kryteria akceptacji:
- Formularz powiernictwa: przycisk "+ Dodaj odbiorcy" disabled gdy już jest 2 odbiorców
- Tooltip: "Maksymalnie 2 odbiorców w planie free"
- Próba dodania 3 odbiorcy (np. via API): błąd walidacji

US-039: Blokada uploadu załączników powyżej 10MB
Jako użytkownik w planie free
Chcę zobaczyć komunikat o limicie gdy próbuję wgrać załącznik przekraczający limit
Aby wiedzieć że osiągnąłem limit

Kryteria akceptacji:
- Przed uploadem: frontend sprawdza sumę obecnych załączników + nowy plik
- Jeśli suma > 10MB: komunikat "Osiągnąłeś limit 10MB załączników. Usuń istniejące załączniki lub zmniejsz rozmiar pliku."
- Backend również waliduje rozmiar (zabezpieczenie przed ominięciem frontend)
- Użytkownik nie może wgrać pliku

### 5.10 Scenariusze skrajne i błędy

US-040: Obsługa bounce dla nieistniejącego emaila odbiorcy
Jako użytkownik
Chcę wiedzieć gdy email odbiorcy jest nieprawidłowy
Aby móc go poprawić

Kryteria akceptacji:
- Webhook od dostawcy email informuje o hard bounce (invalid email)
- Status powiernictwa: delivery_failed
- Email do użytkownika: "Email [recipient_email] jest nieprawidłowy i nie został dostarczony mimo wielu prób ."
- Użytkownik nie może edytować powiernictwa i poprawić email

US-041: Próba dostępu do nieistniejącego linku UUID
Jako osoba z błędnym linkiem
Chcę zobaczyć komunikat błędu gdy link jest nieprawidłowy
Aby wiedzieć że nie mogę pobrać załączników

Kryteria akceptacji:
- Request do /custodianships/{uuid}/download z nieistniejącym UUID
- HTTP 404 Not Found
- Strona błędu: "Link nie został znaleziony. Sprawdź czy skopiowałeś cały link."
- Opcjonalnie: formularz kontaktowy do support

US-042: Wygaśnięcie powiernictwa podczas edycji
Jako użytkownik
Edytuję powiernictwo które właśnie wygasło (timer dobił do zera w trakcie edycji)
System powinien obsłużyć tę sytuację

Kryteria akceptacji:
- Jeśli timer wygaśnie podczas edycji, zmiany nie zostaną zapisane a system wyśle wiadomości do odbiorców

US-044: Zmiana emaila użytkownika
Jako użytkownik
Chcę zmienić mój email adres
Aby używać nowego emaila

Kryteria akceptacji:
- Strona ustawień konta: formularz zmiany emaila
- Użytkownik podaje nowy email
- System wysyła link weryfikacyjny na nowy email
- Po kliknięciu: email zmieniony
- Wszystkie powiadomienia wysyłane na nowy email
- Logowanie możliwe tylko z nowego emaila

US-045: Brak załączników w powiernictwie
Jako użytkownik
Chcę utworzyć powiernictwo tylko z treścią tekstową, bez załączników
Aby przekazać proste informacje

Kryteria akceptacji:
- Pole załączników opcjonalne
- Jeśli brak załączników: email nie zawiera linku do załączników
- Email zawiera tylko treść tekstową
- System nie generuje UUID linku do załączników

US-046: Wielokrotne wysłanie tego samego powiernictwa
Jako użytkownik
Chcę aby powiernictwo zostało wysłane tylko raz
Aby odbiorca nie otrzymał duplikatów

Kryteria akceptacji:
- Po wysłaniu wiadomości status zmienia się na completed
- Cron job pomija powiernictwa ze statusem completed
- Użytkownik nie może "re-send" powiernictwa w MVP (opcjonalnie post-MVP)
- Jeśli użytkownik chce wysłać ponownie: musi utworzyć nowe powiernictwo lub edytować i ręcznie zmienić status (tylko admin)

## 6. Metryki sukcesu

### 6.1 KPI główne (z MVP)

METRIC-001: Time to First Custodianship
- Definicja: Czas od rejestracji do utworzenia i skonfigurowania pierwszego powiernictwa (status active)
- Target: < 5 minut (średnia)
- Pomiar: timestamp rejestracji vs timestamp pierwszego powiernictwa active
- Cel: 90% nowych użytkowników osiąga to w < 5 min

METRIC-002: Monthly User Growth
- Definicja: Liczba nowych zarejestrowanych użytkowników miesięcznie
- Target: Minimum 30 nowych użytkowników/miesiąc w pierwszym roku
- Pomiar: COUNT(users) WHERE created_at BETWEEN start_of_month AND end_of_month
- Cel: Utrzymanie lub przekroczenie 30/miesiąc przez 12 miesięcy

METRIC-003: Active Custodianship Rate
- Definicja: Procent użytkowników z co najmniej 1 aktywnym powiernictwem
- Target: > 90%
- Pomiar: (COUNT(users with active custodianship) / COUNT(all users)) x 100
- Aktywne powiernictwo: status = active AND next_trigger_at > now()
- Cel: Wysoka aktywność = wysoka wartość produktu dla użytkowników

### 6.2 Metryki dodatkowe

METRIC-004: Reset Pattern
- Definicja: Średni procent interwału pozostały w momencie resetu
- Przykład: Jeśli interwał = 90 dni, a użytkownik resetuje przy 70 dniach pozostałych, to 77.8%
- Pomiar: AVG((next_trigger_at - now()) / interval_days) x 100 dla wszystkich resetów
- Insight: Jeśli średnia > 80%, użytkownicy resetują bardzo wcześnie (może interwały za długie lub zbyt dużo stresu)
- Insight: Jeśli średnia < 20%, użytkownicy resetują w ostatniej chwili (może przypomnienia za późno)

METRIC-005: Delivery Success Rate
- Definicja: Procent wiadomości pomyślnie dostarczonych do odbiorców
- Target: > 95%
- Pomiar: (COUNT(emails delivered) / COUNT(emails sent)) x 100
- Delivered: status confirmed by email provider webhook
- Cel: Wysoka deliverability = niezawodność systemu

METRIC-006: User Retention
- Definicja: Procent użytkowników wracających po 30 i 90 dniach
- Target: TBD (do ustalenia po pierwszych danych)
- Pomiar:
  - 30-day retention: (COUNT(users active after 30 days) / COUNT(users registered 30 days ago)) x 100
  - 90-day retention: analogicznie
- Aktywny = zalogowany lub zresetował timer w ostatnich 30/90 dniach
- Cel: Wysoka retencja = długoterminowa wartość produktu

METRIC-007: Draft to Active Conversion
- Definicja: Procent powiernictw przekształconych z draft do active
- Target: TBD
- Pomiar: (COUNT(custodianships transitioned draft → active) / COUNT(custodianships created as draft)) x 100
- Insight: Niska konwersja może wskazywać na problemy z aktywacją email lub onboardingiem

METRIC-008: Average Custodianships per User
- Definicja: Średnia liczba powiernictw na użytkownika
- Target: > 1.5
- Pomiar: AVG(COUNT(custodianships) per user)
- Insight: Użytkownicy z wieloma powiernictwami mają wyższą wartość i engagement

METRIC-009: Email Open Rate (reminder emails)
- Definicja: Procent użytkowników otwierających email z przypomnieniem o resecie
- Target: > 40%
- Pomiar: Tracking pixels lub webhooks email providera
- Insight: Niska open rate może wskazywać na problemy z treścią lub timingiem przypomnienia

METRIC-010: Support Tickets per User
- Definicja: Liczba zgłoszeń do supportu na użytkownika
- Target: < 0.1 (mniej niż 1 zgłoszenie na 100 użytkowników)
- Pomiar: COUNT(support tickets) / COUNT(users)
- Insight: Wysoka liczba zgłoszeń może wskazywać na problemy z UX lub bugami

### 6.3 Definicje kluczowych terminów

DEF-001: Aktywne powiernictwo
- Status = active AND next_trigger_at > now()
- Timer nie wygasł, powiernictwo działa

DEF-002: Wygasłe powiernictwo
- Status = active AND next_trigger_at <= now()
- Timer dobił do zera, oczekuje na wysyłkę

DEF-003: Aktywny użytkownik
- Użytkownik który w ostatnich 30 dniach:
  - Zalogował się LUB
  - Zresetował przynajmniej jeden timer LUB
  - Utworzył/edytował powiernictwo

DEF-004: Nowy użytkownik
- Użytkownik zarejestrowany w ostatnich 30 dniach

DEF-005: Plan free
- Użytkownik z limitami: 3 powiernictwa, 10MB per powiernictwo, 2 odbiorców per powiernictwo

### 6.4 Monitoring i raportowanie

REPORT-001: Dashboard dla administratora
- KPI główne (METRIC-001, 002, 003) wyświetlane w czasie rzeczywistym
- Wykresy trendów: Monthly User Growth, Active Custodianship Rate
- Alerty: Delivery Success Rate < 95%, Support Tickets spike

REPORT-002: Tygodniowy raport emailowy
- Wysyłany do team/administratora co poniedziałek
- Zawiera: KPI główne, metryki dodatkowe, top 3 insights
- Action items jeśli metryki poniżej targetu

REPORT-003: Monthly business review
- Pełna analiza wszystkich metryk
- Porównanie month-over-month
- Rekomendacje dla product roadmap
