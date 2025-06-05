# Mpress Debug Flags

**Wtyczka:** Mpress Debug Flags  
**Wersja:** 1.0.0  
**Autor:** Mateusz Zadorożny  
**Strona autora:** https://mpress.cc  
**Licencja:** GPLv2 lub późniejsza

---

## Opis

„Mpress Debug Flags” to lekka wtyczka dla WordPressa, która pozwala włączyć szczegółowe logowanie wybranych fragmentów kodu (funkcji, modułów lub klas) poprzez zdefiniowanie w pliku `wp-config.php` odpowiednich stałych (flag). Dzięki temu nie musisz włączać wszechobecnego `WP_DEBUG` – możesz precyzyjnie wskazać, co ma trafiać do pliku debug.log, a co ma pozostać ignorowane.

Przykład użycia flagi:
```php
// W wp-config.php:
define('DEB_FUNKCJA_X', true);

// W kodzie wtyczki / motywu:
mpress_debug_log('Start funkcji X', 'DEB_FUNKCJA_X');
```

Jeśli stała `DEB_FUNKCJA_X` jest ustawiona na `true`, komunikat trafi do pliku `wp-content/debug.log`. W przeciwnym wypadku – np. po zakończeniu debugowania danego pluginu/modułu/funkcji – wtyczka nie będzie nic zapisywać.

---

## Funkcje wtyczki

- `mpress_debug_log( $message, $debug_flag )`  
  - `$message` (mixed) – dowolny komunikat do zalogowania (string, array, object itp.).  
  - `$debug_flag` (string) – nazwa stałej zdefiniowanej w `wp-config.php`. Jeśli stała istnieje i jest równa `true`, nastąpi zapis do pliku `debug.log`.  
  - Jeżeli `$message` jest tablicą lub obiektem, zostanie przekonwertowane przy pomocy `print_r()`, w przeciwnym razie zostanie zapisany „goły” łańcuch.  
  - Prefiks każdego wpisu w logu przyjmuje formę:
    ```bash
    [Debug][NAZWA_FLAGI] wiadomość lub zawartość tablicy/obiektu
    ```
  - Jeśli flaga nie istnieje lub ma wartość `false`, funkcja kończy działanie bez generowania logu.

---

## Instalacja

1. **Pobierz wtyczkę**  
   - Utwórz folder wtyczki:
     ```
     wp-content/plugins/mpress-debug/
     ```
   - Wewnątrz tego folderu utwórz plik `mpress-debug.php` i wklej do niego kod wtyczki:

     ```php
     <?php
     /**
      * Plugin Name:       Mpress Debug Flags
      * Plugin URI:        https://mpress.cc
      * Description:       Pozwala włączać logowanie dla konkretnych flag/stałych zdefiniowanych w wp-config.php. Użyj mpress_debug_log('wiadomosc', 'NAZWA_FLAGI').
      * Version:           1.0.0
      * Author:            Mateusz Zadorożny
      * Author URI:        https://mpress.cc
      * License:           GPLv2 or later
      * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
      */

     if ( ! defined( 'ABSPATH' ) ) {
         exit; // Zapobiegamy bezpośredniemu dostępowi
     }

     /**
      * Zapisuje do logu (error_log) lub robi print_r, jeżeli stała-flagę ustawiono na true.
      *
      * @param mixed  $message    Dowolny komunikat lub zmienna (string/array/object).
      * @param string $debug_flag Nazwa stałej (bez $), np. 'DEB_FUNKCJA_KTORA_SPRAWDZAM'.
      */
     if ( ! function_exists( 'mpress_debug_log' ) ) {
         function mpress_debug_log( $message, $debug_flag ) {
             // Upewniamy się, że debug_flag to string i jest niepuste
             if ( ! is_string( $debug_flag ) || empty( $debug_flag ) ) {
                 return;
             }

             // Sprawdzamy, czy taka stała istnieje i czy jest true
             if ( defined( $debug_flag ) && constant( $debug_flag ) ) {
                 // Budujemy prefiks logu z nazwą flagi
                 $prefix = '[Debug][' . $debug_flag . '] ';

                 if ( is_array( $message ) || is_object( $message ) ) {
                     // Jeśli tablica lub obiekt, to print_r do error_log
                     error_log( $prefix . print_r( $message, true ) );
                 } else {
                     // W przeciwnym wypadku zapisujemy string
                     error_log( $prefix . $message );
                 }
             }
             // Jeśli stała nie jest zdefiniowana lub jest false, nic nie robimy
         }
     }
     ```
   
