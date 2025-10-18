Przeanalizowałem dostarczone dokumenty PRD i tech stack. Poniżej przedstawiam listę pytań i zaleceń dotyczących
projektowania schematu bazy danych dla aplikacji Just In Case.

1. Czy tabela custodianships powinna przechowywać treść wiadomości w formie zaszyfrowanej w kolumnie, czy
   wykorzystać szyfrowanie na poziomie aplikacji?

Rekomendacja: Zgodnie z tech_stack.md (sekcja 10.1), treść wiadomości (message_content) powinna być szyfrowana
na poziomie kolumny przy użyciu AES-256 poprzez Laravel encrypt(). Sugeruję kolumnę typu TEXT dla zaszyfrowanej
treści. Zalecam również dodanie kolumny encryption_version (TINYINT) dla potencjalnej rotacji kluczy w
przyszłości.

2. Jak powinna być reprezentowana relacja między custodianships a recipients w kontekście limitu 2 odbiorców na
   powiernictwo w planie free?

Rekomendacja: Relacja powinna być 1:N (custodianship → recipients) realizowana przez tabelę pośrednią
custodianship_recipients z kolumnami: id, custodianship_id, email, created_at, updated_at. Limit 2 odbiorców
powinien być wymuszany na poziomie aplikacji (Form Request validation), nie jako constraint bazy danych,
ponieważ może się zmieniać w różnych planach.

3. W jaki sposób powinien być przechowywany interval_days w tabeli custodianships - czy jako liczba dni, czy z
   obsługą godzin i minut zgodnie z REQ-021?

Rekomendacja: Mimo że PRD wspomina o dniach, godzinach i minutach, sugeruję przechowywanie jako interval_minutes
(INTEGER UNSIGNED) dla elastyczności. To pozwoli na przyszłe wsparcie krótszych interwałów bez zmiany schematu.
Domyślne wartości (30, 90, 180, 365 dni) będą przeliczane na minuty w aplikacji.

4. Czy kolumny last_reset_at i next_trigger_at powinny być indeksowane, biorąc pod uwagę cron job sprawdzający
   wygasłe timery co minutę?

Rekomendacja: Tak, kolumna next_trigger_at MUSI mieć indeks złożony: INDEX idx_active_triggers (status,
next_trigger_at), ponieważ cron job będzie wykonywał zapytanie WHERE status = 'active' AND next_trigger_at <=
NOW() co minutę. Bez indeksu aplikacja nie będzie skalowalna. Dodatkowo last_reset_at powinien mieć osobny
indeks dla raportów historycznych.

5. Jak powinna być zaprojektowana tabela attachments w kontekście przechowywania plików na S3 i linków UUID do
   pobierania?

Rekomendacja: Tabela attachments powinna zawierać: id, custodianship_id (FK), original_filename (VARCHAR 255),
s3_key (VARCHAR 255, unikalny), s3_bucket (VARCHAR 100), file_size_bytes (BIGINT UNSIGNED), mime_type (VARCHAR
100), uuid (CHAR(36), unikalny - dla tokenizowanych linków), created_at, updated_at. Indeks na uuid jest
krytyczny dla wydajności endpointu pobierania.

6. Czy tabela audit log resets powinna przechowywać dane historyczne bezterminowo, czy z polityką retencji?

Rekomendacja: Zgodnie z tech_stack.md (sekcja 10.3), retencja to 24 miesiące. Sugeruję dodanie kolumny
created_at z indeksem oraz scheduled job do usuwania rekordów starszych niż 24 miesiące. Struktura tabeli: id,
custodianship_id (FK), user_id (FK), reset_method (ENUM: 'manual_button', 'post_edit_modal'), ip_address
(VARCHAR 45 - obsługa IPv6), user_agent (TEXT), created_at (indeksowany).

7. Jak powinna być zaimplementowana tabela deliveries do logowania wysyłek emaili z obsługą retry logic (max 3
   próby)?

Rekomendacja: Tabela deliveries powinna mieć: id, custodianship_id (FK), recipient_email (VARCHAR 255),
message_id (VARCHAR 255 - z providera email), status (ENUM: 'pending', 'sent', 'delivered', 'failed',
'bounced'), attempt_number (TINYINT, default 1), error_message (TEXT, nullable), delivered_at (TIMESTAMP,
nullable), created_at, updated_at. Indeks złożony na (custodianship_id, recipient_email, status) dla szybkich
query podczas retry.

