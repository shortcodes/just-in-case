Jesteś asystentem AI, którego zadaniem jest pomoc w zaplanowaniu architektury interfejsu użytkownika
dla MVP (Minimum Viable Product) na podstawie dostarczonych informacji. Twoim celem jest
wygenerowanie listy pytań i zaleceń, które zostaną wykorzystane w kolejnym promptowaniu do utworzenia
szczegółowej architektury UI, map podróży użytkownika i struktury nawigacji.

Prosimy o uważne zapoznanie się z poniższymi informacjami:

<product_requirements>
@docs/prd.md
</product_requirements>

<tech_stack>
@docs/tech_stack.md
</tech_stack>

Przeanalizuj dostarczone informacje, koncentrując się na aspektach istotnych dla projektowania
interfejsu użytkownika w aplikacji Vue.js z Inertia.js. Rozważ następujące kwestie:

1. Zidentyfikuj kluczowe widoki i komponenty na podstawie wymagań produktu i dostępnej struktury
   widoków.
2. Określ potencjalne przepływy użytkownika i nawigację między widokami, uwzględniając możliwości
   Vue.js i Inertia.js.
3. Rozważ komponenty UI i wzorce interakcji, które mogą być konieczne do efektywnego renderowania
   widoków.
4. Pomyśl o responsywności i dostępności interfejsu w kontekście Vue.js.
5. Oceń wymagania bezpieczeństwa i uwierzytelniania w kontekście renderowania widoków po stronie
   serwera.
6. Rozważ wszelkie konkretne biblioteki UI lub frameworki Vue.js, które mogą być korzystne dla
   projektu.
7. Przeanalizuj, jak struktura widoków wpływa na projekt UI i przepływy danych w aplikacji Vue.js z
   Inertia.js.

Na podstawie analizy wygeneruj listę 10 pytań i zaleceń w formie łączonej (pytanie + zalecenie).
Powinny one dotyczyć wszelkich niejasności, potencjalnych problemów lub obszarów, w których potrzeba
więcej informacji, aby stworzyć efektywną architekturę UI. Rozważ pytania dotyczące:

1. Hierarchia i organizacja widoków w odniesieniu do struktury komponentów Vue.js
2. Przepływy użytkownika i nawigacja wspierane przez routing Inertia.js
3. Responsywność i adaptacja do różnych urządzeń w komponentach Vue.js
4. Dostępność i inkluzywność
5. Bezpieczeństwo i autoryzacja na poziomie UI w powiązaniu z mechanizmami Inertia.js
6. Spójność designu i doświadczenia użytkownika
7. Strategia zarządzania stanem aplikacji Vue.js i synchronizacji z Inertia.js
8. Obsługa stanów błędów i wyjątków w komponentach Vue.js
9. Strategie optymalizacji wydajności renderowania widoków i komunikacji Inertia.js

Dane wyjściowe powinny mieć następującą strukturę:

<pytania>
W tym miejscu proszę wymienić pytania i zalecenia, dla przejrzystości opatrzone numerami:

Na przykład:
1. Czy na pocztówce powinno znajdować się nazwisko autora?

Rekomendacja: Tak, na pocztówce powinno znajdować się nazwisko autora.
</pytania>

Pamiętaj, że Twoim celem jest dostarczenie kompleksowej listy pytań i zaleceń, które pomogą w
stworzeniu solidnej architektury UI dla MVP, w pełni zintegrowanej z dostępnymi widokami Vue.js i
możliwościami Inertia.js. Skoncentruj się na jasności, trafności i dokładności swoich wyników. Nie
dołączaj żadnych dodatkowych komentarzy ani wyjaśnień poza określonym formatem wyjściowym.

Kontynuuj ten proces, generując nowe pytania i rekomendacje w oparciu o przekazany kontekst i
odpowiedzi użytkownika, dopóki użytkownik wyraźnie nie poprosi o podsumowanie.

Pamiętaj, aby skupić się na jasności, trafności i dokładności wyników. Nie dołączaj żadnych
dodatkowych komentarzy ani wyjaśnień poza określonym formatem wyjściowym.
⎿  Read docs/prd.md (1098 lines)
⎿  Read docs/tech_stack.md (550 lines)

● Przeanalizowałem dokumentację PRD oraz stack technologiczny projektu Just In Case. Poniżej
przedstawiam listę 10 pytań i rekomendacji dotyczących architektury UI:

Rekomendacja: Dla MVP zalecam single-page layout z lazy loadingiem komponentów kart powiernictw.
Użytkownicy mają maksymalnie 3 powiernictwa (plan free), więc paginacja nie jest konieczna. Można
użyć v-for z v-show dla filtrowania po statusie, co zapewni płynne UX bez dodatkowych requestów.

2. Czy modal edycji powiernictwa powinien być osobnym komponentem Vue z własnym stanem, czy Inertia
   modal visit z pełnym page reload?

Rekomendacja: Zalecam wykorzystanie Inertia modal visits z konfiguracją preserveState: true. Modal
będzie osobnym widokiem Inertia (Custodianship/Edit.vue), ale otworzy się jako overlay bez full page
reload. Po zapisaniu, pytanie o reset timera również w formie modalnego Inertia visit
(Custodianship/ConfirmReset.vue).

3. Czy progress bar timera powinien być real-time countdown (aktualizowany co sekundę) czy static
   snapshot aktualizowany przy każdym page load?

Rekomendacja: Static snapshot aktualizowany przy page load/component mount. Real-time countdown
zwiększyłby złożoność (setInterval, memory leaks) i nie jest krytyczny dla UX - użytkownik nie musi
widzieć odliczania w czasie rzeczywistym. Kalkulacja "X dni pozostało" w computed property Vue na
podstawie next_trigger_at przekazanego z backendu.

4. Czy formularz tworzenia powiernictwa powinien być multi-step wizard (nazwa → odbiorcy → treść →
   załączniki) czy single-page form?

Rekomendacja: Single-page form z sekcjami accordion/collapsible dla MVP. Multi-step wizard wydłuża
time-to-first-custodianship (KPI: <5min). Wszystkie pola widoczne na jednej stronie z walidacją
inline (wykorzystując Laravel Form Request errors przekazane przez Inertia) zapewni szybszy
onboarding.

5. Czy upload załączników powinien używać bezpośredniego uploadu do S3 (presigned URLs) czy przez
   Laravel backend jako proxy?

Rekomendacja: Upload przez Laravel backend jako proxy (REQ-017). Frontend: komponent Vue z <input
type="file"> + axios POST do Laravel endpoint. Backend waliduje rozmiar (max 10MB), zapisuje do S3,
zwraca metadata. Prostsze dla MVP, bezpieczniejsze (backend kontroluje access), łatwiejsze w
debugowaniu.

