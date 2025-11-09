<?php

return [
    'privacy_policy' => [
        'title' => 'Polityka Prywatności',
        'last_updated' => 'Ostatnia aktualizacja',
        'data_controller' => [
            'title' => '1. Administrator Danych',
            'content' => 'Administratorem danych osobowych odpowiedzialnym za przetwarzanie Twoich danych osobowych jest Shortcodes Roman Szymański, NIP 8871788633, ul. Okrzei 28, 55-080 Kąty Wrocławskie, Polska. W przypadku pytań dotyczących ochrony danych, skontaktuj się z nami pod adresem roman.szymanski@shortcodes.pl.',
        ],
        'data_collected' => [
            'title' => '2. Zbierane Dane',
            'intro' => 'Zbieramy i przetwarzamy następujące kategorie danych osobowych:',
            'items' => [
                'Dane konta: imię i nazwisko, adres email, hasło (bezpiecznie przechowywane)',
                'Dane powiernictw: treść wiadomości, adresy email odbiorców, interwały czasowe',
                'Załączniki: pliki przesyłane do powiernictw (przechowywane zaszyfrowane)',
                'Dane techniczne: adresy IP, informacje o przeglądarce, logi dostępu',
                'Dane użytkowania: resety timerów, aktywność powiernictw, historia logowań',
            ],
        ],
        'legal_basis' => [
            'title' => '3. Podstawa Prawna Przetwarzania',
            'intro' => 'Przetwarzamy Twoje dane na podstawie następujących podstaw prawnych zgodnie z Art. 6 RODO:',
            'items' => [
                'Wykonanie umowy: Aby świadczyć usługę Just In Case, na którą się zarejestrowałeś',
                'Zgoda: Dla opcjonalnych funkcji i komunikacji, na które wyraźnie się zgadzasz',
                'Prawnie uzasadniony interes: Aby ulepszać naszą usługę, zapobiegać oszustwom i zapewniać bezpieczeństwo',
            ],
        ],
        'data_usage' => [
            'title' => '4. Jak Wykorzystujemy Twoje Dane',
            'intro' => 'Twoje dane osobowe są wykorzystywane do:',
            'items' => [
                'Zarządzania odliczaniem timerów i automatycznego dostarczania wiadomości',
                'Przechowywania i bezpiecznego przesyłania treści powiernictw i załączników',
                'Wysyłania emaili przypominających przed wygaśnięciem timera',
                'Świadczenia wsparcia klienta i pomocy technicznej',
                'Wykrywania i zapobiegania oszustwom',
                'Wypełniania obowiązków prawnych',
            ],
        ],
        'data_retention' => [
            'title' => '5. Okres Przechowywania Danych',
            'content' => 'Przechowujemy Twoje dane osobowe tak długo, jak Twoje konto jest aktywne. Nieaktywne konta (brak logowania lub resetu timera przez 24 miesiące) zostaną automatycznie usunięte wraz ze wszystkimi powiązanymi danymi. Możesz usunąć swoje konto w dowolnym momencie w ustawieniach profilu.',
        ],
        'user_rights' => [
            'title' => '6. Twoje Prawa (RODO)',
            'intro' => 'Zgodnie z RODO, masz następujące prawa:',
            'items' => [
                'Prawo dostępu: Żądanie kopii swoich danych osobowych',
                'Prawo do sprostowania: Poprawianie niedokładnych lub niekompletnych danych',
                'Prawo do usunięcia: Usunięcie konta i wszystkich powiązanych danych',
                'Prawo do przenoszenia danych: Otrzymanie danych w formacie czytelnym maszynowo',
                'Prawo sprzeciwu: Sprzeciw wobec określonych rodzajów przetwarzania danych',
                'Prawo do wycofania zgody: Cofnięcie zgody na opcjonalne funkcje',
            ],
            'footer' => 'Aby skorzystać z tych praw, skontaktuj się z nami pod adresem roman.szymanski@shortcodes.pl lub użyj ustawień konta w panelu użytkownika.',
        ],
        'data_security' => [
            'title' => '7. Bezpieczeństwo Danych',
            'intro' => 'Wdrażamy standardowe dla branży środki bezpieczeństwa w celu ochrony Twoich danych:',
            'items' => [
                'Szyfrowanie w spoczynku dla wszystkich załączników',
                'Szyfrowana transmisja przy użyciu HTTPS/TLS',
                'Bezpiecznie przechowywane hasła z wykorzystaniem standardowego haszowania',
                'Kontrola dostępu i mechanizmy uwierzytelniania',
                'Regularny monitoring bezpieczeństwa',
            ],
        ],
        'third_party' => [
            'title' => '8. Usługi Zewnętrzne',
            'intro' => 'Korzystamy z zaufanych dostawców usług zewnętrznych do działania Just In Case:',
            'items' => [
                'Dostawcy usług chmurowych: Zaszyfrowane przechowywanie plików dla załączników',
                'Dostawcy usług email: Dostarczanie wiadomości powiernictw i powiadomień',
            ],
            'footer' => 'Wszyscy dostawcy zewnętrzni są zgodni z RODO i przetwarzają dane wyłącznie zgodnie z naszymi instrukcjami w ramach ścisłych umów przetwarzania danych.',
        ],
        'international_transfers' => [
            'title' => '9. Międzynarodowe Transfery Danych',
            'intro' => 'Twoje dane mogą być przekazywane i przetwarzane w krajach poza Europejskim Obszarem Gospodarczym (EOG). Zapewniamy odpowiednią ochronę poprzez:',
            'items' => [
                'Zgodność z EU-US Data Privacy Framework',
                'Standardowe Klauzule Umowne (SCC) z dostawcami zewnętrznymi',
                'Szyfrowanie i zabezpieczenia bezpieczeństwa dla wszystkich transferów danych',
            ],
        ],
        'cookies' => [
            'title' => '10. Pliki Cookie',
            'content' => 'Używamy niezbędnych plików cookie do utrzymania sesji logowania. Żadne pliki cookie śledzące ani analityczne nie są używane bez Twojej zgody.',
        ],
        'changes' => [
            'title' => '11. Zmiany w tej Polityce',
            'content' => 'Możemy okresowo aktualizować tę Politykę Prywatności. Powiadomimy Cię o istotnych zmianach poprzez email lub widoczne ogłoszenie na naszej stronie. Kontynuowanie korzystania z usługi po wprowadzeniu zmian oznacza akceptację zaktualizowanej polityki.',
        ],
        'contact' => [
            'title' => '12. Kontakt',
            'intro' => 'W przypadku pytań lub wątpliwości dotyczących tej Polityki Prywatności lub ochrony danych:',
            'email' => 'Email: roman.szymanski@shortcodes.pl',
        ],
    ],

    'terms_of_service' => [
        'title' => 'Regulamin',
        'last_updated' => 'Ostatnia aktualizacja',
        'acceptance' => [
            'title' => '1. Akceptacja Warunków',
            'content' => 'Tworząc konto i korzystając z Just In Case, zgadzasz się na przestrzeganie niniejszego Regulaminu. Jeśli nie zgadzasz się z tymi warunkami, nie możesz korzystać z usługi.',
        ],
        'service_description' => [
            'title' => '2. Opis Usługi',
            'intro' => 'Just In Case to zautomatyzowana usługa dostarczania informacji, która umożliwia użytkownikom:',
            'items' => [
                'Tworzenie powiernictw zawierających wiadomości i załączniki',
                'Ustawianie interwałów czasowych dla automatycznego dostarczenia wiadomości',
                'Wyznaczanie odbiorców, którzy otrzymają wiadomości po wygaśnięciu timerów',
                'Resetowanie timerów w celu zapobieżenia automatycznemu dostarczeniu',
            ],
        ],
        'disclaimer' => [
            'title' => '3. To nie jest testament',
            'intro' => 'Just In Case NIE jest testamentem, ostatnią wolą ani usługą prawną jakiegokolwiek rodzaju.',
            'items' => [
                'Ta usługa nie zastępuje prawnego planowania spadkowego ani dokumentów testamentowych',
                'Wiadomości i instrukcje nie mają mocy prawnie wiążącej',
                'Nie jesteśmy prawnikami i nie udzielamy porad prawnych',
                'W przypadku prawnie wiążących ustaleń spadkowych, skonsultuj się z wykwalifikowanym prawnikiem',
                'Just In Case jest wyłącznie zautomatyzowanym systemem timerów i dostarczania wiadomości',
            ],
        ],
        'user_responsibilities' => [
            'title' => '4. Obowiązki Użytkownika',
            'intro' => 'Zgadzasz się na:',
            'items' => [
                'Podawanie dokładnych i aktualnych adresów email odbiorców',
                'Regularne resetowanie timerów w celu zapobieżenia niezamierzonemu dostarczeniu wiadomości',
                'Zachowanie danych logowania w tajemnicy i bezpieczeństwie',
                'Niekorzystanie z usługi w celach nielegalnych, szkodliwych lub oszukańczych',
                'Nieprzesyłanie złośliwego oprogramowania, wirusów lub szkodliwych treści',
                'Nienaruszanie praw własności intelektualnej osób trzecich',
                'Przestrzeganie wszystkich obowiązujących przepisów prawnych',
            ],
        ],
        'free_plan' => [
            'title' => '5. Ograniczenia Planu Free',
            'intro' => 'Plan Free obejmuje następujące limity:',
            'items' => [
                'Maksymalnie 3 powiernictwa na użytkownika',
                'Maksymalnie 2 odbiorców na powiernictwo',
                'Maksymalnie 10MB całkowitego rozmiaru załączników na powiernictwo',
            ],
            'footer' => 'Limity te mogą zostać zmienione lub usunięte w przyszłych płatnych planach.',
        ],
        'service_availability' => [
            'title' => '6. Dostępność Usługi',
            'intro' => 'Staramy się zapewnić niezawodną usługę, ale nie gwarantujemy nieprzerwania ani bezbłędnego działania. Usługa jest świadczona "tak jak jest" bez żadnych gwarancji. Zastrzegamy sobie prawo do:',
            'items' => [
                'Przeprowadzania zaplanowanych prac konserwacyjnych z rozsądnym wyprzedzeniem',
                'Modyfikacji lub zaprzestania świadczenia funkcji tymczasowo lub na stałe',
                'Zawieszenia kont naruszających niniejszy Regulamin',
            ],
        ],
        'email_delivery' => [
            'title' => '7. Dostarczanie Email',
            'intro' => 'Chociaż dokładamy wszelkich starań, aby niezawodnie dostarczać wiadomości:',
            'items' => [
                'Nie możemy zagwarantować dostarczenia do wszystkich adresów email',
                'Wiadomości mogą być opóźnione, odfiltrowane jako spam lub odrzucone',
                'Nie odpowiadamy za problemy z serwerami email odbiorców',
                'Nieprawidłowe lub nieistniejące adresy email odbiorców mogą spowodować niepowodzenie dostarczenia',
            ],
            'footer' => 'Zostaniesz powiadomiony w przypadku niepowodzenia dostarczenia wiadomości.',
        ],
        'content_ownership' => [
            'title' => '8. Własność Treści i Ograniczenia',
            'intro' => 'Zachowujesz własność wszystkich przesyłanych treści. Jednak nie możesz przesyłać:',
            'items' => [
                'Treści naruszających przepisy prawa',
                'Treści groźnych, zniesławiających lub nękających',
                'Złośliwego oprogramowania, wirusów lub szkodliwego kodu',
                'Treści naruszających prawa autorskie, znaki towarowe lub inne prawa osób trzecich',
            ],
            'footer' => 'Zastrzegamy sobie prawo do usuwania zabronionych treści i zamykania kont naruszających tę politykę.',
        ],
        'termination' => [
            'title' => '9. Zamknięcie Konta',
            'intro' => 'Możemy zawiesić lub zamknąć Twoje konto, jeśli:',
            'items' => [
                'Naruszasz niniejszy Regulamin',
                'Angażujesz się w oszukańcze lub obraźliwe zachowanie',
                'Nie odpowiadasz na żądania bezpieczeństwa lub weryfikacji',
            ],
            'footer' => 'Możesz w dowolnym momencie usunąć swoje konto z ustawień profilu. Usunięcie konta jest trwałe i nieodwracalne.',
        ],
        'liability' => [
            'title' => '10. Ograniczenie Odpowiedzialności',
            'intro' => 'W maksymalnym zakresie dozwolonym przez prawo:',
            'items' => [
                'Just In Case jest świadczone "tak jak jest" bez gwarancji',
                'Nie ponosimy odpowiedzialności za przerwy w usłudze, utratę danych lub błędy w dostarczeniu',
                'Nie odpowiadamy za konsekwencje wygaśnięcia timera lub dostarczenia wiadomości',
                'Nasza całkowita odpowiedzialność jest ograniczona do kwoty, którą zapłaciłeś (jeśli w ogóle) w ciągu ostatnich 12 miesięcy',
            ],
        ],
        'indemnification' => [
            'title' => '11. Odpowiedzialność użytkownika',
            'intro' => 'Odpowiadasz za wszelkie roszczenia, szkody lub koszty wobec Just In Case wynikające z:',
            'items' => [
                'Twojego korzystania z usługi',
                'Twojego naruszenia niniejszego Regulaminu',
                'Twojego naruszenia praw osób trzecich',
                'Treści, które przesyłasz lub wiadomości, które wysyłasz',
            ],
        ],
        'governing_law' => [
            'title' => '12. Prawo Właściwe',
            'content' => 'Niniejszy Regulamin podlega prawu polskiemu. Wszelkie spory będą rozstrzygane przez sądy polskie.',
        ],
        'changes' => [
            'title' => '13. Zmiany w Regulaminie',
            'content' => 'Możemy okresowo aktualizować niniejszy Regulamin. Istotne zmiany zostaną przekazane za pośrednictwem emaila lub widocznego ogłoszenia. Kontynuowanie korzystania po zmianach oznacza akceptację zaktualizowanego Regulaminu.',
        ],
        'contact' => [
            'title' => '14. Kontakt',
            'intro' => 'W przypadku pytań dotyczących niniejszego Regulaminu:',
            'email' => 'Email: roman.szymanski@shortcodes.pl',
        ],
    ],

    'legal_disclaimer' => [
        'title' => 'Zastrzeżenia Prawne',
        'important' => [
            'title' => 'Informacja',
            'content' => 'Just In Case NIE jest testamentem, ostatnią wolą ani jakąkolwiek formą usługi prawnej.',
        ],
        'what_it_is' => [
            'title' => 'Czym JEST Just In Case:',
            'items' => [
                'Zautomatyzowanym systemem timerów i dostarczania wiadomości',
                'Narzędziem do przechowywania i przekazywania informacji wyznaczonym odbiorcom',
                'Wygodnym sposobem na udostępnianie haseł, instrukcji i dokumentów',
            ],
        ],
        'what_it_is_not' => [
            'title' => 'Czym Just In Case NIE jest:',
            'items' => [
                'Prawnie wiążącym testamentem lub ostatnią wolą',
                'Zamiennikiem właściwego planowania spadkowego',
                'Poradą prawną lub usługą prawną jakiegokolwiek rodzaju',
                'Gwarancją dostarczenia wiadomości we wszystkich okolicznościach',
                'Substytutem konsultacji z wykwalifikowanymi prawnikami',
            ],
        ],
        'no_legal_force' => [
            'title' => 'Brak Mocy Prawnej lub Skutku Wiążącego',
            'intro' => 'Wiadomości, instrukcje i treści przechowywane w Just In Case nie mają mocy prawnej i nie mogą być użyte jako:',
            'items' => [
                'Ważny testament lub ostatnia wola w żadnej jurysdykcji',
                'Prawny dowód własności, dziedziczenia lub transferu aktywów',
                'Prawnie egzekwowalny kontrakt lub umowa',
                'Dowód w postępowaniach prawnych lub spadkowych',
            ],
        ],
        'no_guarantees' => [
            'title' => 'Brak Gwarancji Dostarczenia',
            'intro' => 'Chociaż staramy się zapewnić niezawodną usługę, nie możemy i nie gwarantujemy:',
            'items' => [
                'Że wiadomości zostaną dostarczone we wszystkich okolicznościach',
                'Że dostarczenie nastąpi dokładnie w momencie wygaśnięcia timera',
                'Że odbiorcy faktycznie otrzymają lub przeczytają wiadomości',
                'Że załączniki pozostaną dostępne w nieskończoność',
                'Że usługa będzie działać nieprzerwanie bez przerw',
            ],
        ],
        'consult_professionals' => [
            'title' => 'Skonsultuj się z Prawnikami',
            'intro' => 'W przypadku prawnie wiążących ustaleń dotyczących:',
            'items' => [
                'Testamentów i ostatniej woli',
                'Planowania spadkowego i dziedziczenia',
                'Dystrybucji aktywów i trustów',
                'Pełnomocnictwa lub opieki',
                'Wszelkich innych spraw prawnych',
            ],
            'footer' => 'MUSISZ skonsultować się z wykwalifikowanym prawnikiem posiadającym licencję w Twojej jurysdykcji.',
        ],
        'user_responsibility' => [
            'title' => 'Odpowiedzialność Użytkownika',
            'intro' => 'Korzystając z Just In Case, potwierdzasz, że:',
            'items' => [
                'Rozumiesz, że to nie jest usługa prawna',
                'Jesteś odpowiedzialny za właściwe ustalenia prawne za pośrednictwem odpowiednich kanałów prawnych',
                'Just In Case nie może zastąpić profesjonalnej porady prawnej',
                'Nie ponosimy odpowiedzialności za jakiekolwiek konsekwencje prawne wynikające z korzystania z usługi',
            ],
        ],
        'liability_limitation' => [
            'title' => 'Ograniczenie Odpowiedzialności',
            'content' => 'Just In Case, jego właściciele, operatorzy i pracownicy nie ponoszą odpowiedzialności za jakiekolwiek konsekwencje, szkody lub straty wynikające z korzystania z usługi, niekorzystania, błędów w dostarczeniu lub polegania na wiadomościach jako dokumentach prawnych. Korzystasz z tej usługi na własne ryzyko.',
        ],
    ],
];