8. Czy kolumna status w tabeli custodianships powinna być ENUM czy VARCHAR, biorąc pod uwagę 4 statusy: draft,
   active, completed, delivery_failed?

Rekomendacja: Użyj ENUM('draft', 'active', 'completed', 'delivery_failed') z indeksem. ENUM jest bardziej
wydajny w MySQL (1 bajt vs 4+ bajtów VARCHAR) i wymusza integralność danych na poziomie bazy. Dodaj kolumnę
status_changed_at (TIMESTAMP) dla audytu zmian statusu.

9. Jak powinny być przechowywane dane użytkownika w kontekście RODO i wymogu usuwania nieaktywnych kont po 12
   miesiącach?

Rekomendacja: Tabela users powinna mieć: last_login_at (TIMESTAMP, nullable, indeksowany), last_activity_at
(TIMESTAMP, nullable, indeksowany - aktualizowany przy resecie timera lub edycji), deleted_at (TIMESTAMP,
nullable - soft deletes). Scheduled job będzie identyfikował konta gdzie last_activity_at < NOW() - 12 miesięcy
i wykonywał soft delete. Hard delete (wraz z wszystkimi powiązanymi danymi) po dodatkowych 30 dniach.

10. Czy tabela downloads do logowania pobrań załączników powinna mieć powiązanie z attachment_id, czy z
    custodianship.uuid dla zgodności z endpointem pobierania?

Rekomendacja: Tabela downloads powinna mieć dual reference: custodianship_id (FK - dla raportów), attachment_id
(FK - dla szczegółów), ip_address (VARCHAR 45, indeksowany - rate limiting), user_agent (TEXT), downloaded_at
(TIMESTAMP, indeksowany), success (BOOLEAN - czy pobranie zakończone). Indeks złożony (ip_address,
downloaded_at) jest krytyczny dla rate limiting (10 pobrań/h per IP). Retencja: 24 miesiące.

Czy chciałbyś, abym rozwinął któreś z tych punktów, czy masz pytania dotyczące przedstawionych rekomendacji?

> 1. Może niech to będzie oddzielna tabela może custodianship_message - może niech sama treśc maila będzie w
     osobnej tabeli - nie wiem czy nazwa jest dobra
2. custodianships has many recipients
3. Po prostu interval gdzie będzie chyba datetime określający interwał
4. tak niech będą
5. Attachments powinna mie c tylko nazwę a dodatkowe informacje powinny być w tabeli media, z pakietu spatie/media
   z któego chce korzysać. Ten pakiet się zatroszczy o to żeby byłą odpowiednia struktura w bazie.
6. Niech nie będzie retencji. Sam się o to zatroszcze po MVP
7. Rekomendacja jest ok
   8.Zastanawiam się czy delivery_failed to jest status któy chciałby pokazywać - w sumie jest to po prostu completed
   ale maile nie poszły.
   9.Rekomendacja jest ok
10. Rekomendacja jest ok. ALe daj tu spokój z tym rate limitingiem. i jak zamiast downloaded_at bedzie created_at
    to będzie lepiej. Bardziej ze standardami

● Świetnie, przyjmuję Twoje odpowiedzi. Kontynuuję generowanie kolejnych pytań i zaleceń w oparciu o Twój
feedback:

11. Czy tabela custodianship_messages powinna przechowywać wyłącznie treść wiadomości, czy również metadane jak
    subject i footer?

Rekomendacja: Tabela custodianship_messages powinna zawierać: id, custodianship_id (FK, UNIQUE - relacja 1:1),
message_content (TEXT, zaszyfrowany przez Laravel encrypt()), encryption_version (TINYINT, default 1),
created_at, updated_at. Subject i footer są generowane przez system zgodnie z REQ-028, więc nie wymagają
przechowywania. To podejście separuje wrażliwe dane od metadanych powiernictwa.

12. Jak powinna wyglądać relacja między custodianships a recipients - czy przez tabelę pivot
    custodianship_recipient, czy bezpośrednią kolumną FK w recipients?

Rekomendacja: Bezpośrednia kolumna FK w tabeli recipients jest prostsza dla relacji 1:N: id, custodianship_id
(FK, indeksowany), email (VARCHAR 255), created_at, updated_at. Indeks złożony (custodianship_id, email) zapewni
unikalność odbiorcy w ramach powiernictwa i szybkie query. Brak potrzeby tabeli pivot dla prostej relacji
has-many.

13. Czy kolumna interval w tabeli custodianships powinna być typu INTERVAL MySQL, czy może inny typ danych byłby
    bardziej odpowiedni?

