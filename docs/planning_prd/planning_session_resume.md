Podsumowanie Sesji Planistycznej PRD

1. Podjęte Decyzje

Model Biznesowy

1. Plan Free: 3 powiernictwa, 10MB załączników per powiernictwo (suma), max 2 odbiorców per powiernictwo
2. Plan Paid: Do zdefiniowania - więcej powiernictw, większe limity, backup recipients, SMS, priority support

Mechanizm Timera

3. Użytkownik ustawia interwał (np. 30/90/180 dni), może resetować w dowolnym momencie
4. System sprawdza co minutę czy timer wygasł (wysoka precyzja)
5. Po resecie timer wraca do pełnej wartości

Proces Resetowania

6. Prosty przycisk w widoku powiernictwa oraz na dashboardzie
7. Edycja powiernictwa nie resetuje timera automatycznie
8. Zmiana interwału nie resetuje timera - system przeliczy pozostały czas
9. Po edycji/zmianie interwału: modal z pytaniem czy zresetować
10. Każdy reset logowany (timestamp, IP, user agent, metoda)

Model Odbiorcy

11. Odbiorca nie musi być użytkownikiem systemu - wystarczy email
12. Jedno powiernictwo może mieć wielu odbiorców (max 2 w free)
13. System nie weryfikuje tożsamości odbiorcy końcowego
14. Użycie Mailgun (lub podobnego) do potwierdzenia dostarczenia

Szyfrowanie i Załączniki

15. Szyfrowanie dotyczy tylko danych w spoczynku na serwerze (at-rest encryption)
16. Brak zewnętrznego klucza deszyfrującego znanego użytkownikowi
17. Link do załączników: unikalny tokenizowany URL (UUID), ważny bezterminowo
18. Załączniki na AWS S3 bez publicznego dostępu, aplikacja jako pośrednik
19. Rate limiting na dostęp do linków (zapobieganie brute-force)

Treść Wiadomości

20. Brak placeholderów typu {{recipient_name}} w MVP
21. Treść emaila definiowana bezpośrednio przez użytkownika

Onboarding i Aktywacja

22. Rejestracja (email + hasło) → link aktywacyjny wysyłany
23. Można logować się przed aktywacją emaila
24. Przed aktywacją: możliwość tworzenia tylko draftów (timer nie startuje)
25. Po aktywacji: ręczna aktywacja draftów
26. Brak video tutorial w onboardingu
27. Cel: cały proces < 5 minut

Dashboard i Monitoring

28. Widok: lista powiernictw z nazwą, odbiorcami, progressem, kolorowym statusem
29. Progi kolorów konfigurowalne w configu systemu (nie przez użytkownika)
30. Przykład: zielony >30 dni, żółty 7-30, czerwony <7
31. Jedno przypomnienie przed wygaśnięciem (próg w configu), konfigurowalne przez użytkownika post-MVP

Powiadomienia

32. Alert gdy email nie dojdzie do odbiorcy (dla twórcy i administratora)
33. Brak powiadomienia dla twórcy gdy odbiorca otwiera link (nie w MVP)
34. Po udanym wysłaniu: powiernictwo oznaczone jako "completed" (zielony status)

Obsługa Błędów Dostarczenia

35. Email bounce → powiernictwo "delivery_failed"
36. Alert dla twórcy powiernictwa i administratora
37. Backup recipient (alternatywny odbiorca) - post-MVP

Usuwanie Powiernictwa

38. Hard delete (trwałe usunięcie)
39. Wszystkie dane i załączniki usuwane permanentnie
40. Potwierdzenie: modal + checkbox + wpisanie nazwy powiernictwa

Definicje

41. Aktywne powiernictwo: timer nie dobił do zera
42. Nie można przedłużyć powiernictwa z zerowym czasem

  ---
2. Dopasowane Rekomendacje

Wysokie Dopasowanie

1. Interwał czasowy: System z codziennym scheduled job (zaimplementowano jako co-minutowy cron dla większej precyzji)
2. Resetowanie timera: Prosty przycisk "I'm OK" w dashboardzie i widoku powiernictwa
3. Odbiorca bez konta: Dowolny email, bez wymogu rejestracji
4. Link do załączników: Unikalny tokenizowany URL (UUID) ważny bezterminowo z rate limiting
5. Edycja nie resetuje: Świadome kliknięcie "Reset Timer" wymagane
6. Zmiana interwału: System przeliczy next_trigger_at bez resetu
7. Audit log resetów: Tabela z timestamp, IP, user_agent, metoda
8. Hard delete z potwierdzeniem: Modal + checkbox + wpisanie nazwy
9. Dashboard z progressem: Nazwa, odbiorcy, postęp (X dni z Y), kolorowy status
10. Email alert przed wygaśnięciem: Jedno przypomnienie (próg konfigurowalny)
11. Onboarding wizard: Rejestracja → aktywacja → tworzenie pierwszego powiernictwa
12. Wielu odbiorców: Możliwość dodania do 2 odbiorców w free

