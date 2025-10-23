---
description: Implementacja widoku frontendu
---
Twoim zadaniem jest zaimplementowanie widoku frontendu w oparciu o podany plan implementacji i zasady implementacji. Twoim celem jest stworzenie szczegółowej i dokładnej implementacji, która jest zgodna z dostarczonym planem, poprawnie reprezentuje strukturę komponentów używając zamokowanych danych wewnątrz komponentu obsługuje wszystkie określone interakcje użytkownika.

Najpierw przejrzyj plan implementacji:

<implementation_plan>
Na postawie $ARGUMENTS spróbuj zidentyfikować położenie planu. Jeśli się nie da  - zapytaj o niego.
</implementation_plan>

Teraz przejrzyj zasady implementacji:

<implementation_rules>
@docs/planning_ui/shadcn-helper.mdc
</implementation_rules>

Wdrażaj plan zgodnie z następującym podejściem:

<implementation_approach>
Twórz tymczasowego route, który będziesz mi na końcu każdego kroku podawał abym mógł wejść i zobaczyc jak wygląda widok.Później będę mógł Ci powiedzieć jakieś uwagi i będziemy ten widok mielić aż wypracujemy zadowalający efekt. Kiedy nie będe miał uwag zakończymy prace i usuń tymczasowy route.
</implementation_approach>

Dokładnie przeanalizuj plan wdrożenia i zasady. Zwróć szczególną uwagę na strukturę komponentów i interakcje użytkownika opisane w planie.

Wykonaj następujące kroki, aby zaimplementować widok frontendu:

1. Struktura komponentów:
    - Zidentyfikuj wszystkie komponenty wymienione w planie wdrożenia.
    - Utwórz hierarchiczną strukturę tych komponentów.
    - Upewnij się, że obowiązki i relacje każdego komponentu są jasno zdefiniowane.

2. Interakcje użytkownika:
    - Wylistuj wszystkie interakcje użytkownika określone w planie wdrożenia.
    - Wdróż obsługi zdarzeń dla każdej interakcji.
    - Upewnij się, że każda interakcja wyzwala odpowiednią akcję lub zmianę stanu.

3. Zarządzanie stanem:
    - Zidentyfikuj wymagany stan dla każdego komponentu.
    - Zaimplementuj zarządzanie stanem przy użyciu odpowiedniej metody (stan lokalny, custom hook, stan współdzielony).
    - Upewnij się, że zmiany stanu wyzwalają niezbędne ponowne renderowanie.

4. Stylowanie i layout:
    - Zastosuj określone stylowanie i layout, jak wspomniano w planie wdrożenia.
    - Zapewnienie responsywności, jeśli wymaga tego plan.

5. Obsługa błędów i przypadki brzegowe:
    - Wdrożenie obsługi błędów dla wywołań API i interakcji użytkownika.
    - Rozważ i obsłuż potencjalne edge case'y wymienione w planie.

6. Optymalizacja wydajności:
    - Wdrożenie wszelkich optymalizacji wydajności określonych w planie lub zasadach.
    - Zapewnienie wydajnego renderowania i minimalnej liczby niepotrzebnych ponownych renderowań.

7. Testowanie:
    - Jeśli zostało to określone w planie, zaimplementuj testy jednostkowe dla komponentów i funkcji.
    - Dokładnie przetestuj wszystkie interakcje użytkownika i integracje API.

W trakcie całego procesu implementacji należy ściśle przestrzegać dostarczonych zasad implementacji. Zasady te mają pierwszeństwo przed wszelkimi ogólnymi najlepszymi praktykami, które mogą być z nimi sprzeczne.

Upewnij się, że twoja implementacja dokładnie odzwierciedla dostarczony plan implementacji i przestrzega wszystkich określonych zasad. Zwróć szczególną uwagę na strukturę komponentów, integrację API i obsługę interakcji użytkownika.