Rekomendacja: MySQL nie ma natywnego typu INTERVAL jak PostgreSQL. Sugeruję interval_value (INTEGER UNSIGNED) +
interval_unit (ENUM('minutes', 'hours', 'days'), default 'days'). To daje elastyczność bez używania DATETIME
(który nie reprezentuje okresu czasu, tylko konkretny moment). Alternatywnie, jeśli wolisz prostotę:
interval_days (INTEGER UNSIGNED) i przeliczanie w aplikacji dla przyszłych jednostek.

14. Skoro używasz spatie/laravel-medialibrary, czy tabela attachments powinna w ogóle istnieć, czy wystarczy
    relacja custodianships → media przez package?

Rekomendacja: Możesz całkowicie pominąć tabelę attachments i wykorzystać wyłącznie tabelę media z pakietu
Spatie. Model Custodianship implementuje HasMedia trait, używając kolekcji 'attachments'. Spatie automatycznie
tworzy tabele media z kolumnami: model_type, model_id, collection_name, name, file_name, mime_type, size, uuid
(możesz użyć do tokenizowanych linków). Dodaj custom property download_uuid jeśli UUID z Spatie nie jest
wystarczające.

15. Czy status delivery_failed powinien być osobnym statusem w ENUM, czy lepiej rozdzielić na completed + osobną
    kolumnę delivery_status?

Rekomendacja: Rozważ podejście: status ENUM('draft', 'active', 'completed') + delivery_status ENUM('pending',
'sent', 'delivered', 'failed', 'bounced') nullable. Gdy timer wygasa, status → 'completed', a delivery_status
śledzi proces wysyłki. To separuje lifecycle powiernictwa od delivery lifecycle. Alternatywnie: zostaw
delivery_failed w statusie głównym, ale dodaj delivery_details (JSON) dla kontekstu błędów.

16. Czy tabela custodianships powinna mieć kolumnę user_id z cascade delete, czy soft delete też dla powiernictw
    przy usunięciu użytkownika?

Rekomendacja: Dodaj user_id (FK) z ON DELETE CASCADE dla hard delete użytkownika (RODO compliance). Jeśli
użytkownik zostanie usunięty, wszystkie jego powiernictwa, wiadomości, załączniki (media) i logi powinny zostać
usunięte. Nie używaj soft delete dla powiernictw - REQ-010 wymaga hard delete z explicit confirmation. Indeks na
user_id jest obowiązkowy dla wydajności query dashboardu.

17. Jak powinna być obsłużona tabela deliveries w kontekście wielu odbiorców - czy jeden rekord per
    powiernictwo, czy per recipient?

Rekomendacja: Jeden rekord per recipient: tabela deliveries z kluczem złożonym (custodianship_id, recipient_id)
lub (custodianship_id, recipient_email) jeśli nie chcesz FK do recipients. To pozwala osobno śledzić delivery
status dla każdego odbiorcy, retry logic per recipient i bounce handling. Struktura: każdy odbiorca ma swój
lifecycle wysyłki niezależnie od innych.

18. Czy tabela resets powinna mieć ON DELETE CASCADE przy usunięciu powiernictwa, czy zachować logi nawet po
    usunięciu (orphaned records)?

Rekomendacja: ON DELETE CASCADE - zgodnie z REQ-010, przy usunięciu powiernictwa "wszystkie logi resetów dla
tego powiernictwa" powinny zostać usunięte. Bez retencji policy (Twoja decyzja z pkt 6), logi nie mają wartości
bez kontekstu powiernictwa. FK: custodianship_id ON DELETE CASCADE, user_id ON DELETE CASCADE.

19. Czy powinna istnieć osobna tabela do przechowywania konfiguracji progów kolorów timerów (30 dni - zielony, 7
    dni - żółty), czy wystarczy config file?

Rekomendacja: Zgodnie z REQ-034, progi są konfigurowalne w config/custodianship.php i NIE są edytowalne przez
użytkownika w MVP. Nie twórz tabeli timer_thresholds - to overengineering. Config file wystarczy. Post-MVP,
jeśli progi będą per-user, dodasz kolumny do users: timer_threshold_warning (default 30),
timer_threshold_critical (default 7).

20. Czy tabela users powinna mieć kolumnę plan (ENUM: 'free', 'paid') dla limitów freemium, czy osobna tabela
    subscriptions?

