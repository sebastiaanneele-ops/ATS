# Personeel Partners – ATS (Applicant Tracking System)

Eigen systeem om **vacatures** en **sollicitanten** te beheren, met online weergave van vacatures
op een WordPress-site en een sollicitatieformulier dat sollicitaties direct in het systeem zet.

Deze repository bevat twee onderdelen:

| Map | Wat |
|-----|-----|
| `ats-app/` | De ATS-applicatie (Laravel 13 + Filament 5). Beheerpaneel + REST API. |
| `ats-vacatures/` | WordPress-plugin die vacatures toont en sollicitaties doorstuurt. |

---

## 1. Functionaliteit (in het kort)

- **Vacaturebeheer**: aanmaken, publiceren, sluiten; rich-text omschrijving, salaris, locatie, dienstverband.
- **Publieke API**: toont alleen gepubliceerde vacatures.
- **Sollicitaties** met CV-upload (privé-opslag), AVG-toestemming en anti-spam.
- **Pipeline** met instelbare fases + bord, notities en beoordelingen (score 1–5).
- **Automatische e-mails** per fase (ontvangstbevestiging, uitnodiging, afwijzing) met logboek.
- **Rapportages** op het dashboard (cijfers, per fase, per vacature).
- **AVG-retentie**: oude sollicitaties worden automatisch geanonimiseerd.
- **Rollen/rechten** via Filament Shield.

---

## 2. Lokaal draaien

> Vereist: PHP 8.4 + Composer. (Op de huidige werkmachine staat PHP in `C:\Users\S.Neele\devtools\php`;
> voeg die map toe aan je PATH.)

```bash
cd ats-app
composer install
copy .env.example .env        # of: cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

- Beheerpaneel: **http://127.0.0.1:8000/admin**
- Inloggen: **admin@personeelpartners.nl** / **password**  *(wachtwoord direct wijzigen!)*
- Publieke API: http://127.0.0.1:8000/api/v1/vacancies

Tests draaien: `php artisan test`

---

## 3. E-mail (MijnDomein)

E-mail verloopt via de mailserver van MijnDomein. Instellingen staan in `.env`:

```
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=mail.mijndomein.nl
MAIL_PORT=465
MAIL_USERNAME=vacatures@personeelpartners.nl
MAIL_PASSWORD=<mailbox-wachtwoord>
MAIL_FROM_ADDRESS="vacatures@personeelpartners.nl"
MAIL_FROM_NAME="Personeel Partners"
```

Verbinding testen: `php artisan ats:test-mail jouw@adres.nl`

**Afleverbaarheid verbeteren** (aanrader):
- **SPF**: al goed (`include:spf.mijndomeinhosting.nl`).
- **DKIM**: aanzetten in het MijnDomein-portaal voor personeelpartners.nl.
- **DMARC**: voeg een TXT-record toe op `_dmarc.personeelpartners.nl`, bijv.
  `v=DMARC1; p=none; rua=mailto:postmaster@personeelpartners.nl`

---

## 4. WordPress-plugin koppelen

1. Zip de map `ats-vacatures/` en upload deze als plugin (of plaats in `wp-content/plugins/`).
2. Activeer **ATS Vacatures**.
3. Ga naar **Instellingen → ATS Vacatures** en vul in:
   - **ATS API-URL**: de basis-URL van de ATS (bijv. `https://ats.personeelpartners.nl`), zonder `/api/v1`.
   - **API-sleutel**: dezelfde waarde als `ATS_API_KEY` in de ATS `.env`.
4. Plaats op een pagina (bijv. "Werken bij") de shortcode:
   ```
   [ats_vacatures]
   ```
   De lijst toont automatisch een detailpagina via `?ats_vacature=<slug>` en een sollicitatieformulier.

> De plugin haalt vacatures **server-side** op en stuurt sollicitaties via een eigen WP-REST-proxy door,
> zodat de API-sleutel nooit in de browser belandt (geen CORS-configuratie nodig).

---

## 5. Naar productie — MijnDomein (Plesk)

De ATS draait op een eigen subdomein, bijv. **`ats.personeelpartners.nl`**. WordPress blijft op
het hoofddomein staan en haalt de vacatures op via de API van dit subdomein. Het wildcard
A/AAAA-record dekt het subdomein al.

MijnDomein-webhosting draait op **Plesk** en ondersteunt alles wat nodig is: PHP 8.3+, SSH, cron en MySQL.

**Stappenplan:**

1. **Subdomein aanmaken** in Plesk: `ats.personeelpartners.nl`.
2. **PHP-versie** voor het subdomein op **8.3 of hoger** zetten (Plesk → PHP-instellingen).
   Zorg dat `proc_open` niet is uitgeschakeld (nodig voor Composer/artisan).
