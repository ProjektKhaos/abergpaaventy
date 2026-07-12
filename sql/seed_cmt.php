<?php
// Skapar 25 idempotenta dummy-tips för Chiang Mai.

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/db.php';

$tips = [
    ['Morgonpromenad längs vallgraven', 'En lugn runda innan trafiken och värmen vaknar på riktigt.', 'Old City', 18.7904, 98.9854, 2, [3]],
    ['Kaffe i en grön innergård', 'Ett påhittat favoritställe för iskaffe, skugga och en stund med anteckningsboken.', 'Nimman', 18.7967, 98.9681, null, []],
    ['Solnedgång från Doi Suthep', 'Åk upp i god tid och stanna tills stadens lampor börjar synas.', 'Doi Suthep', 18.8049, 98.9216, 14, [6]],
    ['Nudlar vid norra porten', 'Ett enkelt kvällstips när något varmt, snabbt och smakrikt lockar.', 'Chang Phuak', 18.7998, 98.9862, 17, []],
    ['Söndagsmarknaden steg för steg', 'Börja tidigt, ta små pauser och spara plats för både snacks och hantverk.', 'Ratchadamnoen Road', 18.7888, 98.9833, 15, [9]],
    ['En sval timme vid vattenfallet', 'Ett lätt utflyktsmål när stadsvärmen behöver bytas mot skogsluft.', 'Huay Kaew', 18.8111, 98.9446, 7, []],
    ['Fruktprovning på morgonmarknaden', 'Välj tre sorter du inte känner igen och be försäljaren visa hur de äts.', 'Muang Mai Market', 18.7992, 99.0024, 22, [4]],
    ['Tempelrunda utan stress', 'Tre mindre tempel, gott om tid och respektfull klädsel räcker för en fin förmiddag.', 'Old City', 18.7877, 98.9869, null, []],
    ['Picknick vid Ang Kaew', 'Ta med frukt och något kallt att dricka till sjön på universitetsområdet.', 'Suthep', 18.8059, 98.9525, 20, [21]],
    ['Kvällstur över järnbron', 'Bron och floden passar bra för en kort promenad efter middagen.', 'Wat Ket', 18.7868, 99.0036, 8, []],
    ['Lördagsmarknad på Wua Lai', 'Silversmide, gatumat och mycket folk gör området livligt efter solnedgången.', 'Wua Lai', 18.7773, 98.9834, 24, [25]],
    ['En bokhandel för regniga dagar', 'Det här påhittade bokhandelstipset passar när monsunregnet stannar kvar.', 'Nimman', null, null, null, []],
    ['Cykeltur på lugna smågator', 'Planera en kort slinga och undvik de största lederna under rusningstid.', 'Santitham', 18.8040, 98.9771, null, [16]],
    ['Khao soi för nybörjare', 'Beställ en mildare skål först och prova tillbehören lite i taget.', 'Chiang Mai', null, null, 4, []],
    ['Utflykt till Mae Kampong', 'En heldag med bergsväg, kaffe, promenader och svalare luft.', 'Mae Kampong', 18.8656, 99.3502, 5, [10]],
    ['Tidigt besök på Warorot Market', 'Kom på morgonen för råvaror, kryddor och ett lugnare tempo mellan stånden.', 'Chang Moi', 18.7917, 99.0007, 23, []],
    ['Jazzkväll nära norra porten', 'Ett påhittat kvällsupplägg med livemusik och sen middag i närheten.', 'Chang Phuak', 18.7995, 98.9870, 27, [28]],
    ['En dag utan färdig plan', 'Välj ett kvarter, gå långsamt och låt små skyltar bestämma nästa stopp.', 'Chiang Mai', null, null, null, []],
    ['Utsikt från Wat Pha Lat', 'Skogsstigen och tempelområdet ger en stillsam paus ovanför staden.', 'Doi Suthep', 18.7982, 98.9345, 29, [30]],
    ['Mango sticky rice-testet', 'Dela några portioner och jämför mango, ris och kokosmjölk från olika stånd.', 'Night Bazaar', 18.7843, 98.9996, 9, []],
    ['Keramik i Baan Kang Wat', 'Små verkstäder, skuggiga gångar och lokalt hantverk passar en långsam eftermiddag.', 'Suthep', 18.7887, 98.9468, 10, [6]],
    ['Ta en röd songthaew', 'Bestäm pris och destination innan avfärd och ha gärna adressen på thai till hands.', 'Chiang Mai', null, null, null, []],
    ['Blomstermarknaden efter mörkrets inbrott', 'Färger, dofter och leveranser gör marknaden extra levande på kvällen.', 'Wichayanon Road', 18.7923, 99.0015, 12, [22]],
    ['Frukost före dagens utflykt', 'Risgröt, ägg och kaffe ger en enkel start innan vägarna blir varma.', 'Hai Ya', 18.7779, 98.9857, null, []],
    ['Lugn kväll vid Pingfloden', 'Avsluta dagen med en promenad och välj ett bord där fläktarna når fram.', 'Ping River', 18.7891, 99.0062, 26, [8, 17]],
];