Rekomendacja: Dla MVP wystarczy kolumna plan (ENUM('free', 'paid'), default 'free') w tabeli users. Limity (3
powiernictwa, 2 odbiorców, 10MB) są hard-coded w aplikacji per plan. Nie twórz tabeli subscriptions - to
overengineering przed monetyzacją. Post-MVP, gdy dodasz płatności, użyj laravel/cashier który doda własne
tabele. Dodaj indeks na plan dla raportowania.

Czy te rekomendacje są zgodne z Twoją wizją? Masz dalsze uwagi do któregoś z punktów?

> 11. Subject będzie generyczny z aplikacji. Footer także. TO będzie treść po prostu.
12. bezpośrednią kolumną FK w recipients
13. W sumie nie znam tego typu danych jeśli jest ok to chyba może być
14. Good Catch! Nie powinna.
    15.OOo Niech będzie oddzielna kolumna. To dobry pomysł.
16. Rekomendacja jest ok
17. Jeden rekord per reciepent i delivery powinien miec FK of reciepient
18. Hmm. I think i will use laravel fortify - so i think it comes with its own sets of tables?
19. nie
20. Powinna nie mieć nic . A tabela subscriptions powinna istnieć - jak nie ma subscriptions to znaczy że jest
    free - w MVP nie potrzebujemy tabeli subscriptions

● Świetnie, przyjmuję Twoje odpowiedzi. Kontynuuję z kolejnymi pytaniami:

21. Czy tabela custodianship_messages powinna mieć jakieś dodatkowe kolumny dla audytu (np. updated_by, version)
    w przypadku edycji treści wiadomości?

Rekomendacja: Nie w MVP. Wystarczy created_at i updated_at z Laravel timestamps. Versioning treści wiadomości
(message_content_v1, message_content_v2) to overengineering - użytkownik nie potrzebuje historii edycji w MVP.
Jeśli post-MVP pojawi się taka potrzeba, można użyć pakietu spatie/laravel-activitylog.

22. Czy kolumna email w tabeli recipients powinna mieć unique constraint, czy tylko walidację na poziomie
    aplikacji dla unikalności w obrębie custodianship_id?

Rekomendacja: Unique constraint złożony: UNIQUE KEY unique_recipient (custodianship_id, email). To zapobiega
duplikatom odbiorców w ramach jednego powiernictwa na poziomie bazy danych. Jeden odbiorca może być w wielu
różnych powiernictwach, ale nie może być 2x w tym samym. Dodatkowa walidacja w Form Request dla czytelnych error
messages.

23. Skoro Laravel Fortify obsługuje autentykację, czy tabela users będzie wymagała dodatkowych kolumn poza
    standardowymi (two_factor_secret, two_factor_recovery_codes)?

Rekomendacja: Fortify NIE tworzy własnych tabel - używa standardowej tabeli users z Laravel. Potrzebne kolumny:
id, name, email (unique), email_verified_at, password, remember_token, last_login_at, last_activity_at,
deleted_at (soft deletes dla RODO), created_at, updated_at. Kolumny 2FA (two_factor_*) dodaj tylko jeśli
planujesz 2FA w MVP - według OUT-004 2FA nie jest w MVP, więc pomiń je teraz.

24. Czy powinna istnieć tabela password_reset_tokens dla funkcji resetowania hasła (REQ-004), czy Laravel
    Fortify to obsłuży automatycznie?

Rekomendacja: Laravel (i Fortify) wymaga tabeli password_reset_tokens: email (primary key), token, created_at.
To standardowa tabela Laravel dla reset password flow. Migracja jest w domyślnym Laravel boilerplate. Tokeny
wygasają po 1h zgodnie z REQ-004 - obsługa w config auth.passwords.users.expire.

25. Czy tabela deliveries z FK do recipients powinna mieć ON DELETE CASCADE, czy ON DELETE SET NULL gdy odbiorca
    zostanie usunięty?

Rekomendacja: ON DELETE CASCADE. Jeśli użytkownik usuwa odbiorcę z powiernictwa (REQ-014), powinien usunąć też
historię delivery dla tego odbiorcy. Logi delivery bez kontekstu odbiorcy nie mają wartości. Struktura:
deliveries.recipient_id FK ON DELETE CASCADE. Jeśli potrzebujesz zachować email dla audytu, dodaj kolumnę
recipient_email_snapshot (VARCHAR 255) przed wysyłką.

26. Czy tabela downloads powinna mieć relację do media (Spatie), czy bezpośrednio do custodianships przez UUID?

