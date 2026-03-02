<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reflektion – WildMatch Projekt 2</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=UnifrakturMaguntia&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo-text">WildMatch</div>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Hem</a></li>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <li><a href="login.php">Logga in</a></li>
                    <li><a href="register.php">Registrera</a></li>
                    <?php else: ?>
                    <li><a href="profile.php">Min profil</a></li>
                    <li><a href="rapport.php" class="active">Rapport</a></li>
                    <li><a href="logout.php">Logga ut</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <h1 class="fraktur" style="text-align: center; margin: 2rem 0;">Reflektion över Projekt 2</h1>

        <div class="reflection-section">
            <h2>Inledning</h2>
            <p>
                I Projekt 2 har jag byggt en dynamisk dejtingsajt med PHP och MySQL, där användare kan registrera sig,
                skapa profiler, gilla andra, lämna kommentarer och hantera sitt konto.
                Till skillnad från Projekt 1 (som använde filbaserad lagring) använder denna version en SQL-databas för
                att lagra all data vilket ger bättre prestanda, säkerhet och skalbarhet.
            </p>
        </div>

        <div class="reflection-section">
            <h2>Vad gick bra?</h2>
            <p>
                Det som gick bäst var implementeringen av säkerhet:
            <ul>
                <li>Lösenord hashas med <strong>password_hash()</strong> och verifieras med <strong>password_verify()</strong>.</li>
                <li>Alla databasfrågor använder <strong>prepared statements</strong>, vilket skyddar mot SQL-injektion.</li>
                <li>Känslig information (e-post, årslön) visas endast för inloggade användare.</li>
                <li>Radering av profil kräver lösenordsbekräftelse vilket förhindrar oavsiktlig radering.</li>
            </ul>
            Jag lyckades också implementera avancerade funktioner som <strong>lazy loading</strong>, <strong>sortering
                efter årslön</strong> och <strong>filtrering efter preferens</strong> allt utan siduppdatering tack
            vare AJAX.
            </p>
        </div>

        <div class="reflection-section">
            <h2>Vad var svårt?</h2>
            <p>
                Den största utmaningen var Arcadas miljö:
            <ul>
                <li>Jag kunde inte använda <strong>mysql -u abdullan -p</strong> i terminalen jag fick istället använda
                    phpMyAdmin för alla databasändringar.</li>
                <li>Felmeddelandet "Access denied for user 'abdullan'@'%'" förvirrade mig tills jag insåg att jag måste
                    använda min existerande databas (<strong>abdullan</strong>) inte skapa en ny.</li>
                <li>Sessioner fungerade inte förrän jag lade till <strong>session_start();</strong> i <strong>alla</strong>
                    PHP-filer som använder <strong>$_SESSION</strong>.</li>
            </ul>
            Även like-funktionen bröts flera gånger p.g.a. ett missförstånd mellan JSON och form-data i AJAX-anrop men
            det löste sig när jag bytte till <code>FormData</code>.
            </p>
        </div>

        <div class="reflection-section">
            <h2>Vad tog längst tid?</h2>
            <p>
                Att få lazy loading, sortering och filtrering att fungera tillsammans tog mest tid. Jag behövde:
            <ul>
                <li>Skapa en dynamisk SQL-fråga med <strong>ORDER BY</strong> och <strong>WHERE</strong> baserat på
                    URL-parametrar.</li>
                <li>Synka JavaScript med PHP så att scroll-laddning respekterade vald sortering/filtrering.</li>
                <li>Testa att allt fungerade både på mobil och desktop.</li>
            </ul>
            Men resultatet blev värt ansträngningen sidan laddar snabbt och känns responsiv.
            </p>
        </div>

        <div class="reflection-section">
            <h2>Bonusfunktioner</h2>
            <p>
                Jag valde att implementera även den "svårare delen":
            <ul>
                <li><strong>Rollhantering</strong>: Användare kan ha rollen <strong>user</strong>, <strong>manager</strong>
                    eller <strong>admin</strong>.</li>
                <li><strong>Admin-verktyg</strong>: En admin kan radera vilken profil som helst direkt från
                    <strong>view_profile.php</strong>.</li>
                <li><strong>Full CRUD</strong>: Skapa, läsa, uppdatera och radera profiler med säkerhetskontroller.</li>
            </ul>
            Detta visar att jag förstår hur man bygger en modererbar community-sajt.
            </p>
        </div>

        <div class="reflection-section">
            <h2>Slutsats</h2>
            <p>
                Projekt 2 har gett mig djup förståelse för:
            <ul>
                <li>Hur man bygger säkra webbapplikationer med PHP/MySQL.</li>
                <li>Vikten av dataseparation (frontend vs backend vs databas).</li>
                <li>Hur man skapar en användarvänlig upplevelse med moderna tekniker som AJAX.</li>
            </ul>
            Jag är nöjd med resultatet, WildMatch är nu en fullt fungerande, säker och skalbar dejtingplattform som
            uppfyller alla krav i Projekt 2.pdf.
            </p>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2026 WildMatch</p>
        </div>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const hamburger = document.querySelector('.hamburger');
            const navMenu = document.querySelector('nav ul');
            if (hamburger && navMenu) {
                hamburger.addEventListener('click', () => {
                    navMenu.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>