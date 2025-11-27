<?php

declare(strict_types=1);

namespace CoffeeCups\Ipp;

/**
 * Builds an IPP request.
 */
class IppRequest
{
    private static int $requestCounter = 1;

    /** @var IppAttribute[] */
    private array $operationAttributes = [];

    /** @var IppAttribute[] */
    private array $jobAttributes = [];

    private string $data = '';

    private readonly int $requestId;

    public function __construct(
        private readonly IppOperation $operation,
        int $requestId = 0,
        private readonly int $versionMajor = 2,
        private readonly int $versionMinor = 0,
    ) {
        // Auto-generate request ID if not provided
        $this->requestId = $requestId === 0 ? self::$requestCounter++ : $requestId;

        // Add required operation attributes
        $this->operationAttributes[] = IppAttribute::charset('utf-8');
        $this->operationAttributes[] = IppAttribute::naturalLanguage('en');
    }

    public function getRequestId(): int
    {
        return $this->requestId;
    }

    public function addOperationAttribute(IppAttribute $attribute): self
    {
        $this->operationAttributes[] = $attribute;

        return $this;
    }

    public function addJobAttribute(IppAttribute $attribute): self
    {
        $this->jobAttributes[] = $attribute;

        return $this;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function build(): string
    {
        $encoder = new IppEncoder();

        $encoder
            ->writeVersion($this->versionMajor, $this->versionMinor)
            ->writeOperation($this->operation)
            ->writeRequestId($this->requestId)
            ->writeAttributeGroup(IppAttributeGroup::OPERATION_ATTRIBUTES);

        foreach ($this->operationAttributes as $attribute) {
            $encoder->writeAttribute($attribute);
        }

        if (!empty($this->jobAttributes)) {
            $encoder->writeAttributeGroup(IppAttributeGroup::JOB_ATTRIBUTES);

            foreach ($this->jobAttributes as $attribute) {
                $encoder->writeAttribute($attribute);
            }
        }

        $encoder->writeEndOfAttributes();

        if ($this->data !== '') {
            $encoder->writeData($this->data);
        }

        return $encoder->getBuffer();
    }
}
