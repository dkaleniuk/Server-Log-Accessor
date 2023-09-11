<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="\App\Repository\LogEntryRepository")
 *
 * @Table(name="log_entry", indexes={
 *
 *     @Index(name="datetime", columns={"datetime"})
 * })
 */
class LogEntry
{
    /**
     * @Id
     *
     * @GeneratedValue
     *
     * @Column(type="integer")
     */
    protected int $id;

    /**
     * @Column(type="datetime")
     */
    protected \DateTime $datetime;

    /**
     * @Column(type="text")
     */
    protected string $text;

    public function getId(): int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getDatetime(): \DateTime
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTime $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }
}
