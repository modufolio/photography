<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Entry;
use AppBundle\Entity\Tag;

/**
 * TagRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TagRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Find tags by a entry.
     *
     * @param Entry $entry
     *
     * @return array
     */
    public function findTagsByEntry(Entry $entry)
    {
        $qb = $this->createQueryBuilder('t')
            ->where(':entry MEMBER OF t.entries')
            ->setParameters(['entry' => $entry]);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find related tags by a tag.
     * Method returns the tags from all entries which relate to the current tag under the following conditions:
     *      - excluding the requested tag
     *      - occurrence of min $count times
     *      - max $limit related tags.
     *
     * @param Tag $tag
     * @param int $count
     * @param int $limit
     *
     * @return array
     */
    public function findRelatedTagsByTag(Tag $tag, $count = 3, $limit = 10)
    {
        $in = $this->getEntityManager()->getRepository('AppBundle:Entry')
            ->createQueryBuilder('a_e')
            ->where(':tag MEMBER OF a_e.tags');

        $qb = $this->createQueryBuilder('b_t');
        $qb->innerJoin('b_t.entries', 'b_te')
            ->where($qb->expr()->in('b_te', $in->getDQL()))
            ->andWhere('b_t != :tag')
            ->orderBy('COUNT(b_t)', 'DESC')
            ->addOrderBy('b_t.sort', 'DESC')
            ->groupBy('b_t')
            ->having('COUNT(b_t) >= '.$count)
            ->setMaxResults($limit)
            ->setParameters(['tag' => $tag]);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find a tag by criteria
     * Need this special function, because of translatable
     * https://github.com/stof/StofDoctrineExtensionsBundle/issues/232.
     *
     * @param $params
     *
     * @return mixed
     */
    public function findOneByCriteria(array $params)
    {
        $query = $this->createQueryBuilder('t');

        $i = 0;
        foreach ($params as $column => $value) {
            if ($i < 1) {
                $query->where("t.$column = :$column");
            } else {
                $query->andWhere("t.$column = :$column");
            }
            $query->setParameter($column, $value);

            ++$i;
        }

        $query = $query->getQuery();

        $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');

        return $query->getOneOrNullResult();
    }
}
