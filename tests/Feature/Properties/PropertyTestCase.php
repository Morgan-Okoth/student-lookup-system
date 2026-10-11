<?php

namespace Tests\Feature\Properties;

use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Base class for all property-based tests.
 *
 * Eris 0.12 was built for PHPUnit 9.  Two APIs were removed in PHPUnit 11:
 *   - PHPUnit\Util\Test::parseTestMethodAnnotations()  (called by erisSetup)
 *   - TestCase::hasFailed()                            (called by erisTeardown)
 *
 * We override all three Eris lifecycle hooks (@beforeClass, @before, @after)
 * to provide PHPUnit 11-compatible implementations while keeping core
 * forAll/then/limitTo functionality intact.
 *
 * Key points:
 *  - erisSetupBeforeClass() manually requires each Generator/*.php so the
 *    free namespace functions (choose, elements, etc.) are defined.
 *  - erisSetup() seeds the RNG and sets defaults without touching removed APIs.
 *  - erisTeardown() skips the hasFailed() call that no longer exists.
 *  - Each test calls $this->limitTo(N) explicitly instead of @eris-repeat.
 */
abstract class PropertyTestCase extends TestCase
{
    use RefreshDatabase;
    use TestTrait;

    /**
     * Load every Generator file so their namespace functions are available.
     * (PSR-4 autoload only registers classes, not free functions defined in
     * the same file.  Eris's original @beforeClass hook does this via glob.)
     *
     * @beforeClass
     */
    public static function erisSetupBeforeClass(): void
    {
        $generatorDir = __DIR__ . '/../../../vendor/giorgiosironi/eris/src/Generator';
        foreach (glob($generatorDir . '/*.php') as $file) {
            require_once $file;
        }
        // Also load Antecedent, Listener, Random files as the original does
        foreach (['Antecedent', 'Listener', 'Random'] as $ns) {
            $dir = __DIR__ . "/../../../vendor/giorgiosironi/eris/src/$ns";
            if (is_dir($dir)) {
                foreach (glob($dir . '/*.php') as $file) {
                    require_once $file;
                }
            }
        }
    }

    /**
     * Seed the RNG and apply defaults without calling the removed
     * PHPUnit\Util\Test::parseTestMethodAnnotations() API.
     *
     * @before
     */
    public function erisSetup(): void
    {
        $seed = intval(getenv('ERIS_SEED') ?: (microtime(true) * 1000000));
        if ($seed < 0) {
            $seed *= -1;
        }
        $this->seed       = $seed;
        $this->iterations = 100;

        $this->withRand('rand');
        $this->minimumEvaluationRatio(0.5);
    }

    /**
     * Skip the erisTeardown seed-dump which calls hasFailed() — removed in
     * PHPUnit 11.
     *
     * @after
     */
    public function erisTeardown(): void
    {
        // hasFailed() was removed in PHPUnit 11; nothing to do here.
    }

    /**
     * Return empty annotations — prevents any remaining annotation-reading code
     * from reaching the removed PHPUnit\Util\Test::parseTestMethodAnnotations().
     */
    public function getTestCaseAnnotations(): array
    {
        return [];
    }
}
