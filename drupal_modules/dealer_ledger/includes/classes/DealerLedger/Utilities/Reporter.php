<?php

namespace DealerLedger\Utilities;

/**
 * Responsible for quiet / verbose output to the console / browser.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
class Reporter
{
    /** @var boolean */
    protected $cli;

    /** @var boolean */
    protected $verbose;

    /**
     * Constructor
     */
    public function __construct($devMode)
    {
        // Set defaults.
        $this->cli = false;
        $this->verbose = true;

        // CLI - display is off.
        if (php_sapi_name() === 'cli') {
            $this->cli = true;
            $this->verbose = false;
        }

        // DevMode - always turn on display.
        if ($devMode) {
            $this->verbose = true;
        }
    }

    /**
     * Will echo text.
     *
     * @param string $text
     */
    public function writeLine($text)
    {
        if ($this->verbose) {
            echo $text;

            if ($this->cli) {
                echo "\n";
            } else {
                echo '<br/>';
            }
        }
    }
}
