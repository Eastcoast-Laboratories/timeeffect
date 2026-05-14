# Konzept: Activities in TimeEffect → Kimai

## Ausgangslage

### TimeEffect (aktuell)
- `te_effort` hat: `description`, `note`, `project_id` – aber **keine Activity**
- Jeder Effort hat eine freie Beschreibung als Text

### Kimai (Zielmodell)
- `Project` → enthält `Activity` (z.B. „Programmierung", „Meeting", „Bugfix")
- `Timesheet` muss einer Activity zugeordnet sein
- Activities können **projektspezifisch** oder **global** sein

### Problem
Aktueller Export ordnet alle Efforts der Activity `global` zu und schreibt die Beschreibung nur ins Description-Feld. Damit geht die Bündelungs-Funktion von Activities in Kimai verloren.

## Zielbild

1. TimeEffect bekommt ein eigenes Konzept „Activity" (= Bündelung gleichartiger Efforts anhand der Beschreibung)
2. Beim Anlegen / Ändern eines Efforts wird automatisch eine Activity per Beschreibung gefunden oder erzeugt
3. Beim Export nach Kimai wird die Activity als eigenständiges Feld mitgegeben → ImportBundle legt sie an und ordnet zu

## Datenmodell-Änderungen

### Neue Tabelle: `te_activity`

```sql
CREATE TABLE te_activity (
  id           INT(32) UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id   INT(32) UNSIGNED DEFAULT NULL,    -- NULL = globale Activity
  name         VARCHAR(255)     NOT NULL,
  description  TEXT             DEFAULT NULL,
  visible      TINYINT(1)       NOT NULL DEFAULT 1,
  created_at   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_project_name (project_id, name),  -- gleiche Activity pro Projekt nur 1x
  KEY project_id (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**Designentscheidung: projektspezifisch** (festgelegt)
- `project_id` ist immer gesetzt – jede Activity gehört zu genau einem Projekt
- Dieselbe Beschreibung in zwei Projekten erzeugt zwei unabhängige Activities
- (Spalte bleibt nullable, falls in Zukunft globale Activities nötig sind, aber Code legt sie aktuell nicht an)

### Erweiterung: `te_effort`

```sql
ALTER TABLE te_effort
  ADD COLUMN activity_id INT(32) UNSIGNED DEFAULT NULL AFTER project_id,
  ADD KEY activity_id (activity_id);
```

> Die Activity-Information bleibt **redundant** (denormalisiert), weil die Beschreibung im Effort frei editierbar bleiben soll. `activity_id` ist die *kanonische* Zuordnung, `description` der frei änderbare Text.

## Auto-Linking-Logik

### Activity-Schlüssel = normalisierte Beschreibung

Damit „Mach was", „mach was " und „MACH WAS" dieselbe Activity treffen:

```php
function normalizeActivityName(string $description): string {
    return trim(mb_strtolower(preg_replace('/\s+/u', ' ', $description)));
}
```

Der Activity-`name` selbst wird **case-preserved** gespeichert (erste Schreibweise gewinnt), aber der Match erfolgt über die normalisierte Form. Falls nötig: zusätzliche Spalte `name_normalized` (generated column) mit Unique-Index.

### Hook-Punkte in `effort.inc.php`

Beim Speichern eines Efforts (Insert + Update):

```
function resolveActivityForEffort(int $project_id, string $description): int {
    $name = normalizeActivityName($description);
    if ($name === '') return null;

    // 1. Existierende Activity im Projekt suchen
    $activity_id = lookupActivity($project_id, $name);

    // 2. Falls nicht: anlegen
    if ($activity_id === null) {
        $activity_id = createActivity($project_id, $description);
    }

    return $activity_id;
}
```

**Trigger:**
- **INSERT effort** mit nicht-leerer Description → `activity_id = resolveActivityForEffort(...)`
- **INSERT effort** mit leerer Description → `activity_id = NULL` (keine Activity)
- **UPDATE effort** mit Description-Änderung → erneut auflösen → `activity_id` neu setzen
- **UPDATE effort** ohne Description-Änderung → unverändert lassen
- **UPDATE effort, der bereits gebillt ist (`billed IS NOT NULL`)** → Description-Änderung **verbieten** und Warnung anzeigen: „Dieser Effort ist bereits abgerechnet (gebillt am ...). Beschreibung kann nicht mehr geändert werden."

**Wichtig:** Keine Datenbank-Trigger benutzen, sondern in der Save-Methode des `Effort`-Objekts (`include/effort.inc.php`) implementieren – damit bestehende Logging/Permission-Logik greift.

### Migrations-Skript für Bestand

Einmalig nach Schema-Migration ausführen:

```sql
-- 1. Activities pro (project_id, normalisierte Beschreibung) anlegen
INSERT INTO te_activity (project_id, name)
SELECT DISTINCT project_id, TRIM(description)
FROM te_effort
WHERE description IS NOT NULL AND TRIM(description) <> ''
ON DUPLICATE KEY UPDATE name = name;

-- 2. activity_id in efforts setzen
UPDATE te_effort e
JOIN te_activity a
  ON a.project_id = e.project_id
 AND LOWER(TRIM(a.name)) = LOWER(TRIM(e.description))
SET e.activity_id = a.id
WHERE e.description IS NOT NULL AND TRIM(e.description) <> '';
```

## UI-Auswirkungen (TimeEffect)

### Minimal (Phase 1)
- Activity-Spalte wird in der Effort-Liste **angezeigt** (read-only, nur Anzeige)
- Beim Editieren wird Activity automatisch über die Beschreibung neu zugeordnet

### Optional (Phase 2)
- Eigene Verwaltungsseite für Activities pro Projekt (Umbenennen, Zusammenfassen, Verstecken)
- Beim Effort-Edit: Autocomplete der Beschreibung aus existierenden Activities → Wiederverwendung statt neuer Anlage

## Export nach Kimai

### Aktueller Code (`kimai_efforts_export.php`, Zeile ~106)

```php
$activity = 'global'; // Default activity
```

### Neu

```php
$activity = !empty($db->Record['activity_name']) ? $db->Record['activity_name'] : 'global';
```

Dazu in der SQL-Query joinen:

```sql
SELECT e.*, p.project_name, c.customer_name, a.name AS activity_name
FROM te_effort e
LEFT JOIN te_project  p ON e.project_id  = p.id
LEFT JOIN te_customer c ON p.customer_id = c.id
LEFT JOIN te_activity a ON e.activity_id = a.id
WHERE c.id IN (...)
```

### Kimai ImportBundle-Verhalten (existierend)

Im `AbstractTimesheetImporter::getActivity()`:
- sucht eine Activity mit Name X im Project Y
- legt sie an, wenn nicht vorhanden
- → **es ist nichts zu ändern**, sobald TimeEffect den richtigen Activity-Namen mitliefert

## Umsetzungsreihenfolge

1. **Schema**: `te_activity` anlegen + `activity_id` zu `te_effort` hinzufügen (Migration in `sql/migrations/`)
2. **Bestand**: Migrations-Skript ausführen (Activities aus existierenden Beschreibungen erzeugen)
3. **Code**: `effort.inc.php` Save-Logik um `resolveActivityForEffort()` erweitern
4. **Export**: `kimai_efforts_export.php` SQL + CSV-Spalte anpassen
5. **Test**: Export → Import in Kimai → prüfen, dass Activities korrekt angelegt + zugeordnet sind
6. **(Optional) UI**: Activity-Spalte in Effort-Liste, Activity-Verwaltung

## Festgelegte Designentscheidungen

1. **Activities pro Projekt** – jede Activity gehört zu genau einem Projekt.
2. **Leere Beschreibung → keine Activity** (`activity_id = NULL`). Beim Export bleibt das Activity-Feld leer bzw. fällt auf `global` zurück (Kimai-Default).
3. **Activity umbenennen kaskadiert.** Wird eine Activity in TimeEffect umbenannt, werden alle zugeordneten Effort-`description`-Felder per UPDATE mitgepflegt:
   ```sql
   UPDATE te_effort   SET description = :new_name WHERE activity_id = :id;
   UPDATE te_activity SET name        = :new_name WHERE id          = :id;
   ```
   Dadurch bleiben Activity-Name und Effort-Beschreibung garantiert konsistent.
4. **Gebillte Efforts sind read-only für `description`.** Save-Logik prüft `billed IS NOT NULL` und lehnt Description-Änderungen mit einer Warnung ab. Das Activity-Re-Linking läuft daher gar nicht erst los.

---

## 1. Phase: Komplett ohne TimeEffect-Schema-Änderungen

**Wichtige Erkenntnis aus dem ImportBundle-Code** (`AbstractTimesheetImporter::getActivity()` in `/var/www/ImportBundle/Importer/AbstractTimesheetImporter.php`, Zeile 346-376):

> Der Kimai-Importer **legt Activities automatisch an**, wenn der Activity-Name in der CSV unbekannt ist – und zwar pro Projekt (über `findActivityByName($activity, $project)` + `createNewActivity($project)` + Caching pro `name + project_id`).

Das heißt: **Die Auto-Anlage und das Bündeln gleichnamiger Efforts in einer Activity macht der ImportBundler in Kimai bereits selbst.** Was er nicht kann, ist „aus der Beschreibungsspalte den Activity-Namen ableiten" – die CSV muss schon eine Activity-Spalte mit dem gewünschten Namen mitbringen.

→ **Wir brauchen in TimeEffect gar keine eigene Activity-Tabelle**, solange wir im Export einfach die Beschreibung als Activity-Namen ausgeben.

### Minimal-Lösung (im Wesentlichen 1 Code-Zeile)

In `inventory/kimai_efforts_export.php`:

```php
// vorher
$activity = 'global';

// nachher
$activity = !empty($description) ? $description : 'global';
```

**Effekt in Kimai:**
- Erster Effort mit Beschreibung „Bugfix Login" → Importer legt Activity „Bugfix Login" im Projekt an.
- Zweiter Effort mit derselben Beschreibung → Importer findet die Activity und ordnet zu (Cache + DB-Lookup).
- Beschreibung leer → `'global'` → Default-Activity.

### Trade-offs gegenüber der Voll-Lösung

| Aspekt                                               | Voll-Lösung (`te_activity`-Tabelle) | Minimal-Lösung (nur Export-Mapping) |
|------------------------------------------------------|--------------------------------------|--------------------------------------|
| Schema-Migration                                     | ja, mit Bestandsdaten-Skript         | **keine**                            |
| Code-Änderung TimeEffect                             | mehrere Stellen (effort.inc.php, UI) | **1 Zeile im Export**                |
| Activities pro Projekt                               | ja                                   | ja (durch ImportBundle erledigt)     |
| Auto-Bündelung gleicher Beschreibungen               | ja – in TE und Kimai                 | ja – aber nur in Kimai               |
| Activity in TimeEffect umbenennen → Efforts updaten  | kaskadiert in TE                     | nicht nötig – Description direkt ändern, beim Re-Export wird in Kimai die neue Activity benutzt (alte bleibt verwaist) |
| Konsistenz bei Tippfehlern in der Beschreibung       | Auto-Linking + Normalisierung fängt das ab | jede Tippfehler-Variante wird zu eigener Activity in Kimai |
| Edit gebillter Efforts blockieren                    | ja, in TE-Save-Logik                 | unabhängig davon, kann in TE separat ergänzt werden |
| Aufwand                                              | hoch                                 | minimal                              |
| Risiko                                               | mittel (Migration, UI)               | nahezu null                          |

### Empfehlung

**Mit der Minimal-Lösung starten** – die Bündelung übernimmt der ImportBundler. Wenn sich später herausstellt, dass Tippfehler oder uneinheitliche Beschreibungen zu Activity-Wildwuchs in Kimai führen, kann die Voll-Lösung jederzeit nachgezogen werden; sie ist nicht durch die Minimal-Lösung blockiert.

Die separate Anforderung „**gebillte Efforts dürfen nicht mehr in der Description geändert werden**" ist unabhängig vom Activity-Konzept und sollte in jedem Fall umgesetzt werden – sie schützt die Konsistenz mit Kimai-Imports.
