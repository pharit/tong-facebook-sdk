<?php

namespace Tong\FacebookSdk\Exceptions;

use Exception;

class FacebookApiException extends Exception
{
    protected array $context;

    public function __construct(string $message = '', int $code = 0, Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get the context data associated with the exception
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set context data for the exception
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Check if the exception is due to an invalid access token
     */
    public function isInvalidToken(): bool
    {
        return in_array($this->code, [190, 104]);
    }

    /**
     * Check if the exception is due to insufficient permissions
     */
    public function isInsufficientPermissions(): bool
    {
        return in_array($this->code, [200, 201, 202, 203, 204]);
    }

    /**
     * Check if the exception is due to rate limiting
     */
    public function isRateLimited(): bool
    {
        return in_array($this->code, [4, 17, 32, 613]);
    }
}