Rekomendacja: Relacja do obu: custodianship_id (FK dla raportów - ile razy powiernictwo było pobierane),
media_id (FK do media - który konkretny plik). To pozwala na szczegółowe logi per plik i agregaty per
powiernictwo. Struktura: id, custodianship_id, media_id, ip_address, user_agent, success, created_at. Indeks na
(custodianship_id, created_at) dla raportów.

27. Czy kolumna interval_unit w podejściu interval_value + interval_unit jest naprawdę potrzebna w MVP, skoro
    wszystkie interwały w REQ-006 są w dniach (30, 90, 180)?

Rekomendacja: Nie w MVP. Uproszczenie: użyj tylko interval_days (INTEGER UNSIGNED, default 30). REQ-021 wspomina
o "dniach, godzinach, minutach", ale wszystkie przykłady w PRD to dni. Oszczędzasz kolumnę i logikę
przeliczania. Post-MVP, jeśli potrzeba godzin/minut, dodasz interval_unit i zmigrowujesz dane. YAGNI principle.

28. Czy tabela custodianships powinna mieć kolumnę uuid dla publicznych linków do załączników (REQ-019), czy
    UUID powinien być w tabeli media?

Rekomendacja: custodianships.download_uuid (CHAR(36), unique, indeksowany). Jeden UUID per powiernictwo dla
wszystkich załączników - endpoint /custodianships/{uuid}/download. Spatie media.uuid to UUID per plik, ale
REQ-019 mówi o "unikalnym tokenizowanym URL na powiernictwo", nie per plik. Generuj UUID przy utworzeniu lub
przy pierwszej wysyłce (decyzja do podjęcia).

29. Czy potrzebna jest tabela email_verification_tokens dla REQ-002 (weryfikacja email), czy Laravel Fortify to
    obsłuży?

Rekomendacja: Laravel NIE ma domyślnej tabeli email_verification_tokens - używa signed URLs z expiracją w query
string. Fortify to również obsługuje. Nie potrzebujesz osobnej tabeli. Kolumna users.email_verified_at (nullable
timestamp) wystarczy. Link aktywacyjny ważny 24h - konfiguracja w config/auth.verification.expire.

30. Czy tabela deliveries powinna przechowywać pełną treść wysłanego emaila, czy tylko referencję do
    custodianship_messages?

Rekomendacja: Tylko referencja przez custodianship_id → custodianships → custodianship_messages. Nie duplikuj
treści. Dodaj kolumny dla metadanych wysyłki: email_subject (VARCHAR 255 - wygenerowany subject), sent_at
(timestamp kiedy faktycznie wysłano), mailgun_message_id (VARCHAR 255 - ID z Mailgun dla webhook matching).
Snapshot email content tylko jeśli compliance wymaga, ale w MVP to overkill.

Masz uwagi do tych punktów? Czy któryś wymaga doprecyzowania?

> 21. Nie - wtedy musiałbym też pilnować wersji attachmentów - w MVP nie
22. Nie - można niezależnie przecież do tej samej osoby wysłać różne dokumenty.
23. Wg rekomendacji
24. Wg rekomendacji
25. Wg rekomendacji
26. Wg rekomendacji
27. Nie - użyj typu interwal, który sam określa czas - jak będzie w minutach to zapisze w minutach jak w godzinach
    to w godzinach i to kolumna interval a nie jakiś unit
28. uuid do custodiana powinien być - ale nie do załączników tylko jak uczestnik tworzy nowego custodiana
    sobie.Nie powinien widzieć idków tylko uuidy. Załączniiki bedą miały własny token wygenerwany
29. Wg rekomendacji
30. Wg rekomendacji - nie potrzebuje tego deliveries ma być tylko informacja ze coś zostało wysłane. NIe
    potrzebuje kopii bo to może być tylko po zakońćzeniiu custodianaship

● Przyjmuję Twoje odpowiedzi. Kontynuuję z kolejnymi pytaniami:

31. Czy MySQL wspiera natywny typ INTERVAL, czy potrzebujesz innego rozwiązania dla przechowywania interwału
    czasu?

Rekomendacja: MySQL NIE ma typu INTERVAL (to feature PostgreSQL). Najbliższe rozwiązania w MySQL: (1) TIME -
zakres do 838:59:59 (ok dla dni/godzin/minut), (2) VARCHAR przechowujący ISO 8601 duration (np. "P30D", "PT2H"),
(3) INTEGER z minutami. Sugeruję interval jako VARCHAR(20) z formatem ISO 8601 duration string (np. "P30D",
"P90D", "PT24H") - Laravel Carbon potrafi to parsować przez CarbonInterval::fromString(). Alternatywnie: custom
MySQL type jeśli wymaga refactoringu.