6. Czy przycisk "Reset Timer" powinien wymagać dodatkowego potwierdzenia (confirmation modal) czy
   działać od razu z toast notification?

Rekomendacja: Działanie od razu z toast notification. Reset timera to częsta, bezpieczna akcja (nie
usuwa danych) - dodatkowy modal spowalniałby UX. POST request przez Inertia z preserveScroll: true,
po sukcesie toast z Shadcn-vue (useToast). Modal tylko przy edycji (REQ-008).

7. Jak powinna być zorganizowana struktura komponentów Vue dla maksymalnej reużywalności przy
   zachowaniu prostoty MVP?

Rekomendacja: Struktura:
- resources/js/Pages/ - główne widoki Inertia (Dashboard, Custodianship/Create, Custodianship/Edit)
- resources/js/Components/ - reużywalne komponenty (CustodianshipCard, TimerProgressBar,
  RecipientList, AttachmentUploader)
- resources/js/Layouts/ - layout wrappers (AuthenticatedLayout z nav)
- resources/js/Composables/ - shared logic (useTimerCalculation, useFileUpload)

Shadcn-vue komponenty w resources/js/Components/ui/ (Button, Modal, Toast, etc.)

8. Czy kolory statusów timerów (zielony >30dni, żółty 7-30dni, czerwony <7dni) powinny być hardcoded
   w komponentach Vue czy pobierane z backendu jako konfiguracja?

Rekomendacja: Hardcoded w komponencie jako computed property dla MVP. Wartości progowe (30, 7) jako
constants w pliku resources/js/constants/custodianship.ts. Backend przekazuje tylko next_trigger_at
i interval_days, frontend kalkuluje kolor. Prostsze, mniej requestów. Post-MVP: jeśli progi staną
się user-configurable, przenieść do Inertia shared data.

9. Jak powinna być zaimplementowana walidacja formularzy - client-side (Vue/Vuelidate) czy tylko
   server-side (Laravel Form Request) przez Inertia?

Rekomendacja: Hybrydowa:
- Basic validation (required, email format, max length) - client-side przez proste computed/methods
  Vue dla instant feedback
- Business logic validation (limity freemium, suma załączników) - server-side przez Laravel Form
  Request
- Errory z backendu wyświetlane przez Inertia $page.props.errors w komponentach formularza

Nie używać Vuelidate - dodatkowa dependency niepotrzebna dla prostych walidacji MVP.

10. Czy empty states (brak powiernictw, brak załączników) powinny być osobnymi komponentami Vue czy
    inline conditional rendering w głównych widokach?

Rekomendacja: Osobne komponenty dla większych empty states (EmptyDashboard z ilustracją i CTA),
inline v-if/v-else dla małych (brak załączników w liście). EmptyDashboard jako
resources/js/Components/EmptyDashboard.vue z slotami dla różnych kontekstów. Zapewnia reużywalność i
zgodność z US-024 (helpful empty state z ilustracją).

> 1. Nie - mają być normalne osobne podstrony
2. WOle uniknąć modali. Ma byc strona index, show, form . Jesli chodzi o modale to tylko do
   potwierdazania są dopuszczalne chyba że robi się to już inny sposób  - jaki inline confirmation czy
   coc..
3. Dynamiczny co sekunde
4. single page
5. Jako proxy ale upload powinien być dwuetapowo.  Najpierw pliki lądują jako wgrane na serwer a
   dopiero po przycinięciu zapisz są powiązywane z powiernictwem i biblioteką media-library
6. Powinien wymagać potwierdzenia w jakiej formie.
7. Wg rekomendacji
8. Hardcoded ale nie w komponentach tylko w jakims pliku ts
9. Tylko server side. I tak nie pójdzie bez serwer side. A nie zalezy mi na tym, żeby się co znak
   resetowała.
10. Powinien być jeden komponen empty state z slotem do cta i ikoną.

● Dziękuję za precyzyjne odpowiedzi. Aktualizuję rekomendacje i przechodzę do kolejnej rundy pytań:

Rekomendacja: Oba. Breadcrumbs w górnej części strony (Dashboard → Powiernictwa → [Nazwa]) dla
kontekstu + back button w lewym górnym rogu dla szybkiego powrotu. Breadcrumbs jako komponent
Breadcrumbs.vue z props items: Array<{label: string, href?: string}>.

2. Jak powinna wyglądać inline confirmation dla przycisku "Reset Timer" - double-click,
   click-to-expand z przyciskami Confirm/Cancel, czy zmiana przycisku na "Click again to confirm" po
   pierwszym kliknięciu?

Rekomendacja: Click-to-expand pattern: po kliknięciu "Reset Timer" przycisk zmienia się w
mini-toolbar z dwoma przyciskami: "Potwierdź reset" (zielony) i "Anuluj" (szary). Auto-collapse po 5
sekundach lub po kliknięciu poza. Implementacja w komponencie ConfirmableButton.vue z emitami.

3. Czy dynamiczny countdown timera powinien zatrzymywać się gdy użytkownik zmienia tab/minimalizuje
   okno (Page Visibility API) dla optymalizacji wydajności?

Rekomendacja: Tak. Użyj composable useTimerCountdown z document.visibilityState. Gdy tab nieaktywny,
pause setInterval, gdy wraca - resync z aktualnym czasem (next_trigger_at). Zapobiega battery drain
na mobile i niepotrzebnym przeliczeniom w tle.

4. W jaki sposób powinien działać dwuetapowy upload załączników - czy po wybraniu plików powinny
   automatycznie uploadować się w tle, czy dopiero po kliknięciu dedykowanego przycisku "Upload"?

Rekomendacja: Automatyczny upload w tle po wyborze plików. UX flow: user wybiera plik → instant
upload do temp storage → progress bar → po sukcesie plik pojawia się na liście "gotowych do
przypisania". Przy zapisywaniu formularza backend przypisuje uploaded files do custodianship przez
media-library. Komponent AttachmentUploader.vue z kolejką uploadów.

5. Jak powinna być obsłużona sytuacja gdy użytkownik opuszcza stronę Create/Edit z niewysłanymi
   zmianami - browser prompt "Unsaved changes", Inertia preserveState, czy brak ostrzeżenia?

Rekomendacja: Browser prompt dla Create/Edit z niewysłanymi zmianami. Trackowanie dirty state w
composable useFormDirtyCheck (porównanie initial props z current form data). Event listener
beforeunload + Inertia onBefore hook. Prompt: "Masz niezapisane zmiany. Czy na pewno chcesz opuścić
stronę?".

6. Czy lista powiernictw na Index powinna wspierać bulk actions (zaznacz wiele → reset wszystkich,
   usuń wszystkie) czy tylko pojedyncze akcje?

