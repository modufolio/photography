<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Entry;
use AppBundle\Entity\Tag;
use Doctrine\ORM\Query;

/**
 * EntryRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EntryRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Find entries query by a tag.
     *
     * @param Tag $tag
     *
     * @return Query
     */
    public function findEntriesByTag(Tag $tag)
    {
        $qb = $this->createQueryBuilder('e')
            ->where(':tag MEMBER OF e.tags')
            ->setParameters(['tag' => $tag])
            ->orderBy('e.timestamp', 'DESC');

        return $qb->getQuery();
    }

    /**
     * @param Entry  $entry
     * @param string $compare
     * @param string $order
     *
     * @return Entry|null
     */
    public function findByTimestamp(Entry $entry, $compare = '<', $order = 'DESC')
    {
        $qb = $this->createQueryBuilder('e')
            ->where("e.timestamp {$compare} :timestamp")
            ->andWhere('e != :entry')
            ->orderBy('e.timestamp', $order)
            ->setMaxResults(1)
            ->setParameters([
                'entry' => $entry,
                'timestamp' => $entry->getTimestamp(),
            ])
            ->getQuery();

        return $qb->getOneOrNullResult();
    }

    /**
     * Return query to load all entries.
     *
     * @return \Doctrine\ORM\Query The query
     */
    public function getFindAllQuery()
    {
        $query = $this->createQueryBuilder('e')
            ->select('e')
            ->orderBy('e.timestamp', 'DESC')
            ->getQuery();

        return $query;
    }

    /**
     * Find an entry by criteria
     * Need this special function, because of translatable
     * https://github.com/stof/StofDoctrineExtensionsBundle/issues/232.
     *
     * @param $params
     *
     * @return mixed
     */
    public function findOneByCriteria(array $params)
    {
        $query = $this->createQueryBuilder('e');

        foreach ($params as $column => $value) {
            $query->andWhere("e.$column = :$column")
                ->setParameter($column, $value);
        }

        $query = $query->getQuery();

        $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');

        return $query->getOneOrNullResult();
    }
}