3. **MySQL-database + gebruiker** aanmaken in Plesk (noteer naam, gebruiker, wachtwoord).
4. **Code plaatsen via SSH** (SSH aanzetten in Plesk → Verbindingsinformatie):
   ```bash
   cd ~/                       # of de map van het subdomein
   git clone https://github.com/sebastiaanneele-ops/ATS.git
   cd ATS/ats-app
   composer install --no-dev --optimize-autoloader
   ```
5. **Document-root** van het subdomein in Plesk laten wijzen naar de **`public`-map** van de app
   (bijv. `.../ATS/ats-app/public`).
6. **.env** aanmaken (kopie van `.env.example`) met productiewaarden:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://ats.personeelpartners.nl
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=...   DB_USERNAME=...   DB_PASSWORD=...
   ```
   - `php artisan key:generate`
   - Zet een sterke, unieke **`ATS_API_KEY`** (exact dezelfde waarde invullen in de WP-plugin).
   - Vul de **MAIL_**-gegevens in (zie hoofdstuk 3).
7. **Database vullen**: `php artisan migrate --force --seed`
   (seed zet de pipeline-fases + e-mailsjablonen klaar; daarna kun je het admin-wachtwoord wijzigen).
8. **HTTPS** aanzetten voor het subdomein (Let's Encrypt in Plesk). Verplicht: er gaan persoonsgegevens + CV's overheen.
9. **Cronjob** toevoegen in Plesk (Ingeroosterde taken) — elke minuut:
   ```
   php /pad/naar/ATS/ats-app/artisan schedule:run
   ```
   (verzorgt de dagelijkse AVG-anonimisering).

> **Front-end build niet nodig**: het beheerpaneel (Filament) en de API gebruiken geen Vite-build;
> Filament publiceert zijn eigen assets (al aanwezig in `public/`). `npm run build` is alleen nodig
> als je de standaard Laravel-pagina's zou gebruiken.

> **Geen SSH/Composer beschikbaar?** Dan kun je de app lokaal voorbereiden (`composer install`) en
> de hele map incl. `vendor/` via SFTP uploaden; migraties draai je dan eenmalig via een tijdelijke
> route of via de Plesk-scheduler. SSH is echter sterk aanbevolen.

### Updates uitrollen (na de eerste keer)

De CLI in de chroot draait een oudere PHP-versie, dus migraties/cache draaien we via de **webserver**
(PHP 8.4) met een beveiligde deploy-hook. Eenmalig instellen, daarna is uitrollen twee handelingen:

1. **Deploy-token** zetten in `.env` op de server:
   ```
   ATS_DEPLOY_TOKEN=<lange willekeurige sleutel>
   ```
2. **Code binnenhalen** — kies één:
   - **Plesk Git** (aanbevolen): koppel de repo `sebastiaanneele-ops/ATS`, zet automatische
     deployment (pull bij push) aan. Code landt dan vanzelf op de server.
   - Of handmatig: `git pull` / nieuwe ZIP uitpakken.
3. **Migraties + caches verversen** — open in de browser:
   ```
   https://ats.personeelpartners.nl/__ops/deploy?token=<ATS_DEPLOY_TOKEN>
   ```
   Dit draait `migrate --force` en herbouwt de config/route-caches. Klaar.

> `composer install` is alleen nodig wanneer er **nieuwe dependencies** bijkomen (zelden). Pure
> code-wijzigingen vereisen alleen stap 2 + 3. De homepage (`/`) stuurt automatisch door naar `/admin`.

---

## 6. AVG / privacy

- CV's en persoonsgegevens staan op privé-opslag, alleen te downloaden door ingelogde beheerders.
- Sollicitanten geven expliciet toestemming; dit wordt vastgelegd.
- Bewaartermijn instelbaar via `ATS_RETENTION_DAYS` (standaard 365 dagen). Daarna anonimiseert de
  geplande taak `ats:anonymize-applications` de gegevens (CV + persoonsgegevens worden verwijderd;
  statistiek blijft behouden). Handmatig draaien kan met `--dry-run` om eerst te zien wat er gebeurt.

---

## 7. Belangrijke commando's

| Commando | Doel |
|----------|------|
| `php artisan serve` | Lokale server starten |
| `php artisan migrate --seed` | Database opzetten + voorbeelddata |
| `php artisan test` | Tests draaien |
| `php artisan ats:test-mail <adres>` | E-mailverbinding testen |
| `php artisan ats:anonymize-applications [--dry-run]` | AVG-opschoning |
