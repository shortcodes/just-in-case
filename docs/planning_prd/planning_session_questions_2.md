1. Jak wygląda proces weryfikacji emaila po rejestracji - klasyczny link aktywacyjny? Co się dzieje z powiernictwami utworzonymi przed weryfikacją - są zapisane jako draft czy nie są w ogóle tworzone?

Rekomendacja: Klasyczny flow: rejestracja → email z linkiem aktywacyjnym → kliknięcie aktywuje konto i wszystkie utworzone powiernictwa. Powiernictwa w statusie "pending_activation" mogą być edytowane, ale timer nie startuje do czasu aktywacji.

Odpowiedź: Klasyka rejestracja → email z linkiem aktywacyjnym. z tym, że mozna się zalogować i tworzyć powiernictwa. Email aktywacyjny wysyłany jest niezależnie. W trybie nieaktywnego konta mozna tworzyć tylko powiernictwa w trybie "draft". A po aktywacji mozna je ręcznie aktywować.

2. Jak dokładnie działa "link do załączników" w wysłanym emailu - czy to jednorazowy tokenizowany URL, czy może wymaga logowania? Jak długo link jest ważny?

Rekomendacja: Unikalny, tokenizowany URL (UUID) ważny bezterminowo (bo odbiorca może odkryć email miesiące później). Brak wymogu logowania, ale token jest przypisany do konkretnego powiernictwa. Rozważ rate limiting aby zapobiec brute-force.

Odpowiedź: Tak to będzie  Unikalny, tokenizowany URL (UUID) ważny bezterminowo. Prawdopodobnie na AWS S3 bez publicznego dostępu gdzie pośrednikiem w pobieraniu będzie aplikacjia. RateLimiting to dobry pomysł

3. Czy w planie free użytkownik ma limit liczby powiernictw, czy tylko limit rozmiaru załączników (10MB per powiernictwo)? Jaki będzie model monetyzacji paid plans?

Rekomendacja: Free: unlimited powiernictwa, 10MB per powiernictwo, max 2 odbiorców per powiernictwo. Paid (~$5-10/m): 100MB per powiernictwo, unlimited odbiorców, backup recipients, SMS notifications, priority support.

Odpowiedź: Tak - do 3 powierrnictw

4. Jak wygląda dashboard z timerem - czy pokazuje pozostały czas w dniach/godzinach, progress bar, czy coś innego? Czy są powiadomienia przed wygaśnięciem (np. "zostało 7 dni")?

Rekomendacja: Dashboard pokazuje listę powiernictw z: nazwą, odbiorcą(ami), progressem (np. "12 dni pozostało z 90"), kolorowym statusem (green >30 dni, yellow 7-30, red <7). Email alert do użytkownika na 7 dni przed wygaśnięciem.

Odpowiedź: Tak Dashboard pokazuje listę powiernictw z: nazwą, odbiorcą(ami), progressem (np. "12 dni pozostało z 90"), kolorowym statusem (green >30 dni, yellow 7-30, red <7) ale kolory będą do zdefiniowania w MVC w configu. Przypomnienia będą konfigurowane przez użytkownika po MVC. W MVC będzie to konfigurowalne w config i będzie tylko jeden próg. Tylko jedno przypomnienie.

5. Co się dzieje z timerem gdy użytkownik edytuje powiernictwo (zmienia treść, dodaje załącznik) - resetuje się automatycznie, czy pozostaje bez zmian?

Rekomendacja: Edycja powiernictwa NIE resetuje timera automatycznie. Użytkownik musi świadomie kliknąć "Reset Timer" aby potwierdzić że żyje. To zapobiega przypadkowemu przedłużaniu przez edycję.

Odpowiedź: Przychylam się do rekomendacji. Ewentualnie może po wyedytowaniu dostac modal - czy chce zresetować.

6. Jak działa scheduled job sprawdzający timery - czy to codzienny cron o konkretnej godzinie, czy może sprawdza co godzinę? Co jeśli w momencie wygaśnięcia serwer jest down?

Rekomendacja: Laravel scheduled job co godzinę sprawdza powiernictwa z next_trigger_at <= now(). W razie downtime, po restarcie job wykryje przeterminowane i wyśle emaile (z logiem opóźnienia). Rozważ zewnętrzny monitoring (CronJob monitoring).

Odpowiedź: Cron chodzi co minutę jest bardzo precyzyjny. Dlatego jeśli jakaś przykładowa sent_at jest wcześniejsza niż aktualna to serwer wysyła. Jesli serwer jest down to po wstaniu automatycznie wysyła wszystkie powiernictwa z next_trigger_at <= now().

7. Czy treść emaila do odbiorcy może zawierać zmienne/placeholdery (np. {{recipient_name}}, {{sender_name}}) czy jest to plain text/HTML określony przez użytkownika?

Rekomendacja: Umożliw proste placeholdery: {{recipient_name}}, {{sender_name}}, {{custodianship_name}}, {{current_date}}. To zwiększa personalizację. W MVP edytor może być prosty textarea, w v2 WYSIWYG.

Odpowiedź: W MVP z tego rezygnuję

8. Jak obsługiwane jest usuwanie powiernictwa - czy jest soft delete (archiwizacja) czy hard delete? Co z załącznikami - są usuwane z storage czy pozostają?

Rekomendacja: Soft delete - powiernictwo dostaje status "archived" i nie jest widoczne na dashboardzie, ale można przywrócić przez admin panel (dla safety). Hard delete tylko przez admina. Załączniki pozostają przez 30 dni, potem są usuwane (cleanup job).

Odpowiedź: Hard delete - wszystko musi zostać usunięte wraz z załącznikami - permamentnie!

9. Jaki jest flow gdy użytkownik chce zmienić interwał czasowy - np. z 90 na 180 dni. Czy timer się resetuje, czy dostosowuje proporcjonalnie?

Rekomendacja: Zmiana interwału NIE resetuje timera. System przeliczy next_trigger_at bazując na last_reset_at + nowy interwał. Przykład: reset był 30 dni temu, zmiana z 90 na 180 → pozostało 150 dni.

Odpowiedź. Tak samo jak w przypadku modyfikacji - modal z pytaniem.

10. Czy system loguje historię resetów timera (audit log) - kiedy, skąd (IP), jakie urządzenie? To może być przydatne dla security i debugging.

Rekomendacja: TAK - stwórz tabelę custodianship_resets z: timestamp, user_id, custodianship_id, IP, user_agent, reset_method (manual/email_link). To pomaga w debugging i daje użytkownikowi transparency ("Last reset: 2 days ago from Chrome on Mac").

Odpowiedź: Tak to dobry pomysł. 
