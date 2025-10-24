---
description: Interaktywna sesja planistyczna do tworzenia architektury interfejsu użytkownika dla pojedynczego widoku MVP.
---

Jesteś asystentem AI, którego zadaniem jest pomoc w zaplanowaniu **architektury interfejsu użytkownika dla jednego konkretnego widoku** aplikacji MVP (Minimum Viable Product), na podstawie dostarczonych informacji.  
Twoim celem jest wygenerowanie listy **pytań i zaleceń** dotyczących projektowania tego widoku, które zostaną wykorzystane w kolejnym etapie do opracowania szczegółowego projektu UI, przepływów interakcji i komponentów Vue.js.

Prosimy o uważne zapoznanie się z poniższymi informacjami:

<product_requirements>
@docs/prd.md
</product_requirements>

<tech_stack>
@docs/tech_stack.md
</tech_stack>

<view_details>
$ARGUMENTS
</view_details>

Na podstawie dostarczonych informacji wykonaj analizę, koncentrując się na następujących aspektach:

1. Struktura komponentów Vue.js wymaganych do zbudowania danego widoku.
2. Przepływy interakcji użytkownika wewnątrz widoku oraz ewentualne przejścia do innych widoków przez Inertia.js.
3. Logika danych i stanów powiązana z backendem (np. formularze, walidacja, komunikaty o błędach).
4. Responsywność, dostępność i UX tego widoku.
5. Potencjalne wzorce projektowe i biblioteki UI dla danego kontekstu.
6. Bezpieczeństwo i uwierzytelnianie powiązane z tym widokiem (np. formularze logowania, dane wrażliwe).
7. Wydajność renderowania i optymalizacja komunikacji z Inertia.js.

Na podstawie tej analizy wygeneruj **listę 10 pytań i zaleceń** w formacie łączonym (pytanie + rekomendacja).  
Każdy punkt powinien identyfikować możliwe niejasności, problemy lub decyzje projektowe istotne dla skutecznego zaplanowania UI tego widoku.

Wynik powinien mieć poniższą strukturę:

<pytania>
1. [Treść pytania]
Rekomendacja: [Treść rekomendacji]

2. [Treść pytania]
   Rekomendacja: [Treść rekomendacji]

...
</pytania>

Nie dodawaj żadnych dodatkowych komentarzy ani wyjaśnień poza powyższym formatem.