2. **Włącz wtyczkę w WordPressie**  
   - Zaloguj się do panelu administracyjnego WordPress.  
   - Przejdź do „Wtyczki” → „Zainstalowane wtyczki”.  
   - Znajdź „Mpress Debug Flags” i kliknij „Aktywuj”.

3. **Dodaj flagi w pliku `wp-config.php`**  
   Otwórz swój `wp-config.php` (nad linią `/* That's all, stop editing! Happy blogging. */`) i dodaj dowolne stałe, np.:
   ```php
   // Włączanie debugowania dla poszczególnych funkcji/modułów:
   define('DEB_FUNKCJA_KTORA_SPRAWDZAM', true);
   define('DEB_API_INTEGRACJA', true);
   // Jeśli nie chcesz, by dana flaga logowała, usuń linię lub ustaw false:
   // define('DEB_EMAIL', false);
   ```

---

## Użycie

1. **Logowanie prostego komunikatu**  
   ```php
   // W kodzie Twojego motywu lub innej wtyczki:
   mpress_debug_log( 'Rozpoczęcie działania funkcji A', 'DEB_FUNKCJA_KTORA_SPRAWDZAM' );
   ```

2. **Logowanie tablicy lub obiektu**  
   ```php
   $wynik = array( 'status' => 'OK', 'kod' => 200 );
   mpress_debug_log( $wynik, 'DEB_API_INTEGRACJA' );
   ```
   - Jeśli w `wp-config.php` zdefiniowałeś `define('DEB_API_INTEGRACJA', true);`, w `wp-content/debug.log` pojawi się:
     ```
     [Debug][DEB_API_INTEGRACJA] Array
     (
         [status] => OK
         [kod] => 200
     )
     ```

3. **Przykład warunkowego logowania**  
   Jeżeli wolisz, możesz najpierw sprawdzić w kodzie, czy stała istnieje i jest true, a potem dopiero wywołać `mpress_debug_log`:
   ```php
   if ( defined('DEB_FUNKCJA_KTORA_SPRAWDZAM') && DEB_FUNKCJA_KTORA_SPRAWDZAM ) {
       mpress_debug_log( 'Dane do debugowania', 'DEB_FUNKCJA_KTORA_SPRAWDZAM' );
   }
   ```
   Jednak sama funkcja `mpress_debug_log` już wewnątrz sprawdza wartość stałej, więc nadrzędna kontrola nie jest konieczna – to tylko dodatkowa warstwa bezpieczeństwa.

---

## Gdzie trafiają logi?

- Wpisy generowane przez `mpress_debug_log` w linii:
  ```php
  error_log( $prefix . … );
  ```
  zostaną zapisane domyślnie w pliku:
  ```
  wp-content/debug.log
  ```
- Aby `debug.log` w ogóle istniał i przyjmował wpisy, w pliku `wp-config.php` musisz mieć włączone:
  ```php
  define('WP_DEBUG', true);
  define('WP_DEBUG_LOG', true);
  ```
- Jeśli `debug.log` nie zostanie utworzony automatycznie, upewnij się, że katalog `wp-content/` jest zapisywalny przez serwer (uprawnienia przynajmniej 755 lub 775 w zależności od konfiguracji).

---

## Przykładowy scenariusz

1. Chcesz prześledzić, co się dzieje w funkcji `moja_funkcja_testowa()`:
   ```php
   function moja_funkcja_testowa() {
       $dane = array( 'x' => 10, 'y' => 20 );
       mpress_debug_log( 'Rozpoczęcie moja_funkcja_testowa', 'DEB_TESTOWA' );
       mpress_debug_log( $dane, 'DEB_TESTOWA' );
       // … reszta kodu …
   }
   ```
2. W `wp-config.php` definiujesz:
   ```php
   define('DEB_TESTOWA', true);
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```
3. Uruchamiasz `moja_funkcja_testowa()`, a w `wp-content/debug.log` pojawiają się wpisy:
   ```
   [Debug][DEB_TESTOWA] Rozpoczęcie moja_funkcja_testowa
   [Debug][DEB_TESTOWA] Array
   (
       [x] => 10
       [y] => 20
   )
   ```

