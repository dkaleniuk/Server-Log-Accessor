<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\LogEntry;
use App\Http\DTO\Input\Log\GetLogInputDTO;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class LogEntryRepository extends ServiceEntityRepository implements LogEntryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogEntry::class);
    }

    public function findLogsByQueryData(GetLogInputDTO $inputDTO): array
    {
        $query = $this->createQueryBuilder('le');

        $offset = ($inputDTO->getPage() - 1) * $inputDTO->getLimit();

        $this->addTextLikeFilters($query, $inputDTO);
        $this->addTextRegexFilters($query, $inputDTO);
        $this->addDateTimeFilters($query, $inputDTO);

        $query
            ->setMaxResults($inputDTO->getLimit())
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }

    private function addTextLikeFilters(QueryBuilder $queryBuilder, GetLogInputDTO $inputDTO): void
    {
        if ($inputDTO->getTextLike()) {
            foreach ($inputDTO->getTextLike() as $key => $textLike) {
                if (empty($textLike)) {
                    continue;
                }

                $queryBuilder->andWhere($queryBuilder->expr()->like('le.text', ':text_'.$key))
                    ->setParameter('text_'.$key, '%'.$textLike.'%');
            }
        }
    }

    private function addTextRegexFilters(QueryBuilder $queryBuilder, GetLogInputDTO $inputDTO): void
    {
        if ($inputDTO->getTextRegex()) {
            foreach ($inputDTO->getTextRegex() as $key => $textRegex) {
                if (empty($textRegex)) {
                    continue;
                }

                $queryBuilder->andWhere($queryBuilder->expr()->andX("REGEXP(le.text, :text_$key) = true"))
                    ->setParameter('text_'.$key, $textRegex);
            }
        }
    }

    private function addDateTimeFilters(QueryBuilder $queryBuilder, GetLogInputDTO $inputDTO): void
    {
        if ($inputDTO->getDateTimeBetween()) {
            $whereClauses = [];
            foreach ($inputDTO->getDateTimeBetween() as $key => $dateTimeBetween) {
                if (empty($dateTimeBetween)) {
                    continue;
                }

                list($from, $to) = explode(',', $dateTimeBetween);

                $queryBuilder->setParameter(':from_'.$key, $from);
                $queryBuilder->setParameter(':to_'.$key, $to);

                $whereClauses[] = $queryBuilder->expr()->between('le.datetime', ':from_'.$key, ':to_'.$key);
            }

            if ($whereClauses) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->orX(...$whereClauses)
                );
            }
        }
    }

    public function getLastLogUpdate(): ?string
    {
        $dql = 'SELECT MAX(l.datetime) FROM App\Entity\LogEntry l';

        return $this->_em->createQuery($dql)->getSingleScalarResult();
    }

    public function deleteLessThan(\DateTime $dateTime): ?int
    {
        $dql = 'DELETE FROM App\Entity\LogEntry l where l.datetime < :datetime';

        return $this->_em->createQuery($dql)->execute([
            'datetime' => $dateTime,
        ]);
    }
}
