--TEST--
phpser: double blocks preserve bits and leave the cursor at the next value
--EXTENSIONS--
phpser
--FILE--
<?php
$values = [0.0, -0.0, INF, -INF, NAN, PHP_FLOAT_MIN, PHP_FLOAT_MAX];
$bytes = pack('e*', ...$values);
// A double block followed by a scalar checks the cursor in a packed parent.
$list = "\x02\x00\x07\x02\x09\x07" . $bytes . "\x03\x54";
$decoded = phpser_unserialize($list);
var_dump(pack('e*', ...$decoded[0]) === $bytes, $decoded[1] === 42);
// The next column must start after all seven doubles, including on failure.
$table = "\x02\x02\x01x\x01y\x15\x07\x02\x00\x01\x09" . $bytes
    . "\x08\x00\x02\x04\x06\x08\x0a\x0c";
$decoded = phpser_unserialize($table);
var_dump(pack('e*', ...array_column($decoded, 'x')) === $bytes);
var_dump(array_column($decoded, 'y') === range(0, 6));
foreach ([$list, $table] as $frame) {
    for ($n = 0; $n < strlen($frame); $n++) {
        if (phpser_unserialize(substr($frame, 0, $n)) !== null) {
            throw new Exception("accepted truncated frame at $n");
        }
    }
}
echo "truncations rejected\n";
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
truncations rejected
