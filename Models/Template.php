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
use Modules\Organization\Models\NullUnit;
use Modules\Organization\Models\Unit;
use Modules\Tag\Models\Tag;

/**
 * Template model.
 *
 * @package Modules\Helper\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Template implements \JsonSerializable
{
    /**
     * Template Id.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

    /**
     * Unit.
     *
     * @var Unit
     * @since 1.0.0
     */
    public Unit $unit;

    /**
     * Template status.
     *
     * @var int
     * @since 1.0.0
     */
    public int $status = HelperStatus::INACTIVE;

    /**
     * Template data type.
     *
     * @var int
     * @since 1.0.0
     */
    public int $datatype = TemplateDataType::OTHER;

    /**
     * Template doesn't need reports.
     *
     * @var bool
     * @since 1.0.0
     */
    public bool $isStandalone = false;

    /**
     * Template name.
     *
     * @var string
     * @since 1.0.0
     */
    public string $name = '';

    /**
     * Template description.
     *
     * @var string
     * @since 1.0.0
     */
    public string $description = '';

    /**
     * Template description.
     *
     * @var string
     * @since 1.0.0
     */
    public string $descriptionRaw = '';

    /**
     * Template created at.
     *
     * @var \DateTimeImmutable
     * @since 1.0.0
     */
    public \DateTimeImmutable $createdAt;

    /**
     * Template created by.
     *
     * @var Account
     * @since 1.0.0
     */
    public Account $createdBy;

    /**
     * Template source.
     *
     * @var Collection
     * @since 1.0.0
     */
    public Collection $source;

    /**
     * Expected files.
     *
     * @var array
     * @since 1.0.0
     */
    public array $expected = [];

    /**
     * Reports.
     *
     * @var array
     * @since 1.0.0
     */
    public array $reports = [];

    /**
     * Tags.
     *
     * @var Tag[]
     * @since 1.0.0
     */
    public array $tags = [];

    /**
     * Path for organizing.
     *
     * @var string
     * @since 1.0.0
     *
     * @todo maybe never used, check
     */
    public string $virtualPath = '/';

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable('now');
        $this->unit      = new NullUnit();
        $this->source    = new NullCollection();
        $this->createdBy = new NullAccount();
    }

    /**
     * Get newest report for template.
     *
     * @return Report
     *
     * @since 1.0.0
     */
    public function getNewestReport() : Report
    {
        if (!empty($this->reports)) {
            return \end($this->reports);
        }

        return new NullReport();
    }

    /**
     * Get expected files from report
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getExpected() : array
    {
        return $this->expected;
    }

    /**
     * Add expected file from report
     *
     * @param string $expected Expected file
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addExpected(string $expected) : void
    {
        $this->expected[] = $expected;
    }

    /**
     * Set expected file from report
     *
     * @param array $expected Expected file
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setExpected(array $expected) : void
    {
        $this->expected = $expected;
    }

    /**
     * Set data type basis
     *
     * @param int $datatype Template datatype source
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setDatatype(int $datatype) : void
    {
        $this->datatype = $datatype;
    }

    /**
     * Get data type basis
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getDatatype() : int
    {
        return $this->datatype;
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
            'name'           => $this->name,
            'description'    => $this->description,
            'descriptionRaw' => $this->descriptionRaw,
            'status'         => $this->status,
            'datatype'       => $this->datatype,
            'standalone'     => $this->isStandalone,
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
