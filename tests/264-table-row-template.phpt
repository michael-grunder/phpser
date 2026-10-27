--TEST--
phpser: cloned table rows own their keys and preserve hash chains and geometry
--EXTENSIONS--
phpser
--FILE--
<?php
foreach ([1, 2, 3, 7, 8, 9, 32, 33, 65] as $width) {
    $rows = [];
    for ($r = 0; $r < 5; $r++) {
        $row = [];
        for ($c = 0; $c < $width; $c++) {
            // The first two keys have the same DJBX33A hash.
            $key = $c < 2 ? 'runtime_' . ['aa', 'b@'][$c] : 'column_' . $c;
            $row[$key] = $c % 2 ? $r * 1.5 : "value_$r";
        }
        $rows[] = $row;
    }
    $blob = phpser_serialize($rows);
    $decoded = phpser_unserialize($blob);
    if ($decoded !== $rows) throw new Exception("round trip: $width");
    foreach ($rows[0] as $key => $value) {
        if ($decoded[0][$key] !== $value) throw new Exception("lookup: $width");
    }
    unset($decoded[0]['runtime_aa'], $rows[0]['runtime_aa']);
    $decoded[0][] = $rows[0][] = 'appended';
    $decoded[1]['runtime_aa'] = $rows[1]['runtime_aa'] = 'changed';
    if ($decoded !== $rows) throw new Exception("mutation: $width");
    // Fail after row allocation and after scattering earlier columns.
    if (phpser_unserialize(substr($blob, 0, -1)) !== null) {
        throw new Exception("truncation: $width");
    }
}
echo "table widths, collisions, independent mutation and cleanup OK\n";
?>
--EXPECT--
table widths, collisions, independent mutation and cleanup OK
