--TEST--
phpser: A/B decode mode isolates the selected extra fixture
--EXTENSIONS--
phpser
--FILE--
<?php
putenv('BENCH_ITERS=1');
putenv('BENCH_REPS=1');
putenv('BENCH_MODE=decode');
putenv('BENCH_EXTRA=1');
putenv('BENCH_SHAPE=doubles_10k');
ob_start();
require dirname(__DIR__) . '/ab.php';
$output = ob_get_clean();
var_dump(count($shapes) === 1, count($data) === 10000);
var_dump((bool) preg_match('/^doubles_10k\s+size=\s*80005 enc=\s*0\.0 dec=\s*[0-9.]+\n$/D', $output));
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
