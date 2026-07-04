# ACCEPTANCE CRITERIA

## Version 1 ska vara klar när följande fungerar

- Admin kan logga in.
- Admin kan skapa, redigera och spara inlägg.
- Inlägg kan ha status draft eller published.
- Endast published visas publikt.
- Inlägg kan ha titel, ingress, text, plats och datum.
- En omslagsbild kan laddas upp.
- Startsidan visar senaste publicerade inlägg.
- Enskilt inlägg visas via egen sida.
- Grundläggande galleri eller bildvisning finns.
- Sidan är mobilanpassad.
- Databasen kan skapas från `sql/schema.sql`.
- Första admin kan skapas från `sql/seed.php`.
- Kod använder PDO och prepared statements.
- Login använder hashat lösenord.
- Formulär har CSRF-skydd.
- Alla publika utskrifter escapear HTML.
- Projektet använder `BASE_URL` och `url()`.
- Koden är kommenterad pedagogiskt.