32. Skoro custodianships.uuid służy do identyfikacji przez użytkownika (nie ID), czy wszystkie endpointy powinny
    używać UUID zamiast ID w routingu?

Rekomendacja: Tak. Route model binding przez UUID: Route::get('/custodianships/{custodianship:uuid}'). Model
Custodianship używa uuid jako route key zamiast id. Kolumna uuid (CHAR(36), unique, indeksowany) generowana
automatycznie przy utworzeniu (event/observer). To ukrywa ID i zapewnia non-enumerable URLs. Struktura: id
(internal primary key), uuid (public identifier).

33. Czy tabela media (Spatie) powinna mieć dodatkową kolumnę download_token dla tokenizowanych linków do
    załączników, czy używasz Spatie UUID?

Rekomendacja: Spatie media.uuid (automatycznie generowany) może służyć jako download token. Jeśli potrzebujesz
osobnego tokenu dla bezpieczeństwa, dodaj custom property przez Spatie:
$media->setCustomProperty('download_token', Str::random(64)). Endpoint:
/custodianships/{custodianship:uuid}/attachments/{token}. Spatie przechowuje custom properties w kolumnie
custom_properties (JSON). Token długi (64 chars) jest bezpieczniejszy niż UUID.

34. Czy tabela recipients powinna mieć unique constraint na (custodianship_id, email) jeśli można wysyłać różne
    dokumenty do tej samej osoby w ramach jednego powiernictwa?

Rekomendacja: Nie. Bez unique constraint - jeden odbiorca może być wielokrotnie na liście tego samego
powiernictwa (chociaż to dziwne z UX perspective). Ale jeśli to intencjonalne, brak constraintu daje
elastyczność. Walidacja na poziomie aplikacji może ostrzegać o duplikatach, ale nie blokować. Struktura: tylko
indeks na custodianship_id dla wydajności, brak unique.

35. Czy tabela deliveries powinna mieć osobny rekord dla każdej próby wysyłki (attempt), czy aktualizować
    istniejący rekord przy retry?

Rekomendacja: Aktualizuj istniejący rekord. Struktura: id, custodianship_id, recipient_id (FK), recipient_email
(snapshot), mailgun_message_id, status, attempt_number (incrementowany przy retry), last_attempt_at,
error_message (ostatni błąd), sent_at, delivered_at, created_at, updated_at. Jeśli potrzebujesz historii
wszystkich prób, osobna tabela delivery_attempts z FK do deliveries, ale to overkill dla MVP.

36. Czy custodianships.next_trigger_at powinien być nullable, czy zawsze mieć wartość (nawet dla statusu draft)?

Rekomendacja: Nullable. Dla status='draft' wartość next_trigger_at to NULL (timer nie działa). Po aktywacji
(REQ-012): next_trigger_at = NOW() + interval. To czysto rozróżnia aktywne vs nieaktywne timery. Query cron job:
WHERE status='active' AND next_trigger_at IS NOT NULL AND next_trigger_at <= NOW(). Indeks częściowy (partial
index) na MySQL 8.0: INDEX (status, next_trigger_at) WHERE next_trigger_at IS NOT NULL.

37. Czy tabela custodianships powinna mieć kolumnę deleted_at dla soft deletes, czy REQ-010 wymaga tylko hard
    delete?

Rekomendacja: NIE. REQ-010 jest explicit: "hard delete" z potwierdzeniem. Soft deletes niepotrzebne - użytkownik
świadomie usuwa permanentnie. Bez kolumny deleted_at - oszczędzasz przestrzeń i upraszczasz query (brak
potrzeby withTrashed() itp.). Hard delete z kaskadą usuwa wszystko: messages, media, resets, deliveries,
downloads.

38. Czy potrzebna jest tabela notifications dla przechowywania historii wysłanych powiadomień (REQ-037:
    przypomnienie 7 dni przed wygaśnięciem)?

Rekomendacja: Opcjonalnie, ale przydatne. Laravel ma built-in notifications table (migracja: php artisan
notifications:table). Struktura: id, type, notifiable_type, notifiable_id, data (JSON), read_at, created_at. To
pozwala śledzić które przypomnienia zostały wysłane (zapobieganie duplikatom) i debugging. Query przed wysyłką:
sprawdź czy notifications ma rekord dla custodianship_id + type='ReminderBeforeExpiry'. Jeśli nie ma MVP
priority, pomiń.

