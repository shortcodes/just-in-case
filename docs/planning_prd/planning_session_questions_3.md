1. Jak użytkownik dowiaduje się, że ktoś próbował uzyskać dostęp do jego powiernictwa (np. poprzez link)? Czy to powinno generować alerty dla właściciela?

Rekomendacja: Każde otwarcie linku przez odbiorcę powinno wysyłać email do właściciela powiernictwa (jeśli żyje i ma aktywne konto). Przykład: "Your custodianship '{name}' was accessed by someone on {date}. If you're reading this, you may want to reset the timer or contact
the recipient."

Odpowiedź: Nie. W MVP tego nie robię. Powiadomienie dostaje tylko jak nie doszedł mail do odbiorcy. W przeciwnym razie powiernictwo jest na zielono jako completed.

2. Co się dzieje gdy użytkownik umrze, ale timer był świeżo zresetowany (np. 2 dni przed śmiercią)? Czy są jakieś mechanizmy "emergency trigger" dla rodziny?

Rekomendacja: W MVP: nie. System działa automatycznie według timera. W v2 można dodać "trusted contact" który może zgłosić śmierć użytkownika i przyspieszyć trigger (z odpowiednim procesem weryfikacji).

Odpowiedź: Nie - wtedy trzeba poczkać.

3. Jak wyglądają różne scenariusze użycia - czy możesz opisać 2-3 główne persony użytkowników i ich konkretne potrzeby?

Rekomendacja: Pomyśl o:
- Persona 1: Freelancer z kryptowalutami i dokumentami biznesowymi dla żony
- Persona 2: Starszy pan z testamentem i dostępami do kont bankowych dla dorosłych dzieci
- Persona 3: Młoda osoba z hasłami i instrukcjami dla rodziców

Każda persona może mieć inne potrzeby odnośnie interwałów, liczby odbiorców, etc.

Odpowiedź: Nie wiem do czego to potrzebne - tak wszystkie te trzy rzeczy mogą mieć miejsce. Jeśli chodzi o jakieś usecasy na strone główną to tak. Dziedziczenie - hasła dla współmałżonka w momencie śmierci i inne wiadomości, testament dla dzieci itd.

4. Jaka jest strategia komunikacji z użytkownikiem w różnych momentach jego journey?

Rekomendacja: Kluczowe momenty:
- Welcome email po rejestracji
- Przypomnienie o nieaktywowanym koncie (po 3 dniach)
- Przypomnienie o pustym koncie bez powiernictw (po 7 dniach)
- Regularne przypomnienia o resecie (konfigurowane)
- "Thank you" email po każdym resecie timera
- Annual summary - ile razy resetował, ile powiernictw ma aktywnych

Odpowiedź: to już jest Problem interfejsowy a nie domenowy.

5. Jak mierzysz sukces produktu poza metrykami z MVP.md? Jakie są secondary metrics?

Rekomendacja: Oprócz 30 nowych użytkowników/miesiąc i 90% z aktywnym powinnikiem:
- Average time to first custodianship creation (powinno być <5 min)
- Retention rate (użytkownicy wracający po 30/90 dniach)
- Reset frequency (jak często użytkownicy resetują timery)
- Conversion rate free → paid (gdy wprowadzisz paid plan)
- Customer satisfaction score

Odpowiedź: Może ile czasu przed deadlinem średnio kasują ( w procentach), Average time to first custodianship creation, ile uczedników przrasta miesięcznie 

6. Jaka jest strategia pozyskiwania pierwszych użytkowników? Kto jest early adopter?

Rekomendacja: Early adopters to prawdopodobnie tech-savvy użytkownicy świadomi cyfrowego bezpieczeństwa, ludzie z kryptowalutami, osoby w "ryzykownych" zawodach (podróżnicy, dziennikarze), lub osoby po 50+ planujące sukcesję. Zastanów się nad kanałami: Reddit r/privacy,
Hacker News, grupy na Facebooku o planowaniu spadkowym.

Odpowiedź: Nieistotne dla projektu

7. Jak obsłużyć sytuację gdy użytkownik przez pomyłkę usunie powiernictwo (hard delete) - czy jest jakiś grace period lub potwierdzenie?

Rekomendacja: Przed hard delete: wyraźny modal z ostrzeżeniem "This action is permanent and cannot be undone. All attachments will be deleted." + checkbox "I understand this is permanent" + wpisanie nazwy powiernictwa jako potwierdzenie.

Odpowiedź: Tak jak mówi rekomendacja

8. Czy użytkownik może zobaczyć jakie powiernictwa otrzymuje od innych (jako odbiorca), czy system jest jednostronny?

Rekomendacja: MVP: jednostronny - odbiorca nie wie o powiernictwie dopóki nie dostanie emaila. W v2: opcja "notify recipient" aby odbiorca wiedział z wyprzedzeniem (może dać mu poczucie bezpieczeństwa, ale też zepsuje "niespodziankę").

Odpowiedź: tylko jednostronny

9. Jaki jest język i ton komunikacji aplikacji - formalny, empatyczny, techniczny? To wrażliwy temat (śmierć).

Rekomendacja: Empatyczny ale profesjonalny. Unikaj eufemizmów ("pass away") ale też nie bądź brutalny. Przykład: "Just In Case helps you prepare for the unexpected by ensuring important information reaches the right people when you can't deliver it yourself." Focus na
"peace of mind" i "taking care of loved ones".

Odpowiedź: To użytkownicy wpisują co chcą przekazać. Ja tylko chce wysłać emaila, że to jest "information delivery service".

10. Jakie są prawne/compliance wymagania dla tego typu usługi - czy są jakieś regulacje odnośnie "cyfrowego testamentu" w Polsce/UE?

Rekomendacja: Skonsultuj z prawnikiem, ale ogólnie:
- Wyraźne disclaimer: "This service is NOT a legal will or testament"
- Terms of Service jasno określające że to tylko "information delivery service"
- RODO compliance (dane osobowe, prawo do usunięcia)
- Jasny proces dla rodziny zmarłego użytkownika aby zamknąć konto

Odpowiedź: Rekomendacje wydają się ok. Na pewno trzeba zobaczyć czy jest prawo w polsce i UE, które tego zabrania. 