Średnie Dopasowanie

13. Limity freemium: Przyjęto 3 powiernictwa i 10MB (zamiast sugerowanych unlimited powiernictw/100MB)
14. Backup recipient: Odłożone na post-MVP (zgodnie z rekomendacją)
15. Jednostronny system: Odbiorca nie wie o powiernictwie do czasu emaila
16. Email deliverability: Mailgun wzmiankowany (rekomendacja: wybór między Mailgun/SendGrid/AWS SES)

Odrzucone

17. Video tutorial w onboardingu - odrzucone przez użytkownika
18. Placeholdery w treści - nie w MVP
19. Powiadomienie dla twórcy o otwarciu linku - nie w MVP
20. Persony użytkowników - uznane za nieistotne dla projektu
21. GTM strategy - uznane za nieistotne

  ---
3. Główne Wymagania Funkcjonalne

Autentykacja i Konta

- Rejestracja email + hasło
- Wysyłka linku aktywacyjnego
- Możliwość logowania przed aktywacją
- Tworzenie tylko draftów przed aktywacją

Zarządzanie Powiernictwami

- CRUD dla powiernictw (Create, Read, Update, Delete)
- Statusy: draft, active, completed, delivery_failed
- Konfiguracja interwału czasowego (dni)
- Definiowanie treści wiadomości (textarea/editor)
- Dodawanie wielu odbiorców (max 2 w free)
- Załączanie plików (suma do 10MB w free)

System Timerów

- Timer odlicza od last_reset_at + interwał
- Cron job co minutę sprawdza next_trigger_at <= now()
- Przycisk reset w widoku powiernictwa i na dashboardzie
- Modal po edycji/zmianie interwału: "czy chcesz zresetować?"
- Audit log: timestamp, user_id, custodianship_id, IP, user_agent, reset_method

Wysyłka Email

- Automatyczna wysyłka gdy timer wygaśnie
- Email z treścią użytkownika + link do załączników
- Weryfikacja dostarczenia (via Mailgun lub podobne)
- Logowanie prób wysyłki
- Retry logic w przypadku przejściowych błędów
- Alert przy bounce/failure

Przechowywanie Załączników

- Upload do AWS S3 (lub podobnego)
- Szyfrowanie at-rest
- Brak publicznego dostępu
- Aplikacja jako proxy do pobierania
- Unikalny tokenizowany URL (UUID) per powiernictwo
- Rate limiting na endpoint pobierania
- Trwałe usuwanie przy hard delete

Dashboard

- Lista powiernictw z:
    - Nazwą
    - Odbiorcami
    - Progressem ("X dni pozostało z Y")
    - Kolorowym statusem (green/yellow/red bazując na konfigurowalnych progach)
- Możliwość szybkiego resetu z poziomu listy

Powiadomienia

- Email reminder przed wygaśnięciem (próg w configu)
- Alert dla twórcy i admina przy delivery failure

Limity Freemium

- Max 3 powiernictwa per użytkownik
- Max 10MB załączników per powiernictwo
- Max 2 odbiorców per powiernictwo
- Sprawdzanie limitów przed akcjami

  ---
4. Kluczowe Historie Użytkownika

Historia 1: Freelancer z Kryptowalutami

Jako freelancer posiadający kryptowalutyChcę przekazać żonie seed phrases i dokumenty biznesowe aby nie straciła dostępu do aktywów w razie mojej śmierci

Acceptance Criteria:
- Może utworzyć powiernictwo "Krypto dla Anny"
- Może załączyć PDF z instrukcjami + seed phrase (do 10MB)
- Może ustawić interwał 90 dni
- Co 3 miesiące resetuje jednym kliknięciem
- Jeśli nie zresetuje przez 90 dni → Anna otrzymuje email z linkiem do załączników

Historia 2: Senior z Testamentem

Jako osoba starsza z testamentem chcę przekazać testament i dostępy do kont bankowych dorosłym dzieciom aby mieli dostęp do tych informacji po mojej śmierci

Acceptance Criteria:
- Może utworzyć osobne powiernictwo dla każdego dziecka (max 3 w free)
- Może dodać różne załączniki dla różnych odbiorców
- Może ustawić dłuższy interwał (180 dni)
- Co pół roku resetuje timer
- Każde dziecko otrzymuje email osobno

Historia 3: Młoda Osoba z Hasłami

Jako młoda osobaChcę przekazać rodzicom hasła do moich kont online aby mieli dostęp w razie wypadku

Acceptance Criteria:
- Szybka rejestracja i aktywacja (< 5 min total)
- Może utworzyć jedno powiernictwo z 2 odbiorcami (mama i tata)
- Może wpisać wiadomość tekstową (bez załączników)
- Może ustawić krótszy interwał (30 dni)
- Co miesiąc resetuje timer

  ---
5. Kryteria Sukcesu i Metryki

KPI Główne (z MVP)