Rekomendacja: Tylko pojedyncze akcje dla MVP. Bulk delete ryzykowny (REQ-010: hard delete wymaga
wpisania nazwy), bulk reset mniej krytyczny ale dodaje złożoność. Pojedyncze akcje wystarczają przy
limicie 3 powiernictw. Post-MVP: bulk reset jako "Reset all" button z modal confirmation.

7. Jak powinien być zorganizowany widok Show powiernictwa - sekcje accordion (Szczegóły, Odbiorcy,
   Załączniki, Historia), tabs, czy wszystko rozwiniete na jednej stronie?

Rekomendacja: Wszystko rozwiniete na jednej stronie z wyraźnymi sekcjami (white cards z
marginesami). Struktura: Header (nazwa + status badge) → Timer Progress → Szczegóły (treść,
interwał) → Odbiorcy (lista) → Załączniki (lista z download) → Historia resetów (tabela,
opcjonalne). Proste, bez ukrywania informacji. Accordion niepotrzebny przy małej ilości danych.

8. W jaki sposób powinny być wyświetlane błędy walidacji z backendu - toast notifications, inline
   pod polami formularza, banner na górze strony, czy kombinacja?

Rekomendacja: Inline pod polami + error banner na górze dla błędów globalnych (np. limit freemium).
Inertia $page.props.errors mapowane do pól przez komponent FormField.vue. Error banner (Shadcn
Alert) dla błędów bez konkretnego pola. Toast tylko dla sukcesów ("Powiernictwo zapisane").

9. Czy kolory statusów i progi (constants) powinny być eksportowane z jednego central config file
   czy każdy moduł (custodianship, timer) ma swój plik constans?

Rekomendacja: Jeden centralny plik resources/js/config/custodianship.ts eksportujący:
export const TIMER_THRESHOLDS = {
SAFE_DAYS: 30,
WARNING_DAYS: 7
}
export const STATUS_COLORS = {
safe: 'green',
warning: 'yellow',
danger: 'red'
}
Łatwe w utrzymaniu, single source of truth dla całej aplikacji.

10. Jak powinien być zaimplementowany komponent EmptyState - czy icon powinien być jako props
    (string nazwa ikony z Shadcn), slot, czy hardcoded set ikon dla różnych kontekstów?

Rekomendacja: Slot dla ikony + props dla tekstu. Komponent EmptyState.vue:
<template>
<div class="empty-state">
<slot name="icon"><!-- default icon --></slot>
<h3>{{ title }}</h3>
<p>{{ description }}</p>
<slot name="action"><!-- CTA button --></slot>
</div>
</template>
Props: title, description. Maksymalna elastyczność przy zachowaniu spójności layoutu.

> Ucieło pierwsze pytanie - nie wiem na co odpowiadać

● Przepraszam! Oto pełna lista pytań z rundy 2:

Rekomendacja: Oba. Breadcrumbs w górnej części strony (Dashboard → Powiernictwa → [Nazwa]) dla
kontekstu + back button w lewym górnym rogu dla szybkiego powrotu. Breadcrumbs jako komponent
Breadcrumbs.vue z props items: Array<{label: string, href?: string}>.

2. Jak powinna wyglądać inline confirmation dla przycisku "Reset Timer" - double-click,
   click-to-expand z przyciskami Confirm/Cancel, czy zmiana przycisku na "Click again to confirm" po
   pierwszym kliknięciu?

Rekomendacja: Click-to-expand pattern: po kliknięciu "Reset Timer" przycisk zmienia się w
mini-toolbar z dwoma przyciskami: "Potwierdź reset" (zielony) i "Anuluj" (szary). Auto-collapse po 5
sekundach lub po kliknięciu poza. Implementacja w komponencie ConfirmableButton.vue z emitami.

3. Czy dynamiczny countdown timera powinien zatrzymywać się gdy użytkownik zmienia tab/minimalizuje
   okno (Page Visibility API) dla optymalizacji wydajności?

Rekomendacja: Tak. Użyj composable useTimerCountdown z document.visibilityState. Gdy tab nieaktywny,
pause setInterval, gdy wraca - resync z aktualnym czasem (next_trigger_at). Zapobiega battery drain
na mobile i niepotrzebnym przeliczeniom w tle.

4. W jaki sposób powinien działać dwuetapowy upload załączników - czy po wybraniu plików powinny
   automatycznie uploadować się w tle, czy dopiero po kliknięciu dedykowanego przycisku "Upload"?

Rekomendacja: Automatyczny upload w tle po wyborze plików. UX flow: user wybiera plik → instant
upload do temp storage → progress bar → po sukcesie plik pojawia się na liście "gotowych do
przypisania". Przy zapisywaniu formularza backend przypisuje uploaded files do custodianship przez
media-library. Komponent AttachmentUploader.vue z kolejką uploadów.

5. Jak powinna być obsłużona sytuacja gdy użytkownik opuszcza stronę Create/Edit z niewysłanymi
   zmianami - browser prompt "Unsaved changes", Inertia preserveState, czy brak ostrzeżenia?

Rekomendacja: Browser prompt dla Create/Edit z niewysłanymi zmianami. Trackowanie dirty state w
composable useFormDirtyCheck (porównanie initial props z current form data). Event listener
beforeunload + Inertia onBefore hook. Prompt: "Masz niezapisane zmiany. Czy na pewno chcesz opuścić
stronę?".

6. Czy lista powiernictw na Index powinna wspierać bulk actions (zaznacz wiele → reset wszystkich,
   usuń wszystkie) czy tylko pojedyncze akcje?

Rekomendacja: Tylko pojedyncze akcje dla MVP. Bulk delete ryzykowny (REQ-010: hard delete wymaga
wpisania nazwy), bulk reset mniej krytyczny ale dodaje złożoność. Pojedyncze akcje wystarczają przy
limicie 3 powiernictw. Post-MVP: bulk reset jako "Reset all" button z modal confirmation.

7. Jak powinien być zorganizowany widok Show powiernictwa - sekcje accordion (Szczegóły, Odbiorcy,
   Załączniki, Historia), tabs, czy wszystko rozwiniete na jednej stronie?

Rekomendacja: Wszystko rozwiniete na jednej stronie z wyraźnymi sekcjami (white cards z
marginesami). Struktura: Header (nazwa + status badge) → Timer Progress → Szczegóły (treść,
interwał) → Odbiorcy (lista) → Załączniki (lista z download) → Historia resetów (tabela,
opcjonalne). Proste, bez ukrywania informacji. Accordion niepotrzebny przy małej ilości danych.

8. W jaki sposób powinny być wyświetlane błędy walidacji z backendu - toast notifications, inline
   pod polami formularza, banner na górze strony, czy kombinacja?

