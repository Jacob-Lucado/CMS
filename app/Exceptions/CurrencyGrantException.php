<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when currency cannot be granted safely. The message is for the log,
 * not the player: callers know what the grant was for and phrase their own.
 */
class CurrencyGrantException extends Exception {}
