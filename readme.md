# HistorIQ – interaktywna aplikacja do nauki historii

HistorIQ to webowa aplikacja edukacyjna inspirowana mechanikami znanymi z aplikacji typu Duolingo, której celem jest uatrakcyjnienie nauki historii poprzez quizy tematyczne, system punktów XP oraz œledzenie postêpów u¿ytkownika.

Projekt zosta³ wykonany w architekturze MVC z wyraŸnym podzia³em na backend (PHP + PostgreSQL) oraz frontend (HTML, CSS, JavaScript).

---

## Funkcjonalnoœæ aplikacji

### U¿ytkownik
- rejestracja i logowanie do systemu
- rozwi¹zywanie quizów historycznych podzielonych na tematy
- podgl¹d wyników quizów
- œledzenie postêpów nauki (XP, liczba ukoñczonych quizów, progres per temat)
- profil u¿ytkownika z podsumowaniem statystyk
- wylogowanie z aplikacji

### Administrator
- osobna rola u¿ytkownika (admin)
- panel administracyjny
- dodawanie tematów (topics)
- dodawanie pytañ wraz z odpowiedziami (ABCD)
- wyszukiwanie tematów w czasie rzeczywistym (AJAX / Fetch API)

---

## Architektura aplikacji

Aplikacja zosta³a zrealizowana w architekturze **MVC**:

- **Routing** – centralny router mapuj¹cy œcie¿ki URL na kontrolery
- **Controllers** – obs³uga logiki aplikacji (SecurityController, QuizController, AdminController itd.)
- **Repositories** – warstwa dostêpu do bazy danych (PDO, prepared statements)
- **Views** – warstwa prezentacji (HTML + CSS)
- **Frontend** – JavaScript odpowiedzialny za interakcje UI (dark mode, AJAX)

Backend i frontend s¹ wyraŸnie rozdzielone, a komunikacja z baz¹ danych odbywa siê wy³¹cznie przez repozytoria.

---

## Baza danych (PostgreSQL)

Baza danych zosta³a zaprojektowana relacyjnie i obejmuje m.in. tabele:

- `users`
- `topics`
- `questions`
- `answers`
- `quiz_attempts`
- `user_topic_progress`

Zastosowano:
- klucze g³ówne i obce
- akcje na referencjach (`ON DELETE CASCADE`)
- tabelê poœredni¹ do œledzenia progresu u¿ytkownika

### Dodatkowe elementy bazy
- **VIEW** `v_user_stats` – agregacja statystyk u¿ytkownika
- **FUNCTION** `fn_add_quiz_attempt` – zapis próby quizu w jednym wywo³aniu
- **TRIGGER** pilnuj¹cy poprawnoœci progresu (0–100) oraz aktualizacji daty
- **TRANSAKCJE** przy dodawaniu pytañ i odpowiedzi przez admina

Pe³ny eksport bazy znajduje siê w pliku `docker/db/init.sql`.

---

## Diagram ERD

Diagram relacji encji (ERD) znajduje siê w katalogu `docs/ERD.md` i przedstawia:
- relacje pomiêdzy u¿ytkownikami, tematami, pytaniami i wynikami
- klucze g³ówne i obce
- zale¿noœci jeden-do-wielu oraz wiele-do-wielu

---

## Frontend i design

- HTML5 + CSS
- spójny design wizualny
- tryb jasny i ciemny (dark mode) z zapisem ustawieñ w `localStorage`
- responsywny layout (desktop + mobile)
- przyciski i topbar zrealizowane jako komponenty stylowane CSS

---

## JavaScript i Fetch API

JavaScript wykorzystywany jest do:
- prze³¹czania trybu jasny/ciemny
- obs³ugi elementów UI
- komunikacji AJAX (Fetch API)

Przyk³ad zastosowania Fetch API:
- live search tematów w panelu admina (`/admin-topics-search`)
- pobieranie danych JSON i dynamiczna aktualizacja widoku

---

## Bezpieczeñstwo

Projekt zawiera szereg zabezpieczeñ po stronie backendu:

- **SQL Injection** – wy³¹cznie prepared statements (PDO)
- **CSRF** – tokeny CSRF w formularzach logowania, rejestracji i panelu admina
- **Has³a** – haszowanie przy u¿yciu `bcrypt`
- **Sesje**:
  - regeneracja ID sesji po logowaniu
  - cookies z flagami `HttpOnly` oraz `SameSite`
- **Walidacja danych wejœciowych**:
  - format email
  - limity d³ugoœci pól
- **Ochrona przed brute-force**:
  - limit prób logowania
  - logowanie nieudanych prób (bez zapisywania hase³)
- **Uprawnienia**:
  - rozdzielenie ról (user / admin)
  - dostêp do panelu admina tylko dla administratora
- **Poprawne kody HTTP** (403, 404, 400)
- **Brak wyœwietlania stack trace w œrodowisku produkcyjnym**

---

## Role u¿ytkowników

W systemie wystêpuj¹ co najmniej dwie role:
- **U¿ytkownik** – dostêp do quizów i profilu
- **Administrator** – dostêp do panelu administracyjnego

Uprawnienia s¹ egzekwowane po stronie backendu na podstawie sesji u¿ytkownika.

---

## Technologie

- PHP 8+
- PostgreSQL
- Docker / Docker Compose
- HTML5 / CSS3
- JavaScript (Fetch API)
- Nginx

---

## Podsumowanie

HistorIQ jest kompletn¹ aplikacj¹ webow¹ spe³niaj¹c¹ wymagania projektu:
- architektura MVC
- rozbudowana baza danych
- autoryzacja i role u¿ytkowników
- bezpieczeñstwo
- responsywny interfejs
- komunikacja AJAX
- panel administracyjny

Projekt zosta³ przygotowany w sposób umo¿liwiaj¹cy dalsz¹ rozbudowê o nowe tematy, pytania oraz funkcjonalnoœci edukacyjne.
