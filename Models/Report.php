<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Helper\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Helper\Models;

use Modules\Admin\Models\Account;
use Modules\Admin\Models\NullAccount;
use Modules\Media\Models\Collection;
use Modules\Media\Models\NullCollection;

/**
 * Report model.
 *
 * @package Modules\Helper\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Report implements \JsonSerializable
{
    /**
     * Report Id.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

    /**
     * Report status.
     *
     * @var int
     * @since 1.0.0
     */
    public int $status = HelperStatus::INACTIVE;

    /**
     * Report title.
     *
     * @var string
     * @since 1.0.0
     */
    public string $title = '';

    /**
     * Report description.
     *
     * @var string
     * @since 1.0.0
     */
    public string $description = '';

    /**
     * Report description.
     *
     * @var string
     * @since 1.0.0
     */
    public string $descriptionRaw = '';

    /**
     * Report created at.
     *
     * @var \DateTimeImmutable
     * @since 1.0.0
     */
    public \DateTimeImmutable $createdAt;

    /**
     * Report created by.
     *
     * @var Account
     * @since 1.0.0
     */
    public Account $createdBy;

    /**
     * Report template.
     *
     * @var Template
     * @since 1.0.0
     */
    public Template $template;

    /**
     * Report source.
     *
     * @var Collection
     * @since 1.0.0
     */
    public Collection $source;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->createdBy = new NullAccount();
        $this->createdAt = new \DateTimeImmutable('now');
        $this->template  = new NullTemplate();
        $this->source    = new NullCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'             => $this->id,
            'createdBy'      => $this->createdBy,
            'createdAt'      => $this->createdAt,
            'name'           => $this->title,
            'description'    => $this->description,
            'descriptionRaw' => $this->descriptionRaw,
            'status'         => $this->status,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }
}