Rekomendacja: Inline pod polami + error banner na górze dla błędów globalnych (np. limit freemium).
Inertia $page.props.errors mapowane do pól przez komponent FormField.vue. Error banner (Shadcn
Alert) dla błędów bez konkretnego pola. Toast tylko dla sukcesów ("Powiernictwo zapisane").

9. Czy kolory statusów i progi (constants) powinny być eksportowane z jednego central config file
   czy każdy moduł (custodianship, timer) ma swój plik constants?

Rekomendacja: Jeden centralny plik resources/js/config/custodianship.ts eksportujący:
export const TIMER_THRESHOLDS = {
SAFE_DAYS: 30,
WARNING_DAYS: 7
}
export const STATUS_COLORS = {
safe: 'green',
warning: 'yellow',
danger: 'red'
}
Łatwe w utrzymaniu, single source of truth dla całej aplikacji.

10. Jak powinien być zaimplementowany komponent EmptyState - czy icon powinien być jako props
    (string nazwa ikony z Shadcn), slot, czy hardcoded set ikon dla różnych kontekstów?

Rekomendacja: Slot dla ikony + props dla tekstu. Komponent EmptyState.vue:
<template>
<div class="empty-state">
<slot name="icon"><!-- default icon --></slot>
<h3>{{ title }}</h3>
<p>{{ description }}</p>
<slot name="action"><!-- CTA button --></slot>
</div>
</template>
Props: title, description. Maksymalna elastyczność przy zachowaniu spójności layoutu.

> dalej nie mam . tuż przed drugim pytaniem jest tylko rekomendacja do pytania którego nie ma

● Masz rację, przepraszam! Oto pytanie 1, które się gdzieś zgubiło:

Rekomendacja: Oba. Breadcrumbs w górnej części strony (Dashboard → Powiernictwa → [Nazwa]) dla
kontekstu + back button w lewym górnym rogu dla szybkiego powrotu. Breadcrumbs jako komponent
Breadcrumbs.vue z props items: Array<{label: string, href?: string}>.

2. Jak powinna wyglądać inline confirmation dla przycisku "Reset Timer" - double-click,
   click-to-expand z przyciskami Confirm/Cancel, czy zmiana przycisku na "Click again to confirm" po
   pierwszym kliknięciu?

Rekomendacja: Click-to-expand pattern: po kliknięciu "Reset Timer" przycisk zmienia się w
mini-toolbar z dwoma przyciskami: "Potwierdź reset" (zielony) i "Anuluj" (szary). Auto-collapse po 5
sekundach lub po kliknięciu poza. Implementacja w komponencie ConfirmableButton.vue z emitami.

3. Czy dynamiczny countdown timera powinien zatrzymywać się gdy użytkownik zmienia tab/minimalizuje
   okno (Page Visibility API) dla optymalizacji wydajności?

Rekomendacja: Tak. Użyj composable useTimerCountdown z document.visibilityState. Gdy tab nieaktywny,
pause setInterval, gdy wraca - resync z aktualnym czasem (next_trigger_at). Zapobiega battery drain
na mobile i niepotrzebnym przeliczeniom w tle.

4. W jaki sposób powinien działać dwuetapowy upload załączników - czy po wybraniu plików powinny
   automatycznie uploadować się w tle, czy dopiero po kliknięciu dedykowanego przycisku "Upload"?

Rekomendacja: Automatyczny upload w tle po wyborze plików. UX flow: user wybiera plik → instant
upload do temp storage → progress bar → po sukcesie plik pojawia się na liście "gotowych do
przypisania". Przy zapisywaniu formularza backend przypisuje uploaded files do custodianship przez
media-library. Komponent AttachmentUploader.vue z kolejką uploadów.

5. Jak powinna być obsłużona sytuacja gdy użytkownik opuszcza stronę Create/Edit z niewysłanymi
   zmianami - browser prompt "Unsaved changes", Inertia preserveState, czy brak ostrzeżenia?

Rekomendacja: Browser prompt dla Create/Edit z niewysłanymi zmianami. Trackowanie dirty state w
composable useFormDirtyCheck (porównanie initial props z current form data). Event listener
beforeunload + Inertia onBefore hook. Prompt: "Masz niezapisane zmiany. Czy na pewno chcesz opuścić
stronę?".

6. Czy lista powiernictw na Index powinna wspierać bulk actions (zaznacz wiele → reset wszystkich,
   usuń wszystkie) czy tylko pojedyncze akcje?

Rekomendacja: Tylko pojedyncze akcje dla MVP. Bulk delete ryzykowny (REQ-010: hard delete wymaga
wpisania nazwy), bulk reset mniej krytyczny ale dodaje złożoność. Pojedyncze akcje wystarczają przy
limicie 3 powiernictw. Post-MVP: bulk reset jako "Reset all" button z modal confirmation.

7. Jak powinien być zorganizowany widok Show powiernictwa - sekcje accordion (Szczegóły, Odbiorcy,
   Załączniki, Historia), tabs, czy wszystko rozwiniete na jednej stronie?

Rekomendacja: Wszystko rozwiniete na jednej stronie z wyraźnymi sekcjami (white cards z
marginesami). Struktura: Header (nazwa + status badge) → Timer Progress → Szczegóły (treść,
interwał) → Odbiorcy (lista) → Załączniki (lista z download) → Historia resetów (tabela,
opcjonalne). Proste, bez ukrywania informacji. Accordion niepotrzebny przy małej ilości danych.

8. W jaki sposób powinny być wyświetlane błędy walidacji z backendu - toast notifications, inline
   pod polami formularza, banner na górze strony, czy kombinacja?

Rekomendacja: Inline pod polami + error banner na górze dla błędów globalnych (np. limit freemium).
Inertia $page.props.errors mapowane do pól przez komponent FormField.vue. Error banner (Shadcn
Alert) dla błędów bez konkretnego pola. Toast tylko dla sukcesów ("Powiernictwo zapisane").

9. Czy kolory statusów i progi (constants) powinny być eksportowane z jednego central config file
   czy każdy moduł (custodianship, timer) ma swój plik constants?

Rekomendacja: Jeden centralny plik resources/js/config/custodianship.ts.

10. Jak powinien być zaimplementowany komponent EmptyState - czy icon powinien być jako props
    (string nazwa ikony), slot, czy hardcoded set ikon dla różnych kontekstów?

Rekomendacja: Slot dla ikony + props dla tekstu (title, description). Maksymalna elastyczność.

> 1. Breadcrumbs powinien zawierać Home, Powiernictwa (index), Konkretną nazwe powiernictwa lub Nowe
     powiernictwo, jeli jest edycja to nazweę a w nastepnej sekcji informacje że to Edycja.

