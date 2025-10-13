Pytania i Rekomendacje (v2)

1. Jak dokładnie działa mechanizm "interwału czasowego" - czy to jest jeden globalny timer dla całego powiernictwa, czy może użytkownik ustawia np. "jeśli nie zresetuję przez 6 miesięcy, wyślij wiadomość"?

Rekomendacja: Zaproponuj prosty model: użytkownik ustawia długość interwału (np. 30, 90, 180 dni) i datę ostatniego resetu. System sprawdza codziennie (przez scheduled job) czy interwał nie upłynął i wysyła powiadomienie/wiadomość.

Odpowiedź: Użytkownik ustawia interwał na koniec którego wiadomość się wysyła ale zresetować może w każdym momencie. Po resecie wartośc licznika wraca do inicjalnej wartości.

2. Co dokładnie oznacza "wyślij przygotowaną wiadomość do wyznaczonego odbiorcy" - czy to email z linkiem do odszyfrowania danych, czy może wysyłka klucza deszyfrującego, czy coś innego?

Rekomendacja: Zdefiniuj dokładny flow: email z unikalnym linkiem → odbiorca klika → weryfikacja tożsamości (np. kod z drugiego emaila) → dostęp do odszyfrowanych danych i załączników w przeglądarce.

Odpowiedź: Wysyła wiadomość z linkiem do załączników. Treść maila będzie bezpośrednio definiowana w powiernictwie. Do tego powiernictwa będą też dodane pliki (jeśli będą). Szyfrowanie dotyczy załączników w trybie spoczynkowym na serwerze.

3. Jak wygląda "zewnętrzny klucz" do szyfrowania - czy użytkownik musi go znać/przechowywać, czy system generuje i zarządza kluczami automatycznie, czy może klucz jest związany z odbiorcą powiernictwa?

Rekomendacja: Rozważ model, gdzie klucz jest generowany per powiernictwo, szyfrowany kluczem publicznym odbiorcy (lub hasłem które odbiorca otrzyma osobnym kanałem). Alternatywnie: klucz master użytkownika + klucze per-custodian.

Odpowiedź: Nie ma żadnego klucza. Szyfrowanie dotyczy danych w spoczynku 

4. Jaki jest dokładny workflow "resetowania timera" - czy to jeden przycisk w dashboardzie, czy może bardziej zaawansowany proces z uwierzytelnieniem, czy może różne metody (email, SMS, biometria) mimo że zaawansowana autentykacja nie wchodzi w MVP?

Rekomendacja: W MVP: prosty przycisk "I'm OK" w dashboardzie po zalogowaniu. Opcjonalnie: możliwość resetu przez specjalny link wysłany emailem (jako backup gdy użytkownik nie ma dostępu do konta).

Odpowiedź: W MVP: prosty przycisk. Na dashboardzie mogą być wszystkie timery, żeby łatwiej było dostać się do nich. Natomiast po wejścieu w widok powiernictwa także będzie możliwość resetu.

5. Czy "wyznaczony odbiorca" musi być użytkownikiem systemu, czy może to być dowolny adres email? Jak zweryfikować że wiadomość trafiła do właściwej osoby?

Rekomendacja: W MVP pozwól na dowolny email (nie wymuszaj rejestracji odbiorcy). Dodaj prosty mechanizm weryfikacji: pytanie bezpieczeństwa ustawione przez twórcę powiernictwa, które odbiorca musi poprawnie odpowiedzieć.

Odpowiedź: Ideą systemu jest to, że użytkownik końcowy nie musi być użytkownikiem systemu. Systemu nie interesuje weryfikacja użyttkownika końcowego. Jesli jest email podany top wysyła. Musi się tylko upewnić że na pewno doszedł. Mailgun? Alternatywą jest sms na telefon (nie w MVC)

6. Jaki jest maksymalny rozmiar załączników i jak dużo ich może być per powiernictwo? Czy będą limity różne dla różnych planów (free/paid)?

Odpowiedź: W planie free będzie to do 10MB. Mam na myśli sumę załączników per powiernictwo.

Rekomendacja: MVP: 100MB total per powiernictwo, max 20 plików. To pokryje większość use cases (dokumenty PDF, skany, małe zdjęcia). W przyszłości można wprowadzić tiery: free 100MB, paid 1GB+.

7. Co się dzieje gdy timer wygaśnie, ale email do odbiorcy się nie dostarczy (bounce, spam, nieaktywny email)? Czy jest backup plan lub powiadomienie dla użytkownika?

Rekomendacja: Zaloguj wszystkie próby wysyłki. Jeśli email bounce'uje, oznacz powiernictwo jako "delivery failed" i (jeśli użytkownik żyje) wyślij mu alert. Rozważ opcję "backup recipient" w późniejszej wersji.

Odpowiedź: Backpup recipient w późniejszej wersji to świetny pomysł. W MVP: jesli email nie dojdzie powinien generować alert zarówno dla tworzącego powiernictwo jak i dla administratora. W późniejszej wersji będe rozwijał alternatywne środki komunikacji i martwił się o niezawodność dostarczenia

8. Jak system będzie monitorował "aktywność" użytkownika do kryteriów sukcesu (90% z aktywnym powinnikiem)? Co oznacza "aktywny powiernik" - że ma ustawiony timer, czy że timer jest niezerowy?

Rekomendacja: Aktywny powiernik = ma ustawiony interwał czasowy > 0 dni i nie został zarchiwizowany/usunięty. Dodaj dashboard analytics dla admina z tymi metrykami.

Odpowiedź: Powiernictwo aktywne to takie, w którym timer nie dobił do zera. Nie da się przedłużyć powiernictwa z zerowym czasem.

9. Jak wygląda ścieżka onboardingu aby zmieścić się w 5 minutach? Czy użytkownik musi od razu ustawić powiernika, czy może to zrobić później?

Rekomendacja: Guided onboarding: 1) Rejestracja (email+hasło, 30s), 2) Krótkie intro/video (30s), 3) "Create your first custodian" wizard: nazwa, email odbiorcy, interwał, krótka wiadomość (3min), 4) Opcjonalnie dodaj załącznik (1min). Total: ~5min.

Odpowiedź: Pomiń krok z Video. Po rejestracji można utworzyć powiernictwo - własnie przez wizarda ale będzie on nie aktywny dopóki użytkownik nie potwierdzi adresu email.

10. Czy powiernictwo może mieć wielu odbiorców (np. informacje dla żony I dzieci), czy jedno powiernictwo = jeden odbiorca? Jak to wpływa na szyfrowanie i dostęp?

Rekomendacja: W MVP: jedno powiernictwo = jeden główny odbiorca. Użytkownik może utworzyć wiele powiernictw do różnych osób. W v2 można dodać "secondary recipients" z różnymi poziomami dostępu.

Odpowiedź: Tak powiernictwo moze mieć więcej niż jednego odbiorce. Można ich ustawić w wariancie freemium do 2.
