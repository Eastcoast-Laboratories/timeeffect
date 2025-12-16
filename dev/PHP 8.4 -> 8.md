# PHP 7.4 -> 8.5 compatibility scan

## Metadaten
- timestamp: `20251216_173308`
- script: `/var/www/php-8.5_compat_scan/php-8.5_compat_scan.sh`
- raw: `/var/tmp/PHP85_COMPAT_REPORT_RAW_20251216_173308__var_www_timeeffect.txt`

## Definitiv (Deprecation-Warnings oder harte Breaks)

### curl_close (deprecated in 8.5, no-op since 8.0)
**Issue:** `curl_close()` and `curl_share_close()` are deprecated in PHP 8.5 and have no effect since PHP 8.0.0.

**Manual:** https://www.php.net/manual/en/function.curl-close.php
**Manual:** https://www.php.net/manual/en/function.curl-share-close.php
**Manual:** https://www.php.net/manual/en/migration85.deprecated.php

**Action:** Keep the call for PHP < 8.0.0 (e.g. `if (PHP_VERSION_ID < 80000) { curl_close($ch); }`) and skip it on newer versions.

**Global Search & Replace (Regex):**
- Search: `\bcurl_(close|share_close)\s*\(([^)]*)\)`
- Replace: `if (PHP_VERSION_ID < 80000) { curl_$1($2); }`

- Scope: `.`
- Found: **1** hits across **1** files

**Examples:**

```text
./tests/LoginTest.php:34:            curl_close($this->ch);
```

### xml_parser_free (deprecated in 8.5, no-op since 8.0)
**Issue:** `xml_parser_free()` is deprecated and does nothing on PHP 8+.

**Manual:** https://www.php.net/manual/en/function.xml-parser-free.php
**Manual:** https://www.php.net/manual/en/migration85.deprecated.php

**Action:** Guard it with `if (PHP_VERSION_ID < 80000)` or delete the call.

**Global Search & Replace (Regex):**
- Search: `\bxml_parser_free\s*\(([^)]*)\)`
- Replace: `if (PHP_VERSION_ID < 80000) { xml_parser_free($1); }`

- Scope: `.`
- Found: **2** hits across **1** files

**Examples:**

```text
./include/pear/PEAR/XMLParser.php:105:            xml_parser_free($xp);
./include/pear/PEAR/XMLParser.php:109:        xml_parser_free($xp);
```

### Manual review

### Terminating case statements with semicolon (deprecated)
**Issue:** `case ...;` is deprecated in `switch` blocks.

**Manual:** https://www.php.net/manual/en/migration85.deprecated.php

**Action:** Use `case ...:` instead.

**Global Search & Replace (Regex):**
- Search: `\bcase\b([^:;\n]*);`
- Replace: `case$1:`

- Scope: `.`
- Found: **1** hits across **1** files

**Examples:**

```text
./include/pear/PEAR/Validate.php:284:                            'must have capital RC, not lower-case rc');
```

### imagedestroy (deprecated)
**Issue:** `imagedestroy()` is deprecated and has no effect since PHP 8.0.0.

**Manual:** https://www.php.net/manual/en/function.imagedestroy.php
**Manual:** https://www.php.net/manual/en/function.imagedestroy.php#refsect1-function.imagedestroy-description

**Action:** Keep it for PHP < 8.0.0 only, e.g. `if (PHP_VERSION_ID < 80000) { imagedestroy($img); }`.

**Global Search & Replace (Regex):**
- Search: *(siehe Pattern im Script/Raw-Report)*
- Replace: *(manuell)*

- Scope: `.`
- Found: **1** hits across **1** files

**Examples:**

```text
./include/fpdf.inc.php:1432:	imagedestroy($im);
```

## Typical PHP 7.4 -> 8.0 issues (removed features)

### create_function (removed in 8.0)
**Issue:** `create_function()` no longer exists.

**Manual:** https://www.php.net/manual/en/migration80.incompatible.php
**Manual:** https://www.php.net/manual/en/function.create-function.php

**Action:** Replace with anonymous functions/closures.

**Global Search & Replace (Regex):**
- Search: *(siehe Pattern im Script/Raw-Report)*
- Replace: *(manuell)*

- Scope: `.`
- Found: **2** hits across **2** files

**Examples:**

```text
./include/pear/PEAR/ErrorStack.php:862:                        $ret['function'] = 'create_function() code';
./include/pear/PEAR/Downloader.php:188:                $strtolower = create_function('$a','return strtolower($a);');
```

### ereg family (removed; hard break in 8.0)
**Issue:** This construct is deprecated or removed in modern PHP versions.

**Action:** Review the matches below and migrate to a supported alternative.

**Global Search & Replace (Regex):**
- Search: *(siehe Pattern im Script/Raw-Report)*
- Replace: *(manuell)*

- Scope: `.`
- Found: **1** hits across **1** files

**Examples:**

```text
./install/functions.inc.php:115:	if (!empty($sql) && ereg('[^[:space:]]+', $sql)) {
```

## PHP 8.1 deprecations (migration81.deprecated.php)

## PHP 8.2 deprecations (migration82.deprecated.php)