2. Ok może być rekomendacja
3. Zgodnie z rekomandacją.
4. Wg rekomandacji
5. Może być wg rekomendacji
6. Wg remoendacji
7. wg rekomendacji
8. Bez baneru na górze ale inline i powinny to być form.errors.i nazwa pola bo chce korzystać z
   useFOrm
9. wg rekomendacji
10. wg rekomendacji

● Dziękuję za precyzyjne odpowiedzi. Aktualizuję rekomendacje i przechodzę do kolejnej rundy pytań:

Rekomendacja: Tylko wcześniejsze elementy klikalalne, obecna strona jako plain text z innym kolorem
(gray-500). Przykład: Home (link) > Powiernictwa (link) > Moje hasła (text) > Edycja (text).
Standardowy UX pattern, jasno pokazuje gdzie użytkownik się znajduje.

2. Jak powinna być obsłużona sytuacja gdy timer countdown osiągnie zero podczas gdy użytkownik ma
   otwartą stronę Show/Index - automatyczny refresh strony, banner z komunikatem "Timer wygasł", czy
   tylko wizualna zmiana koloru na czerwony?

Rekomendacja: Wizualna zmiana (progress bar → 0, kolor → czerwony) + toast notification "Timer dla
'[nazwa]' wygasł. Wiadomość zostanie wkrótce wysłana.". Brak auto-refresh (może być irytujące). User
może ręcznie odświeżyć aby zobaczyć status completed. Composable useTimerCountdown emituje event
timer:expired.

3. Czy formularz Create/Edit powinien mieć jeden przycisk "Zapisz" (który tworzy jako active jeśli
   email zweryfikowany, draft jeśli nie) czy dwa przyciski "Zapisz jako draft" i "Zapisz i aktywuj"?

Rekomendacja: Jeden przycisk "Zapisz" z inteligentną logiką backendową. Jeśli email niezweryfikowany
→ auto-draft + toast "Powiernictwo zapisane jako draft. Aktywuj email aby uruchomić timer.". Jeśli
zweryfikowany → active + toast "Powiernictwo aktywne. Timer uruchomiony.". Prostszy UX, mniej
decyzji dla użytkownika.

4. Czy pole "Treść wiadomości" w formularzu powinno być prosty textarea czy rich text editor (np.
   Tiptap dla Vue)?

Rekomendacja: Prosty textarea dla MVP. Rich text dodaje złożoność (sanityzacja HTML, security XSS,
rendering w email). Treść wiadomości to plain text z zachowaniem line breaks (nl2br w emailu).
Post-MVP: markdown support jako kompromis (bezpieczniejszy niż HTML, bardziej ekspresyjny niż plain
text).

5. Jak powinien wyglądać komponent RecipientList w formularzu - każdy odbiorca jako osobny wiersz z
   input email + przycisk "Usuń", czy bardziej zaawansowany UI z chipami/tags?

Rekomendacja: Każdy odbiorca jako wiersz: <input type="email"> + icon button "Usuń" (X). Przycisk "+
Dodaj odbiorcę" na dole (disabled gdy 2/2). Prosty, czytelny, mobile-friendly. Chipy/tags
ładniejsze ale trudniejsze w edycji (trzeba kliknąć chip → otworzyć modal → edytować). Lista wierszy
bardziej intuicyjna.

6. Czy AttachmentUploader powinien wspierać drag & drop oprócz file input browse, czy tylko file
   input dla uproszczenia?

Rekomendacja: Oba. Drag & drop znacząco poprawia UX (szczególnie na desktop), a implementacja w Vue
prosta (directives @drop, @dragover.prevent). Komponent z wizualną strefą dropzone + fallback file
input. Library opcjonalna, można zrobić native.

7. Jak powinna być wyświetlana lista załączników w formularzu - tylko nazwy plików z rozmiarem, czy
   również preview/thumbnail dla obrazów?

Rekomendacja: Nazwa + rozmiar + ikona typu pliku (opcjonalnie preview dla obrazów). Struktura
wiersza: [icon/thumb] filename.pdf (1.2 MB) [przycisk Usuń]. Preview tylko dla images (jpg, png)
jako mały thumbnail 40x40px. Inne typy: generic ikona (PDF icon, DOC icon). Balance między
informacyjnością a prostotą.

8. Czy pole "Interwał czasowy" powinno być dropdown select z predefiniowanymi wartościami (30, 60,
   90, 180, 365 dni) czy custom input z możliwością wpisania dowolnej liczby dni?

Rekomendacja: Dropdown select z predefiniowanymi wartościami dla MVP. PRD sugeruje konkretne
wartości (REQ-006: "np. 30, 90, 180 dni"). Custom input dodaje ryzyko błędów (user wpisuje "0" lub
"9999"). Post-MVP: custom input jako opcja advanced z walidacją min/max. Select options z labels:
"30 dni (miesiąc)", "90 dni (kwartał)", etc.

9. Czy w widoku Show powiernictwa przycisk "Edytuj" powinien być prominentny primary button, czy
   secondary/outline button aby nie przyciągać uwagi od głównej akcji "Reset Timer"?

Rekomendacja: "Reset Timer" jako primary button (green, duży), "Edytuj" jako secondary/outline
button (gray). Hierarchia akcji: reset > edit > delete. Delete jako danger outline button (red
outline, white bg). Układ: [Reset Timer (primary)] [Edytuj (secondary)] [Usuń (danger outline)].

10. Czy Historia resetów w widoku Show powinna być od razu widoczna (może być długa lista) czy
    schowana pod collapsible section "Pokaż historię resetów (X wpisów)"?

Rekomendacja: Collapsible dla MVP (opcjonalna w US-019). Domyślnie zwinięta z headerem "Historia
resetów (15)" + icon chevron. Kliknięcie rozwija tabelę. Gdy brak historii: brak sekcji lub "Brak
resetów". Oszczędza przestrzeń, nie rozpraszając od kluczowych informacji (timer, treść, odbiorcy).

> 1. wg rekomendacji
2. User przedewszystkim nie powinien miec mozliwoci resetu jak dojdzie do zera. I nie powionny jakies
   toasty ić  - nic się nie powinno stać. Status co najwyzej na pending
3. wg rekomendacji
4. Tu rozumiem ze nie html ale moż by dać taki mocno kontrolowany edytor? zeby user nic nie wrzucił
   swojego? I prosty - markdown jest jednak dla programistów nie normalnych ludzi. może być np edytor,
   który zamienia na markdown pod spodem.
5. wg rekomendacji
6. wg rekomendacji
7. wg rekomendacji z tym że bez preview dla obrazków
8. może jest jaki selektor który będzie takie co wybierał a vue już pod spodem sobie przekształci to
   na iso i dobrym pomysłem w MVP bedzie ograniczenie zakresu od do. MOże gdzies w configu w larvelu
