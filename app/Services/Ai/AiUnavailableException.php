<?php

namespace App\Services\Ai;

/**
 * Thrown when the AI assistant cannot answer (no key, or every configured
 * model failed). The controllers catch it and answer with a friendly 503 so
 * the author's page never crashes while writing.
 */
class AiUnavailableException extends \RuntimeException
{
}