### utf8_encode / utf8_decode (deprecated)
**Issue:** This construct is deprecated or removed in modern PHP versions.

**Action:** Review the matches below and migrate to a supported alternative.

**Global Search & Replace (Regex):**
- Search: `\butf8_encode\s*\(`
- Replace: *(manual: use `mb_convert_encoding(..., 'UTF-8', 'ISO-8859-1')`)*

- Search: `\butf8_decode\s*\(`
- Replace: *(manual: use `mb_convert_encoding(..., 'ISO-8859-1', 'UTF-8')`)*

- Scope: `.`
- Found: **6** hits across **1** files

**Examples:**

```text
./include/fpdf.inc.php:235:	$this->metadata['Title'] = $isUTF8 ? $title : utf8_encode($title);
./include/fpdf.inc.php:241:	$this->metadata['Author'] = $isUTF8 ? $author : utf8_encode($author);
./include/fpdf.inc.php:247:	$this->metadata['Subject'] = $isUTF8 ? $subject : utf8_encode($subject);
```

## Manual review

### chr() usage (8.5 deprecates values outside 0..255)
**Issue:** `chr()` now expects a byte value (0..255). Supplying anything else is deprecated.

**Manual:** https://www.php.net/manual/en/migration85.deprecated.php
**Manual:** https://www.php.net/manual/en/function.chr.php

**Action:** Ensure the input falls into 0..255 or switch to the appropriate Unicode/mbstring API.

**Global Search & Replace (Regex):**
- Search: `\bchr\s*\(`
- Replace: *(manual: clamp/modulo value or use Unicode-safe API)*

- Scope: `.`
- Found: **115** hits across **20** files

**Examples:**

```text
./install/step4.ihtml.php:49:        $bytes .= chr(mt_rand(0, 255));
./include/fpdf.inc.php:1193:			$res .= chr((($c1 & 0x0F)<<4) + (($c2 & 0x3C)>>2));
./migrate_config_to_env.php:189:        $bytes .= chr(mt_rand(0, 255));
```

### ord() usage (8.5 deprecates non-single-byte strings)
**Issue:** `ord()` expects a single byte; longer strings are deprecated.

**Manual:** https://www.php.net/manual/en/migration85.deprecated.php
**Manual:** https://www.php.net/manual/en/function.ord.php

**Action:** Guarantee the input is a single byte or migrate to mbstring helpers.

**Global Search & Replace (Regex):**
- Search: `\bord\s*\(`
- Replace: *(manual: ensure single-byte input or switch to mb_* functions)*

- Scope: `.`
- Found: **30** hits across **3** files

**Examples:**

```text
./include/cpdf.inc.php:1129:    $j = ($j + ord($t) + ord($k[$i]))%256;
./include/fpdf.inc.php:1160:		if(ord($s[$i])>127)
./include/pear/DB/dbase.php:458:                        'length' => ord(substr($line, 16, 1)),
```

### report_memleaks ini directive (deprecated)
**Issue:** These INI directives are deprecated.

**Manual:** https://www.php.net/manual/en/migration85.deprecated.php

**Action:** Remove them from configuration files.

**Global Search & Replace (Regex):**
- Search: *(siehe Pattern im Script/Raw-Report)*
- Replace: *(manuell)*

- Scope: `.`
- Found: **1** hits across **1** files

**Examples:**

```text
./include/pear/PEAR/RunTest.php:68:        'report_memleaks=0',
```

### split() (removed; avoid JS false positives)
**Issue:** This construct is deprecated or removed in modern PHP versions.

**Action:** Review the matches below and migrate to a supported alternative.

**Global Search & Replace (Regex):**
- Search: *(siehe Pattern im Script/Raw-Report)*
- Replace: *(manuell)*

- Scope: `.`
- Found: **4** hits across **1** files

**Examples:**

```text
./templates/invoice/form.ihtml.php:158:    document.getElementById('period_start').value = firstDay.toISOString().split('T')[0];
./templates/invoice/form.ihtml.php:159:    document.getElementById('period_end').value = lastDay.toISOString().split('T')[0];
./templates/invoice/form.ihtml.php:167:    document.getElementById('period_start').value = firstDay.toISOString().split('T')[0];
```

### "\${var}" interpolation (deprecated; avoid JS false positives)
**Issue:** This construct is deprecated or removed in modern PHP versions.

**Action:** Review the matches below and migrate to a supported alternative.

**Global Search & Replace (Regex):**
- Search: `\$\{([A-Za-z_][A-Za-z0-9_]*)\}`
- Replace: `{\$$1}`

- Scope: `.`
- Found: **25** hits across **9** files

**Examples:**

```text
./include/pear/PEAR/Installer.php:1266:                    $fmt = "%${longest}s (%s)\n";
./include/pear/DB/ibase.php:710:            $result = $this->query("SELECT GEN_ID(${sqn}, 1) "
./include/pear/DB/pgsql.php:667:            $result = $this->query("SELECT NEXTVAL('${seqname}')");
```

## Notes
- This report is grep-based. Some runtime-only issues (e.g. dynamic properties deprecation, TypeError changes) cannot be fully detected statically.