9. primary
10. wg rekomendacji

● Dziękuję za odpowiedzi. Aktualizuję rekomendacje i przechodzę do kolejnej rundy pytań:

Rekomendacja: Tiptap z ograniczonymi opcjami formatowania. Toolbar tylko: Bold, Italic, Lista
punktowana, Lista numerowana, Link. Output jako markdown pod spodem. Tiptap łatwo konfigurowalny
(włącz tylko wybrane extensions), Vue 3 compatible, TypeScript support. Quill starszy, słabsze
TypeScript types. Config w resources/js/components/RichTextEditor.vue z props modelValue (v-model
support).

2. Czy selektor interwału czasowego powinien być oparty na dniach (30, 60, 90) czy pozwalać na wybór
   jednostki (dni/tygodnie/miesiące) + liczby, co backend przekształci na dni?

Rekomendacja: Dropdown z predefiniowanymi opcjami w ludzkim języku, backend trzyma dni. Options: "1
miesiąc (30 dni)", "2 miesiące (60 dni)", "3 miesiące (90 dni)", "6 miesięcy (180 dni)", "1 rok (365
dni)". Value w option: liczba dni (30, 60, 90, 180, 365). Config w Laravel
config/custodianship.php:
'allowed_intervals' => [30, 60, 90, 180, 365],
'min_interval' => 30,
'max_interval' => 365,
Frontend pobiera z Inertia shared data dla walidacji.

3. Czy przycisk "Reset Timer" na Index (lista powiernictw) powinien wymagać tej samej inline
   confirmation (expand → Potwierdź/Anuluj) jak w Show, czy może działać od razu z inline feedback?

Rekomendacja: Tej samej inline confirmation dla spójności UX. Komponent ConfirmableButton.vue
reużywany w obu miejscach. Na liście może być wizualnie mniejszy (secondary size) ale mechanizm
identyczny. Konsystencja > optymalizacja kilku kliknięć.

4. Jak powinien być wyświetlany status powiernictwa gdy timer osiągnie zero (pending/expired) -
   badge "Oczekuje na wysłanie", zmiana całej karty na inny kolor, czy oba?

Rekomendacja: Oba. Status badge zmienia się na "Oczekuje na wysłanie" (orange/amber) + cała
karta/sekcja ma subtelne amber background (bg-amber-50) + border-amber-200. Progress bar na 0 z
czerwonym kolorem. Przycisk "Reset Timer" disabled z tooltipem "Nie można zresetować - wiadomość
zostanie wkrótce wysłana". Wyraźna ale nie agresywna sygnalizacja.

5. Czy FormField komponent powinien automatycznie bindować się do form.errors przez naming
   convention (prop name="email" → auto-pobiera form.errors.email) czy wymaga explicit props error?

Rekomendacja: Explicit props error dla elastyczności. Component:
<FormField
label="Email"
name="email"
:error="form.errors.email"
v-model="form.email"
/>
Auto-binding przez convention ładny ale może być mylący (magiczne zachowanie). Explicit = jasne data
flow. Helper composable useFormErrors może uprościć: const errors = useFormErrors(form).

6. Czy lista powiernictw na Index powinna pokazywać skróconą treść wiadomości (np. pierwsze 100
   znaków) czy tylko metadane (nazwa, odbiorcy, timer)?

Rekomendacja: Tylko metadane dla MVP. Karta pokazuje: Nazwa (h3), Status badge, Timer progress bar z
tekstem "X dni pozostało", Lista odbiorców (avatary/inicjały + emaile), Akcje (Reset, Edytuj,
Usuń). Treść wiadomości widoczna dopiero w Show. Lista bardziej scannable, mniej clutteru. Post-MVP:
toggle "Pokaż szczegóły" rozwijający treść.

7. Jak powinna być obsłużona responsywność listy powiernictw - cards w grid (desktop) → stack
   (mobile), czy zawsze stack layout?

Rekomendacja: Zawsze stack layout (jedna kolumna) dla prostoty MVP. Desktop: szersze karty z
marginesami, mobile: full-width karty. Grid (2-3 kolumny) ładniejszy na desktop ale komplikuje
layout (jak zmieścić progress bar, akcje, odbiorców). Stack uniwersalny, działa wszędzie. Tailwind:
space-y-4 dla odstępów.

8. Czy navigation menu główne aplikacji powinno być top navbar, side sidebar, czy hamburger menu
   (mobile-first)?

Rekomendacja: Top navbar z hamburger menu na mobile. Desktop: full navbar z linkami (Dashboard,
Powiernictwa, Profil), mobile: hamburger → slide-in menu. Standardowy pattern, Laravel Breeze
dostarcza gotowy layout. Struktura:
- AuthenticatedLayout.vue - wrapper z navbar
- Desktop: horizontal links
- Mobile (<768px): hamburger icon → overlay menu
  Sidebar zbyt heavy dla prostej aplikacji (3-5 głównych sekcji).

9. Czy po zapisaniu powiernictwa (Create) użytkownik powinien być przekierowany na Show nowo
   utworzonego powiernictwa, Index (lista), czy Dashboard?

Rekomendacja: Przekierowanie na Show nowo utworzonego powiernictwa. User chce zobaczyć rezultat
swojej pracy + ma szybki dostęp do akcji (Reset, Edytuj jeśli coś poprawić). Toast: "Powiernictwo
utworzone pomyślnie". Flow: Create → Submit → Show (z sukcesem) → user może kliknąć breadcrumb
"Powiernictwa" aby wrócić do Index.

