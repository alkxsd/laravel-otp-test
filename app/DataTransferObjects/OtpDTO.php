<?php

namespace App\DataTransferObjects;

/**
 * Data transfer object for OTP.
 */
class OtpDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $type,
        public readonly int $userId
    ) {}

    /**
     * Create a new instance from the given array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'],
            type: $data['type'],
            userId: $data['user_id']
        );
    }

    /**
     * Convert the data transfer object to an array.
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type,
            'user_id' => $this->userId,
        ];
    }
}