39. Czy tabela resets powinna mieć kolumnę reset_method jako ENUM czy VARCHAR, skoro w US-019 są tylko 2 metody:
    'manual_button', 'post_edit_modal'?

Rekomendacja: ENUM('manual_button', 'post_edit_modal', 'bulk_reset'). Dodaj 'bulk_reset' dla US-018 (reset
wszystkich timerów jednocześnie) jeśli będzie w MVP. ENUM jest wydajniejszy niż VARCHAR i wymusza integralność.
Jeśli przewidujesz więcej metod (np. 'api_reset', 'scheduled_reset'), użyj VARCHAR(50) dla elastyczności.

40. Czy tabela downloads powinna logować nieudane próby pobrania (np. invalid token, 404), czy tylko successful
    downloads?

Rekomendacja: Loguj wszystkie próby. Kolumna success (BOOLEAN) odróżnia sukces vs porażkę. Dodaj error_type
(VARCHAR 50, nullable) dla debugowania: 'invalid_token', 'not_found', 'rate_limited', 'server_error'. To pomaga
w security monitoring (brute-force attempts na tokeny) i troubleshooting. Struktura: success=0 +
error_type='invalid_token' + ip_address dla rate limiting failed attempts.

Czy te rekomendacje są zgodne z Twoją wizją? Potrzebujesz doprecyzowania któregoś z punktów?

> 31. Ok wersja 2
32. Wg rekomendacji
33. Wg rekomendacji
34. Może być  unique constraint na (custodianship_id, email) to faktycznie byłoby dziwne pozwolić 2 odbiorców dla
    tych samych dokumentów dodawać
35. Deliveries powinna mieć oddzielne rekordy dla oddzielnych prób wysłąnia wiadomości
36. Wg rekomendacji
37. Wg rekomendacji
38. Użyj systemowej - to najlepsz jak rekomendacja mówi
39. W MVP tylko te.
40. Nie powinna logować. Token będzie jedynym accessem- jesli będzie zły to będzie 404 i tyle. Można na ten
    endpoint jakiś throttling nałożyć

● Przyjmuję Twoje odpowiedzi. Kontynuuję z kolejnymi pytaniami:

41. Skoro interval będzie przechowywany jako VARCHAR z formatem ISO 8601 (np. "P30D"), czy potrzebna jest
    walidacja na poziomie bazy danych, czy wystarczy walidacja w aplikacji?

Rekomendacja: Walidacja tylko w aplikacji (Form Request). MySQL nie ma natywnego CHECK constraint dla ISO 8601
duration validation. Laravel validation rule: regex:/^P(\d+D|T\d+H|T\d+M)$/. Dodatkowo: dodaj kolumnę
interval_in_minutes (INTEGER UNSIGNED, generated/computed) dla łatwych obliczeń next_trigger_at bez parsowania
stringa. Alternatywnie: virtual column w MySQL 8.0 jeśli możliwe konwertowanie ISO 8601 → minuty.

42. Czy tabela deliveries z osobnymi rekordami per attempt powinna mieć strukturę flat (wszystkie pola w jednej
    tabeli), czy rozdzielić na deliveries + delivery_attempts?

Rekomendacja: Rozdziel na dwie tabele dla czystości. deliveries: id, custodianship_id, recipient_id,
recipient_email, mailgun_message_id, final_status (ENUM: 'pending', 'delivered', 'failed', 'bounced'),
delivered_at, created_at, updated_at. delivery_attempts: id, delivery_id (FK), attempt_number, status (ENUM:
'sent', 'failed', 'bounced'), error_message, attempted_at. To pozwala na czyste query "wszystkie deliveries" bez
duplikatów z attemptów.

43. Czy tabela custodianships powinna mieć kolumnę activated_at (timestamp kiedy draft → active), czy wystarczy
    śledzić to przez created_at i status?

Rekomendacja: Dodaj activated_at (nullable timestamp). To przydatne dla analityki (REQ-024, METRIC-007: Draft to
Active Conversion) i audytu. Różnica między created_at (kiedy utworzono draft) a activated_at (kiedy użytkownik
aktywował) pokazuje user behavior. Query: "ile czasu użytkownicy czekają przed aktywacją?". Opcjonalne w MVP,
ale tanio dodać teraz.

44. Czy kolumna recipient_email w tabeli deliveries (snapshot) jest naprawdę potrzebna, skoro jest FK do
    recipients.email?