10. Czy pole "Nazwa powiernictwa" w formularzu powinno mieć placeholder z przykładem (np. "np. Hasła
    do kont bankowych") czy pozostać puste z tylko labelem "Nazwa powiernictwa"?

Rekomendacja: Placeholder z przykładem. Label: "Nazwa powiernictwa", Placeholder: "np. Hasła do kont
bankowych, Dostęp do kryptowalut". Pomaga użytkownikowi zrozumieć kontekst i zainspirować do
konkretnej nazwy. Szczególnie ważne dla first-time users (US-006, US-007: onboarding <5min). Nie
jest intrusive, łatwo nadpisać.

> 1. Tip tam jest płatny - nie ma czego darmowego? Albo z dużym darmowym limitem?
2. wg rekomendacji niech bedzie dla mvp
3. wg rekomendacji po potwierdzeniu powinien być widoczny już zresetowany licznik
4. wg rekomendacji
5. https://inertiajs.com/forms i need this
6. wg rekomendacji
7. wg rekomendacji
8. wg rekomendacji
   ⎿  Interrupted · What should Claude do instead?

> 1. Tip tam jest płatny - nie ma czego darmowego? Albo z dużym darmowym limitem?
2. wg rekomendacji niech bedzie dla mvp
3. wg rekomendacji po potwierdzeniu powinien być widoczny już zresetowany licznik
4. wg rekomendacji
5. https://inertiajs.com/forms i need this
6. wg rekomendacji
7. wg rekomendacji
8. wg rekomendacji
   9.wg rekomendacji
10. wg rekomendacji

● Fetch(https://github.com/ueberdosis/tiptap/blob/main/LICENSE.md)
⎿  Received 185.8KB (200 OK)

● Świetna wiadomość - Tiptap jest całkowicie darmowy i open source na licencji MIT! Możesz go używać
bez limitów.

Rozumiem wszystkie odpowiedzi. Notuję:
- Tiptap jako rich text editor (MIT license, darmowy)
- FormField z Inertia useForm() - auto-binding errors przez form.errors
- Po potwierdzeniu reset - licznik natychmiast się aktualizuje (optimistic UI lub reload)

Przechodzę do kolejnej rundy pytań:

Rekomendacja: Osobne komponenty z logką wbudowaną. Przykład: TextInput.vue, TextArea.vue, Select.vue
- każdy z props: label, name, modelValue, error, placeholder. Używane jako:
  <TextInput
  label="Nazwa powiernictwa"
  v-model="form.name"
  :error="form.errors.name"
  placeholder="np. Hasła do kont bankowych"
  />
  Bardziej type-safe, lepsze auto-complete w IDE. Laravel Breeze z Inertia już dostarcza takie
  komponenty - check resources/js/Components/.

2. Czy po udanym resecie timera (inline confirmation → potwierdź) powinien być optimistic update
   (instant UI change) czy pełny reload przez Inertia?

Rekomendacja: Optimistic update + Inertia reload w tle. Flow:
1. User klika "Potwierdź reset"
2. Instant: progress bar resetuje się do 100%, timer pokazuje nowy next_trigger_at
3. W tle: POST request przez router.post() z preserveScroll: true
4. Jeśli sukces: toast, dane już zaktualizowane
5. Jeśli błąd: rollback do poprzedniego stanu + error toast

Najlepsze UX (instant feedback) + pewność że dane zsynchronizowane z backendem.

3. Czy Timer Progress Bar powinien pokazywać procent liczbowo (np. "75% czasu pozostało") czy tylko
   wizualnie przez fill bar + tekst "X dni pozostało"?

Rekomendacja: Tylko wizualnie fill bar + "X dni pozostało". Procent liczbowy mniej intuicyjny (co
oznacza 75%? - trzeba myśleć). "45 dni pozostało z 90" od razu jasne. Progress bar wizualna
reprezentacja. Format: {days_left} {unit} pozostało z {total} gdzie unit = "dni/dzień" z proper
pluralizacją.

4. Jak powinna być zaimplementowana pluralizacja dla polskiego ("1 dzień", "2 dni", "5 dni") -
   helper function, i18n library, czy hardcoded if/else?

Rekomendacja: Helper function w resources/js/utils/pluralize.ts:
export const pluralizeDays = (count: number): string => {
if (count === 1) return 'dzień'
if (count % 10 >= 2 && count % 10 <= 4 && (count % 100 < 10 || count % 100 >= 20)) return 'dni'
return 'dni'
}
Dla MVP wystarczy. Post-MVP: jeśli aplikacja multi-language → vue-i18n. Hardcoded if/else w każdym
komponencie = duplikacja.

5. Czy AttachmentUploader powinien pokazywać globalny progress bar dla wszystkich uploadów (suma)
   czy osobny progress dla każdego pliku?

Rekomendacja: Osobny progress dla każdego pliku. Lista uploadów z wierszami:
[icon] filename.pdf [====70%====] 1.2 MB [X]
[icon] image.jpg [===100%===] 0.5 MB [✓]
Użytkownik widzi status każdego pliku. Globalny progress mniej informatywny (nie wiadomo który plik
się uploaduje). Komponent trackuje array uploads: [{name, progress, status}].

6. Czy w widoku Show powiernictwa sekcja "Odbiorcy" powinna pokazywać tylko emaile czy również
   dodatkowe info (data dodania, status weryfikacji emaila)?

Rekomendacja: Tylko emaile dla MVP. Lista:
1. jan.kowalski@example.com
2. anna.nowak@example.com
   Prosty, czytelny. Status weryfikacji email niepotrzebny (REQ-015: brak weryfikacji tożsamości
   odbiorcy). Data dodania nice-to-have ale nie krytyczna. Post-MVP: można dodać metadata.

7. Czy przycisk "Usuń powiernictwo" powinien być zawsze widoczny w Show (np. w górnym menu akcji)
   czy na końcu strony w sekcji "Danger Zone"?

Rekomendacja: Na końcu strony w sekcji "Danger Zone". Struktura Show:
- Top: akcje częste (Reset Timer, Edytuj)
- Middle: content (szczegóły, odbiorcy, załączniki)
- Bottom: Danger Zone z big red "Usuń powiernictwo" button

Minimalizuje przypadkowe kliknięcia. Standardowy pattern (GitHub settings, etc.). Delete to
destrukcyjna akcja - powinna być oddzielona od primary actions.

8. Czy modal potwierdzenia usunięcia powiernictwa (REQ-010) powinien być Inertia modal visit czy
   client-side Vue modal (Shadcn Dialog)?

Rekomendacja: Client-side Vue modal (Shadcn Dialog) dla lepszego UX. Delete confirmation nie
potrzebuje server-side renderowania. Modal zawiera:
- Checkbox "Rozumiem konsekwencje"
- Text input "Wpisz nazwę powiernictwa"
- Przyciski: Anuluj (secondary) + Usuń (danger primary)

Po potwierdzeniu: router.delete() do backendu. Szybsze (brak page load), bardziej responsive.

9. Czy Dashboard powinien mieć welcome banner dla zalogowanego usera ("Witaj, Jan!") czy od razu
   listę powiernictw bez personalizacji?

Rekomendacja: Od razu lista powiernictw z prostym headerem "Moje powiernictwa" + przycisk "Nowe
powiernictwo". Welcome banner zajmuje miejsce bez value. Personalizacja nice-to-have ale nie
krytyczna dla MVP. Focus na funkcjonalność > ozdobniki. Jeśli brak powiernictw → EmptyState z CTA.

10. Czy filtr statusów na Index (Draft, Aktywne, Wysłane, Błąd) powinien być dropdown select, button
    group (tabs), czy checkboxes z możliwością multi-select?

Rekomendacja: Button group jako tabs dla single-select. Layout:
[Wszystkie] [Draft] [Aktywne] [Wysłane] [Błąd]
Active tab z primary color. Szybkie przełączanie jednym kliknięciem. Multi-select (checkboxes) zbyt
skomplikowany dla prostego filtrowania. Dropdown ukrywa opcje - tabs wszystko widoczne. Komponenty
Shadcn: Tabs.

> 1. wg rekomendacji
2. wg rekomendacji tylko wyskakujace okeienko jak błąd - jak sukces to nic.
3. wg rekomendacji
4. MVP bedzie tylko po angielsku
5. wg rekomendacji ale powinnien gdzie być info o ograniczeniu i sumie wagi wszystkich plików max 10MB
   i jak załączniki przekroczą to sie nie da zapisac (walidation error)
6. Email i data dodanie - żadnej weryfikacji ( to se wymyliłe)
7. wg rekomendacji i to z modalem czy na pewno.
8. wg rekomendacji
9. wg rekomendacji
10. to sobie pomińmy w mvp będą 3 powiernictwa

● Dziękuję za odpowiedzi. Notuję:
- MVP po angielsku (bez pluralizacji PL)
- Optimistic update bez toast przy sukcesie, tylko error toast
- AttachmentUploader z info o limicie i sumie
- Odbiorcy: email + data dodania
- Usunięcie z modalem potwierdzenia
- Brak filtrów statusów (max 3 powiernictwa)

Przechodzę do kolejnej rundy pytań:

Rekomendacja: Dynamic progress "X MB / 10 MB used" z progress bar wizualizującym zapełnienie.
Aktualizacja po każdym upload/delete. Użytkownik od razu widzi ile miejsca pozostało. Komponent
trackuje totalSize jako computed sum z uploads.reduce(). Kolor progress bar: zielony <7MB, żółty
7-9MB, czerwony >9MB.

2. Czy w widoku Show sekcja "Odbiorcy" powinna pokazywać datę w pełnym formacie (January 15, 2025
   14:30) czy relative time (Added 2 days ago)?

Rekomendacja: Relative time z tooltip pokazującym pełną datę. "Added 2 days ago" bardziej
human-friendly. Hover → tooltip "January 15, 2025 14:30". Library: dayjs z plugin relativeTime (już
w tech stack). Format: dayjs(date).fromNow().

3. Czy modal potwierdzenia usunięcia powinien wymagać wpisania pełnej nazwy powiernictwa
   (case-sensitive) czy case-insensitive matching wystarczy?

Rekomendacja: Case-insensitive matching dla lepszego UX. Użytkownik może napisać małymi literami
"moje hasła" zamiast "Moje Hasła". Walidacja: input.toLowerCase() ===
custodianship.name.toLowerCase(). Checkbox + text input nadal wymagane (REQ-010), ale bez frustracji
z caps lock.

4. Czy przycisk "Edytuj" w widoku Show powinien przekierowywać na osobną stronę Edit czy może być
   inline editing (kliknij → pola stają się editable)?

Rekomendacja: Osobna strona Edit (zgodnie z punktem 2 z pierwszej rundy - osobne podstrony). Flow:
Show → klik Edit → Edit page (prefilled form) → Save → redirect Show. Inline editing ładniejszy ale
komplikuje state management i jest niekompatybilny z decyzją o osobnych podstronach.

5. Czy navigation w aplikacji powinna mieć indicator "aktywnej strony" (np. Dashboard link
   highlighted gdy jesteś na Dashboard)?

Rekomendacja: Tak, active state dla navigation links. Tailwind classes: active link ma bg-gray-100
(light mode) i text-primary font color. Implementacja przez Inertia usePage().url matching:
:class="{ 'bg-gray-100 text-primary': $page.url === '/dashboard' }"
Standardowy UX pattern, pomaga orientacji użytkownika.

6. Czy formularz Edit powinien mieć przycisk "Anuluj" przekierowujący do Show, czy tylko
   breadcrumb/back button wystarczy do nawigacji?

Rekomendacja: Oba. Przycisk "Anuluj" (secondary, obok "Save") dla wygody + breadcrumb dla kontekstu.
"Anuluj" sprawdza dirty state - jeśli zmiany niewysłane → confirmation "Discard changes?". Jeśli
brak zmian → bezpośredni redirect do Show. Breadcrumb zawsze dostępny jako alternatywna nawigacja.

7. Czy Timer Progress Bar powinien mieć tooltip pokazujący exact timestamp "next trigger" przy
   hover, czy tylko static text below progress bar?

Rekomendacja: Oba. Static text pod progress bar: "45 days remaining" dla szybkiego overview. Hover
na progress bar → tooltip: "Timer expires on January 30, 2025 14:30". Dodatkowy kontekst dla
użytkowników którzy chcą wiedzieć dokładną datę. Tooltip z Shadcn Tooltip component.

8. Czy po kliknięciu "Reset Timer" (i potwierdzeniu) powinna być krótka animacja progress bar
   resetting (fill 45% → 100% z transition) czy instant jump do 100%?

Rekomendacja: Krótka animacja (300ms transition). CSS: transition: width 0.3s ease-out. Wizualne
potwierdzenie że akcja się powiodła. Instant jump może wyglądać jak bug (czy naprawdę się
zresetowało?). Animacja = feedback bez intrusywnego toast.

9. Czy attachment file size powinien być displayed w KB/MB z fixed precision (1.25 MB) czy dynamic
   (1 MB, 1.2 MB, 1.25 MB zależnie od rozmiaru)?

Rekomendacja: Dynamic z 2 decimal places tylko gdy potrzebne. Helper function:
formatFileSize(bytes: number): string {
if (bytes < 1024) return bytes + ' B'
if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB'
return (bytes / 1048576).toFixed(2) + ' MB'
}
"1.5 MB" bardziej czytelne niż "1.50 MB". "850 KB" zamiast "0.83 MB" dla małych plików.

10. Czy w sekcji Danger Zone (Show powiernictwa) powinien być również przycisk "Deactivate" (active
    → draft) oprócz "Delete", czy tylko Delete dla MVP?

Rekomendacja: Tylko Delete dla MVP. Deactivate użyteczne (user chce wstrzymać timer bez usuwania)
ale dodaje complexity:
- Czy deactivate zachowuje current timer state czy resetuje?
- Jak re-activate?
- Czy to częsty use case?

Post-MVP feature. MVP focus: Create → Edit → Delete flow. Użytkownik może Edit i zmienić interval na
bardzo długi jako workaround.

──────────────────────────────────────────────────────────────────────────────────────────────────────────
> 1. wg rekomendacji
2. Relative i po najechaniu tooltip (vue-tippy) z datą
   3.wg rekomendacji
4. wg rekomendacji
5. wg rekomendacji
6. wg rekomendacji
7. wg rekomendacji
8. wg rekomendacji
9. wg rekomendacji
10. ma po mvp
