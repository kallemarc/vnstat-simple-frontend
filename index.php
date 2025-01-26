<?php
// Einstellungen
$vnstatPath = '/usr/bin/vnstat'; // Pfad zur vnstat-Binary

// Prüfen, ob vnstat installiert ist
if (!file_exists($vnstatPath)) {
    die('Fehler: vnStat ist nicht installiert oder der Pfad ist falsch.');
}

// Ausgabe von vnStat abrufen
$vnstatOutput = shell_exec("$vnstatPath --json");

// Prüfen, ob die Ausgabe gültig ist
if (!$vnstatOutput) {
    die('Fehler: vnStat konnte nicht ausgeführt werden.');
}

// JSON-Ausgabe dekodieren
$vnstatData = json_decode($vnstatOutput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die('Fehler: Die JSON-Daten von vnStat konnten nicht gelesen werden.');
}

// Hilfsfunktion zur Formatierung von Datenmengen
function formatDataSize($bytes) {
    if ($bytes >= 1024 * 1024 * 1024 * 1024) {
        return round($bytes / (1024 * 1024 * 1024 * 1024), 2) . ' TB';
    } elseif ($bytes >= 1024 * 1024 * 1024) {
        return round($bytes / (1024 * 1024 * 1024), 2) . ' GB';
    } elseif ($bytes >= 1024 * 1024) {
        return round($bytes / (1024 * 1024), 2) . ' MB';
    } else {
        return round($bytes / 1024, 2) . ' KB';
    }
}

// vnStat-Daten extrahieren
$interfaceData = $vnstatData['interfaces'][0] ?? null;
if (!$interfaceData) {
    die('Fehler: Keine Schnittstelle gefunden.');
}
$interfaceName = $interfaceData['name'];
$dailyStats   = $interfaceData['traffic']['day']   ?? [];
$monthlyStats = $interfaceData['traffic']['month'] ?? [];
$hourlyStats  = $interfaceData['traffic']['hour']  ?? [];

// Aktuelles Datum/Uhrzeit
$now      = new DateTime();
$currDay  = (int)$now->format('j');
$currMon  = (int)$now->format('n');
$currYear = (int)$now->format('Y');
$currHour = (int)$now->format('G');

// Gesamtsummen berechnen
$dailyTotalRx   = array_sum(array_column($dailyStats, 'rx'));
$dailyTotalTx   = array_sum(array_column($dailyStats, 'tx'));
$monthlyTotalRx = array_sum(array_column($monthlyStats, 'rx'));
$monthlyTotalTx = array_sum(array_column($monthlyStats, 'tx'));

// Stunden sortieren
$hourlyStatsSorted = $hourlyStats;
usort($hourlyStatsSorted, function($a, $b) {
    return $a['time']['hour'] <=> $b['time']['hour'];
});

?><!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>vnStat Statistik</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #f0f0f0;
        }
        details {
            margin-bottom: 20px;
        }
        summary {
            font-weight: bold;
            cursor: pointer;
        }
        .highlight {
            background-color: #fffaad;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Netzwerkstatistik für <?php echo htmlspecialchars($interfaceName); ?></h1>

        <h2>Stundensicht</h2>
        <details>
            <summary>Stundendetails anzeigen</summary>
            <table>
                <thead>
                    <tr>
                        <th>Stunde</th>
                        <th>Empfangen (Rx)</th>
                        <th>Gesendet (Tx)</th>
                        <th>Gesamt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hourlyStatsSorted as $hour): ?>
                        <?php
                            $total    = $hour['rx'] + $hour['tx'];
                            $rowHour  = (int)$hour['time']['hour'];
                            $isCurrentHour = ($rowHour === $currHour);
                            $rowClass = $isCurrentHour ? 'highlight' : '';
                        ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <td><?php echo str_pad($rowHour, 2, '0', STR_PAD_LEFT); ?>:00</td>
                            <td><?php echo formatDataSize($hour['rx']); ?></td>
                            <td><?php echo formatDataSize($hour['tx']); ?></td>
                            <td><?php echo formatDataSize($total); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </details>

        <h2>Tagessicht</h2>
        <table>
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Empfangen (Rx)</th>
                    <th>Gesendet (Tx)</th>
                    <th>Gesamt</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dailyStats as $day): ?>
                    <?php
                        $dayTs   = strtotime($day['date']['year'] . '-' . $day['date']['month'] . '-' . $day['date']['day']);
                        $current = getdate($dayTs);
                        $isToday = ($current['mday'] == $currDay && $current['mon'] == $currMon && $current['year'] == $currYear);
                        $dayTotal = $day['rx'] + $day['tx'];
                    ?>
                    <tr class="<?php echo $isToday ? 'highlight' : ''; ?>">
                        <td><?php echo htmlspecialchars(date('d.m.Y', $dayTs)); ?></td>
                        <td><?php echo formatDataSize($day['rx']); ?></td>
                        <td><?php echo formatDataSize($day['tx']); ?></td>
                        <td><?php echo formatDataSize($dayTotal); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Gesamt (Alle Tage)</th>
                    <th><?php echo formatDataSize($dailyTotalRx); ?></th>
                    <th><?php echo formatDataSize($dailyTotalTx); ?></th>
                    <th><?php echo formatDataSize($dailyTotalRx + $dailyTotalTx); ?></th>
                </tr>
            </tfoot>
        </table>

        <h2>Monatssicht</h2>
        <table>
            <thead>
                <tr>
                    <th>Monat</th>
                    <th>Empfangen (Rx)</th>
                    <th>Gesendet (Tx)</th>
                    <th>Gesamt</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monthlyStats as $month): ?>
                    <?php
                        $monthTs = strtotime($month['date']['year'] . '-' . $month['date']['month'] . '-01');
                        $monthRx = $month['rx'];
                        $monthTx = $month['tx'];
                        $isThisMonth = ($month['date']['year'] == $currYear && $month['date']['month'] == $currMon);
                        $monthTotal = $monthRx + $monthTx;
                    ?>
                    <tr class="<?php echo $isThisMonth ? 'highlight' : ''; ?>">
                        <td><?php echo htmlspecialchars(date('F Y', $monthTs)); ?></td>
                        <td><?php echo formatDataSize($monthRx); ?></td>
                        <td><?php echo formatDataSize($monthTx); ?></td>
                        <td><?php echo formatDataSize($monthTotal); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Gesamt (Alle Monate)</th>
                    <th><?php echo formatDataSize($monthlyTotalRx); ?></th>
                    <th><?php echo formatDataSize($monthlyTotalTx); ?></th>
                    <th><?php echo formatDataSize($monthlyTotalRx + $monthlyTotalTx); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