Rekomendacja: TAK, snapshot jest potrzebny. Jeśli użytkownik zmieni email odbiorcy w recipients po wysyłce,
chcesz wiedzieć na jaki email faktycznie wysłano. Kolumna deliveries.recipient_email (VARCHAR 255) to immutable
snapshot z momentu wysyłki. recipient_id (FK, nullable z ON DELETE SET NULL) dla relacji, ale email zawsze
zachowany. Audyt wymaga: "na jaki adres wysłano 2025-01-15?".

45. Czy tabela notifications (Laravel built-in) wystarczy dla REQ-037, czy potrzebne są dodatkowe kolumny/tabela
    dla reminder-specific logic?

Rekomendacja: Wystarczy standardowa tabela notifications. Kolumna data (JSON) przechowuje: {"custodianship_id":
123, "days_remaining": 7, "type": "expiry_reminder"}. Query przed wysyłką przypomnienia: WHERE notifiable_id =
user_id AND type = 'App\\Notifications\\ExpiryReminder' AND JSON_EXTRACT(data, '$.custodianship_id') = ?. To
zapobiega duplikatom. Nie potrzebujesz custom tabeli.

46. Czy tabela downloads powinna w ogóle istnieć w MVP, skoro nie logujesz failed attempts i throttling jest na
    poziomie middleware?

Rekomendacja: TAK, zachowaj dla successful downloads tylko. REQ-019 wymaga: "aplikacja loguje każde pobranie:
timestamp, IP, user agent". Struktura minimalna: id, custodianship_id, media_id, ip_address, user_agent,
created_at. Usuń success i error_type. To daje audyt trail: "kto i kiedy pobrał załączniki?" - przydatne dla
supportu i potencjalnych sporów.

47. Czy custodianships.uuid powinien być generowany przy utworzeniu rekordu (database trigger), czy w Laravel
    observer/event?

Rekomendacja: Laravel observer. Stwórz CustodianshipObserver z metodą creating(): $custodianship->uuid =
Str::uuid(). To jest Laravel convention i łatwiejsze w testowaniu niż database trigger. Alternatywnie: boot
method w modelu Custodianship z trait HasUuids (Laravel 9+) - automatyczne UUID generation. Sprawdź czy Laravel
12 ma ulepszoną obsługę UUID.

48. Czy tabela media (Spatie) powinna mieć ON DELETE CASCADE przy usunięciu custodianships, czy Spatie to
    obsługuje automatycznie?

Rekomendacja: Spatie obsługuje to przez Laravel events. Gdy usuwasz model z HasMedia trait, Spatie automatycznie
usuwa powiązane media records i pliki z S3. NIE dodawaj manualnie ON DELETE CASCADE na media.model_id - to może
konfliktować z Spatie logic. Spatie używa polymorphic relation (model_type + model_id), więc standardowy FK nie
zadziała.

49. Czy tabela resets powinna mieć indeks na ip_address dla potencjalnego rate limiting resetów, czy to
    niepotrzebne w MVP?

Rekomendacja: Niepotrzebne w MVP. Rate limiting resetów nie jest w wymaganiach PRD. Indeks na (custodianship_id,
created_at) wystarczy dla query "historia resetów dla powiernictwa". Dodaj indeks na user_id dla "wszystkie
resety użytkownika". IP logging to audyt, nie security feature w MVP. Post-MVP: jeśli wykryjesz abuse, dodasz
indeks i rate limiting.

50. Czy potrzebna jest tabela audit_logs dla globalnego audytu wszystkich akcji, czy wystarczają osobne tabele
    (resets, deliveries, downloads, notifications)?

Rekomendacja: Osobne tabele wystarczają dla MVP. Każda ma specific purpose i structure. Generyczna tabela
audit_logs to overengineering bez konkretnego use case. Jeśli post-MVP potrzebujesz comprehensive audit trail,
użyj spatie/laravel-activitylog (wspomniany w tech_stack.md jako optional). W MVP: YAGNI - nie twórz abstrakcji
bez potrzeby.

Czy te rekomendacje są zgodne z Twoją wizją? Potrzebujesz jeszcze jakichś doprecyzowań przed podsumowaniem?

──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────
> 41. Wg rekomendacji
42. W tej samej! przecież jest status failed
43. wg rekomendacji
44. wg rekomendacji
45. wg rekomendacji
46. wg rekomendacji
47. wg rekomendacji
48. wg rekomendacji
49. wg rekomendacji
50. wg rekomendacji

To chyba wszystko. Czy możesz wygenerować dokument? 
