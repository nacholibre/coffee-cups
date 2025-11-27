<?php

declare(strict_types=1);

namespace CoffeeCups;

use CoffeeCups\Ipp\IppAttribute;

/**
 * Represents a print job with a fluent API for configuration.
 */
class Job
{
    private string $name = '';
    private string $documentName = '';
    private string $documentFormat = 'application/octet-stream';
    private int $copies = 1;
    private ?string $sides = null;
    private ?int $orientation = null;
    private ?int $printQuality = null;
    private ?string $mediaSize = null;
    private ?string $mediaType = null;
    private ?string $colorMode = null;
    private ?int $priority = null;
    private bool $holdJob = false;

    /** @var IppAttribute[] */
    private array $customAttributes = [];

    private ?string $content = null;
    private ?string $filePath = null;

    public function __construct(?string $name = null)
    {
        if ($name !== null) {
            $this->name = $name;
        }
    }

    /**
     * Set the job name.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the document name.
     */
    public function setDocumentName(string $name): self
    {
        $this->documentName = $name;

        return $this;
    }

    /**
     * Set the document content directly.
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        $this->filePath = null;

        return $this;
    }

    /**
     * Set the file to print.
     */
    public function setFile(string $filePath): self
    {
        $this->filePath = $filePath;
        $this->content = null;

        // Auto-detect document format
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $this->documentFormat = match ($extension) {
            'pdf' => 'application/pdf',
            'ps' => 'application/postscript',
            'txt' => 'text/plain',
            'html', 'htm' => 'text/html',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'tiff', 'tif' => 'image/tiff',
            default => 'application/octet-stream',
        };

        // Auto-set document name if not set
        if ($this->documentName === '') {
            $this->documentName = basename($filePath);
        }

        return $this;
    }

    /**
     * Set the document MIME type.
     *
     * @param string $mimeType The MIME type (e.g., 'application/pdf', 'text/plain')
     */
    public function setFormat(string $mimeType): self
    {
        $this->documentFormat = $mimeType;

        return $this;
    }

    /**
     * Set number of copies.
     */
    public function setCopies(int $copies): self
    {
        $this->copies = max(1, $copies);

        return $this;
    }

    /**
     * Set duplex (two-sided) printing.
     *
     * @param bool $enabled Enable two-sided printing
     * @param bool $longEdge If true, flip on long edge; if false, flip on short edge
     */
    public function setDuplex(bool $enabled, bool $longEdge = true): self
    {
        if ($enabled) {
            $this->sides = $longEdge ? 'two-sided-long-edge' : 'two-sided-short-edge';
        } else {
            $this->sides = 'one-sided';
        }

        return $this;
    }

    /**
     * Set page orientation.
     *
     * @param string $orientation One of: 'portrait', 'landscape', 'reverse-portrait', 'reverse-landscape'
     */
    public function setOrientation(string $orientation): self
    {
        $this->orientation = match (strtolower($orientation)) {
            'portrait' => 3,
            'landscape' => 4,
            'reverse-landscape' => 5,
            'reverse-portrait' => 6,
            default => null,
        };

        return $this;
    }

    /**
     * Set print quality.
     *
     * @param string $quality One of: 'draft', 'normal', 'high'
     */
    public function setQuality(string $quality): self
    {
        $this->printQuality = match (strtolower($quality)) {
            'draft' => 3,
            'normal' => 4,
            'high' => 5,
            default => null,
        };

        return $this;
    }

    /**
     * Set media size.
     *
     * @param string $size Media size (e.g., 'iso_a4_210x297mm', 'na_letter_8.5x11in', 'a4', 'letter', 'legal')
     */
    public function setMediaSize(string $size): self
    {
        // Support common shortcuts
        $this->mediaSize = match (strtolower($size)) {
            'a4' => 'iso_a4_210x297mm',
            'a3' => 'iso_a3_297x420mm',
            'a5' => 'iso_a5_148x210mm',
            'letter' => 'na_letter_8.5x11in',
            'legal' => 'na_legal_8.5x14in',
            default => $size,
        };

        return $this;
    }

    /**
     * Set media type.
     *
     * @param string $type Media type (e.g., 'stationery', 'transparency', 'envelope')
     */
    public function setMediaType(string $type): self
    {
        $this->mediaType = $type;

        return $this;
    }

    /**
     * Set color mode.
     *
     * @param bool $color True for color, false for monochrome/grayscale
     */
    public function setColor(bool $color): self
    {
        $this->colorMode = $color ? 'color' : 'monochrome';

        return $this;
    }

    /**
     * Set job priority.
     *
     * @param int $priority Priority from 1-100, higher is more urgent
     */
    public function setPriority(int $priority): self
    {
        $this->priority = max(1, min(100, $priority));

        return $this;
    }

    /**
     * Set whether to hold the job after creation.
     */
    public function setHold(bool $hold): self
    {
        $this->holdJob = $hold;

        return $this;
    }

    /**
     * Add a custom IPP attribute.
     */
    public function addAttribute(IppAttribute $attribute): self
    {
        $this->customAttributes[] = $attribute;

        return $this;
    }

    /**
     * Get the job name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the document name.
     */
    public function getDocumentName(): string
    {
        return $this->documentName;
    }

    /**
     * Get the document format.
     */
    public function getDocumentFormat(): string
    {
        return $this->documentFormat;
    }

    /**
     * Get the document content.
     *
     * @throws \RuntimeException If file cannot be read
     */
    public function getContent(): string
    {
        if ($this->content !== null) {
            return $this->content;
        }

        if ($this->filePath !== null) {
            if (!file_exists($this->filePath)) {
                throw new \RuntimeException("File not found: {$this->filePath}");
            }

            $content = file_get_contents($this->filePath);
            if ($content === false) {
                throw new \RuntimeException("Failed to read file: {$this->filePath}");
            }

            return $content;
        }

        return '';
    }

    /**
     * Check if job has content.
     */
    public function hasContent(): bool
    {
        return $this->content !== null || $this->filePath !== null;
    }

    /**
     * Get all job attributes for the IPP request.
     *
     * @return IppAttribute[]
     */
    public function getAttributes(): array
    {
        $attributes = [];

        if ($this->copies > 1) {
            $attributes[] = IppAttribute::copies($this->copies);
        }

        if ($this->sides !== null) {
            $attributes[] = IppAttribute::sides($this->sides);
        }

        if ($this->orientation !== null) {
            $attributes[] = IppAttribute::orientation($this->orientation);
        }

        if ($this->printQuality !== null) {
            $attributes[] = IppAttribute::printQuality($this->printQuality);
        }

        if ($this->mediaSize !== null) {
            $attributes[] = IppAttribute::keyword('media', $this->mediaSize);
        }

        if ($this->mediaType !== null) {
            $attributes[] = IppAttribute::keyword('media-type', $this->mediaType);
        }

        if ($this->colorMode !== null) {
            $attributes[] = IppAttribute::keyword('print-color-mode', $this->colorMode);
        }

        if ($this->priority !== null) {
            $attributes[] = IppAttribute::integer('job-priority', $this->priority);
        }

        if ($this->holdJob) {
            $attributes[] = IppAttribute::keyword('job-hold-until', 'indefinite');
        }

        return [...$attributes, ...$this->customAttributes];
    }
}