$find = $pdo->prepare("SELECT id FROM posts WHERE slug = ? LIMIT 1");
$insert = $pdo->prepare("
    INSERT INTO posts
        (title, slug, content_type, intro, body, location, latitude, longitude,
         post_date, publish_date, status, cover_image_id)
    VALUES
        (?, ?, 'cmt', ?, ?, ?, ?, ?, ?, NULL, 'published', ?)
");
$attach = $pdo->prepare("
    INSERT INTO post_media (post_id, media_id, sort_order)
    SELECT ?, id, ? FROM media WHERE id = ?
");
$insertLink = $pdo->prepare("
    INSERT INTO post_links (post_id, label, url, sort_order)
    VALUES (?, ?, ?, ?)
");

$created = 0;
$skipped = 0;
$startDate = new DateTimeImmutable('2026-07-10');

$pdo->beginTransaction();
try {
    foreach ($tips as $index => [$title, $intro, $location, $latitude, $longitude, $coverId, $galleryIds]) {
        $slugBase = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', strtr($title, [
            'å' => 'a', 'ä' => 'a', 'ö' => 'o', 'Å' => 'a', 'Ä' => 'a', 'Ö' => 'o',
        ])), '-'));
        $slug = 'cmt-dummy-' . $slugBase;

        $find->execute([$slug]);
        if ($find->fetchColumn()) {
            $skipped++;
            continue;
        }

        $body = '<p>' . htmlspecialchars($intro, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>'
            . '<p>Det här är ett påhittat Chiang Mai-tips för att demonstrera tipsfunktionen. Kontrollera alltid aktuella öppettider, priser och lokala förhållanden innan ett besök.</p>';
        $postDate = $startDate->modify('-' . $index . ' days')->format('Y-m-d');

        $insert->execute([
            $title, $slug, $intro, $body, $location, $latitude, $longitude, $postDate, $coverId,
        ]);
        $postId = (int)$pdo->lastInsertId();

        foreach ($galleryIds as $sort => $mediaId) {
            $attach->execute([$postId, $sort, $mediaId]);
        }

        $mapsQuery = rawurlencode($title . ', ' . $location . ', Chiang Mai');
        $insertLink->execute([
            $postId,
            'Sök platsen på Google Maps',
            'https://www.google.com/maps/search/?api=1&query=' . $mapsQuery,
            0,
        ]);

        if ($index % 3 === 0) {
            $insertLink->execute([
                $postId,
                'Läs mer om Chiang Mai',
                'https://en.wikipedia.org/wiki/Chiang_Mai',
                1,
            ]);
        }

        $created++;
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}

echo "Skapade: {$created}\n";
echo "Fanns redan: {$skipped}\n";