4. Gdy już skończysz debugować, możesz w `wp-config.php` usunąć lub ustawić na `false` linię `define('DEB_TESTOWA', true);`. Wtedy `mpress_debug_log` przestanie generować wpisy.

---

## FAQ

1. **Czy muszę mieć włączone WP_DEBUG, żeby `mpress_debug_log` działało?**  
   Tak, wtyczka używa `error_log()`, więc aby wpisy trafiały do pliku `wp-content/debug.log`, w `wp-config.php` musisz mieć:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```
   Bez tego pliku `debug.log` może w ogóle nie zostać utworzony.

2. **Co się stanie, gdy `mpress_debug_log` zostanie wywołane, ale flaga nie jest zdefiniowana?**  
   Funkcja `mpress_debug_log` najpierw sprawdza `defined( $debug_flag )` i `constant( $debug_flag )`. Jeśli flaga nie istnieje lub ma wartość `false`, to funkcja kończy działanie bez żadnego wpisu ani błędu.

3. **Czy mogę używać wielu flag jednocześnie?**  
   Oczywiście – wystarczy w `wp-config.php` zadeklarować wiele stałych:
   ```php
   define('DEB_MODUL_A', true);
   define('DEB_MODUL_B', true);
   define('DEB_MODUL_C

   ', false);
   ```
   Następnie w kodzie stosujesz:
   ```php
   mpress_debug_log('Coś z modułu A', 'DEB_MODUL_A');
   mpress_debug_log('Coś z modułu B', 'DEB_MODUL_B');
   mpress_debug_log('Coś z modułu C', 'DEB_MODUL_C'); // nic nie zapisze, bo false
   ```

4. **Gdzie sprawdzać wygenerowane logi?**  
   Zaloguj się przez FTP/SFTP lub do panelu hostingu i otwórz plik:
   ```
   /wp-content/debug.log
   ```
   Jeśli katalog nie jest zapisywalny, nadaj mu odpowiednie uprawnienia (np. `755` lub `775`).

---

## Wyłączanie lub usuwanie wtyczki

- Aby **czasowo wyłączyć** logowanie dla konkretnej funkcji, usuń lub ustaw na `false` odpowiednią stałą w `wp-config.php`.  
- Aby **całkowicie dezaktywować** wtyczkę, w panelu WordPress przejdź do „Wtyczki” → „Zainstalowane wtyczki” → kliknij „Dezaktywuj” przy „Mpress Debug Flags”.  
- Jeśli jednak chcesz całkowicie usunąć kod wtyczki z serwera, możesz skasować folder:
  ```
  wp-content/plugins/mpress-debug/
  ```

---

## Uwaga dotycząca bezpieczeństwa i stabilności

- Przy standardowym działaniu wtyczka nie wprowadza żadnych nowych tabel w bazie danych ani nie modyfikuje rdzenia WordPressa.  
- Debugowanie odbywa się wyłącznie poprzez zapisy do pliku `debug.log`.  
- **Jeżeli kiedykolwiek potrzebujesz, aby wtyczka istniała, ale nie była aktywna (np. w środowisku, gdzie nie chcesz, aby brak definicji flagi spowodował błąd), możesz w `wp-config.php` zdefiniować pustą wersję funkcji `mpress_debug_log`, tak by wywołania w kodzie nie generowały fatalnego błędu „nieznana funkcja”.** Przykład:

  ```php
  // Przed include/require pliku wtyczki (lub w dowolnym miejscu wp-config.php)
  if ( ! function_exists( 'mpress_debug_log' ) ) {
      function mpress_debug_log( $message = '', $debug_flag = '' ) {
          // Pusta funkcja – nic nie robi
      }
  }
  ```
  Dzięki temu nawet jeśli plugin jest usunięty lub wyłączony, wywołania `mpress_debug_log( … )` w kodzie nie przerwą działania WordPressa.  

---

## Licencja

Mpress Debug Flags jest udostępniona na licencji GPLv2 (lub późniejszej). Pełny tekst licencji znajdziesz w pliku `LICENSE` lub pod adresem:  
https://www.gnu.org/licenses/gpl-2.0.html