1. Time to First Custodianship: < 5 minut od rejestracji do pierwszego skonfigurowanego powiernictwa
2. Monthly User Growth: Min. 30 nowych użytkowników/miesiąc w pierwszym roku
3. Active Custodianship Rate: >90% użytkowników ma co najmniej jedno aktywne powiernictwo

Metryki Dodatkowe

4. Reset Pattern: Ile czasu przed deadlinem średnio użytkownicy resetują (w % interwału)
   - Przykład: Jeśli średnio resetują przy 80% pozostałego czasu, może to wskazywać na zbyt długie interwały
5. Delivery Success Rate: Procent emaili skutecznie dostarczonych do odbiorców
   - Target: >95%
6. User Retention: Użytkownicy wracający po 30/90 dniach
7. Draft to Active Conversion: Procent powiernictw przekształconych z draft do active

Definicje

- Aktywne powiernictwo: Timer nie dobił do zera (next_trigger_at > now())
- Nie można przedłużyć/zresetować powiernictwa z zerowym czasem

  ---
6. Nierozwiązane Kwestie

Prawne i Compliance

- Pytanie: Czy istnieją przepisy w Polsce/UE regulujące "information delivery services" tego typu?
- Action: Konsultacja prawna przed publicznym uruchomieniem
- Uwaga: Wymagany wyraźny disclaimer: "This service is NOT a legal will or testament"
- RODO: Dane nieaktywnych kont będą automatycznie usuwane po określonym czasie. Rodzina zmarłego nie ma dedykowanej ścieżki do zamykania konta

Model Monetyzacji Paid Plan

- Status: Free tier zdefiniowany (3 powiernictwa, 10MB, 2 odbiorców)
- Pytanie: Jakie dokładnie będą limity i features paid planu? Jaka cena ($5-10/m sugerowane)?
- Action: Research konkurencji, analiza willingness to pay

Emergency Scenarios

- Problem: Co jeśli użytkownik umrze 2 dni po resecie 90-dniowego timera?
- Obecna odpowiedź: Rodzina musi poczekać 88 dni
- Pytanie follow-up: Czy to akceptowalne? Czy potrzebujemy "trusted contact" w przyszłości?
- Uwaga: To może być blocking issue dla niektórych use cases

Roadmap Kanałów Komunikacji

- MVP: Email only
- Przyszłość: SMS wzmiankowane, ale brak priorytetu
- Pytanie: Jaka kolejność rozwoju? Email → SMS → Push → Physical mail?
- Uwaga: SMS może być istotny dla osób starszych (target persona 2)

Email Deliverability Partner

- Status: Mailgun wzmiankowany jako opcja
- Pytanie: Czy to finalna decyzja czy potrzebny research alternatyw?
- Opcje: Mailgun, SendGrid, AWS SES, Postmark
- Kryteria: Reliability, delivery rate, cost, API quality

Backup Recipient Priority

- Status: Odłożone na post-MVP
- Pytanie: Jak szybko po MVP to wprowadzić? Czy to będzie w free czy paid?
- Uwaga: Może być silnym argumentem za konwersją free → paid

GTM Strategy (Odłożone, ale Krytyczne)

- Status: Użytkownik uznał za nieistotne dla projektu
- Warning: Bez GTM strategii trudno osiągnąć KPI "30 nowych użytkowników/miesiąc"
- Pytanie: Kto są early adopters? Jakie kanały dystrybucji?
- Sugestia: Wrócić do tematu przed uruchomieniem MVP

Persony i Positioning

- Status: Użytkownik uznał za nieistotne dla projektu
- Uwaga: Może być przydatne dla:
    - Copy writing (ton komunikacji)
    - Onboarding flow (różne ścieżki dla różnych person)
    - Feature prioritization
- Action: Wrócić do tematu przy projektowaniu UX

  ---
7. Podsumowanie dla Następnego Etapu

Just In Case to information delivery service, który:

- Pozwala użytkownikom przygotować wiadomości i załączniki dla bliskich
- Automatycznie wysyła je gdy użytkownik nie zresetuje timera w określonym czasie
- Działa na modelu freemium (3 powiernictwa, 10MB, 2 odbiorców za darmo)
- Skupia się na prostocie i szybkości (< 5 min onboarding)
- NIE jest testamentem ani usługą prawną - to tylko delivery service

Główne założenie: Użytkownik żyje i regularnie resetuje. Jeśli przestanie → system wysyła wiadomości.

Unikalność: Prosty, zautomatyzowany sposób na "cyfrową kapsułę czasu" bez potrzeby angażowania prawników czy skomplikowanych narzędzi.

Następne kroki:
1. Stworzenie pełnego PRD na podstawie tego podsumowania
2. Rozwiązanie kwestii prawnych (disclaimer, RODO)
3. Finalizacja wyboru email delivery partnera
4. Zaplanowanie minimalnego GTM (aby osiągnąć 30 użytkowników/m)
